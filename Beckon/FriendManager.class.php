<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 09/06/14
 * Time: 13:25
 */

class FriendManager {

    public static function addFriend($user, $friendEmailAddress){
        try{
            $friendUser = User::buildFromEmail($friendEmailAddress);
            $friendMe = Friend::buildNew($friendUser->getFirstName() . " " . $friendUser->getLastName(), $user, $friendUser);
            $friendThem = Friend::buildNew($user->getFirstName() . " " . $user->getLastName(), $friendUser, $user);
            $friendThem->setStatus("REQUEST");
            $friendThem->setPeer($friendMe);
            $friendMe->setStatus("PENDING");
            $friendMe->setPeer($friendThem);
            $friendThem->flush();
            $friendMe->flush();
            Notification::buildNew($friendUser,"Friend", 0,"Friend request received");
            return array("status" => 1, "message" => "Friend request sent", "payload" => "");
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function removeFriend($user, $friendId){
        try{
            $friend = Friend::build($friendId);
            if($user->getId() == $friend->getOwner()->getId()){
                $friend->delete();
                return array("status" => 1, "message" => "Friend removed", "payload" => "");
            }
            else{
                return array("status" => 0, "message" => "Operation forbidden", "payload" => "");
            }
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function acceptFriendRequest($user, $friendId){
        try{
            $friend = Friend::build($friendId);
            if($user->getId() == $friend->getOwner()->getId() && $friend->getStatus() == "REQUEST"){
                $friend->setStatus("ACCEPTED");
                $friend->getPeer()->sync();
                $friend->getPeer()->setStatus("ACCEPTED");
                $friend->getPeer()->flush();
                $friend->flush();
                return array("status" => 1, "message" => "Friend request accepted", "payload" => "");
            }
            else{
                return array("status" => 0, "message" => "Operation forbidden", "payload" => "");
            }
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function getFriends($user){
        try{
            return array("status" => 1, "message" => "Friends fetched", "payload" => array("friends" => $user->getFriends()->jsonSerialize()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

} 