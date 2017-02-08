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

use PHPUnit\Framework\TestCase;
use Aura\Router\Matcher;
use Aura\Router\Route;
use Aura\Router\Rule\Accepts;
use Aura\Router\Rule\Allows;
use Aura\Router\Rule\Path;
use Aura\Router\Rule\RuleIterator;
use Caridea\Container\Builder;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\NullLogger;

class DispatcherTest extends TestCase
{
    private $header;

    public function __construct(string $header = 'foobar')
    {
        $this->header = $header;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        return $response->withHeader('X-Unit-Test', $this->header);
    }

    /**
     * @expectedException \Minotaur\Route\Exception\Unroutable
     */
    public function testUnroutable()
    {
        $routeRules = new RuleIterator([new Path(), new Allows(), new Accepts()]);
        $map = new \Aura\Router\Map(new Route());
        $map->get('only.get', '/foo/bar', [self::class, 'postMethod']);
        $matcher = new Matcher($map, new NullLogger(), $routeRules);

        $builder = new Builder();
        $container = $builder->build(null);

        $object = new Dispatcher($matcher, $container);

        $uri = new \Zend\Diactoros\Uri('https://example.com/foo/bar');
        $request = new \Zend\Diactoros\ServerRequest([], [], $uri, 'POST');
        $response = new \Zend\Diactoros\Response();

        $object->__invoke($request, $response, function ($req, $res) {
            return $res;
        });
    }


    public function testNormal()
    {
        $routeRules = new RuleIterator([new Path(), new Allows(), new Accepts()]);
        $map = new \Aura\Router\Map(new Route());
        $map->get('only.get', '/foo/bar', function ($req, $res) {
            return $res->withHeader('X-Unit-Test', 'foo');
        });
        $matcher = new Matcher($map, new NullLogger(), $routeRules);

        $builder = new Builder();
        $container = $builder->build(null);

        $object = new Dispatcher($matcher, $container);

        $uri = new \Zend\Diactoros\Uri('https://example.com/foo/bar');
        $request = new \Zend\Diactoros\ServerRequest([], [], $uri, 'GET');
        $response = new \Zend\Diactoros\Response();

        $res = $object->__invoke($request, $response, function ($req, $res) {
            return $res;
        });
        $req2 = $object->getLastDispatchedRequest();

        $this->assertInstanceOf(\Zend\Diactoros\ServerRequest::class, $req2);
        $this->assertSame($uri, $req2->getUri());
        $this->assertEquals('foo', $res->getHeaderLine('X-Unit-Test'));
    }


    public function testContainer1()
    {
        $routeRules = new RuleIterator([new Path(), new Allows(), new Accepts()]);
        $map = new \Aura\Router\Map(new Route());
        $map->get('only.get', '/foo/bar', [self::class, '__invoke']);
        $matcher = new Matcher($map, new NullLogger(), $routeRules);

        $builder = new Builder();
        $builder->lazy('dispatcherController', self::class, function ($c) {
            return new DispatcherTest('herpderp');
        });
        $container = $builder->build(null);

        $object = new Dispatcher($matcher, $container);

        $uri = new \Zend\Diactoros\Uri('https://example.com/foo/bar');
        $request = new \Zend\Diactoros\ServerRequest([], [], $uri, 'GET');
        $response = new \Zend\Diactoros\Response();

        $res = $object->__invoke($request, $response, function ($req, $res) {
            return $res;
        });
        $this->assertEquals('herpderp', $res->getHeaderLine('X-Unit-Test'));
    }


    public function testContainer2()
    {
        $routeRules = new RuleIterator([new Path(), new Allows(), new Accepts()]);
        $map = new \Aura\Router\Map(new Route());
        $map->get('only.get', '/foo/bar', 'dispatcherController');
        $matcher = new Matcher($map, new NullLogger(), $routeRules);

        $builder = new Builder();
        $builder->lazy('dispatcherController', self::class, function ($c) {
            return new DispatcherTest();
        });
        $container = $builder->build(null);

        $object = new Dispatcher($matcher, $container);

        $uri = new \Zend\Diactoros\Uri('https://example.com/foo/bar');
        $request = new \Zend\Diactoros\ServerRequest([], [], $uri, 'GET');
        $response = new \Zend\Diactoros\Response();

        $res = $object->__invoke($request, $response, function ($req, $res) {
            return $res;
        });
        $this->assertEquals('foobar', $res->getHeaderLine('X-Unit-Test'));
    }

    /**
     * @expectedException \Minotaur\Route\Exception\Uncallable
     * @expectedExceptionMessage The container has no object with the name 'dispatcherController'
     */
    public function testContainer3()
    {
        $routeRules = new RuleIterator([new Path(), new Allows(), new Accepts()]);
        $map = new \Aura\Router\Map(new Route());
        $map->get('only.get', '/foo/bar', 'dispatcherController');
        $matcher = new Matcher($map, new NullLogger(), $routeRules);

        $builder = new Builder();
        $container = $builder->build(null);

        $object = new Dispatcher($matcher, $container);

        $uri = new \Zend\Diactoros\Uri('https://example.com/foo/bar');
        $request = new \Zend\Diactoros\ServerRequest([], [], $uri, 'GET');
        $response = new \Zend\Diactoros\Response();

        $object->__invoke($request, $response, function ($req, $res) {
            return $res;
        });
    }

    /**
     * @expectedException \Minotaur\Route\Exception\Uncallable
     * @expectedExceptionMessage The object 'dispatcherController' cannot be invoked as a function
     */
    public function testContainer4()
    {
        $routeRules = new RuleIterator([new Path(), new Allows(), new Accepts()]);
        $map = new \Aura\Router\Map(new Route());
        $map->get('only.get', '/foo/bar', 'dispatcherController');
        $matcher = new Matcher($map, new NullLogger(), $routeRules);

        $builder = new Builder();
        $builder->lazy('dispatcherController', \SplObjectStorage::class, function ($c) {
            return new \SplObjectStorage();
        });
        $container = $builder->build(null);

        $object = new Dispatcher($matcher, $container);

        $uri = new \Zend\Diactoros\Uri('https://example.com/foo/bar');
        $request = new \Zend\Diactoros\ServerRequest([], [], $uri, 'GET');
        $response = new \Zend\Diactoros\Response();

        $object->__invoke($request, $response, function ($req, $res) {
            return $res;
        });
    }

    /**
     * @expectedException \Minotaur\Route\Exception\Uncallable
     * @expectedExceptionMessage Controller class 'Minotaur\Route\DispatcherTest' doesn't have method 'foobar'
     */
    public function testContainer5()
    {
        $routeRules = new RuleIterator([new Path(), new Allows(), new Accepts()]);
        $map = new \Aura\Router\Map(new Route());
        $map->get('only.get', '/foo/bar', [self::class, 'foobar']);
        $matcher = new Matcher($map, new NullLogger(), $routeRules);

        $builder = new Builder();
        $builder->lazy('dispatcherController', self::class, function ($c) {
            return new DispatcherTest();
        });
        $container = $builder->build(null);

        $object = new Dispatcher($matcher, $container);

        $uri = new \Zend\Diactoros\Uri('https://example.com/foo/bar');
        $request = new \Zend\Diactoros\ServerRequest([], [], $uri, 'GET');
        $response = new \Zend\Diactoros\Response();

        $object->__invoke($request, $response, function ($req, $res) {
            return $res;
        });
    }

    /**
     * @expectedException \Minotaur\Route\Exception\Uncallable
     * @expectedExceptionMessage Controller instance not found: 'Minotaur\Route\DispatcherTest'
     */
    public function testContainer6()
    {
        $routeRules = new RuleIterator([new Path(), new Allows(), new Accepts()]);
        $map = new \Aura\Router\Map(new Route());
        $map->get('only.get', '/foo/bar', [self::class, '__invoke']);
        $matcher = new Matcher($map, new NullLogger(), $routeRules);

        $builder = new Builder();
        $container = $builder->build(null);

        $object = new Dispatcher($matcher, $container);

        $uri = new \Zend\Diactoros\Uri('https://example.com/foo/bar');
        $request = new \Zend\Diactoros\ServerRequest([], [], $uri, 'GET');
        $response = new \Zend\Diactoros\Response();

        $object->__invoke($request, $response, function ($req, $res) {
            return $res;
        });
    }

    /**
     * @expectedException \Minotaur\Route\Exception\Uncallable
     * @expectedExceptionMessageRegExp /^Could not invoke the handler: /
     */
    public function testContainer7()
    {
        $routeRules = new RuleIterator([new Path(), new Allows(), new Accepts()]);
        $map = new \Aura\Router\Map(new Route());
        $map->get('only.get', '/foo/bar', [1, 2, 3]);
        $matcher = new Matcher($map, new NullLogger(), $routeRules);

        $builder = new Builder();
        $container = $builder->build(null);

        $object = new Dispatcher($matcher, $container);

        $uri = new \Zend\Diactoros\Uri('https://example.com/foo/bar');
        $request = new \Zend\Diactoros\ServerRequest([], [], $uri, 'GET');
        $response = new \Zend\Diactoros\Response();

        $object->__invoke($request, $response, function ($req, $res) {
            return $res;
        });
    }
}
