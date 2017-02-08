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

class RescuerTest extends TestCase
{

    public function testRun1()
    {
        $object = new Rescuer(['debug' => true]);
        $status = 406;
        $next = function ($req, $res) use ($status) {
            try {
                $c = new \Caridea\Container\Objects([]);
                $c->named('foo', \RuntimeException::class);
                return $res;
            } catch (\Exception $e) {
                throw new \Minotaur\Route\Exception\Unroutable("Could not route", $status, $e);
            }
        };
        $server = ['HTTP_ACCEPT' => 'application/json'];
        $request = new \Zend\Diactoros\ServerRequest($server);
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertEquals($status, $out->getStatusCode());
        $details = json_decode((string)$out->getBody(), true);
        $this->assertEquals($status, $details['status']);
        $this->assertEquals("Could not route", $details['title']);
        $this->assertEquals("We don't have any content available in the MIME type you specified in your Accept header. Try specifying additional MIME types.", $details['detail']);
        $this->assertEquals(\Minotaur\Route\Exception\Unroutable::class, $details['exception']['class']);
        $this->assertEquals("Could not route", $details['exception']['message']);
        $this->assertEquals(\UnexpectedValueException::class, $details['exception']['previous']['class']);
        $this->assertEquals("A RuntimeException was requested, but null was found", $details['exception']['previous']['message']);
    }


    public function testRun2()
    {
        $this->markTestSkipped(); // TODO figure out XHP replacement
        $object = new Rescuer(['debug' => true]);
        $status = 405;
        $next = function ($req, $res) use ($status) {
            throw new \Minotaur\Route\Exception\Unroutable("Method Not Allowed", $status, null, ['Allows' => 'application/json']);
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertEquals('application/json', $out->getHeaderLine('Allows'));
        $this->assertEquals($status, $out->getStatusCode());
        $details = (string)$out->getBody();
        $body = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
            . '<title>Method Not Allowed</title></head><body><header>'
            . '<h1>Method Not Allowed</h1></header><main role="main">'
            . "<p>You can't use that HTTP method for this URL. "
            . "Check the Allow response header for the ones you can.</p>"
            . "<div><h2>Minotaur\Route\Exception\Unroutable</h2><p>Method Not Allowed</p><pre>#0";
        $assert->bool(substr($details, 0, strlen($body)) === $body)->is(true);
    }


    public function testRun3()
    {
        $this->markTestSkipped(); // TODO figure out XHP replacement
        $object = new Rescuer(['debug' => true]);
        $status = 404;
        $next = function ($req, $res) use ($status) {
            throw new \Minotaur\Route\Exception\Unroutable("Not Found", $status);
        };
        $request = new \Zend\Diactoros\ServerRequest();
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertEquals('Not Found', $out->getReasonPhrase());
        $this->assertEquals($status, $out->getStatusCode());
    }


    public function testRun4()
    {
        $object = new Rescuer(['debug' => true]);
        $status = 404;
        $next = function ($req, $res) use ($status) {
            throw new \Caridea\Dao\Exception\Unretrievable();
        };
        $server = ['HTTP_ACCEPT' => 'application/json'];
        $request = new \Zend\Diactoros\ServerRequest($server);
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertEquals('Not Found', $out->getReasonPhrase());
        $this->assertEquals($status, $out->getStatusCode());
        $details = json_decode((string)$out->getBody(), true);
        $this->assertEquals($status, $details['status']);
        $this->assertEquals("Resource Not Found", $details['title']);
        $this->assertEquals("We don't have anything at this URL. Double-check the URL you requested.", $details['detail']);
    }


    public function testRun5()
    {
        $object = new Rescuer(['debug' => true]);
        $status = 403;
        $next = function ($req, $res) use ($status) {
            throw new \Caridea\Acl\Exception\Forbidden();
        };
        $server = ['HTTP_ACCEPT' => 'application/json'];
        $request = new \Zend\Diactoros\ServerRequest($server);
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertEquals('Forbidden', $out->getReasonPhrase());
        $this->assertEquals($status, $out->getStatusCode());
        $details = json_decode((string)$out->getBody(), true);
        $this->assertEquals($status, $details['status']);
        $this->assertEquals("Access Denied", $details['title']);
        $this->assertEquals("You are not allowed to perform this action.", $details['detail']);
    }


    public function testRun6()
    {
        $object = new Rescuer(['debug' => true]);
        $status = 409;
        $next = function ($req, $res) use ($status) {
            throw new \Caridea\Dao\Exception\Conflicting();
        };
        $server = ['HTTP_ACCEPT' => 'application/json'];
        $request = new \Zend\Diactoros\ServerRequest($server);
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertEquals('Conflict', $out->getReasonPhrase());
        $this->assertEquals($status, $out->getStatusCode());
        $details = json_decode((string)$out->getBody(), true);
        $this->assertEquals($status, $details['status']);
        $this->assertEquals("Concurrent Modification", $details['title']);
        $this->assertEquals("Someone else saved changes to this same data while you were editing. Try your request again using the latest copy of the record.", $details['detail']);
    }


    public function testRun7()
    {
        $object = new Rescuer(['debug' => true]);
        $status = 409;
        $next = function ($req, $res) use ($status) {
            throw new \Caridea\Dao\Exception\Duplicative();
        };
        $server = ['HTTP_ACCEPT' => 'application/json'];
        $request = new \Zend\Diactoros\ServerRequest($server);
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertEquals('Conflict', $out->getReasonPhrase());
        $this->assertEquals($status, $out->getStatusCode());
        $details = json_decode((string)$out->getBody(), true);
        $this->assertEquals($status, $details['status']);
        $this->assertEquals("Constraint Violation", $details['title']);
        $this->assertEquals("The data you submitted violates unique constraints. Most likely, this is a result of an existing record with similar data. Double-check existing records and try again.", $details['detail']);
    }


    public function testRun8()
    {
        $object = new Rescuer(['debug' => true]);
        $status = 422;
        $next = function ($req, $res) use ($status) {
            throw new \Caridea\Validate\Exception\Invalid(['foobar' => 'REQUIRED']);
        };
        $server = ['HTTP_ACCEPT' => 'application/json'];
        $request = new \Zend\Diactoros\ServerRequest($server);
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertEquals('Unprocessable Entity', $out->getReasonPhrase());
        $this->assertEquals($status, $out->getStatusCode());
        $details = json_decode((string)$out->getBody(), true);
        $this->assertEquals($status, $details['status']);
        $this->assertEquals("Data Validation Failure", $details['title']);
        $this->assertEquals("There was a problem with the data you submitted. Review the messages for each field and try again.", $details['detail']);
        $this->assertEquals([['field' => 'foobar', 'code' => 'REQUIRED']], $details['errors']);
    }


    public function testRun9()
    {
        $object = new Rescuer(['debug' => true]);
        $status = 423;
        $next = function ($req, $res) use ($status) {
            throw new \Caridea\Dao\Exception\Locked();
        };
        $server = ['HTTP_ACCEPT' => 'application/json'];
        $request = new \Zend\Diactoros\ServerRequest($server);
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertEquals('Locked', $out->getReasonPhrase());
        $this->assertEquals($status, $out->getStatusCode());
        $details = json_decode((string)$out->getBody(), true);
        $this->assertEquals($status, $details['status']);
        $this->assertEquals("Resource Locked", $details['title']);
        $this->assertEquals("This data is locked. You have permission, but it is no longer allowed to be changed.", $details['detail']);
    }


    public function testRun10()
    {
        $object = new Rescuer(['debug' => true]);
        $status = 500;
        $next = function ($req, $res) use ($status) {
            throw new \RuntimeException("Weird stuff");
        };
        $server = ['HTTP_ACCEPT' => 'application/json'];
        $request = new \Zend\Diactoros\ServerRequest($server);
        $response = new \Zend\Diactoros\Response();
        $out = $object->__invoke($request, $response, $next);
        $this->assertEquals('Internal Server Error', $out->getReasonPhrase());
        $this->assertEquals($status, $out->getStatusCode());
        $details = json_decode((string)$out->getBody(), true);
        $this->assertEquals($status, $details['status']);
        $this->assertEquals("Internal Server Error", $details['title']);
        $this->assertEquals("It looks like we have a problem on our end! Our staff has been notified. Please try again later.", $details['detail']);
    }
}
