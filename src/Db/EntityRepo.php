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
 * Basic interface for entity services.
 */
interface EntityRepo
{
    /**
     * Gets the type of entity produced, mainly for ACL reasons.
     *
     * @return string The entity type
     */
    public function getType(): string;

    /**
     * Gets a Map that relates identifier to instance
     *
     * @param iterable<mixed> $entities The entities to "zip"
     * @return array<string,mixed> The instances keyed by identifier
     */
    public function getInstanceMap(iterable $entities): array;

    /**
     * Finds a single record by some arbitrary criteria.
     *
     * @param array<string,mixed> $criteria Field to value pairs
     * @return mixed|null The object found or null if none
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the result cannot be returned
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function findOne(array $criteria);

    /**
     * Counts several records by some arbitrary criteria.
     *
     * @param array<string,mixed> $criteria Field to value pairs
     * @return int The count of the documents
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the result cannot be returned
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function countAll(array $criteria): int;

    /**
     * Finds several records by some arbitrary criteria.
     *
     * @param array<string,mixed> $criteria Field to value pairs
     * @param $pagination - Optional pagination parameters
     * @param bool $totalCount Return a `CursorSubset` that includes the total
     *        number of records. This is only done if `$pagination` is not using
     *        the defaults.
     * @return iterable<mixed> The objects found or null if none
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the result cannot be returned
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function findAll(array $criteria, \Caridea\Http\Pagination $pagination = null, bool $totalCount = false): iterable;

    /**
     * Gets a single document by ID.
     *
     * @param $id - The document identifier
     * @return mixed|null The entity, or `null`
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the result cannot be returned
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function findById($id);

    /**
     * Gets a single document by ID, throwing an exception if it's not found.
     *
     * @param $id - The document identifier
     * @return mixed The entity, never `null`.
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the document doesn't exist
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function get($id);

    /**
     * Gets several documents by ID.
     *
     * @param iterable<mixed> $ids List of identifiers
     * @return iterable<mixed> The results
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the result cannot be returned
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    public function getAll(iterable $ids): iterable;
}
