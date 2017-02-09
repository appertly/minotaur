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
 * Holds scalar PHP values.
 */
final class Scalar implements Child
{
    /**
     * @var string|float|int
     */
    private $value;

    /**
     * Creates a new Scalar.
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Gets the scalar value. We may not need this method.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return htmlspecialchars((string) $this->value);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): string
    {
        return htmlspecialchars((string) $this->value);
    }
}
