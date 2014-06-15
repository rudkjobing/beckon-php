<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 14/06/14
 * Time: 16:28
 */

class BeckonManager {

    public static function addBeckon($creator, $beckonName, $begins, $ends, $groups, $friends){
        try{
            $beckon = Beckon::buildNew($beckonName, $creator, $begins, $ends);
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
                Notification::buildNew($user, $beckon, "You have been invited to join this Beckon");
            }
            BeckonMember::buildNew($beckon, $creator);

            return array("status" => 1, "message" => "Beckon created", "payload" => array("beckon"=> $beckon->jsonSerialize()));
        }
        catch(Exception $e){
            //return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
            throw $e;
        }
    }

} 