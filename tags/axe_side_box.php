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
 * A box that might appear in a sidebar.
 *
 * ```xml
 * <axe:side-box label="Hello World">
 *     <p>Anything</p>
 * </axe:side-box>
 * ```
 */
class axe_side_box extends Composited
{
    protected function render(): Node
    {
        $buttons = null;
        $inner = new Tag('div.side-box-contents');
        foreach ($this->getChildren() as $kid) {
            if ($kid instanceof axe_toolbar) {
                $buttons = $kid;
                $kid->addClass('side-box-toolbar');
            } else {
                $inner->appendChild($kid);
            }
        }
        $outer = new Tag('section.side-box', [], [
            new Tag('header.side-box-header', [], [
                new Tag('h1', [], (string) $this->getAttribute('label')),
                $buttons
            ]),
            $inner
        ]);
        $this->transferAllAttributes($outer, ['label']);
        return $outer;
    }
}
