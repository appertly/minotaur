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
use Minotaur\Tags\Node;
use Minotaur\Tags\Tag;

/**
 * Human-readable file size.
 */
class axe_pretty_bytes extends Composited
{
    private const UNITS = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];

    protected function render(): Node
    {
        $bytes = $this->getAttribute('value') ?? 0;
        $formatter = $this->getAttribute('formatter', NumberFormatter::class);
        if ($formatter === null) {
            $formatter = new NumberFormatter($this->getAttribute('locale') ?? Locale::getDefault(), NumberFormatter::DECIMAL);
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);
        }
        $factor = $bytes < 1 ? 0 : min(floor(log10($bytes) / 3), 8);
        return new Tag('abbr.pretty', ['title' => (is_float($bytes) ? '~' : '') . $formatter->format($bytes) . ' bytes'], [
            new Tag('span.value', [], $formatter->format($bytes / 1024 ** $factor)),
            new Tag('span.unit', [], self::UNITS[$factor]),
        ]);
    }
}
