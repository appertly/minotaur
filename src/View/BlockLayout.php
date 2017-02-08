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
namespace Minotaur\View;

/**
 * Stores block settings.
 */
class BlockLayout
{
    /**
     * @var array<string,array<string,int>>
     */
    private $blocks = [];

    /**
     * Adds a block definition to this layout.
     *
     * @param $region - The block region
     * @param $order - The display order, smallest shows up first
     * @param $name - The name of the block object in the container
     * @return self provides a fluent interface
     */
    public function add(string $region, int $order, string $name): self
    {
        if (!array_key_exists($region, $this->blocks)) {
            $this->blocks[$region] = [];
        }
        $this->blocks[$region][$name] = $order;
        return $this;
    }

    /**
     * Gets the blocks defined in a region.
     *
     * @param string $region The block region
     * @return array<string> The block object names in display order
     */
    public function get(string $region): array
    {
        $blocks = $this->blocks[$region] ?? [];
        asort($blocks);
        return array_keys($blocks);
    }

    /**
     * Gets all block definitions.
     *
     * @return array<string,array<string>> The block definitions
     */
    public function getAll(): array
    {
        $blocks = [];
        foreach ($this->blocks as $region => $names) {
            $a = $names;
            asort($a);
            $blocks[$region] = array_keys($a);
        }
        return $blocks;
    }

    /**
     * Adds all the block definitions from another block into this one.
     *
     * @param $other - The other object
     * @return self provides a fluent interface
     */
    public function merge(BlockLayout $other): self
    {
        foreach ($other->blocks as $region => $blocks) {
            if (!array_key_exists($region, $this->blocks)) {
                $this->blocks[$region] = [];
            }
            $this->blocks[$region] = array_merge($this->blocks[$region], $blocks);
        }
        return $this;
    }
}
