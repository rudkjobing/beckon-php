<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 15/06/14
 * Time: 16:09
 */

include_once "ChatRoomMemberCollection.class.php";
include_once "ChatMessageCollection.class.php";

class ChatRoom extends Persistence implements JsonSerializable{

    protected function __construct($id = null){
        if(!is_null($id)){
            $this->id = $id;
        }
    }

    //Properties
    private $id = null;
    private $owner = null;//User
    private $members = null;
    private $messages = null;

    //Setters
    protected function setOwner(&$owner){
        if($this->owner != $owner){
            $this->owner = $owner;
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
    public function getMembers(){
        if(!is_null($this->members)){return $this->members;}
        else{$this->members = new ChatRoomMemberCollection($this->getId()); return $this->members;}
    }
    public function getMessages(){
        if(!is_null($this->messages)){return $this->messages;}
        else{$this->messages = new ChatMessageCollection($this->getId()); return $this->messages;}
    }

    //Persistence
    public function flush(){
        if($this->dirty && is_null($this->id)){
            try{
                $stmt = self::getConnection()->prepare("insert into ChatRoom (owner) values (:owner)");
                $stmt->execute(array("owner" => $this->getOwner()->getId()));
                $this->id = self::$connection->lastInsertId();
                self::cachePut("ChatRoom", $this->getId(), $this);
                $this->dirty = false;
            }
            catch(Exception $e){
                throw $e;
            }
        }
    }

    public function delete(){
        $this->q("delete from ChatRoom where id = {$this->id}");
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    public function sync(){
        if(!is_null($this->id)){
            $set = $this->q("select * from ChatRoom where id = {$this->id}");
            if($set->rowCount() > 0){
                foreach($set as $row){
                    $this->id = $row['id'];
                    $this->owner = User::build($row['owner']);
                    $this->dirty = false;
                }
            }
            else{
                throw new Exception("Object does not exist");
            }
        }
        else{
            throw New Exception("ChatRoom with id " . $this->getId() . " does not exist");
        }
    }

    //Serialization
    public function jsonSerialize(){
        return array("id" => $this->getId(), "owner" => $this->getOwner()->getId(), "members" => $this->getMembers()->jsonSerialize(), "messages" => $this->getMessages()->jsonSerialize());
    }

    //Factory
    public static function build($id){
        try{
            return self::cacheGet("ChatRoom", $id);
        }
        catch(Exception $e){
            $chatRoom = new ChatRoom($id);
            self::cachePut("ChatRoom", $id, $chatRoom);
            return $chatRoom;
        }
    }

    public static function buildNew(&$owner){
        $chatRoom = New ChatRoom();
        $chatRoom->setOwner($owner);
        try{
            $chatRoom->flush();
        }
        catch(Exception $e){
            throw New Exception(__function__,0 ,$e);
        }
        return $chatRoom;
    }

    public static function buildExisting($id, $ownerId){
        $chatRoom = self::build($id);
        $chatRoom->setOwner(User::build($ownerId));
        return $chatRoom;
    }
}