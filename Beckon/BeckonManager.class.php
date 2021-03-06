<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 14/06/14
 * Time: 16:28
 */

class BeckonManager {

    public static function addBeckon(User &$creator, $title, $description, $locationString, $begins, $ends, $groups, $latitude, $longitude, $friends){
        try{
            Beckon::beginTransaction();
            $chatRoom = ChatRoom::buildNew($creator);
            if($description != ""){
                ChatMessage::buildNew($chatRoom, $creator, $description);
            }
            $beckon = Beckon::buildNew($title, $locationString, $creator, $begins, $ends, $latitude, $longitude, $chatRoom);
            $users = array();
            foreach($groups as $group){
                $members = Group::build($group)->getMembers()->getIterator();
                foreach($members as $member){/* @var $member GroupMember */
                    array_push($users, $member->getFriend()->getPeer()->getOwner());
                }
            }
            foreach($friends as $friend){
                $friend = Friend::build($friend);
                array_push($users, $friend->getPeer()->getOwner());
            }
            $users = array_unique($users);
            foreach($users as $user){
                BeckonMember::buildNew($beckon, $user, "PENDING");
                ChatRoomMember::buildNew($chatRoom, $user, false);
                Notification::buildNew($user, "Beckon", 0, $creator->getFirstName() . " Invites you to " . $beckon->getTitle());
            }
            BeckonMember::buildNew($beckon, $creator, "ACCEPTED");
            ChatRoomMember::buildNew($chatRoom, $creator, false);
            $beckon->setInvited(count($users) + 1);
            $beckon->flush();
            Beckon::commitTransaction();
            return array("status" => 1, "message" => "Beckon created", "payload" => array("beckon"=> $beckon->jsonSerialize()));
        }
        catch(Exception $e){
            Beckon::rollbackTransaction();
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function getBeckons(User &$user, $id = 0){
        try{
            $beckons = $user->getBeckons()->getIterator();

            $beckonsArray = array();

            foreach($beckons as $beckon){/* @var $beckon Beckon */

                $myChatRoomMember = ChatRoomMember::buildFromChatRoomAndUser($beckon->getChatRoom(), $user);

                $beckonArray = array(
                    "begins" => $beckon->getBegins(),
                    "chatRoom" => $beckon->getChatRoom()->getId(),
                    "ends" => $beckon->getEnds(),
                    "id" => $beckon->getId(),
                    "latitude" => $beckon->getLatitude(),
                    "locationString" => $beckon->getLocationString(),
                    "longitude" => $beckon->getLongtitude(),
                    "members" => $beckon->getMembers()->jsonSerialize(),
                    "owner" => $beckon->getOwner()->getId(),
                    "title" => $beckon->getTitle(),
                    "hasUnreadMessages" => $myChatRoomMember->getHasUnreadMessages(),
                    "invited" => $beckon->getInvited(),
                    "accepted" => $beckon->getAccepted()
                );
                array_push($beckonsArray, $beckonArray);
            }

            return array("status" => 1, "message" => "Beckons fetched", "payload" => array("beckons" => $beckonsArray));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function acceptBeckon(User &$user, $beckonId){
        try{
            Beckon::beginTransaction();
            $beckon = Beckon::build($beckonId);
            $beckonMember = BeckonMember::buildFromUserAndBeckonId($user, $beckonId);
            if($beckonMember->getStatus() == "PENDING"){
                $beckon->setAccepted($beckon->getAccepted() + 1);
                $beckon->flush();
                $beckonMember->setStatus("ACCEPTED");
                $beckonMember->flush();
                foreach($beckon->getMembers()->getIterator() as $member) {
                    /* @var $member BeckonMember */
                    if($member->getStatus() == "ACCEPTED" && $member->getUser()->getId() != $user->getId()) {
                        Notification::buildNew($member->getUser(), "Beckon", $beckon->getId(), "{$user->getFirstName()} {$user->getLastName()} will attend {$beckon->getTitle()}");
                    }
                }
                Beckon::commitTransaction();
                return array("status" => 1, "message" => "Beckon accepted", "payload" => "");
            }
            elseif($beckonMember->getStatus() == "REJECTED"){
                $beckon->setRejected($beckon->getRejected() - 1);
                $beckon->setAccepted($beckon->getAccepted() + 1);
                $beckon->flush();
                $beckonMember->setStatus("ACCEPTED");
                $beckonMember->flush();
                foreach($beckon->getMembers()->getIterator() as $member) {
                    /* @var $member BeckonMember */
                    if($member->getStatus() == "ACCEPTED" && $member->getUser()->getId() != $user->getId()) {
                        Notification::buildNew($member->getUser(), "Beckon", $beckon->getId(), "{$user->getFirstName()} {$user->getLastName()} will attend {$beckon->getTitle()}");
                    }
                }
                Beckon::commitTransaction();
                return array("status" => 1, "message" => "Beckon accepted", "payload" => "");
            }
            else{
                Beckon::rollbackTransaction();
                return array("status" => 0, "message" => "Beckon already accepted", "payload" => "");
            }
        }
        catch(Exception $e){
            Beckon::rollbackTransaction();
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function rejectBeckon(User &$user, $beckonId){
        try{
            Beckon::beginTransaction();
            $beckon = Beckon::build($beckonId);
            $beckonMember = BeckonMember::buildFromUserAndBeckonId($user, $beckonId);
            if($beckonMember->getStatus() == "PENDING"){
                $beckon->setRejected($beckon->getRejected() + 1);
                $beckon->flush();
                $beckonMember->setStatus("REJECTED");
                $beckonMember->flush();
                foreach($beckon->getMembers()->getIterator() as $member){
                    /* @var $member BeckonMember */
                    if($member->getStatus() == "ACCEPTED" && $member->getUser()->getId() != $user->getId()){
                        Notification::buildNew($member->getUser(), "Beckon", $beckon->getId(), "{$user->getFirstName()} {$user->getLastName()} passed on {$beckon->getTitle()}");
                    }
                }
                Beckon::commitTransaction();
                return array("status" => 1, "message" => "Beckon rejected", "payload" => "");
            }
            elseif($beckonMember->getStatus() == "ACCEPTED"){
                $beckon->setAccepted($beckon->getAccepted() - 1);
                $beckon->setRejected($beckon->getRejected() + 1);
                $beckon->flush();
                $beckonMember->setStatus("REJECTED");
                $beckonMember->flush();
                foreach($beckon->getMembers()->getIterator() as $member) {
                    /* @var $member BeckonMember */
                    if($member->getStatus() == "ACCEPTED" && $member->getUser()->getId() != $user->getId()) {
                        Notification::buildNew($member->getUser(), "Beckon", $beckon->getId(), "{$user->getFirstName()} {$user->getLastName()} passed on {$beckon->getTitle()}");
                    }
                }
                Beckon::commitTransaction();
                return array("status" => 1, "message" => "Beckon rejected", "payload" => "");
            }
            else{
                Beckon::rollbackTransaction();
                return array("status" => 0, "message" => "Beckon already rejected", "payload" => "");
            }
        }
        catch(Exception $e){
            Beckon::rollbackTransaction();
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function deleteBeckon(User &$user, $beckonId){
        try {
            Beckon::beginTransaction();
            $beckon = Beckon::build($beckonId);
            $beckonMember = BeckonMember::buildFromUserAndBeckonId($user, $beckonId);

            $memberCount = 0;
            $newBeckonOwner = null;
            foreach($beckon->getMembers()->getIterator() as $member) {
                /* @var $member BeckonMember */
                if ($member->getStatus() != "REJECTED") {
                    $memberCount++;
                    $newBeckonOwner = $member->getUser();
                }
            }
            if($memberCount == 0){
                $beckon->delete();
            }
            else{
                if($beckon->getOwner()->getId() == $user->getId()){
                    $beckon->setOwner($newBeckonOwner);
                    $beckon->flush();
                }
                $beckonMember->delete();
            }
            Beckon::commitTransaction();
            return array("status" => 1, "message" => "Beckon deleted", "payload" => "");
        }
        catch(Exception $e){
            Beckon::rollbackTransaction();
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function addMembers(User &$user, $beckonId, $members){
        try{
            Beckon::beginTransaction();

            $beckon = Beckon::build($beckonId);
            $users = array();

            foreach($members as $friend){
                $friend = Friend::build($friend);
                array_push($users, $friend->getPeer()->getOwner());
            }
            $users = array_unique($users);
            foreach($users as $user){
                BeckonMember::buildNew($beckon, $user, "PENDING");
                ChatRoomMember::buildNew($beckon->getChatRoom(), $user, false);
                Notification::buildNew($user, "Beckon", 0, $beckon->getOwner()->getFirstName() . " Invites you to " . $beckon->getTitle());
            }
            $beckon->setInvited($beckon->getInvited() + count($users));
            $beckon->flush();
            Beckon::commitTransaction();
            return array("status" => 1, "message" => "Members added", "payload" => "");
        }
        catch(Exception $e){
            Beckon::rollbackTransaction();
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

} 