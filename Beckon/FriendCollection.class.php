<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 05/06/14
 * Time: 14:51
 */

class FriendCollection extends Persistence implements JsonSerializable,Collection{

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

    public function addItem(&$object, $key){
        if(get_class($object) == "Friend"){
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
            foreach($this->q("select * from Friend where owner = {$this->id}") as $friend){
                $this->addItem(Friend::build($friend['id']), $friend['id']);
                //$this->addItem(Friend::buildExisting($friend['id'], $friend['nickName'], $friend['owner'], $friend['user'], $friend['peer']), $friend['id']);
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