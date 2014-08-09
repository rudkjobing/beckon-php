<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 14/06/14
 * Time: 16:28
 */

class BeckonManager {

    public static function addBeckon(User $creator, $title, $begins, $ends, $groups, $latitude, $longitude, $friends){
        try{
            $chatRoom = ChatRoom::buildNew($creator);
            $beckon = Beckon::buildNew($title, $creator, $begins, $ends, $latitude, $longitude, $chatRoom);
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
            //$users = array_unique($users);
            foreach($users as $user){
                $beckonMember = BeckonMember::buildNew($beckon, $user, "PENDING");
                $chatRoomMember = ChatRoomMember::buildNew($chatRoom,$user);
                Notification::buildNew($user, "Beckon", 0, $creator->getFirstName() . " Beckons you");
            }
            BeckonMember::buildNew($beckon, $creator, "ACCEPTED");
            $chatRoomMember = ChatRoomMember::buildNew($chatRoom,$creator);

            return array("status" => 1, "message" => "Beckon created", "payload" => array("beckon"=> $beckon->jsonSerialize()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function getBeckons(User $user, $id = 0){
        try{
            $beckons = $user->getBeckons();
            return array("status" => 1, "message" => "Beckons fetched æøå", "payload" => array("beckons" => $beckons->jsonSerialize()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

} 