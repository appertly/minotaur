<?php

use PHPUnit\Framework\TestCase;
use function Minotaur\Tags\fcomposited as c;

class axe_selectTest extends TestCase
{
    public function testRender()
    {
        $options = [
            '1' => 'Lions',
            '2' => 'Tigers',
            '3' => 'Bears'
        ];
        $s = c('axe_select', ['id' => 'foobar', 'name' => 'test', 'value' => 2, 'options' => $options, 'blank' => true]);
        $this->assertEquals(
            '<select id="foobar" name="test"><option> </option><option value="1">Lions</option><option value="2" selected>Tigers</option><option value="3">Bears</option></select>',
            (string) $s
        );
    }
}
