<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 07/06/14
 * Time: 13:14
 */

class BeckonMember extends Persistence implements JsonSerializable{

    protected function __construct($id = null){
        if(!is_null($id)){
            $this->id = $id;
        }
    }

    //Properties
    private $id = null;
    private $user = null;
    private $beckon = null;

    //Setters
    protected function setUser(&$user){
        if($this->user != $user){
            $this->user = $user;
            $this->dirty = true;
        }
    }

    public function setBeckon(&$beckon){
        if($this->beckon != $beckon){
            $this->beckon = $beckon;
            $this->dirty = true;
        }
    }

    //Lazy Getters
    public function getId(){
        if(!is_null($this->id)){return $this->id;}
        else{$this->sync();return $this->id;}
    }
    public function getUser(){
        if(!is_null($this->user)){return $this->user;}
        else{$this->sync();return $this->user;}
    }
    public function getBeckon(){
        if(!is_null($this->beckon)){return $this->beckon;}
        else{$this->sync();return $this->beckon;}
    }

    //Persistence
    public function flush(){
        if($this->dirty){
            if(!is_null($this->id)){
                $this->q("update BeckonMember set user = {$this->getUser()->getId()}, beckon = {$this->getBeckon()->getId()} where id = {$this->getId()}");
                $this->dirty = false;
            }
            elseif(is_null($this->id)){
                try{
                    $this->q("insert into BeckonMember (user, beckon) values ({$this->getUser()->getId()}, {$this->getBeckon()->getId()})");
                    $this->id = self::$connection->lastInsertId();
                    self::cachePut("BeckonMember", $this->getId(), $this);
                    $this->dirty = false;
                }
                catch(Exception $e){
                    throw $e;
                }
            }
        }
    }

    public function delete(){
        $this->q("delete from BeckonMember where id = {$this->getId()}");
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    public function sync(){
        if(!is_null($this->id)){
            $set = $this->q("select * from GroupMember where id = {$this->getId()}");
            if($set->rowCount() > 0){
                foreach($set as $row){
                    $this->id = $row['id'];
                    $this->user = User::build($row['user']);
                    $this->beckon = Beckon::build($row['beckon']);
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
        return array("user" => $this->getUser()->jsonSerialize(), "beckon" => $this->getBeckon()->getId());
    }

    //Factory
    public static function build($id){
        try{
            return self::cacheGet("BeckonMember", $id);
        }
        catch(Exception $e){
            $beckonMember = new BeckonMember($id);
            self::cachePut("BeckonMember", $id, $beckonMember);
            return $beckonMember;
        }
    }

    public static function buildNew($beckon, $user){
        $beckonMember = New BeckonMember();
        $beckonMember->setBeckon($beckon);
        $beckonMember->setUser($user);
        try{
            $beckonMember->flush();
        }
        catch(Exception $e){
            throw New Exception($e->getMessage(),0 ,$e);
        }
        return $beckonMember;
    }

    public static function buildExisting($id, $beckonId, $userId){
        $beckonMember = New BeckonMember($id);
        self::cachePut("BeckonMember", $id, $beckonMember);
        try{
            $beckonMember->setBeckon(self::cacheGet("Beckon", $beckonId));
        }
        catch(Exception $e){
            $beckonMember->setBeckon(Group::build($beckonId));
        }
        try{
            $beckonMember->setuser(self::cacheGet("User", $userId));
        }
        catch(Exception $e){
            $beckonMember->setFriend(User::build($userId));
        }
        return $beckonMember;
    }
}