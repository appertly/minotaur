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
namespace Minotaur\Net\Exception;

/**
 * Exception for translation problems (e.g. json_decode on non-JSON).
 */
class Illegible extends \InvalidArgumentException implements \Minotaur\Net\Exception
{
    /**
     * @var mixed
     */
    private $argument;

    /**
     * Creates a new Illegible.
     */
    public function __construct(
        $argument = "",
        string $message = "",
        int $code = 0,
        \Exception $cause = null
    ) {
        $this->argument = $argument;
        parent::__construct($message, $code, $cause);
    }

    /**
     * Gets the illegible argument
     *
     * @return mixed The illegible argument
     */
    public function getArgument()
    {
        return $this->argument;
    }
}
