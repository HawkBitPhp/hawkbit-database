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
     * @var Mapper
     */
    protected $mapper;

    /**
     * @var array
     */
    protected $work = [
        'create' => [],
        'update' => [],
        'delete' => [],
    ];

    /**
     * UnitOfWork constructor.
     * @param Mapper $mapper
     */
    public function __construct(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @param $entity
     * @return UnitOfWork
     */
    public function create($entity){
        return $this->plan(__FUNCTION__, function() use($entity) {
            return $this->mapper->create($entity);
        });
    }

    /**
     * @param $entity
     * @return UnitOfWork
     */
    public function update($entity){
        return $this->plan(__FUNCTION__, function() use($entity) {
            return $this->mapper->update($entity);
        });
    }

    /**
     * @param $entity
     * @return UnitOfWork
     */
    public function delete($entity){
        return $this->plan(__FUNCTION__, function() use($entity) {
            return $this->mapper->delete($entity);
        });
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute(){

        $connection = $this->mapper->getConnection();

        try{
            $connection->beginTransaction();

            // insert
            $this->doWork('create');
            // update
            $this->doWork('update');
            // delete
            $this->doWork('delete');

            $connection->commit();

        }catch(\Exception $e){
            $connection->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * @param $task
     */
    protected function doWork($task){
        foreach ($this->work[$task] as $task){
            $task();
        }
    }

    /**
     * @param $action
     * @param callable $callback
     * @return $this
     */
    protected function plan($action, callable $callback){
        $this->work[$action][] = $callback;
        return $this;
    }

}