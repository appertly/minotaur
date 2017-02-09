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
 * A textarea that should have golden ratio width to height.
 *
 * ```xml
 * <labrys:golden-textarea name="foobar" id="foobar">
 *     Lorem ipsum dolor sit amet
 * </labrys:golden-textarea>
 * ```
 */
class labrys_golden_textarea extends Composited
{
    protected function render(): Node
    {
        $ta = new Tag('textarea', [], $this->getChildren());
        $this->transferAllAttributes($ta);
        return new Tag('div.golden-textarea', [], $ta);
    }
}
