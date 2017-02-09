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
class axe_items_per_page extends Composited
{
    protected function render(): Node
    {
        $pagination = new Tag('ul.pagination');
        $current = max(1, (int) ($this->getAttribute('current') ?? 10));
        $params = $this->ensureAttribute('queryParams', 'array', []);
        $href = (string) $this->getAttribute('href');
        $param = (string) ($this->getAttribute('parameter') ?? 'count');
        $prefix = (string) $this->getAttribute('prefix');

        if ($prefix) {
            $pagination->appendChild(
                new Tag('li.page-item.disabled', [], [
                    new Tag('span.page-link', [], $prefix)
                ])
            );
        }

        foreach ($this->ensureAttribute('items', 'iterable', [10, 25, 50]) as $item) {
            $pagination->appendChild(
                $this->numberedLink((int) $item, $current, $href, $params, $param)
            );
        }

        $suffix = $this->ensureAttribute('suffix', 'string', 'items per page');
        if ($suffix) {
            $pagination->appendChild(
                new Tag('li.page-item.disabled', [], [
                    new Tag('span.page-link', [], $suffix)
                ])
            );
        }
        $this->transferAllAttributes($pagination, ['suffix', 'prefix', 'href', 'parameter', 'queryParams', 'current']);
        return $pagination;
    }

    private function numberedLink(int $item, int $current, string $href, array $params, string $param)
    {
        if ($current === $item) {
            return new Tag('li.page-item.active', [], [
                new Tag('span.page-link', [], $item)
            ]);
        } else {
            $params[(string) $param] = $item;
            $query = http_build_query($params);
            return new Tag('li.page-item', [], [
                new Tag('a.page-link', ['href' => "$href?$query"], $item)
            ]);
        }
    }
}
