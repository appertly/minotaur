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

use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDateTime;

/**
 * Can be used by any class which accesses MongoDB.
 */
trait MongoHelper
{
    /**
     * Transforms a literal into a MongoDB ObjectId.
     *
     * @param $id - If it's an `ObjectID`, returns that, otherwise creates a new
     *              `ObjectID`.
     * @return - The ObjectID
     */
    protected function toId($id): ObjectID
    {
        return $id instanceof ObjectID ? $id : new ObjectID((string) $id);
    }

    /**
     * Transforms literals into MongoDB ObjectIds.
     *
     * @param iterable<mixed> $ids Goes through each entry, converts to `ObjectID`
     * @return array<\MongoDB\BSON\ObjectID> The ObjectIDs
     */
    protected function toIds(iterable $ids): array
    {
        $ids = [];
        foreach ($ids as $a) {
            $ids[] = $a instanceof ObjectID ? $a : new ObjectID((string) $a);
        }
        return $ids;
    }

    /**
     * Gets the current time.
     *
     * @return - The current time
     * @deprecated 0.7.0:1.0.0 UTCDateTime now doesn't need an argument.
     */
    protected function now(): \MongoDB\BSON\UTCDateTime
    {
        $parts = explode(' ', microtime());
        return new \MongoDB\BSON\UTCDateTime(sprintf('%d%03d', $parts[1], $parts[0] * 1000));
    }

    /**
     * Tries to parse a date.
     *
     * @param $date - The possible string date value, a string, a
     *        `\DateTimeInterface`, or a `\MongoDB\BSON\UTCDateTime`
     * @return - The MongoDB datetime or null
     */
    protected function toDate($date): ?UTCDateTime
    {
        if ($date instanceof UTCDateTime) {
            return $date;
        } elseif ($date instanceof \DateTimeInterface) {
            return new UTCDateTime($date);
        } else {
            $date = trim((string)$date);
            return strlen($date) > 0 ? new UTCDateTime(strtotime($date) * 1000) : null;
        }
    }

    /**
     * Makes sure a document isn't null.
     *
     * @param mixed $id The document identifier, either a `\MongoDB\BSON\ObjectID` or string
     * @param mixed $document The document to check
     * @return mixed Returns `$document`
     * @throws \Caridea\Dao\Exception\Unretrievable if the document is null
     */
    protected function ensure($id, $document)
    {
        if ($document === null) {
            throw new \Caridea\Dao\Exception\Unretrievable("Could not find document with ID $id");
        }
        return $document;
    }
}
