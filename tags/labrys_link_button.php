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
 * A link styled as a button.
 *
 * ```xml
 * <labrys:link-button href="http://example.com" icon="group">
 *     Hello World
 * </labrys:link-button>
 * ```
 */
class labrys_link_button extends Composited
{
    protected function render(): Node
    {
        $icon = (string) $this->getAttribute('icon', 'string', '');
        $a = new Tag('a.btn', [], [
            c('labrys_icon', ['icon' => $icon]),
            new Tag('span.button-text', [], $this->getChildren())
        ]);
        $this->transferAllAttributes($a, ['icon']);
        return $a;
    }
}
