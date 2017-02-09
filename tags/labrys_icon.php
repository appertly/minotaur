<?php
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
 * FontAwesome icon
 *
 * ```xml
 * <labrys:icon icon="group" />
 * ```
 */
class labrys_icon extends Composited
{
    protected function render(): Node
    {
        $icon = $this->getAttribute('icon');
        if (!is_string($icon)) {
            return new Frag();
        }
        $i = new Tag('i.fa.fa-' . $icon, ['role' => "presentation"]);
        $this->transferAllAttributes($i, ['icon']);
        return $i;
    }
}
