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

trait HasAttributes
{
    /**
     * @var array<string,mixed>
     */
    protected $attributes = [];

    /**
     * @inheritDoc
     */
    public function getAttribute(string $attr)
    {
        return $this->attributes[$attr] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function setAttribute(string $attr, $val)
    {
        $this->attributes[$attr] = $val;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAttributes(array $attrs)
    {
        $this->attributes = array_merge($this->attributes, $attrs);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasAttribute(string $attr): bool
    {
        return array_key_exists($attr, $this->attributes);
    }

    /**
     * @inheritDoc
     */
    public function removeAttribute(string $attr)
    {
        unset($this->attributes[$attr]);
        return $this;
    }
}
