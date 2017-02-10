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

use Minotaur\Tags\Composited;
use Minotaur\Tags\Node;
use Minotaur\Tags\Tag;

/**
 * A group of checkboxes
 */
class axe_checkboxes extends Composited
{
    protected function render(): Node
    {
        $name = (string) $this->getAttribute('name');
        $id = $this->getId();
        $inputs = new Tag('div', ['id' => $id]);
        $options = $this->getAttribute('options', 'iterable', []);
        $this->transferAllAttributes($inputs, ['name', 'options', 'inline', 'value']);

        $values = array_flip(
            array_map(function ($a) {
                return (string) $a;
            }, $this->ensureAttribute('value', 'array', []))
        );
        if ($this->getAttribute('inline')) {
            foreach ($options as $k => $v) {
                $inputs->appendChild(
                    new Tag('span.form-check-inline', [], [
                        new Tag('input.form-check-input', ['type' => 'checkbox', 'id' => "$id-$k", 'name' => $name, 'value' => $k, 'checked' => array_key_exists($k, $values)]),
                        new Tag('label.form-check-label', ['for' => "$id-$k"], (string) $v)
                    ])
                );
            }
        } else {
            foreach ($options as $k => $v) {
                $inputs->appendChild(
                    new Tag('div.form-check', [], [
                        new Tag('input.form-check-input', ['type' => 'checkbox', 'id' => "$id-$k", 'name' => $name, 'value' => $k, 'checked' => array_key_exists($k, $values)]),
                        new Tag('label.form-check-label', ['for' => "$id-$k"], (string) $v),
                    ])
                );
            }
        }
        return $inputs;
    }
}
