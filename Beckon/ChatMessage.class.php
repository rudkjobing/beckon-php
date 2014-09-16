<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 15/06/14
 * Time: 17:13
 */

class ChatMessage extends Persistence implements JsonSerializable{

    protected function __construct($id = null){
        if(!is_null($id)){
            $this->id = $id;
        }
    }

    //Properties
    private $id = null;
    private $chatRoom = null;//ChatRoom
    private $owner = null;
    private $message = null;
    private $date = null;

    //Setters
    protected function setChatRoom(&$chatRoom){
        if($this->chatRoom != $chatRoom){
            $this->chatRoom = $chatRoom;
            $this->dirty = true;
        }
    }
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

    //Lazy Getters
    public function getId(){
        if(!is_null($this->id)){return $this->id;}
        else{$this->sync();return $this->id;}
    }
    public function getChatRoom(){
        if(!is_null($this->chatRoom)){return $this->chatRoom;}
        else{$this->sync();return $this->chatRoom;}
    }
    public function getOwner(){
        if(!is_null($this->owner)){return $this->owner;}
        else{$this->sync();return $this->owner;}
    }
    public function getMessage(){
        if(!is_null($this->message)){return $this->message;}
        else{$this->sync();return $this->message;}
    }
    public function getDate(){
        if(!is_null($this->date)){return $this->date;}
        else{$this->sync();return $this->date;}
    }

    //Persistence
    public function flush(){
        if($this->dirty && is_null($this->id)){
            try{
                $stmt = self::getConnection()->prepare("insert into ChatMessage (chatRoom, owner, message) values (:chatRoom, :owner, :message)");
                $stmt->execute(array("chatRoom" => $this->getChatRoom()->getId(), "owner" => $this->getOwner()->getId(), "message" => $this->getMessage()));
                $this->id = self::$connection->lastInsertId();
                self::cachePut("ChatMessage", $this->getId(), $this);
                $this->dirty = false;
            }
            catch(Exception $e){
                throw $e;
            }
        }
    }

    public function delete(){
        $this->q("delete from ChatMessage where id = {$this->id}");
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    public function sync(){
        if(!is_null($this->id)){
            $set = $this->q("select * from ChatMessage where id = {$this->id}");
            if($set->rowCount() > 0){
                foreach($set as $row){
                    $this->id = $row['id'];
                    $this->chatRoom = ChatRoom::build($row['chatRoom']);
                    $this->owner = User::build($row['owner']);
                    $this->message = $row['message'];
                    $this->date = $row['date'];
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
        return array("id" => $this->getId(), "chatRoom" => $this->getChatRoom()->getId(), "owner" => $this->getOwner()->getId(), "message" => $this->getMessage(), "date" => $this->getDate());
    }

    //Factory
    public static function build($id){
        try{
            return self::cacheGet("ChatMessage", $id);
        }
        catch(Exception $e){
            $chatMessage = new ChatMessage($id);
            self::cachePut("ChatMessage", $id, $chatMessage);
            return $chatMessage;
        }
    }

    public static function buildNew(&$chatRoom, &$owner, $message){
        $chatMessage = New ChatMessage();
        $chatMessage->setChatRoom($chatRoom);
        $chatMessage->setOwner($owner);
        $chatMessage->setMessage($message);
        try{
            $chatMessage->flush();
        }
        catch(Exception $e){
            throw New Exception($e->getMessage(),0 ,$e);
        }
        return $chatMessage;
    }

    public static function buildExisting($id, $chatRoomId, $ownerId, $message){
        $chatMessage = self::build($id);
        $chatMessage->setChatRoom(ChatRoom::build($chatRoomId));
        $chatMessage->setOwner(User::build($ownerId));
        $chatMessage->setMessage($message);
        return $chatMessage;
    }
}