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
use function Minotaur\Tags\fcomposited as c;

/**
 * Flash messages using the `axe:heads-up` tag.
 *
 * ```hack
 * <labrys:flash-messages service={$service} />
 * ```
 */
class labrys_flash_messages extends Composited
{
    protected function render(): Node
    {
        $service = $this->getAttribute('service', \Minotaur\View\Service::class);
        $container = new Tag('div.flash-messages');
        $this->transferAllAttributes($container, ['service']);
        if ($service === null) {
            return $container;
        }
        foreach ($service->getFlashMessages() as $status => $messages) {
            $status = substr($status, 0, 4) === 'msg-' ? substr($status, 4) : 'info';
            $hu = c('axe_heads_up', ['status' => $status]);
            foreach ($messages as $message) {
                $hu->appendChild(new Tag('p', [], $message));
            }
            $container->appendChild($hu);
        }
        return $container;
    }
}
