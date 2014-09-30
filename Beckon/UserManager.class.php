<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 09/06/14
 * Time: 12:09
 */

class UserManager {

    /**
     * @param $cookieId
     * @param $nonce
     * @param $hashPresented
     * @return User
     * @throws Exception
     */
    public static function eatCookie($cookieId, $nonce, $hashPresented){
        try{
            $cookie =  Cookie::build($cookieId);
            $key = $cookie->getCookie();
            $hashCalculated = base64_encode(hash_hmac('sha256', $nonce, $key, true));
            if($hashPresented == $hashCalculated){
                return $cookie->getOwner();
            }
            else{
                throw new Exception("Invalid hash");
            }
        }
        catch(Exception $e){
            throw $e;
        }
    }

    public static function signIn($email, $password){
        try{
            $user = User::buildFromEmailAndPassword($email, $password);
            $cookie = Cookie::buildNew($user);
            return array("status" => 1, "message" => "User verified", "payload" => array("cookie" => $cookie->jsonSerialize(), "userId" => $user->getId()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function signUp($firstName, $lastName, $email, $password){
        try{
            $user = User::buildNew($firstName, $lastName, $email, $password);
            $cookie = Cookie::buildNew($user);
            return array("status" => 1, "message" => "User created", "payload" => array("cookie" => $cookie->jsonSerialize(), "userId" => $user->getId()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

//    public static function getState($user){
//        try{
//            return array("status" => 1, "message" => "State", "payload" => array("user" => $user->getJsonSerializedTree()));
//        }
//        catch(Exception $e){
//            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
//        }
//    }

    public static function signOut($cookieId, $cookie){
        try{
            $c = Cookie::build($cookieId, $cookie);
            $c->delete();
            return array("status" => 1, "message" => "User signed out", "payload" => "");
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }


} 