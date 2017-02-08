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
namespace Minotaur\Db;

use Labrys\Getter;

/**
 * Abstract DAO-backed Service
 */
abstract class AbstractDaoService implements EntityRepo
{
    /**
     * @var \Minotaur\Acl\Gatekeeper
     */
    protected $gatekeeper;
    /**
     * @var string
     */
    protected $readPermission;

    /**
     * Creates a new AbstractDaoService.
     *
     * @param $gatekeeper - The security gatekeeper
     * @param $readPermission - The ACL permission for read access
     */
    public function __construct(
        \Minotaur\Acl\Gatekeeper $gatekeeper,
        string $readPermission = 'read'
    ) {
        $this->gatekeeper = $gatekeeper;
        $this->readPermission = $readPermission;
    }

    /**
     * Gets the DAO.
     *
     * @return - The backing DAO
     */
    protected abstract function getDao(): EntityRepo;

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return $this->getDao()->getType();
    }

    /**
     * {@inheritDoc}
     */
    public function countAll(array $criteria): int
    {
        return $this->getDao()->countAll($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function findOne(array $criteria)
    {
        return $this->getDao()->findOne($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(array $criteria, \Caridea\Http\Pagination $pagination = null, bool $totalCount = false): iterable
    {
        return $this->getDao()->findAll($criteria, $pagination, $totalCount);
    }

    /**
     * {@inheritDoc}
     * @throws \Caridea\Acl\Exception\Forbidden If the user has no access
     */
    public function findById($id)
    {
        $dao = $this->getDao();
        $entity = $dao->findById($id);
        if ($entity !== null) {
            $this->gatekeeper->assert($this->readPermission, $dao->getType(), $id);
        }
        return $entity;
    }

    /**
     * {@inheritDoc}
     * @throws \Caridea\Acl\Exception\Forbidden If the user has no access
     */
    public function get($id)
    {
        return $this->getAndAssert($id, $this->readPermission);
    }

    /**
     * {@inheritDoc}
     * @throws \Caridea\Acl\Exception\Forbidden If the user has no access
     */
    public function getAll(iterable $ids): iterable
    {
        $dao = $this->getDao();
        $all = $dao->getAll($ids);
        $this->gatekeeper->assertAll($this->readPermission, $dao->getType(), $ids);
        return $all;
    }

    /**
     * {@inheritDoc}
     */
    public function getInstanceMap(iterable $entities): array
    {
        return $this->getDao()->getInstanceMap($entities);
    }

    /**
     * Gets the entity and asserts an ACL permission.
     *
     * @param $id - The entity id
     * @param $verb - The verb (e.g. 'read', 'write')
     * @return - The entity
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the document doesn't exist
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     * @throws \Caridea\Acl\Exception\Forbidden If the user has no access
     */
    protected function getAndAssert($id, string $verb)
    {
        $dao = $this->getDao();
        $entity = $dao->get($id);
        $this->gatekeeper->assert($verb, $dao->getType(), $id);
        return $entity;
    }
}
