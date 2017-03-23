<?php

use PHPUnit\Framework\TestCase;
use function Minotaur\Tags\fcomposited as c;
use function Minotaur\Tags\ftag as h;

/**
 * @requires extension intl
 */
class PrettyBytesTest extends TestCase
{
    public function testRender()
    {
        $out = [
            '<abbr title="0 bytes" class="pretty"><span class="value">0</span><span class="unit">B</span></abbr>' => c('axe_pretty_bytes', ['locale' => "en_US"]),
            '<abbr title="1 bytes" class="pretty"><span class="value">1</span><span class="unit">B</span></abbr>' => c('axe_pretty_bytes', ['value' => 1, 'locale' => "en_US"]),
            '<abbr title="1,000 bytes" class="pretty"><span class="value">0.98</span><span class="unit">KiB</span></abbr>' => c('axe_pretty_bytes', ['value' => 1000, 'locale' => "en_US"]),
            '<abbr title="1,024 bytes" class="pretty"><span class="value">1</span><span class="unit">KiB</span></abbr>' => c('axe_pretty_bytes', ['value' => 1024, 'locale' => "en_US"]),
            '<abbr title="123,456 bytes" class="pretty"><span class="value">120.56</span><span class="unit">KiB</span></abbr>' => c('axe_pretty_bytes', ['value' => 123456, 'locale' => "en_US"]),
            '<abbr title="123,456,789 bytes" class="pretty"><span class="value">117.74</span><span class="unit">MiB</span></abbr>' => c('axe_pretty_bytes', ['value' => 123456789, 'locale' => "en_US"]),
            '<abbr title="123,456,789,123 bytes" class="pretty"><span class="value">114.98</span><span class="unit">GiB</span></abbr>' => c('axe_pretty_bytes', ['value' => 123456789123, 'locale' => "en_US"]),
            '<abbr title="123,456,789,123,456 bytes" class="pretty"><span class="value">112.28</span><span class="unit">TiB</span></abbr>' => c('axe_pretty_bytes', ['value' => 123456789123456, 'locale' => "en_US"]),
            '<abbr title="123,456,789,123,456,789 bytes" class="pretty"><span class="value">109.65</span><span class="unit">PiB</span></abbr>' => c('axe_pretty_bytes', ['value' => 123456789123456789, 'locale' => "en_US"]),
            '<abbr title="9,223,372,036,854,775,807 bytes" class="pretty"><span class="value">8</span><span class="unit">EiB</span></abbr>' => c('axe_pretty_bytes', ['value' => 9223372036854775807, 'locale' => "en_US"]),
            '<abbr title="~9,223,372,036,854,780,000 bytes" class="pretty"><span class="value">8</span><span class="unit">EiB</span></abbr>' => c('axe_pretty_bytes', ['value' => 9223372036854775808.0, 'locale' => "en_US"]),
            '<abbr title="~123,456,789,123,457,000,000 bytes" class="pretty"><span class="value">107.08</span><span class="unit">EiB</span></abbr>' => c('axe_pretty_bytes', ['value' => 123456789123456789123.0, 'locale' => "en_US"]),
            '<abbr title="~123,456,789,123,457,000,000,000 bytes" class="pretty"><span class="value">104.57</span><span class="unit">ZiB</span></abbr>' => c('axe_pretty_bytes', ['value' => 123456789123456789123456.0, 'locale' => "en_US"]),
            '<abbr title="~123,456,789,123,457,000,000,000,000 bytes" class="pretty"><span class="value">102.12</span><span class="unit">YiB</span></abbr>' => c('axe_pretty_bytes', ['value' => 123456789123456789123456789.0, 'locale' => "en_US"]),
            '<abbr title="~123,456,789,123,457,000,000,000,000,000 bytes" class="pretty"><span class="value">102,121.06</span><span class="unit">YiB</span></abbr>' => c('axe_pretty_bytes', ['value' => 123456789123456789123456789123.0, 'locale' => "en_US"]),
        ];
        foreach ($out as $k => $v) {
            $this->assertEquals($k, (string) $v);
        }
    }
}
