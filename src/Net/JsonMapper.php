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
namespace Minotaur\Net;

/**
 * A class that converts JSON data to equivalent Hack collections.
 */
class JsonMapper
{
    /**
     * Converts a JSON string to a Vector.
     *
     * @param $json - The JSON to convert
     * @return \ConstVector  The `Vector` version
     * @throws \Minotaur\Net\Exception\Illegible if the JSON is invalid
     */
    public function toVector(?string $json): \ConstVector
    {
        $a = $json === null ? null : json_decode($json, true);
        if (!is_array($a)) {
            throw new Exception\Illegible($json, "Invalid JSON array");
        }
        return new \HH\Vector($a);
    }

    /**
     * Converts a JSON string to a Map.
     *
     * @param $json - The JSON to convert
     * @return \ConstMap  The `Map` version
     * @throws \Minotaur\Net\Exception\Illegible if the JSON is invalid
     */
    public function toMap(?string $json): \ConstMap
    {
        $a = $json === null ? null : json_decode($json, true);
        if (!is_array($a)) {
            throw new Exception\Illegible($json, "Invalid JSON map");
        }
        return new \HH\Map($a);
    }
}
