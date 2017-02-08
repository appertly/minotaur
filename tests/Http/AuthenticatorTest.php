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

class AuthenticatorTest extends TestCase
{
    public function testRun1()
    {
        $session = M::mock(\Caridea\Session\Session::class);
        $session->shouldReceive('getValues')->andReturn(new \Caridea\Session\NullMap());
        $service = new \Caridea\Auth\Service($session);
        $object = new Authenticator($service, '/auth/login');
        $next = function ($req, $res) {
            $this->assertSame(\Caridea\Auth\Principal::getAnonymous(), $req->getAttribute('principal'));
            return $res;
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertSame($response, $out);
        M::close();
    }

    public function testRun2()
    {
        $session = M::mock(\Caridea\Session\Session::class);
        $session->shouldReceive('canResume')->andReturn(true);
        $session->shouldReceive('getValues')->andReturn(new \Caridea\Session\NullMap());
        $service = new \Caridea\Auth\Service($session);
        $object = new Authenticator($service, '/auth/login');
        $next = function ($req, $res) {
            throw new \Minotaur\Route\Exception\Unroutable("Something happened", 403);
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withRequestTarget("/my/place");
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertSame('/auth/login?then=/my/place', $out->getHeaderLine('Location'));
        $this->assertEquals(303, $out->getStatusCode());
        M::close();
    }
}
