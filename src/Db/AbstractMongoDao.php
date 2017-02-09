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

use MongoDB\BSON\ObjectID;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use MongoDB\Driver\WriteResult;
use Caridea\Dao\MongoDb as MongoDbDao;
use Caridea\Event\PublisherAware;
use Minotaur\Getter;

/**
 * Abstract MongoDB DAO Service
 */
abstract class AbstractMongoDao extends MongoDbDao implements EntityRepo, DbRefResolver, PublisherAware
{
    use MongoHelper;
    use \Caridea\Dao\Event\Publishing;

    /**
     * @var bool Whether to enforce optimistic locking
     */
    private $versioned = true;
    /**
     * @var bool Whether entities will be put in the first-level cache
     */
    private $caching = true;
    /**
     * @var array<string,?string> The MongoDB type map when reading records
     */
    private $typeMap = ['root' => null, 'document' => null];
    /**
     * @var \MongoDB\Driver\ReadPreference The MongoDB read preference, or `null`
     */
    private $readPreference;
    /**
     * @var \MongoDB\Driver\ReadPreference The MongoDB write concern, or `null`
     */
    private $writeConcern;
    /**
     * @var array<string,mixed> First-level cache
     */
    private $cache = [];

    /**
     * Creates a new AbstractMongoDao.
     *
     * Current accepted configuration values:
     * * `versioned` – Whether to enforce optimistic locking via a version field (default: true)
     * * `caching` – Whether to cache entities by ID (default: true)
     * * `typeMapRoot` – The type used to unserialize BSON root documents
     * * `typeMapDocument` – The type used to unserialize BSON nested documents
     * * `readPreference` – Must be a `MongoDB\Driver\ReadPreference`
     * * `writeConcern` – Must be a `MongoDB\Driver\WriteConcern`
     *
     * As for the `typeMap` options, you can see
     * [Deserialization from BSON](http://php.net/manual/en/mongodb.persistence.deserialization.php#mongodb.persistence.typemaps)
     * for more information.
     *
     * @param \MongoDB\Driver\Manager $manager The MongoDB Manager
     * @param string $collection The collection to wrap
     * @param array<string,mixed> $options Optional. Map of configuration values
     */
    public function __construct(
        Manager $manager,
        string $collection,
        array $options = null
    ) {
        parent::__construct($manager, $collection);
        if ($options !== null) {
            $this->versioned = array_key_exists('version', $options) ?
                (bool) $options['version'] : true;
            $this->caching = array_key_exists('caching', $options) ?
                (bool) $options['caching'] : true;
            if (array_key_exists('typeMapRoot', $options)) {
                $r = $options['typeMapRoot'];
                $this->typeMap['root'] = $r === null ? null : (string)$r;
            }
            if (array_key_exists('typeMapDocument', $options)) {
                $d = $options['typeMapDocument'];
                $this->typeMap['document'] = $d === null ? null : (string)$d;
            }
            $rp = $options['readPreference'] ?? null;
            if ($rp instanceof ReadPreference) {
                $this->readPreference = $rp;
            }
            $wc = $options['writeConcern'] ?? null;
            if ($wc instanceof WriteConcern) {
                $this->writeConcern = $wc;
            }
        }
        $this->publisher = new \Caridea\Event\NullPublisher();
    }

    /**
     * @inheritDoc
     */
    public function countAll(array $criteria): int
    {
        $result = $this->doExecute(function (Manager $m, string $c) use ($criteria) {
            list($db, $coll) = explode('.', $c, 2);
            $command = new \MongoDB\Driver\Command([
                'count' => $coll,
                'query' => $criteria,
            ]);
            $cursor = $m->executeCommand($db, $command, $this->readPreference);
            $cursor->setTypeMap(['root' => 'array']);
            $resa = $cursor->toArray();
            return count($resa) > 0 ? current($resa) : null;
        });

        // Older server versions may return a float
        if (!is_array($result) || !array_key_exists('n', $result) || !(is_int($result['n']) || is_float($result['n']))) {
            throw new \Caridea\Dao\Exception\Unretrievable('count command did not return a numeric "n" value');
        }

        return (int) $result['n'];
    }

    /**
     * @inheritDoc
     */
    public function findOne(array $criteria)
    {
        return $this->maybeCache(
            $this->doExecute(function (Manager $m, string $c) use ($criteria) {
                $q = new \MongoDB\Driver\Query($criteria, ['limit' => 1]);
                $res = $m->executeQuery($c, $q, $this->readPreference);
                $res->setTypeMap($this->typeMap);
                $resa = $res->toArray();
                return count($resa) > 0 ? current($resa) : null;
            })
        );
    }

    /**
     * @inheritDoc
     */
    public function findAll(array $criteria, \Caridea\Http\Pagination $pagination = null, bool $totalCount = false): iterable
    {
        $total = null;
        if ($totalCount === true && $pagination !== null && ($pagination->getMax() != PHP_INT_MAX || $pagination->getOffset() > 0)) {
            $total = $this->countAll($criteria);
        }
        $results = $this->doExecute(function (Manager $m, string $c) use ($criteria, $pagination) {
            $qo = [];
            if ($pagination !== null) {
                if ($pagination->getMax() != PHP_INT_MAX) {
                    $qo['limit'] = $pagination->getMax();
                }
                $qo['skip'] = $pagination->getOffset();
                $sorts = [];
                foreach ($pagination->getOrder() as $k => $v) {
                    $sorts[$k] = $v ? 1 : -1;
                }
                if (count($sorts) > 0) {
                    $qo['sort'] = $sorts;
                }
            }
            $q = new \MongoDB\Driver\Query($criteria, $qo);
            $res = $m->executeQuery($c, $q, $this->readPreference);
            $res->setTypeMap($this->typeMap);
            return $res;
        });
        return $total === null ? $results : new CursorSubset($results, $total);
    }

    /**
     * @inheritDoc
     */
    public function findById($id)
    {
        try {
            $mid = $this->toId($id);
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException $e) {
            if ($e->getMessage() === 'Invalid BSON ID provided') {
                throw new \Caridea\Dao\Exception\Unretrievable('Could not find document', 0, $e);
            }
            throw $e;
        }
        return $this->getFromCache((string)$id) ??
            $this->findOne(['_id' => $mid]);
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        return $this->ensure($id, $this->findById($id));
    }

    /**
     * @inheritDoc
     */
    public function getAll(iterable $ids): iterable
    {
        $ids = is_array($ids) ? $ids : iterator_to_array($ids);
        if (count($ids) === 0) {
            return [];
        }
        try {
            $mids = $this->toIds($ids);
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException $e) {
            if ($e->getMessage() === 'Invalid BSON ID provided') {
                throw new \Caridea\Dao\Exception\Unretrievable('Could not load documents', 0, $e);
            }
            throw $e;
        }
        $cmp = array_flip(array_map(function ($a) {
            return (string) $a;
        }, $ids));
        $fromCache = array_intersect_key($this->cache, $cmp);
        if (count($fromCache) === count($ids)) {
            return $fromCache;
        } elseif (count($fromCache) > 0) {
            $mids = array_filter($mids, function ($a) {
                return !array_key_exists((string)$a, $this->cache);
            });
            return array_merge(
                $fromCache,
                $this->maybeCacheAll($this->findAll(['_id' => ['$in' => $mids->toArray()]]))
            );
        } else {
            return $this->maybeCacheAll($this->findAll(['_id' => ['$in' => $mids->toArray()]]));
        }
    }

    /**
     * @inheritDoc
     */
    public function getInstanceMap(iterable $entities): array
    {
        $instances = [];
        foreach ($entities as $entity) {
            $instances[(string) Getter::getId($entity)] = $entity;
        }
        return $instances;
    }

    /**
     * Gets the read preference.
     *
     * If no read preference was specified at creation, this method returns the
     * read preference as returned by the `Manager`.
     *
     * @return - The read preference, or `null`
     */
    public function getReadPreference(): ReadPreference
    {
        return $this->readPreference ?? $this->manager->getReadPreference();
    }

    /**
     * Gets the write concern.
     *
     * If no write concern was specified at creation, this method returns the
     * write concern as returned by the `Manager`.
     *
     * @return - The write concern
     */
    public function getWriteConcern(): WriteConcern
    {
        return $this->writeConcern ?? $this->manager->getWriteConcern();
    }

    /**
     * @inheritDoc
     */
    public function isResolvable(string $ref): bool
    {
        return strstr($this->collection, '.') === ".$ref";
    }

    /**
     * @inheritDoc
     */
    public function resolve(array $ref): ?T
    {
        if (!array_key_exists('$ref', $ref) || !array_key_exists('$id', $ref)) {
            throw new \InvalidArgumentException('Not a DBRef. Needs both $ref and $id keys.');
        }
        if (!$this->isResolvable($ref['$ref'])) {
            throw new \InvalidArgumentException("Unsupported reference type: " . $ref['$ref']);
        }
        return $this->findById($ref['$id']);
    }

    /**
     * @inheritDoc
     */
    public function resolveAll(iterable $refs): iterable
    {
        $types = [];
        $ids = [];
        foreach ($refs as $ref) {
            $types[$ref['$ref']] = true;
            $ids[] = $ref['$id'];
        }
        foreach ($types as $type => $_) {
            if (!$this->isResolvable($type)) {
                throw new \InvalidArgumentException("Unsupported reference type: " . $type);
            }
        }
        return $this->getAll($ids);
    }

    /**
     * Creates a record.
     *
     * @param $record - The record to insert, ready to go
     * @return - Whatever MongoDB returns
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Violating If a constraint is violated
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    protected function doCreate(array $record): WriteResult
    {
        if ($this->versioned) {
            $record['version'] = 0;
        }

        return $this->doExecute(function (Manager $m, string $c) use ($record) {
            $bulk = new \MongoDB\Driver\BulkWrite();
            $bulk->insert($record);
            return $m->executeBulkWrite($c, $bulk, $this->writeConcern);
        });
    }

    /**
     * Creates a record using a MongoDB `Persistable`.
     *
     * @param $record - The document to insert, ready to go
     * @return - Whatever MongoDB returns
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Violating If a constraint is violated
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    protected function doPersist(\MongoDB\BSON\Persistable $record): WriteResult
    {
        $this->preInsert($record);
        $wr = $this->doExecute(function (Manager $m, string $c) use ($record) {
            $bulk = new \MongoDB\Driver\BulkWrite();
            $bulk->insert($record);
            return $m->executeBulkWrite($c, $bulk, $this->writeConcern);
        });
        $this->postInsert($record);
        return $wr;
    }

    /**
     * Updates a record.
     *
     * @param $entity - The entity to update
     * @param $version - Optional version for optimistic lock checking
     * @return - Whatever MongoDB returns
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Conflicting If optimistic/pessimistic lock fails
     * @throws \Caridea\Dao\Exception\Violating If a constraint is violated
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    protected function doUpdateModifiable(Entity\Modifiable $entity, int $version = null): ?WriteResult
    {
        if (!$entity->isDirty()) {
            return null;
        }
        $mid = Getter::getId($entity);
        $ops = $entity->getChanges();

        if ($this->versioned) {
            if ($version !== null) {
                $orig = $this->findOne(['_id' => $mid]);
                if ($version < (int) Getter::get($orig, 'version')) {
                    throw new \Caridea\Dao\Exception\Conflicting("Document version conflict");
                }
            }
            $ops['$inc']['version'] = 1;
        }

        $this->preUpdate($entity);
        $this->cache->removeKey((string) $mid);
        $wr = $this->doExecute(function (Manager $m, string $c) use ($mid, $ops) {
            $bulk = new \MongoDB\Driver\BulkWrite();
            $bulk->update(['_id' => $mid], $ops);
            return $m->executeBulkWrite($c, $bulk, $this->writeConcern);
        });
        $this->postUpdate($entity);
        return $wr;
    }

    /**
     * Updates a record.
     *
     * @param \MongoDB\BSON\ObjectID|string $id The document identifier, either a string or `ObjectID`
     * @param array<string,array<string,mixed>> $operations The operations to send to MongoDB
     * @param $version - Optional version for optimistic lock checking
     * @return - Whatever MongoDB returns
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the document doesn't exist
     * @throws \Caridea\Dao\Exception\Conflicting If optimistic/pessimistic lock fails
     * @throws \Caridea\Dao\Exception\Violating If a constraint is violated
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    protected function doUpdate($id, array $ops, int $version = null): WriteResult
    {
        // ensure record exists
        $mid = $this->toId($id);
        $orig = $this->get($id);

        // check optimistic locking
        if ($this->versioned) {
            if ($version !== null) {
                if ($version < (int) Getter::get($orig, 'version')) {
                    throw new \Caridea\Dao\Exception\Conflicting("Document version conflict");
                }
            }
            $ops['$inc']['version'] = 1;
        }

        // do update operation
        $this->cache->removeKey((string)$id);
        return $this->doExecute(function (Manager $m, string $c) use ($mid, $ops) {
            $bulk = new \MongoDB\Driver\BulkWrite();
            $bulk->update(['_id' => $mid], $ops);
            return $m->executeBulkWrite($c, $bulk, $this->writeConcern);
        });
    }

    /**
     * Deletes a record.
     *
     * @param \MongoDB\BSON\ObjectID|string $id The document identifier, either a string or `ObjectID`
     * @return - Whatever MongoDB returns
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the document doesn't exist
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    protected function doDelete($id): WriteResult
    {
        $mid = $this->toId($id);
        $entity = $this->get($mid);
        $this->cache->removeKey((string)$id);
        $this->preDelete($entity);
        $wr = $this->doExecute(function (Manager $m, string $c) use ($mid) {
            $bulk = new \MongoDB\Driver\BulkWrite();
            $bulk->delete(['_id' => $mid], ['limit' => 1]);
            return $m->executeBulkWrite($c, $bulk, $this->writeConcern);
        });
        $this->postDelete($entity);
        return $wr;
    }

    /**
     * Executes an aggregation command.
     *
     * @param iterable<array<string,mixed>> $pipeline The aggregation pipeline operations
     * @param array<string,mixed> $options Any aggregation options
     * @see https://docs.mongodb.com/manual/reference/method/db.collection.aggregate/
     * @since 0.7.2
     */
    protected function doAggregate(iterable $pipeline, array $options): \MongoDB\Driver\Cursor
    {
        return $this->doExecute(function (Manager $m, string $c) use ($pipeline, $options) {
            list($db, $coll) = explode('.', $c, 2);
            $cmd = [
                'aggregate' => $coll,
                'pipeline' => $options->toArray(),
            ];
            foreach ($options as $k => $v) {
                $cmd[$k] = $v;
            }
            $command = new \MongoDB\Driver\Command($cmd);
            return $m->executeCommand($db, $command, $this->readPreference);
        });
    }

    /**
     * Executes a projection.
     *
     * @param array<string,mixed> $criteria Field to value pairs
     * @param array<string,mixed> $projections Field name to projection value
     *        (either boolean or projection operator)
     * @param $pagination - Optional pagination parameters
     * @param $totalCount - Return a `CursorSubset` that includes the total
     *        number of records. This is only done if `$pagination` is not using
     *        the defaults.
     * @return - The projection cursor
     * @throws \Caridea\Dao\Exception\Unreachable If the connection fails
     * @throws \Caridea\Dao\Exception\Unretrievable If the result cannot be returned
     * @throws \Caridea\Dao\Exception\Generic If any other database problem occurs
     */
    protected function doProjection(array $criteria, array $projections, \Caridea\Http\Pagination $pagination = null, bool $totalCount = false): iterable
    {
        $total = null;
        if ($totalCount === true && $pagination !== null && ($pagination->getMax() != PHP_INT_MAX || $pagination->getOffset() > 0)) {
            $total = $this->countAll($criteria);
        }
        $results = $this->doExecute(function (Manager $m, string $c) use ($criteria, $projections, $pagination) {
            $qo = [];
            if ($pagination !== null) {
                if ($pagination->getMax() != PHP_INT_MAX) {
                    $qo['limit'] = $pagination->getMax();
                }
                $qo['skip'] = $pagination->getOffset();
                $sorts = [];
                foreach ($pagination->getOrder() as $k => $v) {
                    $sorts[$k] = $v ? 1 : -1;
                }
                if (count($sorts) > 0) {
                    $qo['sort'] = $sorts;
                }
            }
            if (!$projections->isEmpty()) {
                $qo['projection'] = $projections->toArray();
            }
            $q = new \MongoDB\Driver\Query($criteria->toArray(), $qo);
            return $m->executeQuery($c, $q, $this->readPreference);
        });
        return $total === null ? $results : new CursorSubset($results, $total);
    }

    /**
     * Possibly add the entity to the cache.
     *
     * @param $entity - The entity to possibly cache
     * @return - The same entity that came in
     */
    protected function maybeCache(?T $entity) : ?T
    {
        if ($this->caching && $entity !== null) {
            $id = (string) Getter::getId($entity);
            if (!array_key_exists($id, $this->cache)) {
                $this->cache[$id] = $entity;
            }
        }
        return $entity;
    }

    /**
     * Possibly add entities to the cache.
     *
     * @param $entities - The entities to possibly cache
     * @return - The same entities that came in
     */
    protected function maybeCacheAll(\Iterator $entities): iterable
    {
        if ($this->caching) {
            $results = $entities instanceof \MongoDB\Driver\Cursor ?
                $entities->toArray() : iterator_to_array($entities, false);
            foreach ($results as $entity) {
                if ($entity !== null) {
                    $id = (string) Getter::getId($entity);
                    if (!array_key_exists($id, $this->cache)) {
                        $this->cache[$id] = $entity;
                    }
                }
            }
            return $results;
        } else {
            return $entities;
        }
    }

    /**
     * Gets an entry from the cache
     *
     * @param $id - The cache key
     * @return - The entity found or null
     */
    protected function getFromCache(string $id)
    {
        return $this->cache[$id] ?? null;
    }
}
