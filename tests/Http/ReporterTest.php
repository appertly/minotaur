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
namespace Minotaur\Http;

use PHPUnit\Framework\TestCase;
use Mockery as M;

class ReporterTest extends TestCase
{
    /**
     * @tearDown
     */
    public function tearDown()
    {
        M::close();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRun1()
    {
        $logger = M::mock(\Psr\Log\LoggerInterface::class);
        $logger->shouldReceive('log')->andReturnUsing(function ($a, $b, $c) {
            $this->assertSame(\Psr\Log\LogLevel::ERROR, $a);
            $this->assertSame('foobar', $b);
            $this->assertInstanceOf(\RuntimeException::class, $c['exception']);
        });
        $errorLogger = new \Minotaur\ErrorLogger($logger);
        $reporter = new Reporter($errorLogger);
        $next = function ($req, $res) {
            throw new \RuntimeException("foobar");
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $response = new \Zend\Diactoros\Response();
        $reporter->__invoke($request, $response, $next);
    }
}
