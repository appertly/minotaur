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
 * Any node that can have context.
 */
interface Contextual
{
    /**
     * Returns all contexts currently set.
     *
     * @return array<string,mixed>  All contexts
     */
    public function getAllContexts(): array;

    /**
     * Returns a specific context value. Can include a default if not set.
     *
     * @param string $key  The context key
     * @param mixed $default  The value to return if not set (optional)
     * @return mixed  The context value or $default
     */
    public function getContext(string $key, $default = null);

    /**
     * Sets a context value.
     *
     * @param string $key  A key
     * @param mixed $value  The value to set
     * @return self provides a fluent interface
     */
    public function setContext(string $key, $value);

    /**
     * Sets all context values.
     *
     * @param array<string,mixed> $context  A map of key/value pairs
     * @return self provides a fluent interface
     */
    public function setAllContexts(array $context);

    /**
     * Transfers the context but will not overwrite anything.
     *
     * @param array<string,mixed> $parentContext The context to transfer
     * @return self provides a fluent interface
     */
    public function setAllMissingContexts(array $parentContext);
}
