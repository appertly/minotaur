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
namespace Minotaur\Route;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Ability to influence front controller.
 */
interface Plugin
{
    /**
     * Gets the plugin priority; larger means first.
     *
     * @return int The plugin priority
     */
    public function getPriority(): int;

    /**
     * Middleware request–response handling.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The server request
     * @param \Psr\Http\Message\ResponseInterface $response The response
     * @param callable $callable A function accepting a Request and a Response, returning a Response.
     * @return \Psr\Http\Message\ResponseInterface The response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response;
}
