<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 15/06/14
 * Time: 16:34
 */

class ChatRoomMember extends Persistence implements JsonSerializable{

    protected function __construct($id = null){
        if(!is_null($id)){
            $this->id = $id;
        }
    }

    //Properties
    private $id = null;
    private $chatRoom = null;//User
    private $user = null;
    private $hasUnreadMessages = null;

    //Setters
    public function setChatRoom(&$chatRoom){
        if($this->chatRoom != $chatRoom){
            $this->chatRoom = $chatRoom;
            $this->dirty = true;
        }
    }
    public function setUser(&$user){
        if($this->user != $user){
            $this->user = $user;
            $this->dirty = true;
        }
    }
    public function setHasUnreadMessages($hasUnreadMessages){
        if($this->hasUnreadMessages !== $hasUnreadMessages){
            $this->hasUnreadMessages = $hasUnreadMessages;
            $this->dirty = true;
        }
    }

    //Lazy Getters
    public function getId(){
        $this->caller = "id";
        if(!is_null($this->id)){return $this->id;}
        else{$this->sync();return $this->id;}
    }
    public function getChatRoom(){
        $this->caller = "chatRoom";
        if(!is_null($this->chatRoom)){return $this->chatRoom;}
        else{$this->sync();return $this->chatRoom;}
    }
    public function getUser(){
        $this->caller = "user";
        if(!is_null($this->user)){return $this->user;}
        else{$this->sync();return $this->user;}
    }
    public function getHasUnreadMessages(){
        $this->caller = "hasUnreadMessages";
        if(!is_null($this->hasUnreadMessages)){return $this->hasUnreadMessages;}
        else{$this->sync();return $this->hasUnreadMessages;}
    }

    //Persistence
    public function flush(){
        if($this->dirty){
            if(is_null($this->id)){
                try{
                    $stmt = self::getConnection()->prepare("insert into ChatRoomMember (chatRoom, `user`, hasUnreadMessages) values (:chatRoom, :user, :hasUnreadMessages)");
                    $stmt->execute(array("chatRoom" => $this->getChatRoom()->getId(), "user" => $this->getUser()->getId(), "hasUnreadMessages" => $this->getHasUnreadMessages()));
                    $this->id = self::$connection->lastInsertId();
                    self::cachePut("ChatRoomMember", $this->getId(), $this);
                    $this->dirty = false;
                }
                catch(Exception $e){
                    throw new Exception($e->getMessage(), 0, $e);
                }
            }
            elseif(!is_null($this->id)){
                try{
                    $stmt = self::getConnection()->prepare("update ChatRoomMember set hasUnreadMessages = :hasUnreadMessages where id = :id");
                    $stmt->execute(array("hasUnreadMessages" => $this->getHasUnreadMessages(), "id" => $this->getId()));
                }
                catch(Exception $e){
                    throw new Exception($e->getMessage(), 0, $e);
                }
            }
        }
    }

    public function delete(){
        $this->q("delete from ChatRoomMember where id = {$this->id}");
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    public function sync(){
        if(!is_null($this->id)){
            $set = $this->q("select * from ChatRoomMember where id = {$this->id}");
            if($set->rowCount() > 0){
                foreach($set as $row){
                    $this->id = $row['id'];
                    $this->chatRoom = ChatRoom::build($row['chatRoom']);
                    $this->hasUnreadMessages = $row['hasUnreadMessages'];
                    $this->user = User::build($row['user']);
                }
            }
            else{
                throw new Exception("Object does not exist");
            }
        }
        else{
            throw New Exception("Unable to sync unknown ChatRoomMember instance");
        }
    }

    //Serialization
    public function jsonSerialize(){
        return array("id" => $this->getId(), "chatRoom" => $this->getChatRoom()->getId(), "user" => $this->getUser()->getId());
    }

    //Factory
    public static function build($id){
        try{
            return self::cacheGet("ChatRoomMember", $id);
        }
        catch(Exception $e){
            $chatRoomMember = new ChatRoomMember($id);
            self::cachePut("ChatRoomMember", $id, $chatRoomMember);
            return $chatRoomMember;
        }
    }

    public static function buildNew($chatRoom, $user, $hasUnreadMessages = false){
        $chatRoomMember = New ChatRoomMember();
        $chatRoomMember->setChatRoom($chatRoom);
        $chatRoomMember->setUser($user);
        $chatRoomMember->setHasUnreadMessages($hasUnreadMessages);
        try{
            $chatRoomMember->flush();
        }
        catch(Exception $e){
            throw New Exception($e->getMessage(),0 ,$e);
        }
        return $chatRoomMember;
    }

    public static function buildExisting($id, $chatRoomId, $userId, $hasUnreadMessages = false){
        $chatRoomMember = self::build($id);
        $chatRoomMember->chatRoom = ChatRoom::build($chatRoomId);
        $chatRoomMember->user = User::build($userId);
        $chatRoomMember->hasUnreadMessages = $hasUnreadMessages;
        return $chatRoomMember;
    }

    public static function buildFromChatRoomAndUser(&$chatRoom, &$user){
        $stmt = self::getConnection()->prepare("select * from ChatRoomMember where user = :user and chatRoom = :chatRoom");
        $stmt->execute(array("user" => $user->getId(), "chatRoom" => $chatRoom->getId()));
        $set = $stmt->fetchAll();
        if($stmt->rowCount() > 0){
            foreach($set as $row){
                $chatRoomMember = self::build($row['id']);
                $chatRoomMember->chatRoom = ChatRoom::build($row['chatRoom']);
                $chatRoomMember->user = User::build($row['user']);
                $chatRoomMember->hasUnreadMessages = $row['hasUnreadMessages'];
                return $chatRoomMember;
            }
        }
        else{
            throw new Exception("Object not found");
        }
    }
}