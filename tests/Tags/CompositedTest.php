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

class CompositedTest extends TestCase
{
    public function testNormal()
    {
        $a = new class extends Composited {
            protected function render(): Node
            {
                return new Tag('a', ['href' => 'foobar.html'], 'Here');
            }
        };
        $this->assertEquals('<a href="foobar.html">Here</a>', (string) $a);
    }

    public function testNested()
    {
        $a = new class extends Composited {
            protected function render(): Node
            {
                return new Tag('a', ['href' => 'foobar.html'], 'Here ' . $this->getContext('foo'));
            }
        };
        $b = new class($a) extends Composited {
            private $z;
            public function __construct($z)
            {
                $this->z = $z;
            }
            protected function render(): Node
            {
                return $this->z;
            }
        };
        $b->setContext('foo', 'bar');
        $this->assertEquals('<a href="foobar.html">Here bar</a>', (string) $b);
    }

    public function testNested2()
    {
        $a = new class extends Composited {
            protected function render(): Node
            {
                return new Tag('a', ['href' => 'foobar.html'], 'Here ' . $this->getContext('foo'));
            }
        };
        $b = new class($a) extends Composited {
            private $z;
            public function __construct($z)
            {
                $this->z = $z;
            }
            protected function render(): Node
            {
                return $this->z;
            }
        };
        $c = new class($b) extends Composited {
            private $z;
            public function __construct($z)
            {
                $this->z = $z;
            }
            protected function render(): Node
            {
                return $this->z;
            }
        };
        $c->setContext('foo', 'bar');
        $this->assertEquals('<a href="foobar.html">Here bar</a>', (string) $c);
    }
}
