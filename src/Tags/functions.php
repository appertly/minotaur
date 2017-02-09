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

/**
 * Convenience function to create a new Tag.
 *
 * ```php
 * use Minotaur\Tags\ftag as h;
 *
 * $a = h('a', ['href' => 'foobar.html']);
 * ```
 *
 * @param string $name  The tag name
 * @param array<string,mixed> $attributes  Optional. Any attributes to set.
 * @param iterable|mixed $children  The child or children to add.
 * @return Tag  The tag
 */
function ftag(string $name, array $attributes, $children = null): Tag
{
    return new Tag($name, $attributes, $children);
}

/**
 * Convenience function to instantiate a Composited.
 *
 * ```php
 * use Minotaur\Tags\fcomposited as c;
 *
 * $component = c('my_component', ['foo' => 'bar']);
 * ```
 *
 * @param string $class  The class name
 * @param array<string,mixed> $attributes  Optional. Any attributes to set.
 * @param iterable|mixed $children  The child or children to add.
 * @return Composited  The composited
 * @throws \InvalidArgumentException if `$class` doesn't extend `Composited`
 */
function fcomposited(string $class, array $attributes, $children = null): Composited
{
    $rc = new \ReflectionClass($class);
    if (!$rc->isSubclassOf(Composited::class)) {
        throw new \InvalidArgumentException("$class must extend " . Composited::class);
    }
    $c = $rc->newInstanceWithoutConstructor();
    /** @var $c Composited */
    $c->setAttributes($attributes);
    return $c->appendChild($children);
}
