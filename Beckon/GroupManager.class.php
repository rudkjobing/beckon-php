<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 09/06/14
 * Time: 12:18
 */

class GroupManager {

    public static function addGroup($user, $groupName){
        try{
            $group = Group::buildNew($groupName, $user);
            return array("status" => 1, "message" => "Group created", "payload" => array("group"=> $group->jsonSerialize()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function getGroups($user){
        try{
            $groups = $user->getGroups();
            return array("status" => 1, "message" => "Group created", "payload" => array("groups"=> $groups->jsonSerialize()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function getGroupMembers($user, $groupId){
        try{
            $group = Group::build($groupId);
            if($group->getOwner()->getId() == $user->getId()){
                return array("status" => 1, "message" => "Group created", "payload" => array("groups"=> $group->getMembers()->jsonSerialize()));
            }
            else{
                return array("status" => 0, "message" => "Operation forbidden", "payload" => "");
            }
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function addGroupMember($user, $groupId, $friendId){
        try{
            $group = Group::build($groupId);
            $friend = Friend::build($friendId);
            if($group->getOwner()->getId() == $user->getId() && $friend->getOwner()->getId() == $user->getId()){
                $member = GroupMember::buildNew($group, $friend);
                return array("status" => 1, "message" => "Member added", "payload" => array("member"=> $member->jsonSerialize()));
            }
            else{
                return array("status" => 0, "message" => "Operation forbidden", "payload" => "");
            }
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function deleteGroup($user, $groupId){
        try{
            $group = Group::build($groupId);
            if($group->getOwner()->getId() == $user->getId()){
                $group->delete();
                return array("status" => 1, "message" => "Group deleted", "payload" => "");
            }
            else{
                return array("status" => 0, "message" => "Operation forbidden", "payload" => "");
            }
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function removeMember($user, $memberId){
        try{
            $member = GroupMember::build($memberId);
            if($member->getGroup()->getOwner->getId() == $user->getId()){
                $member->delete();
                return array("status" => 1, "message" => "Group member deleted", "payload" => "");
            }
            else{
                return array("status" => 0, "message" => "Operation forbidden", "payload" => "");
            }
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

} 