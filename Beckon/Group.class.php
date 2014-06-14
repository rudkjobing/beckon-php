<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 06/06/14
 * Time: 17:50
 */

include "GroupMemberCollection.class.php";

class Group extends Persistence implements JsonSerializable{

    protected function __construct($id = null){
        if(!is_null($id)){
            $this->id = $id;
        }
    }

    //Properties
    private $id = null;
    private $name = null;
    private $owner = null;//User
    private $members = null;//GroupMemberCollection of Friends

    //Setters
    protected function setOwner(&$owner){
        if($this->owner != $owner){
            $this->owner = $owner;
            $this->dirty = true;
        }
    }

    public function setName($name){
        $name = self::getConnection()->quote($name);
        if($this->name != $name){
            $this->name = $name;
            $this->dirty = true;
        }
    }

    //Lazy Getters
    public function getId(){
        if(!is_null($this->id)){return $this->id;}
        else{$this->sync();return $this->id;}
    }
    public function getName(){
        if(!is_null($this->name)){return $this->name;}
        else{$this->sync();return $this->name;}
    }
    public function getOwner(){
        if(!is_null($this->owner)){return $this->owner;}
        else{$this->sync();return $this->owner;}
    }
    public function getMembers(){
        if(!is_null($this->members)){return $this->members;}
        else{$this->members = new GroupMemberCollection($this->getId()); return $this->members;}
    }

    //Persistence
    public function flush(){
        if($this->dirty){
            if($this->name == ""){throw new Exception("Object contains empty fields");}
            elseif(!is_null($this->id)){
                $stmt = self::getConnection()->prepare("update `Group` set owner = :owner, name = :name where id = :id");
                $stmt->execute(array("owner" => $this->getOwner()->getId(), "name" => $this->getName(), "id" => $this->getId()));
                //$this->q("update `Group` set owner = {$this->getOwner()->getId()}, name = {$this->getName()} where id = {$this->getId()}");
                $this->dirty = false;
            }
            elseif(is_null($this->id)){
                try{
                    $stmt = self::getConnection()->prepare("insert into `Group` (name, owner) values (:name, :owner");
                    $stmt->execute(array("name" => $this->getName(), "owner" => $this->getOwner()->getId()));
                    //$this->q("insert into `Group` (name, owner) values ({$this->getName()}, {$this->getOwner()->getId()})");
                    $this->id = self::$connection->lastInsertId();
                    self::cachePut("Group", $this->getId(), $this);
                    $this->dirty = false;
                }
                catch(Exception $e){
                    throw $e;
                }
            }
        }
    }

    public function delete(){
        $this->q("delete from Group where id = {$this->id}");
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    public function sync(){
        if(!is_null($this->id)){
            $set = $this->q("select * from `Group` where id = {$this->id}");
            if($set->rowCount() > 0){
                foreach($set as $row){
                    $this->id = $row['id'];
                    $this->owner = User::build($row['owner']);
                    $this->name = $row['name'];
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
        return array("id" => $this->getId(), "owner" => $this->getOwner()->jsonSerialize(), "name" => $this->getName(), "members" => $this->getMembers()->jsonSerialize());//TODO add memberprintout*/
    }

    public static function build($id){
        try{
            return self::cacheGet("Group", $id);
        }
        catch(Exception $e){
            $group = new Group($id);
            self::cachePut("Group", $id, $group);
            return $group;
        }
    }

    public static function buildNew($name, $owner){
        $group = New Group();
        $group->setName($name);
        $group->setOwner($owner);
        try{
            $group->flush();
        }
        catch(Exception $e){
            throw New Exception($e->getMessage(),0 ,$e);
        }
        return $group;
    }

    public static function buildExisting($id, $name, $ownerId){
        $group = self::build($id);
        self::cachePut("Group", $id, $group);
        $group->setName($name);
        try{
            $group->setOwner(self::cacheGet("User", $ownerId));
        }
        catch(Exception $e){
            $group->setOwner(User::build($ownerId));
        }
        return $group;
    }

}