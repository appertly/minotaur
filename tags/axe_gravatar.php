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
 * Displays a Gravatar user image
 */
class axe_gravatar extends Composited
{
    const URL = "https://secure.gravatar.com/avatar/";

    protected function render(): Node
    {
        $hash = md5(strtolower(trim((string) $this->getAttribute('email'))));
        $qs = [
            'd' => $this->ensureAttribute('default', 'string', 'identicon'),
            'r' => $this->ensureAttribute('rating', 'string', 'g')
        ];
        $size = $this->ensureAttribute('size', 'int', 0);
        if ($size > 0) {
            $qs['s'] = $size;
        }
        $url = $this->ensureAttribute('url', 'string', self::URL) . $hash . '?' . http_build_query($qs);
        $img = new Tag('img', ['src' => $url, 'alt' => "User avatar"]);
        if ($size > 0) {
            $img->setAttribute('width', $size);
            $img->setAttribute('height', $size);
        }
        $span = new Tag('span.gravatar', ['role' => "presentation"], $img);
        $this->transferAllAttributes($span, ['email', 'url', 'default', 'rating', 'size']);
        return $span;
    }
}
