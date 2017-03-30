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
use Minotaur\Tags\Node;
use Minotaur\Tags\Tag;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * A block of content.
 *
 * ```hack
 * <labrys:block block={$block} request={$request} />
 * ```
 */
class labrys_block extends Composited
{
    protected function render(): Node
    {
        $block = $this->ensureAttribute('block', \Minotaur\View\Block::class);
        if ($block === null) {
            throw new \UnexpectedValueException("block attribute must not be null");
        }
        $request = $this->getContext('request');
        $kid = $block->compose($request instanceof Request ? $request : null);
        $out = new Tag('div.block', [], [$kid]);
        $region = (string) $this->getContext('region');
        if ($region) {
            $out->addClass("$region-block");
        }
        $this->transferAllAttributes($out, ['block']);
        return $out;
    }
}
