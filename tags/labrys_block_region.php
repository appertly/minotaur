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

/**
 * A region for several blocks.
 *
 * ```xml
 * <labrys:block-region>
 *     <labrys:block />
 *     <labrys:block />
 * </labrys:block-region>
 * ```
 */
class labrys_block_region extends Composited
{
    /**
     * @inheritDoc
     */
    public function appendChild($child): self
    {
        if (is_iterable($child)) {
            foreach ($child as $v) {
                $this->appendChild($v);
            }
        } elseif ($child instanceof Frag) {
            foreach ($child->getChildren() as $v) {
                $this->appendChild($v);
            }
        } elseif ($child !== null) {
            if (!($child instanceof labrys_block)) {
                throw new \InvalidArgumentException("Children must implement labrys_block");
            }
            $this->children[] = $child;
        }
        return $this;
    }

    protected function render(): Node
    {
        $t = new Tag('div.region.clearfix', [], $this->getChildren());
        $this->transferAllAttributes($t);
        return $t;
    }
}
