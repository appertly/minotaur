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

/**
 * Immutable access control helper.
 *
 * This class determines the user's subjects upon instantiation and stores them
 * for its duration.
 */
class Gatekeeper
{
    /**
     * @var \Caridea\Acl\Service
     */
    private $aclService;
    /**
     * @var \Caridea\Auth\Principal
     */
    private $principal;
    /**
     * @var array<\Caridea\Acl\Subject>
     */
    private $subjects;

    /**
     * Creates a new Gatekeeper.
     *
     * @param \Caridea\Acl\Service $aclService The ACL service
     * @param \Caridea\Auth\Principal $principal The authenticated principal
     * @param array<\Minotaur\Acl\SubjectResolver> $subjectResolvers Any additional subject resolvers
     */
    public function __construct(
        \Caridea\Acl\Service $aclService,
        \Caridea\Auth\Principal $principal,
        array $subjectResolvers
    ) {
        $this->aclService = $aclService;
        $this->principal = $principal;
        $subjects = [\Caridea\Acl\Subject::principal((string)$principal->getUsername())];
        foreach ($subjectResolvers as $resolver) {
          foreach($resolver->getSubjects($principal) as $subject){
            $subjects[] = $subject;
          }
        }
        $this->subjects = $subjects;
    }

    /**
     * Determines if the currently authenticated user can access the resource.
     *
     * @param $verb - The verb (e.g. 'read', 'write')
     * @param $type - The type of object
     * @param $id - The object identifier
     * @throws \Caridea\Acl\Exception\Forbidden If the user has no access
     */
    public function assert(string $verb, string $type, $id): void
    {
        $this->aclService->assert(
            $this->subjects,
            $verb,
            new \Caridea\Acl\Target($type, $id)
        );
    }

    /**
     * Determines if the currently authenticated user can access the resources.
     *
     * @param string $verb The verb (e.g. 'read', 'write')
     * @param string $type The type of object
     * @param iterable<mixed> $ids The object identifiers
     * @throws \Caridea\Acl\Exception\Forbidden If the user has no access
     */
    public function assertAll(string $verb, string $type, iterable $ids): void
    {
        $targets = array_map(function ($a) use ($type) {
            return new \Caridea\Acl\Target($type, $a);
        }, is_array($ids) ? $ids : iterator_to_array($ids));
        $acls = $this->aclService->getAll($targets, $this->subjects);
        foreach ($acls as $acl) {
            if (!$acl->can($this->subjects, $verb)) {
                throw new \Caridea\Acl\Exception\Forbidden("Access denied to $verb " . (string)$acl->getTarget());
            }
        }
    }

    /**
     * Determines if the currently authenticated user can access the resource.
     *
     * @param string $verb The verb (e.g. 'read', 'write')
     * @param string $type The type of object
     * @param mixed $id The object identifier
     * @return bool Whether the user has access
     */
    public function can(string $verb, string $type, $id): bool
    {
        return $this->aclService->can(
            $this->subjects,
            $verb,
            new \Caridea\Acl\Target($type, $id)
        );
    }
}
