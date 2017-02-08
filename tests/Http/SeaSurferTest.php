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

class SeaSurferTest extends TestCase
{

    public function testRun0()
    {
        $plugin = M::mock(\Caridea\Session\CsrfPlugin::class);
        $plugin->shouldNotReceive('isValid');
        $errorLogger = M::mock(\Minotaur\ErrorLogger::class);
        $errorLogger->shouldNotReceive('log');
        $object = new SeaSurfer($plugin, $errorLogger);
        $next = function ($req, $res) {
            return $res;
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withMethod('GET');
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertSame($response, $out);
        M::close();
    }


    public function testRun1()
    {
        $token = 'foobarbazbiz';
        $plugin = M::mock(\Caridea\Session\CsrfPlugin::class);
        $plugin->shouldReceive('isValid')->withArgs([$token])->andReturn(false);
        $errorLogger = M::mock(\Minotaur\ErrorLogger::class);
        $errorLogger->shouldReceive('log');
        $object = new SeaSurfer($plugin, $errorLogger, 'csrfToken', 'example.com');
        $next = function ($req, $res) {
            return $res;
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withMethod('POST')
            ->withAttribute('principal', \Caridea\Auth\Principal::get('foobar', []))
            ->withHeader('Referer', 'https://example.com/test')
            ->withParsedBody(['csrfToken' => $token]);
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertInstanceOf(\Zend\Diactoros\Response::class, $out);
        ;
        $this->assertEquals(440, $out->getStatusCode());
        $this->assertEquals('Login Timeout', $out->getReasonPhrase());
        M::close();
    }


    public function testRun10()
    {
        $token = 'foobarbazbiz';
        $plugin = M::mock(\Caridea\Session\CsrfPlugin::class);
        $plugin->shouldNotReceive('isValid');
        $errorLogger = M::mock(\Minotaur\ErrorLogger::class);
        $errorLogger->shouldNotReceive('log');
        $object = new SeaSurfer($plugin, $errorLogger, 'csrfToken', 'example.com');
        $next = function ($req, $res) {
            return $res;
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withMethod('POST')
            ->withAttribute('principal', \Caridea\Auth\Principal::get('foobar', []))
            ->withHeader('Referer', 'https://example.com/test')
            ->withHeader('X-Requested-With', 'XMLHttpRequest');
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertInstanceOf(\Zend\Diactoros\Response::class, $out);
        ;
        $this->assertEquals(200, $out->getStatusCode());
        $this->assertEquals('OK', $out->getReasonPhrase());
        M::close();
    }


    public function testRun11()
    {
        $token = 'foobarbazbiz';
        $plugin = M::mock(\Caridea\Session\CsrfPlugin::class);
        $plugin->shouldNotReceive('isValid');
        $errorLogger = M::mock(\Minotaur\ErrorLogger::class);
        $errorLogger->shouldNotReceive('log');
        $object = new SeaSurfer($plugin, $errorLogger, 'csrfToken', 'example.com');
        $next = function ($req, $res) {
            return $res;
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withMethod('POST')
            ->withAttribute('principal', \Caridea\Auth\Principal::getAnonymous())
            ->withHeader('Referer', 'https://example.com/test');
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertInstanceOf(\Zend\Diactoros\Response::class, $out);
        ;
        $this->assertEquals(200, $out->getStatusCode());
        $this->assertEquals('OK', $out->getReasonPhrase());
        M::close();
    }


    public function testRun2()
    {
        $token = 'foobarbazbiz';
        $plugin = M::mock(\Caridea\Session\CsrfPlugin::class);
        $plugin->shouldReceive('isValid')->withArgs([$token])->andReturn(false);
        $errorLogger = M::mock(\Minotaur\ErrorLogger::class);
        $errorLogger->shouldReceive('log');
        $object = new SeaSurfer($plugin, $errorLogger, 'csrfToken', 'example.com');
        $next = function ($req, $res) {
            return $res;
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withMethod('POST')
            ->withAttribute('principal', \Caridea\Auth\Principal::get('foobar', []))
            ->withHeader('Referer', 'https://example.com/test')
            ->withParsedBody([]);
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertInstanceOf(\Zend\Diactoros\Response::class, $out);
        ;
        $this->assertEquals(440, $out->getStatusCode());
        $this->assertEquals('Login Timeout', $out->getReasonPhrase());
        M::close();
    }


    public function testRun3()
    {
        $token = 'foobarbazbiz';
        $plugin = M::mock(\Caridea\Session\CsrfPlugin::class);
        $errorLogger = M::mock(\Minotaur\ErrorLogger::class);
        $errorLogger->shouldReceive('log');
        $object = new SeaSurfer($plugin, $errorLogger, 'csrfToken', 'example.com');
        $next = function ($req, $res) {
            return $res;
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withMethod('PUT')
            ->withAttribute('principal', \Caridea\Auth\Principal::get('foobar', []))
            ->withHeader('Referer', 'https://example.net/test');
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertInstanceOf(\Zend\Diactoros\Response::class, $out);
        ;
        $this->assertEquals(440, $out->getStatusCode());
        $this->assertEquals('Login Timeout', $out->getReasonPhrase());
        M::close();
    }


    public function testRun7()
    {
        $token = 'foobarbazbiz';
        $plugin = M::mock(\Caridea\Session\CsrfPlugin::class);
        $errorLogger = M::mock(\Minotaur\ErrorLogger::class);
        $errorLogger->shouldReceive('log');
        $object = new SeaSurfer($plugin, $errorLogger);
        $next = function ($req, $res) {
            return $res;
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withMethod('PUT')
            ->withAttribute('principal', \Caridea\Auth\Principal::get('foobar', []))
            ->withHeader('Host', 'example.com')
            ->withHeader('Referer', 'https://example.net/test');
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertInstanceOf(\Zend\Diactoros\Response::class, $out);
        ;
        $this->assertEquals(440, $out->getStatusCode());
        $this->assertEquals('Login Timeout', $out->getReasonPhrase());
        M::close();
    }


    public function testRun8()
    {
        $token = 'foobarbazbiz';
        $plugin = M::mock(\Caridea\Session\CsrfPlugin::class);
        $errorLogger = M::mock(\Minotaur\ErrorLogger::class);
        $errorLogger->shouldReceive('log');
        $object = new SeaSurfer($plugin, $errorLogger);
        $next = function ($req, $res) {
            return $res;
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withMethod('PUT')
            ->withAttribute('principal', \Caridea\Auth\Principal::get('foobar', []))
            ->withHeader('Host', 'example.org')
            ->withHeader('X-Forwarded-Host', 'example.com')
            ->withHeader('Referer', 'https://example.net/test');
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertInstanceOf(\Zend\Diactoros\Response::class, $out);
        ;
        $this->assertEquals(440, $out->getStatusCode());
        $this->assertEquals('Login Timeout', $out->getReasonPhrase());
        M::close();
    }


    public function testRun9()
    {
        $token = 'foobarbazbiz';
        $plugin = M::mock(\Caridea\Session\CsrfPlugin::class);
        $plugin->shouldReceive('isValid')->withArgs([$token])->andReturn(true);
        $errorLogger = M::mock(\Minotaur\ErrorLogger::class);
        $errorLogger->shouldReceive('log');
        $object = new SeaSurfer($plugin, $errorLogger);
        $next = function ($req, $res) {
            return $res;
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withMethod('PUT')
            ->withAttribute('principal', \Caridea\Auth\Principal::get('foobar', []))
            ->withHeader('Host', 'example.org')
            ->withHeader('X-Forwarded-Host', 'example.com')
            ->withHeader('Referer', 'https://example.com/test')
            ->withParsedBody(['csrfToken' => $token]);
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertInstanceOf(\Zend\Diactoros\Response::class, $out);
        ;
        $this->assertEquals(200, $out->getStatusCode());
        $this->assertEquals('OK', $out->getReasonPhrase());
        M::close();
    }


    public function testRun4()
    {
        $token = 'foobarbazbiz';
        $plugin = M::mock(\Caridea\Session\CsrfPlugin::class);
        $errorLogger = M::mock(\Minotaur\ErrorLogger::class);
        $errorLogger->shouldReceive('log');
        $object = new SeaSurfer($plugin, $errorLogger, 'csrfToken', 'example.com');
        $next = function ($req, $res) {
            return $res;
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withMethod('DELETE')
            ->withAttribute('principal', \Caridea\Auth\Principal::get('foobar', []));
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertInstanceOf(\Zend\Diactoros\Response::class, $out);
        ;
        $this->assertEquals(449, $out->getStatusCode());
        $this->assertEquals('Retry With', $out->getReasonPhrase());
        M::close();
    }


    public function testRun5()
    {
        $token = 'foobarbazbiz';
        $plugin = M::mock(\Caridea\Session\CsrfPlugin::class);
        $plugin->shouldReceive('isValid')->withArgs([$token])->andReturn(true);
        $errorLogger = M::mock(\Minotaur\ErrorLogger::class);
        $errorLogger->shouldNotReceive('log');
        $object = new SeaSurfer($plugin, $errorLogger, 'csrfToken', 'example.com', false);
        $next = function ($req, $res) {
            return $res;
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withMethod('DELETE')
            ->withAttribute('principal', \Caridea\Auth\Principal::get('foobar', []))
            ->withParsedBody(['csrfToken' => $token]);
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertInstanceOf(\Zend\Diactoros\Response::class, $out);
        ;
        $this->assertEquals(200, $out->getStatusCode());
        $this->assertEquals('OK', $out->getReasonPhrase());
        M::close();
    }


    public function testRun6()
    {
        $token = 'foobarbazbiz';
        $plugin = M::mock(\Caridea\Session\CsrfPlugin::class);
        $plugin->shouldReceive('isValid')->withArgs([$token])->andReturn(true);
        $errorLogger = M::mock(\Minotaur\ErrorLogger::class);
        $errorLogger->shouldNotReceive('log');
        $object = new SeaSurfer($plugin, $errorLogger, 'csrfToken', 'example.com');
        $next = function ($req, $res) {
            return $res;
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withMethod('POST')
            ->withAttribute('principal', \Caridea\Auth\Principal::get('foobar', []))
            ->withHeader('Referer', 'https://example.com/test')
            ->withParsedBody(['csrfToken' => $token]);
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertInstanceOf(\Zend\Diactoros\Response::class, $out);
        ;
        $this->assertEquals(200, $out->getStatusCode());
        $this->assertEquals('OK', $out->getReasonPhrase());
        M::close();
    }
}
