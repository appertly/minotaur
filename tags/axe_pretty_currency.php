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
 * Human-readable currency.
 */
class axe_pretty_currency extends Composited
{
    private const UNITS = ['', 'K', 'M', 'B', 'T'];

    protected function render(): Node
    {
        $money = (float) $this->getAttribute('value') ?? 0.0;
        $formatter = $this->ensureAttribute('formatter', NumberFormatter::class);
        if ($formatter === null) {
            $formatter = new NumberFormatter($this->getAttribute('locale') ?? Locale::getDefault(), NumberFormatter::CURRENCY);
        }
        $currency = $this->getAttribute('currency') ?? $formatter->getSymbol(NumberFormatter::INTL_CURRENCY_SYMBOL);
        $factor = $money < 1 ? 0 : min(floor(log10($money) / 3), 4);
        return new Tag('abbr.pretty', ['title' => $formatter->formatCurrency($money, $currency)], [
            new Tag('span.value', [], $formatter->formatCurrency($money / 1000 ** $factor, $currency)),
            new Tag('span.multiplier', [], self::UNITS[$factor])
        ]);
    }
}
