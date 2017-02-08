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
 * Exception for when a server response was not what we were expecting.
 */
class Unexpected extends \UnexpectedValueException implements \Minotaur\Net\Exception
{
    /**
     * @var array<string,mixed> The cURL response info
     */
    private $info;
    /**
     * @var string the response body, or `null`
     */
    private $body;

    /**
     * @param $body - The response body
     * @param $info - The cURL response info
     * @param $msg - The message
     * @param $code - The code
     * @param $previous - Any nested exception
     */
    public function __construct(?string $body, iterable $info, string $msg, int $code = 0, \Exception $previous = null)
    {
        $this->info = is_array($info) ? $info : iterator_to_array($info, true);
        $this->body = $body;
        parent::__construct($msg, $code, $previous);
    }

    /**
     * Gets the cURL handle info.
     *
     * @return array<string,mixed> the handle info
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * Gets the response body.
     *
     * @return string|null the response body
     */
    public function getBody(): ?string
    {
        return $this->body;
    }
}
