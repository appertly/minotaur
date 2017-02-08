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
namespace Minotaur\Db\Entity;

/**
 * A trait for entities which can track changes.
 */
trait Tracking
{
    /**
     * @var array<string,array<string,mixed>> Changes to persist.
     */
    protected $changes = [];

    /**
     * Gets the pending changes.
     *
     * @return array<string,array<string,mixed>> The pending changes
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * Whether the object has any changes.
     *
     * @return - Whether the object has any changes
     */
    public function isDirty(): bool
    {
        return !empty($this->changes);
    }

    /**
     * Sets a field for update.
     *
     * @param $field - The field name
     * @param $value - The field value
     * @return - provides a fluent interface
     */
    protected function fieldSet(string $field, $value): self
    {
        if (!array_key_exists('$set', $this->changes)) {
            $this->changes['$set'] = [];
        }
        $this->changes['$set'][$field] = $value;
        return $this;
    }

    /**
     * Sets a field for removal.
     *
     * @param $field - The field name
     * @return - provides a fluent interface
     */
    protected function fieldUnset(string $field): self
    {
        if (!array_key_exists('$unset', $this->changes)) {
            $this->changes['$unset'] = [];
        }
        $this->changes['$unset'][$field] = '';
        return $this;
    }

    /**
     * Sets a field for increment.
     *
     * @param $field - The field name
     * @param $value - Optional. The increment value. Default is `1`.
     * @return - provides a fluent interface
     */
    protected function fieldIncrement(string $field, int $value = 1): self
    {
        if (!array_key_exists('$inc', $this->changes)) {
            $this->changes['$inc'] = [];
        }
        $this->changes['$inc'][$field] = $value;
        return $this;
    }

    /**
     * Sets a field to the current date.
     *
     * @param $field - The field name
     * @return - provides a fluent interface
     */
    protected function fieldNow(string $field): self
    {
        if (!array_key_exists('$currentDate', $this->changes)) {
            $this->changes['$currentDate'] = [];
        }
        $this->changes['$currentDate'][$field] = true;
        return $this;
    }

    /**
     * Pushes a value onto a field.
     *
     * @param $field - The field name
     * @param $value - The value to push
     * @return - provides a fluent interface
     */
    protected function fieldPush(string $field, $value): self
    {
        if (!array_key_exists('$push', $this->changes)) {
            $this->changes['$push'] = [];
        }
        $this->changes['$push'][$field] = $value;
        return $this;
    }

    /**
     * Pushes a value onto an array field.
     *
     * @param $field - The field name
     * @param $value - The values to push
     * @return - provides a fluent interface
     */
    protected function fieldPushAll(string $field, iterable $value): self
    {
        if (!array_key_exists('$push', $this->changes)) {
            $this->changes['$push'] = [];
        }
        $this->changes['$push'][$field] = ['$each' => is_array($value) ? $value : iterator_to_array($value, false)];
        return $this;
    }

    /**
     * Pulls a value from a array field.
     *
     * In addition to a single value, you can also specify a query document.
     * ```
     * $this->fieldPull('vegetables', 'carrot');
     * $this->fieldPull('listOfDocs', ['foo' => 'bar']);
     * ```
     *
     * @param $field - The field name
     * @param $value - The value to pull
     * @return - provides a fluent interface
     */
    protected function fieldPull(string $field, $value): self
    {
        if (!array_key_exists('$pull', $this->changes)) {
            $this->changes['$pull'] = [];
        }
        $this->changes['$pull'][$field] = $value;
        return $this;
    }

    /**
     * Takes all the changes from a `Modifiable` and copies them under a field name.
     *
     * @param $child - The object containing updates
     * @param $field - The field name
     * @return - provides a fluent interface
     */
    protected function aggregateChanges(Modifiable $child, string $field): self
    {
        foreach ($child->getChanges() as $op => $sets) {
            if (!array_key_exists($op, $this->changes)) {
                $this->changes[$op] = [];
            }
            foreach ($sets as $k => $v) {
                $this->changes[$op]["$field.$k"] = $v;
            }
        }
        return $this;
    }
}
