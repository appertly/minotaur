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
namespace Minotaur\Tags;

use PHPUnit\Framework\TestCase;

class FragTest extends TestCase
{
    public function testNormal()
    {
        $lis = [
            new Tag('li', [], ["Foo"]),
            new Tag('li', [], ["Bar"]),
            new Tag('li', [], ["Baz"]),
        ];
        $frag = new Frag($lis);
        $this->assertEquals('<li>Foo</li><li>Bar</li><li>Baz</li>', (string) $frag);
    }

    public function testContext()
    {
        $a = new Tag('div');
        $frag = new Frag($a);
        $frag->setContext('foo', 'bar');
        $this->assertEquals('bar', $frag->getContext('foo'));
        $this->assertNull($a->getContext('foo'));
        $frag->toString();
        $this->assertEquals('bar', $a->getContext('foo'));
    }

    public function testAttributes()
    {
        $frag = new Frag();
        $this->assertNull($frag->getAttribute('foo'));
        $this->assertEmpty($frag->getAttributes());
        $this->assertFalse($frag->hasAttribute('foo'));
        $this->assertSame($frag, $frag->removeAttribute('foo'));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testException1()
    {
        $frag = new Frag();
        $frag->setAttribute('foo', 'bar');
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testException2()
    {
        $frag = new Frag();
        $frag->setAttributes(['foo' => 'bar']);
    }
}
