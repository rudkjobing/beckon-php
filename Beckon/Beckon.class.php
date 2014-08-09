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
    private $title = null;
    private $owner = null;//User
    private $members = null;//BeckonMemberCollection of Friends
    private $begins = null;
    private $ends = null;
    private $latitude = null;
    private $longitude = null;
    private $chatRoom = null;

    //Setters
    protected function setOwner(&$owner){
        if($this->owner != $owner){
            $this->owner = $owner;
            $this->dirty = true;
        }
    }

    public function setTitle($title){
        if($this->title != $title){
            $this->title = $title;
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

    public function setLatitude($latitude){
        if($this->latitude != $latitude){
            $this->latitude = $latitude;
            $this->dirty = true;
        }
    }

    public function setLongtitude($longitude){
        if($this->longitude != $longitude){
            $this->longitude = $longitude;
            $this->dirty = true;
        }
    }

    public function setChatRoom(&$chatRoom){
        if($this->chatRoom != $chatRoom){
            $this->chatRoom = $chatRoom;
            $this->dirty = true;
        }
    }

    //Lazy Getters
    public function getId(){
        if(!is_null($this->id)){return $this->id;}
        else{$this->sync();return $this->id;}
    }
    public function getTitle(){
        if(!is_null($this->title)){return $this->title;}
        else{$this->sync();return $this->title;}
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
    public function getLatitude(){
        if(!is_null($this->latitude)){return $this->latitude;}
        else{$this->sync();return $this->latitude;}
    }
    public function getLongtitude(){
        if(!is_null($this->longitude)){return $this->longitude;}
        else{$this->sync();return $this->longitude;}
    }
    public function getChatRoom(){
        if(!is_null($this->chatRoom)){return $this->chatRoom;}
        else{$this->sync();return $this->chatRoom;}
    }


    //Persistence
    public function flush(){
        if($this->dirty){
            if($this->title == ""){throw new Exception("Object contains empty fields");}
            elseif(!is_null($this->id)){
                $stmt = self::getConnection()->prepare("update Beckon set owner = :owner, title = :title, begins = :begins, ends = :ends, latitude = :latitude, longitude = :longitude, chatRoom = :chatRoom where id = :id");
                $stmt->execute(array("title" => $this->getTitle(), "owner" => $this->getOwner()->getId(), "begins" => $this->getBegins(), "ends" => $this->getEnds(), "latitude" => $this->getLatitude(), "longitude" => $this->getLongtitude(), "chatRoom" => $this->getChatRoom()->getId(), "id" => $this->getId()));
                $this->dirty = false;
            }
            elseif(is_null($this->id)){
                try{
                    $stmt = self::getConnection()->prepare("insert into Beckon (title, owner, begins, ends, latitude, longitude, chatRoom) values (:title, :owner, :begins, :ends, :latitude, :longitude, :chatRoom)");
                    $stmt->execute(array("title" => $this->getTitle(), "owner" => $this->getOwner()->getId(), "begins" => $this->getBegins(), "ends" => $this->getEnds(), "latitude" => $this->getLatitude(), "longitude" => $this->getLongtitude(), "chatRoom" => $this->getChatRoom()->getId()));
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
                    $this->title = $row['title'];
                    $this->begins = $row['begins'];
                    $this->ends = $row['ends'];
                    $this->latitude = $row['latitude'];
                    $this->longitude = $row['longitude'];
                    $this->chatRoom = ChatRoom::build($row['chatRoom']);
                    $this->dirty = false;
                }
            }
            else{
                throw new Exception("Beckon with id " . $this->getId() . " does not exist");
            }
        }
        else{
            throw New Exception("Unable to sync unknown object instance");
        }
    }

    //Serialization
    public function jsonSerialize(){
        return array("id" => $this->getId(), "owner" => $this->getOwner()->getId(), "title" => $this->getTitle(), "begins" => $this->getBegins(), "ends" => $this->getEnds(), "latitude" => $this->getLatitude(), "longitude" => $this->getLongtitude(), "chatRoom" => $this->getChatRoom()->getId());
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

    public static function buildNew($title, &$owner, $begins, $ends, $latitude, $longitude, &$chatRoom){
        $beckon = New Beckon();
        $beckon->setTitle($title);
        $beckon->setOwner($owner);
        $beckon->setBegins($begins);
        $beckon->setEnds($ends);
        $beckon->setLatitude($latitude);
        $beckon->setLongtitude($longitude);
        $beckon->setChatRoom($chatRoom);
        try{
            $beckon->flush();
        }
        catch(Exception $e){
            throw New Exception($e->getMessage(),0 ,$e);
        }
        return $beckon;
    }

    public static function buildExisting($id, $title, $begins, $ends, $ownerId, $latitude, $longitude,  $chatRoomId){
        $beckon = self::build($id);
        $beckon->title = $title;
        $beckon->begins = $begins;
        $beckon->ends = $ends;
        $beckon->owner = User::build($ownerId);
        $beckon->latitude = $latitude;
        $beckon->longitude = $longitude;
        $beckon->chatRoom = ChatRoom::build($chatRoomId);
        return $beckon;
    }
}