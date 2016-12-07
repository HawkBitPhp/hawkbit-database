<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 06.12.2016
 * Time: 15:46
 */

namespace Hawkbit\Storage;


final class UnitOfWork
{

    /**
     * @var object[]
     */
    private $newObjects = [];

    /**
     * @var object[]
     */
    private $updatedObjects = [];

    /**
     * @var object[]
     */
    private $deletedObjects = [];

    /**
     * @var object[]
     */
    private $completed = [];

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * UnitOfWork constructor.
     * @param Connection $connection
     * @internal param Mapper $mapper
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return \object[]
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param $object
     */
    public function update($object)
    {
        $mapper = $this->connection->loadMapper($object);
        $data = $mapper->getHydrator()->extract($object);

        if(isset($data[$mapper->getLastInsertIdReference()])){
            $mapper->getIdentityMap()->set($data[$mapper->getLastInsertIdReference()], $object);
        }

        $this->updatedObjects[ spl_object_hash($object) ] = $object;
    }
    /**
     * Register an object as dirty. This is valid unless:
     * - The object is registered to be removed
     * - The object is registered as dirty (has been changed)
     * - The object is already registered as new
     *
     * @param $object
     */
    public function create($object)
    {
        // Check if we meet our criteria.
        if ($this->isDeleted($object)) {
            throw new \InvalidArgumentException('Cannot register as new, object is marked for deletion.');
        }
        if ($this->isUpdated($object)) {
            throw new \InvalidArgumentException('Cannot register as new, object is marked as dirty.');
        }
        if ($this->isNew($object)) {
            throw new \InvalidArgumentException('Cannot register as new, object is already marked as new.');
        }

        $this->newObjects[ spl_object_hash($object) ] = $object;
    }
    /**
     * @param $object
     */
    public function delete($object)
    {
        $this->connection->loadMapper($object)->getIdentityMap()->removeObject($object);
        $this->deletedObjects[ spl_object_hash($object) ] = $object;
    }
    /**
     * @param $object
     * @return bool
     */
    public function isUpdated($object)
    {
        return isset($this->updatedObjects[ spl_object_hash($object) ]);
    }
    /**
     * @param $object
     * @return bool
     */
    public function isNew($object)
    {
        return isset($this->newObjects[ spl_object_hash($object) ]);
    }
    /**
     * @param $object
     * @return bool
     */
    public function isDeleted($object)
    {
        return isset($this->deletedObjects[ spl_object_hash($object) ]);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function commit(){

        $connection = $this->connection;

        try{
            $connection->beginTransaction();

            // insert
            $this->process('new',$this->newObjects, function($entity) {
                return $this->connection->loadMapper($entity)->create($entity);
            });
            // update
            $this->process('updated',$this->updatedObjects, function($entity) {
                return $this->connection->loadMapper($entity)->update($entity);
            });
            // delete
            $this->process('deleted',$this->deletedObjects, function($entity) {
                return $this->connection->loadMapper($entity)->delete($entity);
            });

            $connection->commit();
            return true;

        }catch(\Exception $e){
            $connection->rollBack();
            $this->exception = $e;
            return false;
        }
    }

    /**
     * @param $entities
     * @param $task
     */
    protected function process($label, $entities, callable $task){
        foreach ($entities as $entity){
            $task($entity);
            $this->completed[$label][$entity];
        }
    }

}