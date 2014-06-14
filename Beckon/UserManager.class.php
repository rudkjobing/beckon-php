<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 09/06/14
 * Time: 12:09
 */

class UserManager {

    public static function eatCookie($cookieId, $cookie){
        try{
            return Cookie::build($cookieId, $cookie)->getOwner();
        }
        catch(Exception $e){
            throw $e;
        }
    }

    public static function signIn($email, $password){
        try{
            $user = User::buildFromEmailAndPassword($email, $password);
            $cookie = Cookie::buildNew($user);
            return array("status" => 1, "message" => "User verified", "payload" => array("cookie" => $cookie->jsonSerialize()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function signUp($firstName, $lastName, $email, $password){
        try{
            $user = User::buildNew($firstName, $lastName, $email, $password);
            $cookie = Cookie::buildNew($user);
            return array("status" => 1, "message" => "User created", "payload" => array("cookie" => $cookie->jsonSerialize()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    //Returns the entire data tree of the user
    public static function getSnapshot($user){
        try{
            return array("status" => 1, "message" => "User snapshot", "payload" => array("cookie" => $user->getJsonSerializedTree()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

} 