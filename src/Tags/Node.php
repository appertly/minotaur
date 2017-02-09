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
 * Interface for anything that can be a tag child.
 */
abstract class Node implements Child, Contextual
{
    /**
     * Appends a child to the node.
     *
     * @return self  provides a fluent interface
     */
    abstract public function appendChild($child);

    /**
     * Adds a child to the beginning of this node.
     *
     * @return self  provides a fluent interface
     */
    abstract public function prependChild($child);

    /**
     * Gets all children directly beneath this node.
     *
     * @return Child[]  The children
     */
    abstract public function getChildren(): array;

    /**
     * Gets the first child appended to this node.
     *
     * @return Child  The child, or `null`
     */
    abstract public function getFirstChild(): ?Child;

    /**
     * Gets the last child appended to this node.
     *
     * @return Child  The child, or `null`
     */
    abstract public function getLastChild(): ?Child;

    /**
     * Gets a single attribute on this node.
     *
     * @param string $attr  The attribute name
     * @return mixed  The attribute found or `null`
     */
    abstract public function getAttribute(string $attr);

    /**
     * Gets all attributes on this node.
     *
     * @return array<string,mixed>  An associative array.
     */
    abstract public function getAttributes(): array;

    /**
     * Sets a single attribute on the node.
     *
     * @param string $attr  The attribute name
     * @param mixed $val  The attribute value
     * @return self  provides a fluent interface
     */
    abstract public function setAttribute(string $attr, $val);

    /**
     * Sets multiple attributes on the node.
     *
     * @param array<string,mixed> $attrs  The attributes to set
     * @return self  provides a fluent interface
     */
    abstract public function setAttributes(array $attrs);

    /**
     * Whether an attribute has been set on this node.
     *
     * @param string $attr  The attribute name
     * @return bool  `true` if the attribute is set
     */
    abstract public function hasAttribute(string $attr): bool;

    /**
     * Removes an attribute from the node.
     *
     * @return self  provides a fluent interface
     */
    abstract public function removeAttribute(string $attr);

    /**
     * Returns a string representation of this class.
     *
     * @return string
     */
    abstract public function toString(): string;

    final public function __toString(): string
    {
        return $this->toString();
    }

    final public function jsonSerialize(): string
    {
        return $this->toString();
    }
}
