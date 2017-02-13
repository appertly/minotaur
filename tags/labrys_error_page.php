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

use Minotaur\Tags\Composited;
use Minotaur\Tags\Frag;
use Minotaur\Tags\Node;
use Minotaur\Tags\Tag;

/**
 * Renders an Error Page.
 *
 * Accepts an attribute, `ConstMap<string,mixed> values`, which can contain the
 * following fields: `title`, `detail`, `extra`. The field `extra` itself should
 * be a `KeyedTraversable` which can contain `exception`, 'message', 'stack',
 * and `errors` (which is a `Traversable` that should contain `ConstMap` values
 * that have a `field` and `code`).
 *
 * ```hack
 * $values = Map{
 *     'title' => 'Foo',
 *     'detail' => 'Bar',
 *     'extra' => [
 *         'errors' => [
 *             Map{'field' => 'foobar', 'code' => 'REQUIRED'},
 *             Map{'field' => 'example', 'code' => 'REQUIRED'}
 *         ],
 *         'exception' => [
 *             'class' => 'RuntimeException',
 *             'message' => 'Hello World',
 *             'stack' => '…',
 *             'previous' => [
 *                 'class' => 'RuntimeException',
 *                 'message' => 'Hello World',
 *                 'stack' => '…'
 *             ]
 *         ]
 *     ]
 * };
 * return <labrys:error-page values={$values} />;
 * ```
 */
class labrys_error_page extends Composited
{
    protected function render(): Node
    {
        $values = $this->ensureAttribute('values', 'iterable', []);
        $frag = new Frag();
        $extra = $values['extra'];
        if (is_array($extra)) {
            $errors = $extra['errors'] ?? null;
            if (is_iterable($errors)) {
                $ul = new Tag('ul');
                foreach ($errors as $err) {
                    $err = is_array($err) ? $err : [];
                    $ul->appendChild(
                        new Tag('li', [], ($err['field'] ?? '') . ': ' . ($err['code'] ?? ''))
                    );
                }
                $frag->appendChild($ul);
            }
            if (array_key_exists('exception', $extra)) {
                $frag->appendChild($this->getExceptionBlock($extra['exception']));
            }
        }
        return new x_doctype(
            new Tag('html', ['lang' => "en"], [
                new Tag('head', [], [
                    new Tag('meta', ['charset' => "utf-8"]),
                    new Tag('title', [], $values['title'])
                ]),
                new Tag('body', [], [
                    new Tag('header', [], [
                        new Tag('h1', [], $values['title'])
                    ]),
                    new Tag('main', ['role' => "main"], [
                        new Tag('p', [], $values['detail']),
                        $frag
                    ])
                ])
            ])
        );
    }

    private function getExceptionBlock(array $evalues)
    {
        $frag = new Tag('div');
        if (array_key_exists('class', $evalues)) {
            $frag->appendChild(new Tag('h2', [], $evalues['class']));
        }
        if (array_key_exists('message', $evalues)) {
            $frag->appendChild(new Tag('p', [], $evalues['message']));
        }
        if (array_key_exists('stack', $evalues)) {
            $frag->appendChild(new Tag('pre', [], $evalues['stack']));
        }
        $p = $evalues['previous'] ?? null;
        if (is_array($p)) {
            $frag->appendChild($this->getExceptionBlock($p));
        }
        return $frag;
    }
}
