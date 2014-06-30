<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 14/06/14
 * Time: 16:28
 */

class BeckonManager {

    public static function addBeckon(User $creator, $title, $begins, $ends, $groups, $friends){
        try{
            $beckon = Beckon::buildNew($title, $creator, $begins, $ends);
            $users = array();
            foreach($groups as $group){
                $members = Group::build($group)->getMembers()->getIterator();
                foreach($members as $member){
                    array_push($users, $member->getFriend()->getPeer()->getOwner());
                }
            }
            foreach($friends as $friend){
                $friend = Friend::build($friend);
                array_push($users, $friend->getPeer()->getOwner());
            }
            $users = array_unique($users);
            foreach($users as $user){
                $beckonMember = BeckonMember::buildNew($beckon, $user);
                Notification::buildNew($user, $beckon, $creator->getFirstName() . " has sent you a Beckon");
            }
            BeckonMember::buildNew($beckon, $creator);

            return array("status" => 1, "message" => "Beckon created", "payload" => array("beckon"=> $beckon->jsonSerialize()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function getBeckons(User $user){
        try{
            return array("status" => 1, "message" => "Friends fetched", "payload" => array("beckons" => $user->getBeckons()->jsonSerialize()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

} 