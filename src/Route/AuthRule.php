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
use Caridea\Auth\Principal;
use Aura\Router\Route;
use Aura\Router\Rule\RuleInterface;

/**
 * Aura Router rule to test user is authenticated.
 */
class AuthRule implements RuleInterface
{
    /**
     * Check if the Request matches the Route.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The HTTP request
     * @param \Aura\Router\Route $route The route.
     * @return bool `true` on success, `false` on failure
     */
    public function __invoke(Request $request, Route $route): bool
    {
        return !$route->auth || !$this->getPrincipal($request)->isAnonymous();
    }

    /**
     * Gets the stored principal, or the anonymous user if none was found.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The HTTP request
     * @return \Caridea\Auth\Principal The authenticated principal
     */
    protected function getPrincipal(Request $request): Principal
    {
        $principal = $request->getAttribute('principal', Principal::getAnonymous());
        if (!($principal instanceof Principal)) {
            throw new \UnexpectedValueException("Type mismatch: principal");
        }
        return $principal;
    }
}
