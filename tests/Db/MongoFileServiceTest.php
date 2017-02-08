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

use PHPUnit\Framework\TestCase;
use Mockery as M;
use MongoDB\BSON\ObjectID;

/**
 * @requires extension mongodb
 */
class MongoFileServiceTest extends TestCase
{
    private $mockId;

    public function setUp()
    {
        $this->mockId = new ObjectID('51b14c2de8e185801f000006');
    }

    public function testStore()
    {
        $mockStream = M::mock(\Psr\Http\Message\StreamInterface::class);
        $mockStream->shouldReceive('getMetadata')->withArgs(['uri'])->andReturn('/tmp/foobar');
        $mockStream->shouldReceive('detach')->andReturn(fopen('php://memory', 'r+'));

        $mockFile = M::mock(\Psr\Http\Message\UploadedFileInterface::class);
        $mockFile->shouldReceive('getStream')->andReturn($mockStream);
        $mockFile->shouldReceive('getClientFilename')->andReturn('my_file.txt');
        $mockFile->shouldReceive('getClientMediaType')->andReturn('text/html');

        $mockMetaData = ["foo" => "bar", 'contentType' => 'text/html'];

        $expectedData = ['contentType' => 'text/html', 'metadata' => ['foo' => 'bar', 'contentType' => 'text/html']];
        $mockGridFS = M::mock(\MongoDB\GridFS\Bucket::class);
        $cw = M::mock(\MongoDB\GridFS\CollectionWrapper::class);
        $rc = new \ReflectionClass(\MongoDB\GridFS\Bucket::class);
        $p = $rc->getProperty('collectionWrapper');
        $p->setAccessible(true);
        $p->setValue($mockGridFS, $cw);
        $mockGridFS->shouldReceive('uploadFromStream')->withArgs(['my_file.txt', M::type('resource'), $expectedData])->andReturn($this->mockId);

        $object = new MongoFileService($mockGridFS);

        $this->assertSame($this->mockId, $object->store($mockFile, $mockMetaData));
        M::close();
    }

    public function testRead()
    {
        $mockGridFSFile = new \stdClass();

        $cw = M::mock(\MongoDB\GridFS\CollectionWrapper::class);
        $cw->shouldReceive('findFileById')->withArgs([$this->mockId])->andReturn($mockGridFSFile);
        $mockGridFS = M::mock(\MongoDB\GridFS\Bucket::class);
        $rc = new \ReflectionClass(\MongoDB\GridFS\Bucket::class);
        $p = $rc->getProperty('collectionWrapper');
        $p->setAccessible(true);
        $p->setValue($mockGridFS, $cw);

        $object = new MongoFileService($mockGridFS);
        $this->assertSame($mockGridFSFile, $object->read($this->mockId));

        M::close();
    }
}
