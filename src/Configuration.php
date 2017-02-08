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
namespace Minotaur;

use Caridea\Container\Properties;

/**
 * A bootstrapper for reading in module and configuration info.
 *
 * This class expects a `Traversable` full of class names in the
 * `system.modules` configuration setting. Each class name *must* extend
 * `Minotaur\Module` or an `UnexpectedValueException` will be thrown.
 */
class Configuration
{
    /**
     * @var array<Module> Instantiated modules
     */
    protected $modules;
    /**
     * @var Properties The config container
     */
    protected $config;

    /**
     * Creates a new Configuration.
     *
     * This constructor expects a `Traversable` full of class names in the
     * `system.modules` configuration setting. Each class name *must* extend
     * `Minotaur\Module` or an `UnexpectedValueException` will be thrown.
     *
     * @param array $config The system configuration
     * @throws \UnexpectedValueException if a module class doesn't extend `Minotaur\Module`
     */
    public function __construct(array $config)
    {
        $this->modules = $this->createModules($config);
        $this->config = $this->createConfigContainer($config);
    }

    private function createModules(array $config)
    {
        $modules = [];
        $sysModules = $config['system.modules'] ?? null;
        if (is_iterable($sysModules)) {
            foreach ($sysModules as $className) {
                if (!is_a($className, \Minotaur\Module::class, true)) {
                    throw new \UnexpectedValueException("Not a module class: '$className'");
                } else {
                    $modules[] = new $className();
                }
            }
        }
        return $modules;
    }

    private function createConfigContainer(array $config): Properties
    {
        $sysConfig = [];
        // first set module defaults
        foreach ($this->modules as $module) {
            $sysConfig = array_merge($sysConfig, $module->getConfig());
        }
        // then bring in user-specified values
        $sysConfig = array_merge($sysConfig, $config);
        return new Properties($sysConfig);
    }

    /**
     * Gets the configuration settings container.
     *
     * @return - The config container
     */
    public function getConfigContainer(): Properties
    {
        return $this->config;
    }

    /**
     * Gets the loaded modules.
     *
     * @return array<Module> The loaded modules
     */
    public function getModules(): array
    {
        return $this->modules;
    }
}
