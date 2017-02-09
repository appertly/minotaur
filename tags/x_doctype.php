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

use Minotaur\Tags\Primitive;
use Minotaur\Tags\Node;
use Minotaur\Tags\Frag;
use Minotaur\Tags\Tag;

/**
 * HTML DOCTYPE
 */
class x_doctype extends Primitive
{
    /**
     * Creates a new Doctype.
     *
     * @param iterable|mixed $children The child or children to add.
     */
    public function __construct($children = null)
    {
        $this->appendChild($children);
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(string $attr)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function setAttribute(string $attr, $val): self
    {
        throw new \BadMethodCallException(__CLASS__ . " does not support attributes");
    }

    /**
     * @inheritDoc
     */
    public function setAttributes(array $attrs): self
    {
        throw new \BadMethodCallException(__CLASS__ . " does not support attributes");
    }

    /**
     * @inheritDoc
     */
    public function hasAttribute(string $attr): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function removeAttribute(string $attr): self
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $out = '<!DOCTYPE html>';
        foreach ($this->children as $child) {
            if ($child instanceof Contextual) {
                $child->setAllMissingContexts($this->context);
            }
            $out .= $this->stringifyChild($child);
        }
        return $out;
    }
}
