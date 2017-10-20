<?php

use PHPUnit\Framework\TestCase;
use function Minotaur\Tags\fcomposited as c;
use function Minotaur\Tags\ftag as h;

class axe_fieldsetTest extends TestCase
{
    public function testRender1()
    {
        $c = c('axe_fieldset', ['legend' => 'Foobar'], [
            h('span', [], 'foobar'),
        ]);
        $this->assertEquals(
            '<fieldset class="fieldset-and-legend"><legend><span class="legend-text">Foobar</span></legend><div class="form-groups"><span>foobar</span></div></fieldset>',
            (string) $c
        );
    }

    public function testRender2()
    {
        $c = c('axe_fieldset', ['legend' => 'Foobar', 'inline' => true], [
            h('span', [], 'foobar'),
        ]);
        $this->assertEquals(
            '<fieldset class="inline-fieldset"><legend><span class="legend-text">Foobar</span></legend><div class="form-groups"><span>foobar</span></div></fieldset>',
            (string) $c
        );
    }
}
