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
 * Fieldset
 */
class axe_fieldset extends Composited
{
    protected function render(): Node
    {
        $class = $this->getAttribute('inline') ? 'inline-fieldset' : 'fieldset-and-legend';
        $a = new Tag("fieldset.$class", [], [
            new Tag('legend', [], [
                new Tag('span.legend-text', [], $this->ensureAttribute('legend', 'string', ''))
            ]),
            new Tag('div.form-groups', [], $this->getChildren()),
        ]);
        $this->transferAllAttributes($a, ['inline', 'legend']);
        return $a;
    }
}
