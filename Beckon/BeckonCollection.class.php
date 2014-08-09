<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 06/06/14
 * Time: 23:18
 */

class BeckonCollection extends Persistence implements JsonSerializable,Collection{

    private $id = null;
    private $entities;

    public function __construct($id = null, $newest = 0){
        $this->id = $id;
        $this->entities = array();
        $this->sync($newest);
    }

    public function getItem($key){
        if(in_array($this->entities, $key)){
            return $this->entities[$key];
        }
        else{
            $this->addItem(Beckon::build($key), $key);
            return $this->entities[$key];
        }
    }

    public function getKeys(){
        return array_keys($this->entities);
    }

    public function addItem(&$object, $key){
        if(get_class($object) == "Beckon"){
            $this->entities[$key] = &$object;
        }
        else{
            throw new Exception("Invalid object type");
        }
    }

    public function deleteItem($key){
        $this->entities[$key]->delete();
    }

    public function sync($id = 0){
        if(!is_null($this->id)){
            foreach($this->q("select Beckon.id, Beckon.title, Beckon.begins, Beckon.ends, Beckon.owner, Beckon.id, Beckon.latitude, Beckon.longitude, Beckon.chatRoom from BeckonMember inner join Beckon on BeckonMember.beckon = Beckon.id where BeckonMember.user = {$this->id} and Beckon.id > {$id}") as $beckon){
                $this->addItem(Beckon::buildExisting($beckon['id'], $beckon['title'], $beckon['begins'], $beckon['ends'], $beckon['owner'], $beckon['latitude'], $beckon['longitude'], $beckon['chatRoom']), $beckon['id']);
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