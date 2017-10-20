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
namespace Minotaur;

use PHPUnit\Framework\TestCase;

class GetterTest extends TestCase
{
    public function testGet()
    {
        $array = ['foo' => 'bar'];
        $this->assertEquals('bar', Getter::get($array, 'foo'));
        $this->assertNull(Getter::get($array, 'faz'));

        $map = new \ArrayObject(['bar' => 'foo']);
        $this->assertEquals('foo', Getter::get($map, 'bar'));
        $this->assertNull(Getter::get($map, 'faz'));

        $object = (object)['abc' => 123];
        $this->assertEquals(123, Getter::get($object, 'abc'));
        $this->assertNull(Getter::get($object, 'def'));

        $this->assertEquals('bar', Getter::get($this, 'foo'));
    }

    public function testGetId()
    {
        $this->assertNull(Getter::getId([]));

        $array = ['_id' => 'bar'];
        $this->assertEquals('bar', Getter::getId($array));

        $map = new \ArrayObject(['_id' => 'foo']);
        $this->assertEquals('foo', Getter::getId($map));

        $object = (object)['_id' => 123];
        $this->assertEquals(123, Getter::getId($object));

        $object2 = (object)['id' => 123];
        $this->assertEquals(123, Getter::getId($object2));

        $this->assertEquals('foobar', Getter::getId($this));
    }

    public function getFoo()
    {
        return 'bar';
    }

    public function getId()
    {
        return 'foobar';
    }
}
