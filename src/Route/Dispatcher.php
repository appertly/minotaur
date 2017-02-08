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
use Aura\Router\Matcher;
use Caridea\Container\Container;

/**
 * The final, innermost layer of the request–response dispatch queue.
 *
 * This class supports the following handlers on `Aura\Router\Route` objects:
 *
 * - Anonymous functions (closures and lambdas)
 * - An array containing a class name and a function name; this object will be
 *   retrieved from the container.
 * - A string; the object with this name in the container will be invoked.
 */
class Dispatcher implements Plugin
{
    /**
     * @var \Aura\Router\Matcher
     */
    private $matcher;
    /**
     * @var \Caridea\Container\Container
     */
    private $container;
    /**
     * @var \Psr\Http\Message\ServerRequestInterface the request, or `null`
     */
    private $lastDispatchedRequest;

    /**
     * Creates a new Dispatcher plugin
     *
     * @param \Aura\Router\Matcher $matcher The route matcher
     * @param \Caridea\Container\Container $container The dependency injection container
     */
    public function __construct(Matcher $matcher, Container $container)
    {
        $this->matcher = $matcher;
        $this->container = $container;
    }

    /**
     * Gets the plugin priority; larger means first.
     *
     * @return int The plugin priority
     */
    public function getPriority(): int
    {
        return PHP_INT_MIN;
    }

    /**
     * Gets the last request that was passed to the `__invoke` method.
     *
     * @return \Psr\Http\Message\ServerRequestInterface The last request or `null`
     */
    public function getLastDispatchedRequest(): ?Request
    {
        return $this->lastDispatchedRequest;
    }

    /**
     * Perform the actual routing and dispatch, returning the Response
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The server request
     * @param \Psr\Http\Message\ResponseInterface $response The response
     * @param callable $next - The next layer, (function (Request,Response): Response)
     * @return \Psr\Http\Message\ResponseInterface The new response
     * @throws Exception\Unroutable if route matching fails
     * @throws Exception\Uncallable if a controller method can't be invoked
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $route = $this->matcher->match($request);
        if (!$route) {
            $failedRoute = $this->matcher->getFailedRoute();
            throw Exception\Unroutable::fromRoute($failedRoute);
        }
        foreach ($route->attributes as $k => $v) {
            $request = $request->withAttribute($k, $v);
        }
        $request = $request->withAttribute('_route', $route);
        $this->lastDispatchedRequest = $request;
        $handler = $route->handler;
        if ($handler instanceof \Closure) {
            $response = $handler($request, $response);
        } elseif (is_array($handler) && count($handler) == 2) {
            list($className, $methodName) = $handler;
            $controller = $this->container->getFirst($className);
            if ($controller === null) {
                throw new Exception\Uncallable("Controller instance not found: '$className'");
            } elseif (!method_exists($controller, $methodName) && !method_exists($controller, '__call')) {
                throw new Exception\Uncallable("Controller class '$className' doesn't have method '$methodName'");
            }
            $toInvoke = [$controller, $methodName];
            $response = $toInvoke($request, $response);
        } elseif (is_string($handler)) {
            if (!$this->container->contains($handler)) {
                throw new Exception\Uncallable("The container has no object with the name '$handler'");
            }
            $toInvoke = $this->container->get($handler);
            if (!method_exists($toInvoke, '__invoke')) {
                throw new Exception\Uncallable("The object '$handler' cannot be invoked as a function");
            }
            $response = $toInvoke($request, $response);
        } else {
            throw new Exception\Uncallable("Could not invoke the handler: " . print_r($handler, true));
        }
        return $response; // forget $next, we don't care.
    }
}
