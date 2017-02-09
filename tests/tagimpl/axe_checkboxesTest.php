<?php

use PHPUnit\Framework\TestCase;
use function Minotaur\Tags\fcomposited;

class axe_checkboxesTest extends TestCase
{
    public function testRender()
    {
        $options = ['1' => 'Lions', '2' => 'Tigers', '3' => 'Bears'];
        $out = [
            '<div id="test"><div class="form-check"><input type="checkbox" id="test-1" name="animal" value="1" class="form-check-input"/><label for="test-1" class="form-check-label">Lions</label></div><div class="form-check"><input type="checkbox" id="test-2" name="animal" value="2" class="form-check-input"/><label for="test-2" class="form-check-label">Tigers</label></div><div class="form-check"><input type="checkbox" id="test-3" name="animal" value="3" class="form-check-input"/><label for="test-3" class="form-check-label">Bears</label></div></div>' => fcomposited('axe_checkboxes', ['id' => "test", 'name' => "animal", 'options' => $options]),
            '<div id="test"><div class="form-check"><input type="checkbox" id="test-1" name="animal" value="1" checked class="form-check-input"/><label for="test-1" class="form-check-label">Lions</label></div><div class="form-check"><input type="checkbox" id="test-2" name="animal" value="2" class="form-check-input"/><label for="test-2" class="form-check-label">Tigers</label></div><div class="form-check"><input type="checkbox" id="test-3" name="animal" value="3" class="form-check-input"/><label for="test-3" class="form-check-label">Bears</label></div></div>' => fcomposited('axe_checkboxes', ['id' => "test", 'name' => "animal", 'options' => $options, 'value' => [1]]),
            '<div id="test"><div class="form-check"><input type="checkbox" id="test-1" name="animal" value="1" checked class="form-check-input"/><label for="test-1" class="form-check-label">Lions</label></div><div class="form-check"><input type="checkbox" id="test-2" name="animal" value="2" checked class="form-check-input"/><label for="test-2" class="form-check-label">Tigers</label></div><div class="form-check"><input type="checkbox" id="test-3" name="animal" value="3" class="form-check-input"/><label for="test-3" class="form-check-label">Bears</label></div></div>' => fcomposited('axe_checkboxes', ['id' => "test", 'name' => "animal", 'options' => $options, 'value' => [1, 2]]),
            '<div id="test"><span class="form-check-inline"><input type="checkbox" id="test-1" name="animal" value="1" class="form-check-input"/><label for="test-1" class="form-check-label">Lions</label></span><span class="form-check-inline"><input type="checkbox" id="test-2" name="animal" value="2" checked class="form-check-input"/><label for="test-2" class="form-check-label">Tigers</label></span><span class="form-check-inline"><input type="checkbox" id="test-3" name="animal" value="3" checked class="form-check-input"/><label for="test-3" class="form-check-label">Bears</label></span></div>' => fcomposited('axe_checkboxes', ['id' => "test", 'name' => "animal", 'options' => $options, 'value' => [2, 3], 'inline' => true]),
        ];
        foreach ($out as $k => $v) {
            $this->assertEquals($k, (string) $v);
        }
    }
}
