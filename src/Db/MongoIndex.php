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
 * A MongoDB Index definition
 */
class MongoIndex
{
    /**
     * @var array<string,mixed>
     */
    private $values;

    private const BOOL_FIELDS = ['background', 'unique', 'sparse'];
    private const FLOAT_FIELDS = ['min', 'max', 'bucketSize'];
    private const INT_FIELDS = ['bits', '2dsphereIndexVersion', 'textIndexVersion', 'expireAfterSeconds'];
    private const STRING_FIELDS = ['default_language', 'language_override'];
    private const DOCUMENT_FIELDS = ['partialFilterExpression', 'storageEngine', 'weights'];

    /**
     * Creates an immutable MongoDB Index creation definition
     *
     * @param $keys - The key definition
     * @param $name - Optional. The index name.
     * @param $options - Optional. Any index creation options.
     * @see https://docs.mongodb.com/manual/reference/command/createIndexes/
     */
    public function __construct(iterable $keys, string $name = null, ?iterable $options = null)
    {
        $values = [
            'key' => is_array($keys) ? $keys : iterator_to_array($keys, true)
        ];
        if ($name !== null) {
            $values['name'] = $name;
        }
        if ($options) {
            foreach (self::BOOL_FIELDS as $v) {
                if (array_key_exists($v, $options)) {
                    $values[$v] = (bool) $options[$v];
                }
            }
            foreach (self::FLOAT_FIELDS as $v) {
                if (array_key_exists($v, $options)) {
                    $values[$v] = (float) $options[$v];
                }
            }
            foreach (self::INT_FIELDS as $v) {
                if (array_key_exists($v, $options)) {
                    $values[$v] = (int) $options[$v];
                }
            }
            foreach (self::STRING_FIELDS as $v) {
                if (array_key_exists($v, $options)) {
                    $values[$v] = (string) $options[$v];
                }
            }
            foreach (self::DOCUMENT_FIELDS as $v) {
                if (array_key_exists($v, $options)) {
                    $doc = $options[$v];
                    if (is_iterable($doc)) {
                        $values[$v] = is_array($doc) ? $doc : iterator_to_array($doc, true);
                    }
                }
            }
        }
        $this->values = $values;
    }

    /**
     * Gets the array version of this index.
     *
     * @return array<string,mixed> The array version
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
