<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 08/06/14
 * Time: 14:20
 */

class Device extends Persistence implements JsonSerializable{

    protected function __construct($id = null){
        if(!is_null($id)){
            $this->id = $id;
        }
    }

    //Properties
    private $id = null;
    private $owner = null;//User
    private $type = null;
    private $notificationKey = null;

    //Setters
    protected function setOwner(&$owner){
        if($this->owner != $owner){
            $this->owner = $owner;
            $this->dirty = true;
        }
    }

    public function setType($type){
        if($this->type != $type){
            $this->type = $type;
            $this->dirty = true;
        }
    }

    public function setNotificationKey($notificationKey){
        if($this->notificationKey != $notificationKey){
            $this->notificationKey = $notificationKey;
            $this->dirty = true;
        }
    }

    //Lazy Getters
    public function getId(){
        if(!is_null($this->id)){return $this->id;}
        else{$this->sync();return $this->id;}
    }
    public function getOwner(){
        if(!is_null($this->owner)){return $this->owner;}
        else{$this->sync();return $this->owner;}
    }
    public function getType(){
        if(!is_null($this->type)){return $this->type;}
        else{$this->sync();return $this->type;}
    }
    public function getNotificationKey(){
        if(!is_null($this->notificationKey)){return $this->notificationKey;}
        else{$this->sync();return $this->notificationKey;}
    }

    //Persistence
    public function flush(){
        if($this->dirty){

            if(!is_null($this->id)){
                $this->q("update Device set owner = {$this->getOwner()->getId()}, type = '{$this->getType()}', notificationKey = '{$this->getNotificationKey()}' where id = {$this->getId()}");
                $this->dirty = false;
            }
            elseif(is_null($this->id)){
                try{
                    $this->q("insert into Device (owner, type, notificationKey) values ({$this->getOwner()->getId()}, '{$this->getType()}', '{$this->getNotificationKey()}')");
                    $this->id = self::$connection->lastInsertId();
                    self::cachePut("Device", $this->getId(), $this);
                    $this->dirty = false;
                }
                catch(Exception $e){
                    throw $e;
                }
            }
        }
    }

    public function delete(){
        $this->q("delete from Device where id = {$this->id}");
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    public function sync(){
        if(!is_null($this->id)){
            $set = $this->q("select * from Device where id = {$this->id}");
            if($set->rowCount() > 0){
                foreach($set as $row){
                    $this->id = $row['id'];
                    $this->owner = User::build($row['owner']);
                    $this->type = $row['type'];
                    $this->notificationKey = $row['notificationKey'];
                    $this->dirty = false;
                }
            }
            else{
                throw new Exception("Object does not exist");
            }
        }
        else{
            throw New Exception("Unable to sync unknown object instance");
        }
    }

    //Serialization
    public function jsonSerialize(){
        return array("id" => $this->getId(), "owner" => $this->getOwner()->jsonSerialize(), "type" => $this->getType(), "notificationKey" => $this->getNotificationKey());
    }

    //Factory
    public static function build($id){
        try{
            return self::cacheGet("Device", $id);
        }
        catch(Exception $e){
            $device = new Device($id);
            self::cachePut("Device", $id, $device);
            return $device;
        }
    }

    public static function buildNew(&$owner, $type, $notificationKey){
        $device = New Device();
        $device->setOwner($owner);
        $device->setType($type);
        $device->setNotificationKey($notificationKey);
        try{
            $device->flush();
        }
        catch(Exception $e){
            throw New Exception(__function__,0 ,$e);
        }
        return $device;
    }

    public static function buildExisting($id, $ownerId, $type, $notificationKey){
        $device = self::build($id);
        $device->setOwner(User::build($ownerId));
        $device->setType($type);
        $device->setNotificationKey($notificationKey);
        return $device;
    }
}