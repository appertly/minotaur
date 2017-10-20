<?php

use PHPUnit\Framework\TestCase;
use function Minotaur\Tags\fcomposited as c;
use function Minotaur\Tags\ftag as h;

class axe_heads_upTest extends TestCase
{
    public function testRender1()
    {
        $o = c('axe_heads_up', ['data-foo' => 'bar', 'status' => 'warning'], [
            h('p', [], 'Nothing')
        ]);
        $this->assertEquals(
            '<div class="heads-up" data-foo="bar"><div role="alert" class="alert alert-warning"><p>Nothing</p></div></div>',
            (string) $o
        );
    }

    public function testRender2()
    {
        $o = c('axe_heads_up', ['data-foo' => 'bar', 'status' => 'warning']);
        $this->assertEquals(
            '',
            (string) $o
        );
    }
}
