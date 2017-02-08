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
namespace Minotaur\Acl;

use PHPUnit\Framework\TestCase;
use Mockery as M;

class GatekeeperTest extends TestCase
{
    public function testAssert()
    {
        $subject = \Caridea\Acl\Subject::role('user');
        $principal = \Caridea\Auth\Principal::get('foobar@example.com', []);
        $psubject = \Caridea\Acl\Subject::principal('foobar@example.com');

        $resolver = M::mock(SubjectResolver::class);
        $resolver->shouldReceive('getSubjects')->withArgs([$principal])->andReturn([$subject]);

        $target = new \Caridea\Acl\Target('foo', 'bar');
        $aclService = M::mock(\Caridea\Acl\Service::class);

        $aclService->shouldReceive('assert')->andReturnUsing(function ($a, $b, $c) use ($subject, $psubject, $target) {
            $this->assertEquals($target, $c);
            $this->assertEquals('write', $b);
            $this->assertContains($subject, $a);
            return true;
        })->once();

        $object = new Gatekeeper($aclService, $principal, [$resolver]);
        $object->assert('write', 'foo', 'bar');

        M::close();
    }

    public function testCan()
    {
        $principal = \Caridea\Auth\Principal::get('foobar@example.com', []);
        $subject = \Caridea\Acl\Subject::role('user');
        $psubject = \Caridea\Acl\Subject::principal($principal->getUsername());
        $aclService = M::mock(\Caridea\Acl\Service::class);
        $target = new \Caridea\Acl\Target('foo', 'bar');
        $aclService->shouldReceive('can')->andReturnUsing(function ($a, $b, $c) use ($subject, $psubject, $target) {
            $this->assertEquals($target, $c);
            $this->assertEquals('write', $b);
            $this->assertContains($subject, $a);
            return true;
        });
        $resolver = M::mock(SubjectResolver::class);
        $resolver->shouldReceive('getSubjects')->withArgs([$principal])->andReturn([$subject]);
        $object = new Gatekeeper($aclService, $principal, [$resolver]);

        $this->assertTrue($object->can('write', 'foo', 'bar'));

        M::close();
    }
}
