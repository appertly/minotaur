<?php

use PHPUnit\Framework\TestCase;
use function Minotaur\Tags\fcomposited as c;
use function Minotaur\Tags\ftag as h;

class SideBoxTest extends TestCase
{
    public function testRender()
    {
        $out = [
            '<section class="side-box"><header class="side-box-header"><h1>Hello</h1><div role="toolbar" class="side-box-toolbar"><button>Yup</button></div></header><div class="side-box-contents"><p>Stuff</p></div></section>'
                => c('axe_side_box', ['label' => "Hello"], [c('axe_toolbar', [], [h('button', [], 'Yup')]), h('p', [], 'Stuff')]),
            '<section class="side-box"><header class="side-box-header"><h1>Hello</h1></header><div class="side-box-contents"><p>Stuff</p></div></section>'
                => c('axe_side_box', ['label' => "Hello"], [h('p', [], 'Stuff')]),
            '<section class="side-box"><header class="side-box-header"><h1>Hello</h1></header><div class="side-box-contents"></div></section>'
                => c('axe_side_box', ['label' => "Hello"]),
            '<section class="side-box"><header class="side-box-header"><h1>Hello</h1><div role="toolbar" class="side-box-toolbar"><button>Nope</button></div></header><div class="side-box-contents"></div></section>'
                => c('axe_side_box', ['label' => "Hello"], [c('axe_toolbar', [], [h('button', [], 'Nope')])]),
            '<section class="side-box"><header class="side-box-header"><h1>Hello</h1><div role="toolbar" class="side-box-toolbar"><button>Nope</button></div></header><div class="side-box-contents"><p>Stuff</p>Hello</div></section>'
                => c('axe_side_box', ['label' => "Hello"], [c('axe_toolbar', [], [h('button', [], 'Nope')]), h('p', [], 'Stuff'), "Hello",]),
        ];
        foreach ($out as $k => $v) {
            $this->assertEquals($k, (string) $v);
        }
    }
}
