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
use Minotaur\Tags\Frag;
use Minotaur\Tags\Node;
use Minotaur\Tags\Tag;

/**
 * A message display.
 */
class axe_heads_up extends Composited
{
    protected function render(): Node
    {
        $kids = $this->getChildren();
        if (empty($kids)) {
            return new Frag();
        }
        $div = new Tag('div.heads-up', [], [
            new Tag('div.alert.alert-' . ($this->getAttribute('status') ?? 'info'), ['role' => 'alert'], $kids)
        ]);
        $this->transferAllAttributes($div, ['status']);
        return $div;
    }
}
