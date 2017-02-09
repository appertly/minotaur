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

/**
 * A standard HTML tag.
 */
class Tag extends Primitive
{
    use HasHtml;
    use HasAttributes;

    /**
     * @var string
     */
    private $name;

    private const SINGLETONS = ['area' => null, 'base' => null, 'br' => null,
        'col' => null, 'command' => null, 'embed' => null, 'hr' => null,
        'img' => null, 'input' => null, 'keygen' => null, 'link' => null,
        'meta' => null, 'param' => null, 'source' => null, 'track' => null,
        'wbr' => null];

    /**
     * Creates a new Tag.
     *
     * @param string $name  The tag name
     * @param array<string,mixed> $attributes  Optional. Any attributes to set.
     * @param iterable|mixed $children  The child or children to add.
     */
    public function __construct(string $name, array $attributes = [], $children = null)
    {
        list($tname, $classes) = array_pad(explode('.', $name, 2), 2, null);
        $this->name = strtolower($tname);
        $this->attributes = $attributes;
        if (!empty($classes)) {
            $this->addClass(str_replace('.', ' ', $classes));
        }
        $this->appendChild($children);
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $out = "<" . $this->name;
        foreach ($this->attributes as $k => $v) {
            if ($v !== null && $v !== false) {
                if ($v === true) {
                    $out .= ' ' . htmlspecialchars($k);
                } else {
                    $out .= ' ' . htmlspecialchars($k) . '="' .
                        htmlspecialchars((string) $v, ENT_COMPAT) . '"';
                }
            }
        }
        if (array_key_exists($this->name, self::SINGLETONS)) {
            $out .= "/>";
        } else {
            $out .= '>';
            if ($this->name === 'script' || $this->name === 'style') {
                foreach ($this->children as $child) {
                    if ($child instanceof Scalar) {
                        $out .= (string) $child->getValue();
                    }
                }
            } else {
                foreach ($this->children as $child) {
                    if ($child instanceof Contextual) {
                        $child->setAllMissingContexts($this->context);
                    }
                    $out .= $this->stringifyChild($child);
                }
            }
            $out .= '</' . $this->name . '>';
        }
        return $out;
    }
}
