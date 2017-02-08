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
namespace Minotaur\View;

use PHPUnit\Framework\TestCase;

class BlockLayoutTest extends TestCase
{
    public function testBasic()
    {
        $object = new BlockLayout();
        $this->assertEmpty($object->get('left'));
        $object->add('left', 2, 'bar');
        $object->add('left', 1, 'foo');
        $object->add('right', 3, 'ghi');
        $object->add('right', 3, 'jkl');
        $object->add('right', 2, 'def');
        $object->add('right', 0, 'abc');
        $this->assertEquals(['foo', 'bar'], $object->get('left'));
        $this->assertEquals(['abc', 'def', 'ghi', 'jkl'], $object->get('right'));
    }

    public function testMerge()
    {
        $object = new BlockLayout();
        $object->add('left', 2, 'bar')
            ->add('left', 1, 'foo');

        $other = new BlockLayout();
        $other->add('right', 3, 'ghi')
            ->add('right', 3, 'jkl')
            ->add('right', 2, 'def')
            ->add('right', 0, 'abc');

        $object->merge($other);

        $this->assertEquals(['foo', 'bar'], $object->get('left'));
        $this->assertEquals(['abc', 'def', 'ghi', 'jkl'], $object->get('right'));
    }

    public function testGetAll()
    {
        $object = new BlockLayout();
        $object->add('left', 2, 'bar');
        $object->add('left', 1, 'foo');
        $object->add('right', 3, 'ghi');
        $object->add('right', 3, 'jkl');
        $object->add('right', 2, 'def');
        $object->add('right', 0, 'abc');

        $this->assertEquals([
            'left' => ['foo', 'bar'],
            'right' => ['abc', 'def', 'ghi', 'jkl'],
        ], $object->getAll());
    }
}
