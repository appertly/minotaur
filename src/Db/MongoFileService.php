<?php
declare(strict_types=1);
/**
 * Minotaur
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 *
 * @copyright 2015-2017 Appertly
 * @license   Apache-2.0
 */
namespace Minotaur\Db;

use MongoDB\Driver\ReadPreference;
use MongoDB\BSON\ObjectID;
use MongoDB\GridFS\Bucket;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * File upload service backed by GridFS.
 *
 * Requires the `mongodb/mongodb` composer package to be installed.
 */
class MongoFileService implements \Minotaur\Io\FileService
{
    use MongoHelper;

    /**
     * @var \MongoDB\GridFS\Bucket
     */
    private $bucket;

    /**
     * Creates a new MongoFileService
     *
     * @param $bucket - The GridFS Bucket
     */
    public function __construct(Bucket $bucket)
    {
        $this->bucket = $bucket;
    }

    /**
     * Stores an uploaded file.
     *
     * You should specify `contentType` in the `metadata` Map.
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file The uploaded file
     * @param array<string,mixed> $metadata Any additional fields to persist. At the very least, try to supply `contentType`.
     * @return ObjectID The document ID of the stored file
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Violating If a constraint is violated
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function store(UploadedFileInterface $file, array $metadata): ObjectID
    {
        $meta = [
            "contentType" => $metadata['contentType'] ?? $file->getClientMediaType(),
            'metadata' => $metadata
        ];
        return $this->bucket->uploadFromStream(
            $file->getClientFilename(),
            $file->getStream()->detach(),
            $meta
        );
    }

    /**
     * Gets the file as a PSR-7 Stream.
     *
     * @param $id - The document identifier, either a string or `ObjectID`
     * @return \Psr\Http\Message\StreamInterface The readable stream
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the document doesn't exist
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function messageStream($id): StreamInterface
    {
        $file = $this->read($id);
        $collectionWrapper = $this->getCollectionWrapper($this->bucket);
        return new MongoDownloadStream(
            new \MongoDB\GridFS\ReadableStream($collectionWrapper, $file)
        );
    }

    /**
     * Gets a readable stream resource for the given ID.
     *
     * @param $id - The document identifier, either a string or `ObjectID`
     * @return resource The readable stream
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the document doesn't exist
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function resource($id)
    {
        return $this->bucket->openDownloadStream($id instanceof ObjectID ? $id : new ObjectID((string) $id));
    }

    /**
     * Efficiently writes the contents of a file to a Stream.
     *
     * @param \stdClass $file The file
     * @param \Psr\Http\Message\StreamInterface $stream The stream
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Violating If a constraint is violated
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function stream($file, StreamInterface $stream): void
    {
        if (!is_object($file)) {
            throw new \InvalidArgumentException("Expected object, got: " . gettype($file));
        }
        $this->bucket->downloadToStream(
            $file->_id,
            \Labrys\Io\StreamWrapper::getResource($stream)
        );
    }

    /**
     * Gets a stored file.
     *
     * @param mixed $id The document identifier, either a string or `ObjectID`
     * @return \stdClass|null The stored file, or `null`
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the result cannot be retrieved
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function read($id): ?\stdClass
    {
        $mid = $this->toId($id);
        return $this->doExecute(function (Bucket $bucket) use ($mid) {
            return $this->getCollectionWrapper($bucket)->findFileById($mid);
        });
    }

    /**
     * Deletes a stored file.
     *
     * @param mixed $id The document identifier, either a string or `ObjectID`
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the document doesn't exist
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function delete($id): void
    {
        $mid = $this->toId($id);
        $this->doExecute(function (Bucket $bucket) use ($mid) {
            $bucket->delete($mid);
        });
    }

    /**
     * Finds several files by some arbitrary criteria.
     *
     * @param array<string,mixed> $criteria Field to value pairs
     * @return Traversable<\stdClass> The objects found
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the result cannot be retrieved
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function readAll(array $criteria): \Traversable
    {
        $readPreference = new ReadPreference(ReadPreference::RP_PRIMARY);
        return $this->doExecute(function (Bucket $bucket) use ($criteria) {
            return $bucket->find(
                $criteria,
                ['sort' => ['filename' => 1], 'readPreference' => $readPreference]
            );
        });
    }

    /**
     * Executes something in the context of the collection.
     *
     * Exceptions are caught and translated.
     *
     * @param callable $cb The closure to execute, takes the Bucket.
     * @return - Whatever the function returns, this method also returns
     * @throws \Caridea\Dao\Exception If a database problem occurs
     */
    protected function doExecute(callable $cb)
    {
        try {
            return $cb($this->bucket);
        } catch (\Exception $e) {
            throw \Caridea\Dao\Exception\Translator\MongoDb::translate($e);
        }
    }

    /**
     * @return \MongoDB\GridFS\CollectionWrapper
     */
    private function getCollectionWrapper(Bucket $b)
    {
        $p = new \ReflectionProperty(Bucket::class, 'collectionWrapper');
        $p->setAccessible(true);
        return $p->getValue($b);
    }
}
