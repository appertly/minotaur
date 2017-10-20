<?php

use PHPUnit\Framework\TestCase;
use function Minotaur\Tags\fcomposited as c;
use function Minotaur\Tags\ftag as h;

class axe_form_groupTest extends TestCase
{
    public function testRender1()
    {
        $o = c('axe_form_group', ['for' => 'test', 'label' => 'Test', 'required' => true], [
            h('p', [], 'Nothing')
        ]);
        $this->assertEquals(
            '<div id="form-group-test" class="form-group required"><div class="form-control-label"><label for="test">Test</label></div><div class="form-control-input"><p>Nothing</p></div></div>',
            (string) $o
        );
    }

    public function testRender2()
    {
        $o = c('axe_form_group', ['for' => 'test', 'label' => 'Test', 'inline' => true], [
            h('p', [], 'Nothing')
        ]);
        $this->assertEquals(
            '<div id="form-group-test" class="form-group form-inline"><div class="form-control-label"><label for="test">Test</label></div><div class="form-control-input"><p>Nothing</p></div></div>',
            (string) $o
        );
    }
}
