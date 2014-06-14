<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 06/06/14
 * Time: 18:53
 */

class GroupMember extends Persistence implements JsonSerializable{

    protected function __construct($id = null){
        if(!is_null($id)){
            $this->id = $id;
        }
    }

    //Properties
    private $id = null;
    private $friend = null;
    private $group = null;

    //Setters
    protected function setFriend(&$friend){
        if($this->friend != $friend){
            $this->friend = $friend;
            $this->dirty = true;
        }
    }

    public function setGroup(&$group){
        if($this->group != $group){
            $this->group = $group;
            $this->dirty = true;
        }
    }

    //Lazy Getters
    public function getId(){
        if(!is_null($this->id)){return $this->id;}
        else{$this->sync();return $this->id;}
    }
    public function getFriend(){
        if(!is_null($this->friend)){return $this->friend;}
        else{$this->sync();return $this->friend;}
    }
    public function getGroup(){
        if(!is_null($this->group)){return $this->group;}
        else{$this->sync();return $this->group;}
    }

    //Persistence
    public function flush(){
        if($this->dirty){
            if(!is_null($this->id)){
                $this->q("update GroupMember set friend = {$this->getFriend()->getId()}, `group` = {$this->getGroup()->getId()} where id = {$this->getId()}");
                $this->dirty = false;
            }
            elseif(is_null($this->id)){
                try{
                    $this->q("insert into GroupMember (friend, `group`) values ({$this->getFriend()->getId()}, {$this->getGroup()->getId()})");
                    $this->id = self::$connection->lastInsertId();
                    self::cachePut("GroupMember", $this->getId(), $this);
                    $this->dirty = false;
                }
                catch(Exception $e){
                    throw $e;
                }
            }
        }
    }

    public function delete(){
        $this->q("delete from GroupMember where id = {$this->id}");
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    public function sync(){
        if(!is_null($this->id)){
            $set = $this->q("select * from GroupMember where id = {$this->id}");
            if($set->rowCount() > 0){
                foreach($set as $row){
                    $this->id = $row['id'];
                    $this->friend = Friend::build($row['friend']);
                    $this->group = Group::build($row['group']);
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
        return array("friend" => $this->getFriend()->jsonSerialize(), "group" => $this->getGroup()->getId());//TODO add memberprintout
    }

    //Factory
    public static function build($id){
        try{
            return self::cacheGet("GroupMember", $id);
        }
        catch(Exception $e){
            $groupMember = new GroupMember($id);
            self::cachePut("GroupMember", $id, $groupMember);
            return $groupMember;
        }
    }

    public static function buildNew($group, $friend){
        $groupMember = New GroupMember();
        $groupMember->setGroup($group);
        $groupMember->setFriend($friend);
        try{
            $groupMember->flush();
        }
        catch(Exception $e){
            throw New Exception($e->getMessage(),0 ,$e);
        }
        return $groupMember;
    }

    public static function buildExisting($id, $groupId, $friendId){
        $groupMember = self::build($id);
        self::cachePut("GroupMember", $id, $groupMember);
        $groupMember->setGroup(Group::build($groupId));
        $groupMember->setFriend(Friend::build($friendId));
        return $groupMember;
    }
}