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
namespace Minotaur\Db;

/**
 * Resolves DbRef objects
 */
interface DbRefResolver
{
    /**
     * Gets whether or not this class supports the reference type.
     *
     * @param $ref - The reference type (usually a MongoDB collection name)
     * @return - `true` if the reference type is supported
     */
    public function isResolvable(string $ref): bool;

    /**
     * Resolves a MongoDB DbRef.
     *
     * @param array<string,mixed> $ref - The DbRef to load
     * @return - The loaded entity or `null` if not found
     * @throws \InvalidArgumentException If `$ref` is of an unsupported type
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the result cannot be retrieved
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function resolve(array $ref);

    /**
     * Resolves a MongoDB DbRef.
     *
     * @param iterable<array<string,mixed>> $refs The DbRefs to load
     * @return \Traversable<mixed> The loaded entities
     * @throws \InvalidArgumentException If any `$ref`s are of an unsupported type
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the result cannot be retrieved
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function resolveAll(iterable $refs): iterable;
}
