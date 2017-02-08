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
namespace Minotaur\Io;

/**
 * Interface for storing and retrieving uploaded files.
 */
interface FileService
{
    /**
     * Stores an uploaded file.
     *
     * You should specify `contentType` in the `metadata` Map.
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file The uploaded file
     * @param array<string,mixed> $metadata Any additional fields to persist. At the very least, try to supply `contentType`.
     * @return mixed The document ID of the stored file
     */
    public function store(\Psr\Http\Message\UploadedFileInterface $file, array $metadata);

    /**
     * Gets the file as a PSR-7 Stream.
     *
     * @param mixed $id The file identifier
     * @return \Psr\Http\Message\StreamInterface The readable stream
     */
    public function messageStream($id): \Psr\Http\Message\StreamInterface;

    /**
     * Gets a stream resource for the given ID.
     *
     * @param mixed $id The file identifier
     * @return resource The stream
     */
    public function resource($id);

    /**
     * Efficiently writes the contents of a file to a Stream.
     *
     * @param mixed $file The file
     * @param \Psr\Http\Message\StreamInterface $stream The stream
     */
    public function stream($file, \Psr\Http\Message\StreamInterface $stream): void;

    /**
     * Gets a stored file.
     *
     * @param mixed $id The file identifier
     * @return mixed The stored file, or `null`
     */
    public function read($id);

    /**
     * Finds several files by some arbitrary criteria.
     *
     * @param array<string,mixed> $criteria Field to value pairs
     * @return iterable The objects found
     */
    public function readAll(array $criteria): iterable;

    /**
     * Deletes a stored file.
     *
     * @param mixed $id The file identifier
     */
    public function delete($id): void;
}
