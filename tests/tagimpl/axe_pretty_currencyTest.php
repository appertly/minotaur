<?php

use PHPUnit\Framework\TestCase;
use function Minotaur\Tags\fcomposited as c;
use function Minotaur\Tags\ftag as h;

/**
 * @requires extension intl
 */
class axe_pretty_currencyTest extends TestCase
{
    public function testRender()
    {
        $out = [
            '<abbr title="$0.00" class="pretty"><span class="value">$0.00</span><span class="multiplier"></span></abbr>' => c('axe_pretty_currency', ['locale' => "en_US"]),
            '<abbr title="$1.00" class="pretty"><span class="value">$1.00</span><span class="multiplier"></span></abbr>' => c('axe_pretty_currency', ['value' => 1, 'locale' => "en_US"]),
            '<abbr title="$1,000.00" class="pretty"><span class="value">$1.00</span><span class="multiplier">K</span></abbr>' => c('axe_pretty_currency', ['value' => 1000, 'locale' => "en_US"]),
            '<abbr title="$123,456.00" class="pretty"><span class="value">$123.46</span><span class="multiplier">K</span></abbr>' => c('axe_pretty_currency', ['value' => 123456, 'locale' => "en_US"]),
            '<abbr title="$123,456,789.00" class="pretty"><span class="value">$123.46</span><span class="multiplier">M</span></abbr>' => c('axe_pretty_currency', ['value' => 123456789, 'locale' => "en_US"]),
            '<abbr title="$123,456,789,123.00" class="pretty"><span class="value">$123.46</span><span class="multiplier">B</span></abbr>' => c('axe_pretty_currency', ['value' => 123456789123, 'locale' => "en_US"]),
            '<abbr title="$123,456,789,123,456.00" class="pretty"><span class="value">$123.46</span><span class="multiplier">T</span></abbr>' => c('axe_pretty_currency', ['value' => 123456789123456, 'locale' => "en_US"]),
            '<abbr title="$123,456,789,123,457,000.00" class="pretty"><span class="value">$123,456.79</span><span class="multiplier">T</span></abbr>' => c('axe_pretty_currency', ['value' => 123456789123456789, 'locale' => "en_US"]),
        ];
        foreach ($out as $k => $v) {
            $this->assertEquals($k, (string) $v);
        }
    }
}
