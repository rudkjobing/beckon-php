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

    //Setters
    protected function setChatRoom(&$chatRoom){
        if($this->chatRoom != $chatRoom){
            $this->chatRoom = $chatRoom;
            $this->dirty = true;
        }
    }
    protected function setUser(&$user){
        if($this->user != $user){
            $this->user = $user;
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
    public function getUser(){
        if(!is_null($this->user)){return $this->user;}
        else{$this->sync();return $this->user;}
    }

    //Persistence
    public function flush(){
        if($this->dirty && is_null($this->id)){
            try{
                $stmt = self::getConnection()->prepare("insert into ChatRoomMember (chatRoom, `user`) values (:chatRoom, :user)");
                $stmt->execute(array("chatRoom" => $this->getChatRoom()->getId(), "user" => $this->getUser()->getId()));
                $this->id = self::$connection->lastInsertId();
                self::cachePut("ChatRoomMember", $this->getId(), $this);
                $this->dirty = false;
            }
            catch(Exception $e){
                throw $e;
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
                    $this->user = User::build($row['user']);
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

    public static function buildNew($chatRoom, $user){
        $chatRoomMember = New ChatRoomMember();
        $chatRoomMember->setChatRoom($chatRoom);
        $chatRoomMember->setUser($user);
        try{
            $chatRoomMember->flush();
        }
        catch(Exception $e){
            throw New Exception($e->getMessage(),0 ,$e);
        }
        return $chatRoomMember;
    }

    public static function buildExisting($id, $chatRoomId, $userId){
        $chatRoomMember = self::build($id);
        $chatRoomMember->setChatRoom(ChatRoom::build($chatRoomId));
        $chatRoomMember->setUser(User::build($userId));
        return $chatRoomMember;
    }
}