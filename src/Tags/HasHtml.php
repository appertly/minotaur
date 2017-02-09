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
 * Helpers for HTML tags
 */
trait HasHtml
{
    /**
     * Appends a string to the "class" attribute (space separated).
     */
    public function addClass(string $class): self
    {
        $current = $this->getAttribute('class');
        return $this->setAttribute('class', trim("$current $class"));
    }

    /**
     * Conditionally adds a class to the "class" attribute.
     */
    public function conditionClass(bool $cond, string $class): self
    {
        return $cond ? $this->addClass($class) : $this;
    }

    /**
     * Generates a unique ID (and sets it) on the "id" attribute.
     *
     * A unique ID will only be generated if one has not already been set.
     */
    protected function requireUniqueID(): string
    {
        $id = $this->getAttribute('id');
        if ($id === null || $id === '') {
            $id = substr(bin2hex(random_bytes(6)), 0, 10);
            $this->setAttribute('id', $id);
        }
        return (string) $id;
    }

    /**
     * Gets the `id` attribute, and generates a random one if unset.
     *
     * @return string
     */
    final public function getId(): string
    {
        return $this->requireUniqueId();
    }
}
