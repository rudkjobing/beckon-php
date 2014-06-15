<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 06/06/14
 * Time: 23:02
 */

include "BeckonMemberCollection.class.php";

class Beckon extends Persistence implements JsonSerializable{

    protected function __construct($id = null){
        if(!is_null($id)){
            $this->id = $id;
        }
    }

    //Properties
    private $id = null;
    private $name = null;
    private $owner = null;//User
    private $members = null;//BeckonMemberCollection of Friends
    private $begins = null;
    private $ends = null;

    //Setters
    protected function setOwner(&$owner){
        if($this->owner != $owner){
            $this->owner = $owner;
            $this->dirty = true;
        }
    }

    public function setName($name){
        if($this->name != $name){
            $this->name = $name;
            $this->dirty = true;
        }
    }

    public function setBegins($begins){
        if($this->begins != $begins){
            $this->begins = $begins;
            $this->dirty = true;
        }
    }

    public function setEnds($ends){
        if($this->ends != $ends){
            $this->ends = $ends;
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
        else{$this->members = new BeckonMemberCollection($this->getId()); return $this->members;}
    }
    public function getBegins(){
        if(!is_null($this->begins)){return $this->begins;}
        else{$this->sync();return $this->begins;}
    }
    public function getEnds(){
        if(!is_null($this->ends)){return $this->ends;}
        else{$this->sync();return $this->ends;}
    }


    //Persistence
    public function flush(){
        if($this->dirty){
            if($this->name == ""){throw new Exception("Object contains empty fields");}
            elseif(!is_null($this->id)){
                $stmt = self::getConnection()->prepare("update Beckon set owner = :owner, name = :name, begins = :begins, ends = :ends where id = :id");
                $stmt->execute(array("name" => $this->getName(), "owner" => $this->getOwner()->getId(), "begins" => $this->getBegins(), "ends" => $this->getEnds(), "id" => $this->getId()));
                $this->dirty = false;
            }
            elseif(is_null($this->id)){
                try{
                    $stmt = self::getConnection()->prepare("insert into Beckon (name, owner, begins, ends) values (:name, :owner, :begins, :ends)");
                    $stmt->execute(array("name" => $this->getName(), "owner" => $this->getOwner()->getId(), "begins" => $this->getBegins(), "ends" => $this->getEnds()));
                    $this->id = self::$connection->lastInsertId();
                    self::cachePut("Beckon", $this->getId(), $this);
                    $this->dirty = false;
                }
                catch(Exception $e){
                    throw $e;
                }
            }
        }
    }

    public function delete(){
        $this->q("delete from Beckon where id = {$this->id}");
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    public function sync(){
        if(!is_null($this->id)){
            $set = $this->q("select * from Beckon where id = {$this->id}");
            if($set->rowCount() > 0){
                foreach($set as $row){
                    $this->id = $row['id'];
                    $this->owner = User::build($row['owner']);
                    $this->name = $row['name'];
                    $this->begins = $row['begins'];
                    $this->ends = $row['ends'];
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
        return array("id" => $this->getId(), "owner" => $this->getOwner()->getId(), "name" => $this->getName(), "begins" => $this->getBegins(), "ends" => $this->getEnds(), "members" => $this->getMembers()->jsonSerialize());//TODO add memberprintout
    }

    //Factory
    public static function build($id){
        try{
            return self::cacheGet("Beckon", $id);
        }
        catch(Exception $e){
            $beckon = new Beckon($id);
            self::cachePut("Beckon", $id, $beckon);
            return $beckon;
        }
    }

    public static function buildNew($name, $owner, $begins, $ends){
        $beckon = New Beckon();
        $beckon->setName($name);
        $beckon->setOwner($owner);
        $beckon->setBegins($begins);
        $beckon->setEnds($ends);
        try{
            $beckon->flush();
        }
        catch(Exception $e){
            throw New Exception($e->getMessage(),0 ,$e);
        }
        return $beckon;
    }

    public static function buildExisting($id, $name, $begins, $ends, $ownerId){
        $beckon = self::build($id);
        $beckon->name = $name;
        $beckon->begins = $begins;
        $beckon->ends = $ends;
        $beckon->owner = User::build($ownerId);
        return $beckon;
    }
}