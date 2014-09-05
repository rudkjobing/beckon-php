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
                BeckonMember::buildNew($beckon, $user, "PENDING");
                ChatRoomMember::buildNew($chatRoom,$user);
                Notification::buildNew($user, "Beckon", 0, $creator->getFirstName() . " Beckons you");
            }
            BeckonMember::buildNew($beckon, $creator, "ACCEPTED");
            ChatRoomMember::buildNew($chatRoom,$creator);

            return array("status" => 1, "message" => "Beckon created", "payload" => array("beckon"=> $beckon->jsonSerialize()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function getBeckons(User $user, $id = 0){
        try{
            $beckons = $user->getBeckons();
            return array("status" => 1, "message" => "Beckons fetched", "payload" => array("beckons" => $beckons->jsonSerialize()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function acceptBeckon(User $user, $beckonId){
        try{
            $beckon = Beckon::build($beckonId);
            $beckonMember = BeckonMember::buildFromUserAndBeckonId($user, $beckonId);
            $beckonMember->setStatus("ACCEPTED");
            $beckonMember->flush();
            Notification::buildNew($beckon->getOwner(), "beckon", $beckon->getId(), "{$user->getFirstName()} has accepted your invitation");
            return array("status" => 1, "message" => "Beckon accepted", "payload" => "");
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function rejectBeckon(User $user, $beckonId){
        try{
            $beckon = Beckon::build($beckonId);
            $beckonMember = BeckonMember::buildFromUserAndBeckonId($user, $beckonId);
            $beckonMember->setStatus("REJECTED");
            $beckonMember->flush();
            Notification::buildNew($beckon->getOwner(), "beckon", $beckon->getId(), "{$user->getFirstName()} has rejected your invitation");
            return array("status" => 1, "message" => "Beckon rejected", "payload" => "");
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

} 