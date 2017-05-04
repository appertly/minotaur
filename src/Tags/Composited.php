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
 * A node which is composed of other nodes.
 */
abstract class Composited extends Primitive
{
    use HasAttributes;
    use HasHtml;

    /**
     * Returns the composed node.
     *
     * @return Node  The composed node
     */
    abstract protected function render(): Node;

    /**
     * Gets an attribute of the specified type.
     *
     * @return mixed  The attribute found, or `$default`
     */
    protected function ensureAttribute(string $key, string $type, $default = null)
    {
        $attribute = $this->getAttribute($key);
        if (!$this->hasAttribute($key) || $attribute === null) {
            return $default;
        }
        switch ($type) {
            case 'string':
                if (!is_string($attribute)) {
                    throw new \UnexpectedValueException("Expected attribute '$key' to be a string");
                }
                break;
            case 'int':
                if (!is_int($attribute)) {
                    throw new \UnexpectedValueException("Expected attribute '$key' to be an integer");
                }
                break;
            case 'float':
                if (!is_float($attribute)) {
                    throw new \UnexpectedValueException("Expected attribute '$key' to be a float");
                }
                break;
            case 'bool':
                if (!is_bool($attribute)) {
                    throw new \UnexpectedValueException("Expected attribute '$key' to be a boolean");
                }
                break;
            case 'array':
                if (!is_array($attribute)) {
                    throw new \UnexpectedValueException("Expected attribute '$key' to be an array");
                }
                break;
            case 'callable':
                if (!is_callable($attribute)) {
                    throw new \UnexpectedValueException("Expected attribute '$key' to be a callable");
                }
                break;
            case 'iterable':
                if (!is_iterable($attribute)) {
                    throw new \UnexpectedValueException("Expected attribute '$key' to be an iterable");
                }
                break;
            case 'resource':
                if (!is_resource($attribute)) {
                    throw new \UnexpectedValueException("Expected attribute '$key' to be a resource");
                }
                break;
            default:
                if (!($attribute instanceof $type)) {
                    throw new \UnexpectedValueException("Expected attribute '$key' to be of type '$type', got '" . get_class($attribute) . "'");
                }
        }
        return $attribute ?? $default;
    }

    /**
     * Transfers attributes from this node to another node, removing them from this one.
     */
    protected function transferAllAttributes(Node $node, array $ignore = []): void
    {
        $attributes = array_diff_key($this->attributes, array_flip($ignore));
        if (array_key_exists('class', $attributes)) {
            $attributes['class'] = trim($node->getAttribute('class') . ' ' . $attributes['class']);
        }
        $node->setAttributes($attributes);
        foreach (array_keys($attributes) as $k) {
            unset($this->attributes[$k]);
        }
    }

    /**
     * Gets the composited node and adds context info.
     */
    protected function renderWithContext(): Node
    {
        $composed = $this->render();
        if ($composed instanceof Contextual) {
            $composed->setAllMissingContexts($this->context);
        }
        return $composed;
    }

    /**
     * Gets the composited node.
     *
     * @return Node  The composited node.
     */
    protected function renderAll(): Node
    {
        $composed = $this;
        while ($composed instanceof Composited) {
            $composed = $composed->renderWithContext();
        }
        return $composed;
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return $this->renderAll()->toString();
    }
}
