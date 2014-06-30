<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 15/06/14
 * Time: 13:40
 */

class NotificationCollection extends Persistence implements JsonSerializable,Collection{

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
            $this->addItem(BeckonMember::build($key), $key);
            return $this->entities[$key];
        }
    }

    public function getKeys(){
        return array_keys($this->entities);
    }

    public function addItem(&$object, $key){
        if(get_class($object) == "Notification"){
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
            foreach($this->q("select * from Notification where owner = {$this->id}") as $notification){
                $this->addItem(Notification::buildExisting($notification['id'],  $notification['owner'], $notification['objectClass'], $notification['object'], $notification['message']), $notification['id']);
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
            array_push($result, array($key => $object->jsonSerialize()));
        }
        return $result;
    }

} 