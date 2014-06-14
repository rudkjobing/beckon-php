<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 08/06/14
 * Time: 14:39
 */

class DeviceCollection extends Persistence implements JsonSerializable,Collection{

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
            $this->addItem(Device::build($key), $key);
            return $this->entities[$key];
        }
    }

    public function getKeys(){
        return array_keys($this->entities);
    }

    public function addItem(&$object, $key){
        if(get_class($object) == "Device"){
            $this->entities[$key] = $object;
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
            foreach($this->q("select * from Device where owner = {$this->id}") as $device){
                $this->addItem(Device::buildExisting($device['id'], $device['owner'], $device['type'], $device['notificationKey']), $device['id']);
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
            array_push($result, array($key, $object->jsonSerialize()));
        }
        return $result;
    }

} 