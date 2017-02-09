<?php
declare(strict_types=1);
/**
 * Minotaur
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 *
 * @copyright 2015-2017 Appertly
 * @license   Apache-2.0
 */
namespace Minotaur\Tags;

use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    public function testNormal()
    {
        $lis = [
            new Tag('li', [], ["Foo"]),
            new Tag('li', [], ["Bar"]),
            new Tag('li', [], ["Baz"]),
        ];
        $ul = new Tag('ul', ['style' => 'background-color:#fff;', 'class' => 'bar'], $lis);
        $this->assertEquals('<ul style="background-color:#fff;" class="bar"><li>Foo</li><li>Bar</li><li>Baz</li></ul>', (string) $ul);
    }

    public function testRaw()
    {
        $tag = new Tag('style', ['type' => 'text/css'], ".foo {\n    background-image: url(\"test.png\");\n}\n");
        $this->assertEquals('<style type="text/css">.foo {' . "\n    background-image: url(\"test.png\");\n}\n" . '</style>', (string) $tag);
        $tag = new Tag('script', ['src' => 'foo.js']);
        $this->assertEquals('<script src="foo.js"></script>', (string) $tag);
        $tag = new Tag('script', [], ['function foobar(a, b) { return a > b; } ','console.log("Hey");']);
        $this->assertEquals('<script>function foobar(a, b) { return a > b; } console.log("Hey");</script>', (string) $tag);
    }

    public function testSingleton()
    {
        $singletons = ['area' => null, 'base' => null, 'br' => null,
            'col' => null, 'command' => null, 'embed' => null, 'hr' => null,
            'img' => null, 'input' => null, 'keygen' => null, 'link' => null,
            'meta' => null, 'param' => null, 'source' => null, 'track' => null,
            'wbr' => null];
        foreach ($singletons as $k => $_) {
            $tag = new Tag($k, ['foo' => 'bar'], ['aoeu']);
            $this->assertEquals("<$k foo=\"bar\"/>", (string) $tag);
        }
    }

    public function testClass()
    {
        $tag = new Tag('div.testing.foobar', [], ['Hello']);
        $this->assertEquals('<div class="testing foobar">Hello</div>', (string) $tag);
    }

    public function testEscape()
    {
        $tag = new Tag('p', [], ['function foobar(a, b) { return a > b; } ','console.log("Hey");']);
        $this->assertEquals('<p>function foobar(a, b) { return a &gt; b; } console.log(&quot;Hey&quot;);</p>', (string) $tag);
        $tag = new Tag('span', ['title' => 'This is "something" we love'], 123.45);
        $this->assertEquals('<span title="This is &quot;something&quot; we love">123.45</span>', (string) $tag);
    }

    public function testTrait()
    {
        $tag = new Tag('a', ['href' => 'foobar.html'], "Text goes here");
        $tag->addClass('shiny');
        $tag->conditionClass(true, 'ponyta ');
        $tag->conditionClass(false, 'nope');
        $this->assertEquals('<a href="foobar.html" class="shiny ponyta">Text goes here</a>', (string) $tag);
        $tag = new Tag('div', ['id' => 'foobar']);
        $this->assertEquals('foobar', $tag->getId());
        $tag = new Tag('div');
        $this->assertEquals(10, strlen($tag->getId()));
    }
}
