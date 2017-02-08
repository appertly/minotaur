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

/**
 * Interface for system module.
 */
abstract class Module
{
    /**
     * Gets the module metadata.
     *
     * This array should contain at the very least the keys `name`, `version`,
     * `author`, and `description`. Feel free to add any additional fields you
     * like, such as `license`, or `copyright`.
     *
     * @return array<string,string> A Map of the module metadata
     */
    abstract public function getMeta(): array;

    /**
     * Gets static configuration settings.
     *
     * @return array<string,mixed> The module configuration settings
     */
    public function getConfig(): array
    {
        return [];
    }

    /**
     * Allows the module to register classes in the backend container.
     *
     * This method must only invoke the `eager`, `lazy`, and `proto` methods. It
     * should *not* attempt to build the container.
     *
     * @param $builder - The backend dependency injection container
     * @param $properties - The configuration settings
     */
    public function setupBackend(\Caridea\Container\Builder $builder, \Caridea\Container\Properties $properties): void
    {
    }

    /**
     * Allows the module to register classes in the frontend container.
     *
     * This method must only invoke the `eager`, `lazy`, and `proto` methods. It
     * should *not* attempt to build the container.
     *
     * @param $builder - The frontend dependency injection container
     * @param $properties - The configuration settings
     */
    public function setupFrontend(\Caridea\Container\Builder $builder, \Caridea\Container\Properties $properties): void
    {
    }
}
