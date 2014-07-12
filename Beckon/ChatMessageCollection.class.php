<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 15/06/14
 * Time: 17:21
 */

class ChatMessageCollection extends Persistence implements JsonSerializable,Collection{

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

    public function getIterator(){
        return array_values($this->entities);
    }

    public function addItem(&$object, $key){
        if(get_class($object) == "ChatMessage"){
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
            foreach($this->q("select * from ChatMessage where chatRoom = {$this->id}") as $chatMessage){
                $this->addItem(ChatMessage::buildExisting($chatMessage['id'],  $chatMessage['chatRoom'], $chatMessage['owner'], $chatMessage['message']), $chatMessage['id']);
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