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
 * Delays the render of scripts.
 */
class axe_scripts extends Composited
{
    protected function render(): Node
    {
        $frag = new Frag();
        $page = $this->ensureAttribute('page', \Minotaur\View\Page::class);
        /** @var \Minotaur\View\Page $page */
        if ($page === null) {
            throw new \UnexpectedValueException("page attribute must not be null");
        }
        $scripts = $this->ensureAttribute('location', 'string', 'body') === "head" ?
            $page->getHeadScripts() : $page->getBodyScripts();
        foreach ($scripts as $script) {
            $frag->appendChild($script);
        }
        return $frag;
    }
}
