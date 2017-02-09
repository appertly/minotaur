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
 * Pagination links
 */
class axe_pagination extends Composited
{
    protected function render(): Node
    {
        $pagination = new Tag('ul.pagination');
        $current = max(1, $this->ensureAttribute('current', 'int', 1));
        $total = max(1, $this->ensureAttribute('total', 'int', 1));
        if ($total === 1) {
            return $pagination;
        }
        $params = $this->ensureAttribute('queryParams', 'array', []);
        $href = (string) $this->getAttribute('href');
        $param = $this->ensureAttribute('parameter', 'string', 'page');
        $short = (bool) $this->getAttribute('short');

        $plabel = $short ?
            new Frag([
                new Tag('span', ['aria-hidden' => "true"], "«"),
                new Tag('span.sr-only', [], "Previous"),
            ])
            : "Previous";
        if ($current === 1) {
            $pagination->appendChild(
                new Tag('li.page-item.disabled', [], [
                    new Tag('span.page-link', [], $plabel)
                ])
            );
        } else {
            $params[$param] = $current - 1;
            $query = http_build_query($params);
            $pagination->appendChild(
                new Tag('li.page-item', [], [
                    new Tag('a.page-link', ['href' => "$href?$query", 'rel' => "prev"], $plabel)
                ])
            );
        }

        if ($total < 10) {
            for ($i = 1; $i <= $total; $i++) {
                $pagination->appendChild(
                    $this->numberedLink($i, $current, $total, $href, $params, $param)
                );
            }
        } else {
            $pagination->appendChild(
                $this->numberedLink(1, $current, $total, $href, $params, $param)
            );
            $pagination->appendChild(
                $this->numberedLink(2, $current, $total, $href, $params, $param)
            );

            $lstart = $total - $current >= 4 ? max(3, $current - 2) : $total - 6;
            $lend = $lstart + 4;

            if ($lstart > 3) {
                $pagination->appendChild(
                    new Tag('li.page-item.disabled', [], [
                        new Tag('span.page-link.page-gap', [], "…")
                    ])
                );
            }

            for ($i = $lstart; $i <= $lend; $i++) {
                $pagination->appendChild(
                    $this->numberedLink($i, $current, $total, $href, $params, $param)
                );
            }

            if ($lend < $total - 2) {
                $pagination->appendChild(
                    new Tag('li.page-item.disabled', [], [
                        new Tag('span.page-link.page-gap', [], "…")
                    ])
                );
            }

            $pagination->appendChild(
                $this->numberedLink($total - 1, $current, $total, $href, $params, $param)
            );
            $pagination->appendChild(
                $this->numberedLink($total, $current, $total, $href, $params, $param)
            );
        }

        $nlabel = $short ?
            new Frag([
                new Tag('span', ['aria-hidden' => "true"], "»"),
                new Tag('span.sr-only', [], "Next"),
            ])
            : "Next";
        if ($current === $total) {
            $pagination->appendChild(
                new Tag('li.page-item.disabled', [], [
                    new Tag('span.page-link', [], $nlabel)
                ])
            );
        } else {
            $params[(string) $param] = min($current + 1, $total);
            $query = http_build_query($params);
            $pagination->appendChild(
                new Tag('li.page-item', [], [
                    new Tag('a.page-link', ['href' => "$href?$query", 'rel' => "next"], $nlabel)
                ])
            );
        }
        $this->transferAllAttributes($pagination, ['href', 'queryParams', 'current', 'total', 'parameter', 'short']);
        return $pagination;
    }

    private function numberedLink(int $page, int $current, int $total, string $href, array $params, string $param)
    {
        if ($current === $page) {
            return new Tag('li.page-item.active', [], [
                new Tag('span.page-link', [], $page)
            ]);
        } else {
            $params[$param] = $page;
            $query = http_build_query($params);
            $link = new Tag('a.page-link', ['href' => "$href?$query"], $page);
            if ($page === $total) {
                $link->setAttribute('rel', 'last');
            } elseif ($page === 1) {
                $link->setAttribute('rel', 'first');
            }
            return new Tag('li.page-item', [], $link);
        }
    }
}
