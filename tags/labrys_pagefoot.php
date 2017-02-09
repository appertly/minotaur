<?php
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
use function Minotaur\Tags\fcomposited as c;

/**
 * Page Footer
 *
 * ```hack
 * <labrys:pagefoot service={$service} />
 * ```
 */
class labrys_pagefoot extends Composited
{
    protected function render(): Node
    {
        $service = $this->getAttribute('service', \Minotaur\View\Service::class);
        if ($service === null) {
            throw new \UnexpectedValueException("service attribute must not be null");
        }
        $blocks = new labrys_block_region();
        $blocks->setContext('region', 'foot');
        foreach ($service->getBlocks('foot') as $block) {
            $blocks->appendChild(
                c('labrys_block', ['block' => $block])
            );
        }
        $f = new Tag('footer.page-footer', ['role' => "contentinfo"], $blocks);
        $this->transferAllAttributes($f, ['service']);
        return $f;
    }
}
