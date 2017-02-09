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
use function Minotaur\Tags\fcomposited as c;

/**
 * An item to appear in a navigation list.
 *
 * ```xml
 * <labrys:nav-item href="http://example.com" icon="group">
 *     <em>Hello.</em>
 * </labrys:nav-item>
 * ```
 */
class labrys_nav_item extends Composited
{
    protected function render(): Node
    {
        return new Tag('li.nav-item', [], [
            new Tag('a', ['href' => $this->getAttribute('href'), 'class' => $this->getAttribute('class'), 'title' => $this->getAttribute('title')], [
                c('labrys_icon', ['icon' => $this->getAttribute('icon')]),
                new Tag('span.nav-item-label', [], $this->getChildren())
            ])
        ]);
    }
}
