<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 05/06/14
 * Time: 15:40
 */

class Friend extends Persistence implements JsonSerializable{

    protected function __construct($id = null){
        if(!is_null($id)){
            $this->id = $id;
        }
    }

    //Properties
    private $id = null;
    private $owner = null;
    private $user = null;
    private $nickName = null;
    private $peer = null;
    private $status = null;

    //Setters
    protected function setOwner(&$owner){
        if($this->owner != $owner){
            $this->owner = $owner;
            $this->dirty = true;
        }
    }

    protected function setUser(&$user){
        if($this->user != $user){
            $this->user = $user;
            $this->dirty = true;
        }
    }

    public function setNickName($nickName){
        if($this->nickName != $nickName){
            $this->nickName = $nickName;
            $this->dirty = true;
        }
    }

    public function setPeer(&$peer){
        if($this->peer != $peer){
            $this->peer = $peer;
            $this->dirty = true;
        }
    }

    public function setStatus($status){
        if($this->status != $status){
            $this->status = $status;
            $this->dirty = true;
        }
    }

    //Lazy Getters
    public function getId(){
        if(!is_null($this->id)){return $this->id;}
        else{$this->sync();return $this->id;}
    }

    /**
     * @return User
     * @throws Exception
     */
    public function getOwner(){
        if(!is_null($this->owner)){return $this->owner;}
        else{$this->sync();return $this->owner;}
    }

    /**
     * @return User
     * @throws Exception
     */
    public function getUser(){
        if(!is_null($this->user)){return $this->user;}
        else{$this->sync();return $this->user;}
    }
    public function getNickName(){
        if(!is_null($this->nickName)){return $this->nickName;}
        else{$this->sync();return $this->nickName;}
    }

    /**
     * @return Friend
     * @throws Exception
     */
    public function getPeer(){
        if(!is_null($this->peer)){return $this->peer;}
        else{$this->sync();return $this->peer;}
    }
    public function getStatus(){
        if(!is_null($this->status)){return $this->status;}
        else{$this->sync();return $this->status;}
    }

    //Persistence
    public function flush(){
        if($this->dirty){
            if(!is_null($this->id)){
                $stmt = self::getConnection()->prepare("update Friend set owner = :owner, nickName = :nickName, peer = :peer, status = :status, user = :user where id = :id");
                $stmt->execute(array("owner" => $this->getOwner()->getId(), "nickName" => $this->getNickName(), "peer" => $this->getPeer()->getId(), "status" => $this->getStatus(), "user" => $this->getUser()->getId(), "id" => $this->getId()));
                //$this->q("update Friend set owner = {$this->getOwner()->getId()}, nickName = {$this->getNickName()}, peer = {$this->getPeer()->getId()}, status = {$this->getStatus()}, user = {$this->getUser()->getId()} where id = {$this->getId()}");
                $this->dirty = false;
            }
            elseif(is_null($this->id)){
                try{
                    $stmt = self::getConnection()->prepare("insert into Friend (nickName, owner, peer) values (:nickName, :owner, :peer)");
                    $stmt->execute(array("nickName" => $this->getNickName(), "owner" => $this->getOwner()->getId(), "peer" => $this->getPeer()->getId()));
                    //$q = $this->q("insert into Friend (nickName, owner, peer) values ({$this->getNickName()}, {$this->getOwner()->getId()}, {$this->getPeer()->getId()})");
                    $this->id = self::$connection->lastInsertId();
                    self::cachePut("Friend", $this->getId(), $this);
                    $this->dirty = false;
                }
                catch(Exception $e){
                    throw $e;
                }
            }
        }
    }

    public function delete(){
        $this->q("delete from Friend where id = {$this->getId()}");
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    public function sync(){
        if(!is_null($this->id)){
            $set = $this->q("select * from Friend where id = {$this->getId()}");
            if($set->rowCount() > 0){
                foreach($set as $row){
                    $this->id = $row['id'];
                    $this->owner = User::build($row['owner']);
                    $this->user = User::build($row['user']);
                    $this->nickName = $row['nickName'];
                    $this->peer = Friend::build($row['peer']);
                    $this->status = $row['status'];
                    $this->dirty = false;
                }
            }
            else{
                throw new Exception("Object does not exist");
            }
        }
        else{
            throw New Exception("Unable to sync unknown Friend instance");
        }
    }

    //Serialization
    public function jsonSerialize(){
        return array("id" => $this->getId(), "user" => $this->getUser()->jsonSerialize(), "nickname" => $this->getNickName(), "status" => $this->getStatus());
    }

    //Factory
    public static function build($id){
        try{
            return self::cacheGet("Friend", $id);
        }
        catch(Exception $e){
            $friend = new Friend($id);
            self::cachePut("Friend", $id, $friend);
            return $friend;
        }
    }

    public static function buildNew($nickName, $owner, $user){
        $friend = New Friend();
        $friend->setNickName($nickName);
        $friend->setOwner($owner);
        $friend->setUser($user);
        try{
            $stmt = self::getConnection()->prepare("insert into Friend (nickName, owner, user) values (:nickName, :owner, :user)");
            $stmt->execute(array("nickName" => $nickName, "owner" => $owner->getId(), "user" => $user->getId()));
            //$q = self::q("insert into Friend (nickName, owner, user) values ('$nickName', {$owner->getId()}, {$user->getId()})");
            $friend->id = self::$connection->lastInsertId();
            self::cachePut("Friend", $friend->getId(), $friend);
        }
        catch(Exception $e){
            throw New Exception($e->getMessage(),0 ,$e);
        }
        return $friend;
    }

    public static function buildExisting($id, $nickName, $ownerId, $userId, $peerId){
        try{
            return self::cacheGet("Friend", $id);
        }
        catch(Exception $e){
            $friend = self::build($id);
            self::cachePut("Friend", $id, $friend);
            $friend->setNickName($nickName);
            try{
                $friend->setUser(self::cacheGet("User", $userId));
            }
            catch(Exception $e){
                $friend->setUser(User::build($userId));
            }
            try{
                $friend->setOwner(self::cacheGet("User", $ownerId));
            }
            catch(Exception $e){
                $friend->setOwner(User::build($ownerId));
            }
            try{
                $friend->setPeer(self::cacheGet("Friend", $peerId));
            }
            catch(Exception $e){
                $friend->setPeer(Friend::build($peerId));
            }
            return $friend;
        }
    }
}