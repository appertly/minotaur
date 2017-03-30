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
 * A low-level node.
 */
abstract class Primitive extends Node
{
    /**
     * @var Child[]
     */
    protected $children = [];
    /**
     * @var array<string,mixed>
     */
    protected $context = [];

    /**
     * @inheritDoc
     */
    final public function getAllContexts(): array
    {
        return $this->context;
    }

    /**
     * @inheritDoc
     */
    final public function getContext(string $key, $default = null)
    {
        return $this->context[$key] ?? $default;
    }

    /**
     * @inheritDoc
     */
    final public function setContext(string $key, $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function setAllContexts(array $context): self
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function setAllMissingContexts(array $parentContext): void
    {
        foreach ($parentContext as $key => $value) {
            if (!array_key_exists($key, $this->context)) {
                $this->context[$key] = $value;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function appendChild($child): self
    {
        if (is_iterable($child)) {
            foreach ($child as $v) {
                $this->appendChild($v);
            }
        } elseif ($child instanceof Frag) {
          foreach($child->getChildren() as $v)
            $this->children[] = $v;
        } elseif ($child !== null) {
            if (is_string($child) || is_float($child) || is_int($child)) {
                $child = new Scalar($child);
            }
            if (!($child instanceof Child)) {
                throw new \InvalidArgumentException("Children must implement " . Child::class);
            }
            $this->children[] = $child;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function prependChild($child): self
    {
        if (is_iterable($child)) {
            foreach ($child as $v) {
                $this->prependChild($v);
            }
        } elseif ($child instanceof Frag) {
            array_unshift($this->children, ...$child->getChildren());
        } elseif ($child !== null) {
            if (is_string($child) || is_float($child) || is_int($child)) {
                $child = new Scalar($child);
            }
            if (!($child instanceof Child)) {
                throw new \InvalidArgumentException("Children must implement " . Child::class);
            }
            array_unshift($this->children, $child);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @inheritDoc
     */
    public function getFirstChild(): ?Child
    {
        return empty($this->children) ? null : $this->children[0];
    }

    /**
     * @inheritDoc
     */
    public function getLastChild(): ?Child
    {
        return empty($this->children) ?
            null : $this->children[count($this->children) - 1];
    }

    protected function stringifyChild(Child $child): string
    {
        if ($child instanceof Raw) {
            return $child->toHtmlString();
        } elseif ($child instanceof Node) {
            return $child->toString();
        } else {
            return (string) $child;
        }
    }
}
