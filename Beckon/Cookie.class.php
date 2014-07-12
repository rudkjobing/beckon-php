<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 08/06/14
 * Time: 15:19
 */

class Cookie extends Persistence implements JsonSerializable{

    protected function __construct($id = null){
        if(!is_null($id)){
            $this->id = $id;
        }
    }

    //Properties
    private $id = null;
    private $owner = null;//User
    private $cookie = null;

    //Setters
    protected function setOwner(&$owner){
        if($this->owner != $owner){
            $this->owner = $owner;
            $this->dirty = true;
        }
    }

    public function setCookie($cookie){
        if($this->cookie != $cookie){
            $this->cookie = $cookie;
            $this->dirty = true;
        }
    }

    //Lazy Getters
    public function getId(){
        if(!is_null($this->id)){return $this->id;}
        else{$this->sync();return $this->id;}
    }
    public function getOwner(){
        if(!is_null($this->owner)){return $this->owner;}
        else{$this->sync();return $this->owner;}
    }
    public function getCookie(){
        if(!is_null($this->cookie)){return $this->cookie;}
        else{$this->sync();return $this->cookie;}
    }

    public function getUseCounter(){
        if(!is_null($this->useCounter)){return $this->useCounter;}
        else{$this->sync();return $this->useCounter;}
    }

    //Persistence
    public function flush(){
        if($this->dirty){
            if(!is_null($this->id)){
                $this->q("update Cookie set owner = {$this->getOwner()->getId()}, cookie = '{$this->getCookie()}' where id = {$this->getId()}");
                $this->dirty = false;
            }
            elseif(is_null($this->id)){
                try{
                    $this->q("insert into Cookie (owner, cookie) values ({$this->getOwner()->getId()}, '{$this->getCookie()}')");
                    $this->id = self::$connection->lastInsertId();
                    self::cachePut("Cookie", $this->getId(), $this);
                    $this->dirty = false;
                }
                catch(Exception $e){
                    throw $e;
                }
            }
        }
    }

    public function delete(){
        $this->q("delete from Cookie where id = {$this->id}");
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    public function sync(){
        if(!is_null($this->id)){
            $set = $this->q("select * from Cookie where id = {$this->id} and cookie = '{$this->cookie}'");
            if($set->rowCount() > 0){
                foreach($set as $row){
                    $this->id = $row['id'];
                    $this->owner = User::build($row['owner']);
                    $this->cookie = $row['cookie'];
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
        return array("id" => $this->getId(), "owner" => $this->getOwner()->jsonSerialize(), "cookie" => $this->getCookie());
    }

    //Factory
    public static function build($id, $_cookie){
        try{
            return self::cacheGet("Cookie", $id);
        }
        catch(Exception $e){
            $cookie = new Cookie($id);
            $cookie->setCookie($_cookie);
            self::cachePut("Cookie", $id, $cookie);//TODO FIX THIS SHIT
            return $cookie;
        }
    }

    public static function buildNew(&$owner){
        $cookie = New Cookie();
        $cookie->setOwner($owner);
        $cookie->setCookie(sha1(uniqid(rand(),true)));
        try{
            $cookie->flush();
        }
        catch(Exception $e){
            throw New Exception($e->getMessage(),0 ,$e);
        }
        return $cookie;
    }
}