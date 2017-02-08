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
 * Collects any Route plugins and runs them, returning the response.
 */
class Runner
{
    /**
     * @var \Relay\Relay The nested Runner
     */
    private $runner;

    /**
     * Creates a new Runner.
     *
     * @param $c - The container
     */
    public function __construct(\Caridea\Container\Container $c)
    {
        $plugins = $c->getByType(Plugin::class);
        usort($plugins, function ($a, $b) {
            return $b->getPriority() <=> $a->getPriority();
        });
        $relayBuilder = new \Relay\RelayBuilder();
        $this->runner = $relayBuilder->newInstance($plugins);
    }

    /**
     * Middleware request–response handling.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The server request
     * @param \Psr\Http\Message\ResponseInterface $response The response
     * @return \Psr\Http\Message\ResponseInterface The response
     */
    public function run(Request $request, Response $response): Response
    {
        $relay = $this->runner;
        return $relay($request, $response);
    }
}
