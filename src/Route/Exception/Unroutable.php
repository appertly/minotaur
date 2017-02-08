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
namespace Minotaur\Route\Exception;

/**
 * Exceptions for requests that cannot be routed to a controller method.
 */
class Unroutable extends \RuntimeException implements \Minotaur\Route\Exception
{
    /**
     * @var array<string,string>
     */
    private $headers;

    /**
     * Creates a new Unroutable.
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        \Exception $cause = null,
        array $headers = []
    ) {
        parent::__construct($message, $code, $cause);
        $this->headers = $headers;
    }

    /**
     * Gets the headers to set.
     *
     * @return array<string,string> associative array of header names to values
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Creates a new Unroutable based on a failed Aura Route.
     *
     * @param \Aura\Router\Route $failedRoute The failed Aura Route
     * @return static A new Unroutable
     */
    public static function fromRoute(\Aura\Router\Route $failedRoute): Unroutable
    {
        switch ($failedRoute->failedRule) {
            case \Aura\Router\Rule\Allows::class:
                $allows = ['Allow' => implode(',', $failedRoute->allows)];
                return new self("Method Not Allowed", 405, null, $allows);
            case \Aura\Router\Rule\Accepts::class:
                return new self("Not Acceptable", 406);
            case \Minotaur\Route\AuthRule::class:
                return new self("You Must Be Authenticated", 403);
            default:
                return new self("Not Found", 404);
        }
    }
}
