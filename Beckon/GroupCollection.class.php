<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 06/06/14
 * Time: 18:19
 */

class GroupCollection extends Persistence implements JsonSerializable,Collection{

    private $id = null;
    private $entities;

    public function __construct($id = null){
        $this->id = $id;
        $this->entities = array();
        $this->sync();
    }

    public function getItem($key){
        if(in_array($this->entities, $key)){
            return $this->entities[$key];
        }
        else{
            $this->addItem(Friend::build($key), $key);
            return $this->entities[$key];
        }
    }

    public function getKeys(){
        return array_keys($this->entities);
    }

    public function getIterator(){
        return array_values($this->entities);
    }

    public function addItem(&$object, $key){
        if(get_class($object) == "Group"){
            $this->entities[$key] = &$object;
        }
        else{
            throw new Exception("Invalid object type");
        }
    }

    public function deleteItem($key){
        $this->entities[$key]->delete();
    }

    public function sync(){
        if(!is_null($this->id)){
            foreach($this->q("select * from `Group` where owner = {$this->id}") as $group){
                $this->addItem(Group::buildExisting($group['id'], $group['name'], $group['owner']), $group['id']);
            }
        }
    }

    public function flush(){
        foreach($this->entities as $key => $object){
            $c = self::getConnection();
            $c->beginTransaction();
            $object->flush();
            $c->commit();
        }
    }

    public function delete(){
        foreach($this->entities as $key => $object){
            $object->delete();
        }
    }

    public function jsonSerialize(){
        $result = array();
        foreach($this->entities as $key => $object){
            array_push($result, $object->jsonSerialize());
        }
        return $result;
    }

} 