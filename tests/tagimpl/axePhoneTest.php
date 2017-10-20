<?php

use PHPUnit\Framework\TestCase;
use function Minotaur\Tags\fcomposited as c;
use function Minotaur\Tags\ftag as h;

class axe_phoneTest extends TestCase
{
    public function testRender1()
    {
        $c = c('axe_phone_number', ['phone' => '8005551234'], [
            h('span', [], 'foobar'),
        ]);
        $this->assertEquals(
            '<a href="tel:8005551234"><span>foobar</span></a>',
            (string) $c
        );
    }

    public function testRender2()
    {
        $c = c('axe_phone_number', ['phone' => '8005551234']);
        $this->assertEquals(
            '<a href="tel:8005551234">8005551234</a>',
            (string) $c
        );
    }

    public function testRender3()
    {
        $c = c('axe_phone_number', [], ['Hey']);
        $this->assertEquals(
            'Hey',
            (string) $c
        );
    }
}
