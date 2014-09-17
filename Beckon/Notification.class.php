<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 15/06/14
 * Time: 13:01
 */

class Notification extends Persistence implements JsonSerializable{

    protected function __construct($id = null){
        if(!is_null($id)){
            $this->id = $id;
        }
    }

    //Properties
    private $id = null;
    private $owner = null;//User
//    private $objectClass = null;
//    private $object = null;
    private $message = null;
    private $updateType = null;
    private $destinationId = null;

    //Setters
    protected function setOwner(&$owner){
        if($this->owner != $owner){
            $this->owner = $owner;
            $this->dirty = true;
        }
    }

    public function setMessage($message){
        if($this->message != $message){
            $this->message = $message;
            $this->dirty = true;
        }
    }
    public function setUpdateType($updateType){
        if($this->updateType != $updateType){
            $this->updateType = $updateType;
            $this->dirty = true;
        }
    }
    public function setDestinationId($destinationId){
        if($this->destinationId != $destinationId){
            $this->destinationId = $destinationId;
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
//    public function getObjectClass(){
//        if(!is_null($this->objectClass)){return $this->objectClass;}
//        else{$this->sync();return $this->objectClass;}
//    }
//    public function getObject(){
//        if(!is_null($this->object)){return $this->object;}
//        else{$this->sync();return $this->object;}
//    }
    public function getMessage(){
        if(!is_null($this->message)){return $this->message;}
        else{$this->sync();return $this->message;}
    }
    public function getUpdateType(){
        if(!is_null($this->updateType)){return $this->updateType;}
        else{$this->sync();return $this->updateType;}
    }
    public function getDestinationId(){
        if(!is_null($this->destinationId)){return $this->destinationId;}
        else{$this->sync();return $this->destinationId;}
    }


    //Persistence
    public function flush(){
        if($this->dirty){
            if(is_null($this->id)){
                try{
                    $objectClass = get_class($this->object);
                    $stmt = self::getConnection()->prepare("insert into Notification (owner, updateType, destinationId, message) values (:owner, :updateType, :destinationId, :message)");
                    $stmt->execute(array("owner" => $this->getOwner()->getId(), "updateType" => $this->updateType, "destinationId" => $this->destinationId, "message" => $this->getMessage()));
                    $this->id = self::$connection->lastInsertId();
                    self::cachePut("Notification", $this->getId(), $this);
                    $this->dirty = false;
                }
                catch(Exception $e){
                    throw $e;
                }
            }
        }
    }

    public function delete(){
        $this->q("delete from Notification where id = {$this->id}");
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    public function sync(){
        if(!is_null($this->id)){
            $set = $this->q("select * from Notification where id = {$this->id}");
            if($set->rowCount() > 0){
                foreach($set as $row){
                    $this->id = $row['id'];
                    $this->owner = User::build($row['owner']);
                    $this->updateType = $row['updateType'];
                    $this->destinationId = $row['destinationId'];
                    $this->message = $row['message'];
                    $this->dirty = false;
                }
            }
            else{
                throw new Exception("Notification with id ". $this->getId() ." does not exist");
            }
        }
        else{
            throw New Exception("Unable to sync unknown object instance");
        }
    }

    //Serialization
    public function jsonSerialize(){
        return array("owner" => $this->getOwner()->getId(), "updateType" => $this->getUpdateType(), "destinationId" => $this->getDestinationId(), "message" => $this->getMessage());
    }

    //Factory
    public static function build($id){
        try{
            return self::cacheGet("Notification", $id);
        }
        catch(Exception $e){
            $notification = new Notification($id);
            self::cachePut("Notification", $id, $notification);
            return $notification;
        }
    }

    public static function buildNew(&$owner, $updateType, $destinationId, $message){
        $notification = New Notification();
        $notification->setOwner($owner);
        $notification->setUpdateType($updateType);
        $notification->setDestinationId($destinationId);
        $notification->setMessage($message);
        try{
            $notification->flush();
        }
        catch(Exception $e){
            throw New Exception($e->getMessage(),0 ,$e);
        }
        return $notification;
    }

    public static function buildExisting($id, $ownerId, $updateType, $destinationId, $message){
        $notification = self::build($id);
        $notification->owner = User::build($ownerId);
        $notification->updateType = $updateType;
        $notification->destinationId = $destinationId;
        $notification->message = $message;
        self::cachePut("Notification", $id, $notification);
        return $notification;
    }
}