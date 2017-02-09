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
use function Minotaur\Tags\fcomposited;

/**
 * The base layout
 */
class axe_layout extends Composited
{
    protected function render(): Node
    {
        $page = $this->ensureAttribute('page', \Minotaur\View\Page::class);
        if ($page === null) {
            throw new \UnexpectedValueException("page attribute must not be null");
        }

        $body = new Tag('body', [], [
            $this->getChildren(),
            fcomposited('axe_scripts', ['page' => $page]),
        ]);

        foreach ($page->getBodyClasses() as $class) {
            $body->addClass($class);
        }
        if ($page->getBodyId()) {
            $body->setAttribute('id', $page->getBodyId());
        }
        return new x_doctype(
            new Tag('html', ['lang' => $page->getLang()], [
                new Tag('head', [], [
                    new Tag('meta', ['charset' => $page->getEncoding()]),
                    new Tag('title', [], $page->getTitle()),
                    $page->getMeta(),
                    fcomposited('axe_scripts', ['location' => 'head', 'page' => $page]),
                    $page->getLinks(),
                ]),
                $body
            ])
        );
    }
}
