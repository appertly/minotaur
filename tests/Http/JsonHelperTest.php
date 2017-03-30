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

class JsonHelperTest extends TestCase
{
    use JsonHelper;

    public function testSendItems1()
    {
        $response = new \Zend\Diactoros\Response();
        $pagination = new \Caridea\Http\Pagination(PHP_INT_MAX, 0);
        $items = ['foo', 'bar', 'baz'];
        $output = $this->sendItems($response, $items, $pagination, 3);
        $this->assertEquals(json_encode($items), (string)$output->getBody());
        $this->assertEquals('items 0-2/3', $output->getHeaderLine('Content-Range'));
    }


    public function testSendItems2()
    {
        $response = new \Zend\Diactoros\Response();
        $pagination = new \Caridea\Http\Pagination(3, 2);
        $items = ['foo', 'bar', 'baz'];
        $output = $this->sendItems($response, $items, $pagination, 5);
        $this->assertEquals(json_encode($items), (string)$output->getBody());
        $this->assertEquals('items 2-4/5', $output->getHeaderLine('Content-Range'));
    }


    public function testSendItems3()
    {
        $response = new \Zend\Diactoros\Response();
        $pagination = new \Caridea\Http\Pagination(PHP_INT_MAX, 0);
        $items = ['foo', 'bar', 'baz'];
        $output = $this->sendItems($response, $items, $pagination, PHP_INT_MAX);
        $this->assertEquals(json_encode($items), (string)$output->getBody());
        $this->assertEquals('items 0-' . (PHP_INT_MAX - 1) . '/' . PHP_INT_MAX, $output->getHeaderLine('Content-Range'));
    }


    public function testSendItems4()
    {
        $response = new \Zend\Diactoros\Response();
        $pagination = new \Caridea\Http\Pagination(PHP_INT_MAX, 5);
        $items = ['foo', 'bar', 'baz'];
        $output = $this->sendItems($response, $items, $pagination, PHP_INT_MAX);
        $this->assertEquals(json_encode($items), (string)$output->getBody());
        $this->assertEquals('items 5-' . (PHP_INT_MAX - 1) . '/' . PHP_INT_MAX, $output->getHeaderLine('Content-Range'));
    }


    public function testSendItems5()
    {
        $response = new \Zend\Diactoros\Response();
        $pagination = new \Caridea\Http\Pagination(PHP_INT_MAX, 0);
        $items = [];
        $output = $this->sendItems($response, $items, $pagination);
        $this->assertEquals(json_encode($items), (string)$output->getBody());
        $this->assertEquals('items 0-0/0', $output->getHeaderLine('Content-Range'));
    }


    public function testSendItems6()
    {
        $response = new \Zend\Diactoros\Response();
        $pagination = new \Caridea\Http\Pagination(3, 0);
        $items = new \Minotaur\Db\CursorSubset(new \ArrayIterator(['a', 'b', 'c']), 9);
        $output = $this->sendItems($response, $items, $pagination, 5);
        $this->assertEquals(json_encode($items), (string)$output->getBody());
        $this->assertEquals('items 0-2/9', $output->getHeaderLine('Content-Range'));
    }
}
