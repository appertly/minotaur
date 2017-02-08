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

class CursorSubsetTest extends TestCase
{
    public function testArrayObject()
    {
        $in = new \ArrayObject(['foo', 'bar', 'baz']);
        $object = new CursorSubset($in, 4);
        $this->assertEquals(4, $object->getTotal());
        $this->assertEquals($in->getIterator(), $object->getInnerIterator());
        $this->assertEquals(['foo', 'bar', 'baz'], $object->toArray());
    }


    public function testIterator()
    {
        $in = new \ArrayIterator(['foo', 'bar', 'baz']);
        $object = new CursorSubset($in, 4);
        $this->assertEquals(4, $object->getTotal());
        $this->assertSame($in, $object->getInnerIterator());
        $this->assertEquals(['foo', 'bar', 'baz'], $object->toArray());
    }


    public function testIterator2()
    {
        $in = new \EmptyIterator();
        $object = new CursorSubset($in, 4);
        $this->assertEquals(4, $object->getTotal());
        $this->assertSame($in, $object->getInnerIterator());
        $this->assertEmpty($object->toArray());
    }

    /**
     * @expectedException \RangeException
     * @expectedExceptionMessage Total cannot be a negative number
     */
    public function testException()
    {
        new CursorSubset(new \EmptyIterator(), -1);
    }
}
