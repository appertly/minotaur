<?php

use PHPUnit\Framework\TestCase;
use function Minotaur\Tags\fcomposited as c;
use function Minotaur\Tags\ftag as h;

class axe_emailTest extends TestCase
{
    public function testRender1()
    {
        $c = c('axe_email', ['email' => 'nobody@example.com'], [
            h('span', [], 'foobar'),
        ]);
        $this->assertEquals(
            '<a href="mailto:nobody@example.com"><span>foobar</span></a>',
            (string) $c
        );
    }

    public function testRender2()
    {
        $c = c('axe_email', ['email' => 'nobody@example.com']);
        $this->assertEquals(
            '<a href="mailto:nobody@example.com">nobody@example.com</a>',
            (string) $c
        );
    }

    public function testRender3()
    {
        $c = c('axe_email', [], ['Hey']);
        $this->assertEquals(
            'Hey',
            (string) $c
        );
    }
}
