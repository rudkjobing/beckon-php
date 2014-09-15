<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 05/06/14
 * Time: 13:25
 */

include_once "FriendCollection.class.php";
include_once "GroupCollection.class.php";
include_once "BeckonCollection.class.php";
include_once "DeviceCollection.class.php";
include_once "NotificationCollection.class.php";
include_once "ChatRoomCollection.class.php";

class User extends Persistence implements JsonSerializable{

    protected function __construct($id = null){
        if(!is_null($id)){
            $this->id = $id;
        }
    }

    //Properties
    private $id = null;
    private $firstName = null;
    private $lastName = null;
    private $emailAddress = null;
    private $password = null;
    private $devices;//Device collection
    private $friends;//Friend collection
    private $beckons;//Beckon collection
    private $groups;//Group collection
    private $notifications;//Notification collection
    private $chatRooms;//ChatRoom collection


    //Setters
    public function setFirstName($firstName){
        if($this->firstName != $firstName){
            $this->firstName = $firstName;
            $this->dirty = true;
        }
    }

    public function setLastName($lastName){
        if($this->lastName != $lastName){
            $this->lastName = $lastName;
            $this->dirty = true;
        }
    }

    public function setEmailAddress($emailAddress){
        if($this->emailAddress != $emailAddress){
            $this->emailAddress = $emailAddress;
            $this->dirty = true;
        }
    }
    public function setPassword($password){
        $this->password = $password;
        $this->dirty = true;
    }

    //Lazy Getters
    public function getId(){
        if(!is_null($this->id)){return $this->id;}
        else{$this->sync();return $this->id;}
    }
    public function getFirstName(){
        if(!is_null($this->firstName)){return $this->firstName;}
        else{$this->sync();return $this->firstName;}
    }
    public function getLastName(){
        if(!is_null($this->lastName)){return $this->lastName;}
        else{$this->sync();return $this->lastName;}
    }
    public function getEmailAddress(){
        if(!is_null($this->emailAddress)){return $this->emailAddress;}
        else{$this->sync();return $this->emailAddress;}
    }
    public function getPassword(){
        if(!is_null($this->password)){return $this->password;}
        else{$this->sync();return $this->password;}
    }
    public function getDevices(){
        if(!is_null($this->devices)){return $this->devices;}
        else{$this->devices = new DeviceCollection($this->id);return $this->devices;}
    }
    public function getFriends(){
        if(!is_null($this->friends)){return $this->friends;}
        else{$this->friends = new FriendCollection($this->id);return $this->friends;}
    }
    public function getGroups(){
        if(!is_null($this->groups)){return $this->groups;}
        else{$this->groups = new GroupCollection($this->id);return $this->groups;}
    }
    public function getBeckons($newest = 0){
        if(!is_null($this->beckons)){return $this->beckons;}
        else{$this->beckons = new BeckonCollection($this->id, $newest);return $this->beckons;}
    }
    public function getNotifications(){
        if(!is_null($this->notifications)){return $this->notifications;}
        else{$this->notifications = new NotificationCollection($this->id);return $this->notifications;}
    }
    public function getChatRooms(){
        if(!is_null($this->chatRooms)){return $this->chatRooms;}
        else{$this->chatRooms = new ChatRoomCollection($this->id);return $this->chatRooms;}
    }

    //Persistence
    public function flush(){
        if($this->dirty){
            if($this->firstName == "" || $this->lastName == "" || $this->emailAddress == ""|| $this->password == ""){throw new Exception("Object contains empty fields");}
            elseif(!is_null($this->id)){
                $stmt = self::getConnection()->prepare("update User set firstName = :firstName, lastName = :lastName, emailAddress = :emailAddress, password = :password where id = :id");
                $stmt->execute(array("firstName" => $this->getFirstName(), "lastName" => $this->getLastName(), "emailAddress" => $this->getEmailAddress(), "password" => $this->getPassword(), "id" => $this->getId()));
                //$this->q("update User set firstName = {$this->getFirstName()}, lastName = {$this->getLastName()}, emailAddress = {$this->getEmailAddress()}, password = {$this->getPassword()} where id = {$this->getId()}");
                $this->dirty = false;
            }
            elseif(is_null($this->id)){
                try{
                    $stmt = self::getConnection()->prepare("insert into User (firstName, lastName, emailAddress, password) values (:firstName, :lastName, :emailAddress, :password)");
                    $stmt->execute(array("firstName" => $this->getFirstName(), "lastName" => $this->getLastName(), "emailAddress" => $this->getEmailAddress(), "password" => $this->getPassword()));
                    //$this->q("insert into User (firstName, lastName, emailAddress, password) values ({$this->getFirstName()}, {$this->getLastName()}, {$this->getEmailAddress()}, {$this->password})");
                    $this->id = self::$connection->lastInsertId();
                    self::cachePut("User", $this->getId(), $this);
                    $this->dirty = false;
                }
                catch(Exception $e){
                    throw new Exception("Flush failed", 0, $e);
                }
            }
        }
    }

    public function delete(){
        $this->q("delete from User where id = {$this->getId()}");
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    public function sync(){
        if($this->id){
            $set = $this->q("select * from User where id = {$this->getId()}");
            if($set->rowCount() > 0){
                foreach($set as $row) {
                    $this->id = $row['id'];
                    $this->firstName = $row['firstName'];
                    $this->lastName = $row['lastName'];
                    $this->emailAddress = $row['emailAddress'];
                    $this->password = $row['password'];
                    $this->dirty = false;
                }
            }
            else{
                throw new Exception(__FUNCTION__ . " Object does not exist");
            }
        }
        else{
            throw New Exception("User with id " . $this->getId() . " does not exist");
        }
    }

    //This method will make array_unique work on an array of users
    public function __toString() {
        return strval($this->getId());
    }

    //Serialization
    public function jsonSerialize() {
        return array("firstName" => $this->getFirstName(), "lastName" => $this->getLastName(), "emailAddress" => $this->getEmailAddress());
    }

    public function getJsonSerializedTree() {
        return array("id" => $this->getId(), "firstName" => $this->getFirstName(), "lastName" => $this->getLastName(), "emailAddress" => $this->getEmailAddress(), "devices" => $this->getDevices()->jsonSerialize(), "friends" => $this->getFriends()->jsonSerialize(), "beckons" => $this->getBeckons()->jsonSerialize(), "groups" => $this->getGroups()->jsonSerialize(), "notifications" => $this->getNotifications()->jsonSerialize(), "chatRooms" => $this->getChatRooms()->jsonSerialize());
    }

    //Factory
    public static function build($id){
        try{
            return self::cacheGet("User", $id);
        }
        catch(Exception $e){
            $user = new User($id);
            self::cachePut("User", $id, $user);
            return $user;
        }
    }

    public static function buildNew($firstName, $lastName, $emailAddress, $password){
        $user = New User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmailAddress($emailAddress);
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
        try{
            $user->flush();
        }
        catch(Exception $e){
            throw new Exception("Build failed", 0, $e);
        }
        return $user;
    }

    public static function buildFromEmailAndPassword($email, $password){

        $stmt = self::getConnection()->prepare("select * from User where emailAddress = :email");
        $stmt->execute(array("email" => $email));
        $set = $stmt->fetchAll();
        if($stmt->rowCount() > 0){
            foreach($set as $row){
                if(password_verify($password, $row['password'])){
                    $user = self::build($row['id']);
                    $user->emailAddress = $row['emailAddress'];
                    $user->firstName = $row['firstName'];
                    $user->lastName = $row['lastName'];
                    return $user;
                }
                else{
                    throw new Exception("Verification failed");
                }
            }
        }
        else{
            throw new Exception("Verification failed");
        }
    }

    public static function buildFromEmail($email){
        $stmt = self::getConnection()->prepare("select * from User where emailAddress = :email");
        $stmt->execute(array("email" => $email));
        $set = $stmt->fetchAll();
        if($stmt->rowCount() > 0){
            foreach($set as $row){
                $user = self::build($row['id']);
                $user->emailAddress = $row['emailAddress'];
                $user->firstName = $row['firstName'];
                $user->lastName = $row['lastName'];
                return $user;
            }
        }
        else{
            throw new Exception("Object not found");
        }
    }

}