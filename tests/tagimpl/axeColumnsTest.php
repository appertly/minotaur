<?php

use PHPUnit\Framework\TestCase;
use function Minotaur\Tags\fcomposited as c;
use function Minotaur\Tags\ftag as h;

class axe_columnsTest extends TestCase
{
    public function testRender1()
    {
        $c = c('axe_columns', [], [
            h('p.foo', [], 'bar'),
            h('p.bar', [], 'foo'),
        ]);
        $this->assertEquals(
            '<div class="columns clearfix"><div class="column"><p class="foo">bar</p></div><div class="column"><p class="bar">foo</p></div></div>',
            (string) $c
        );
    }

    public function testRender2()
    {
        $c = c('axe_columns', ['golden' => true, 'big' => 'left'], [
            h('p.foo', [], 'bar'),
            h('p.bar', [], 'foo'),
        ]);
        $this->assertEquals(
            '<div class="columns clearfix columns-golden big-left"><div class="column"><p class="foo">bar</p></div><div class="column"><p class="bar">foo</p></div></div>',
            (string) $c
        );
    }
}
