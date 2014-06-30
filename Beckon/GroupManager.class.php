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
            $group = Group::buildNew($user, $groupName);
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
                return array("status" => 1, "message" => "Group created", "payload" => array("groupMembers"=> $group->getMembers()->jsonSerialize()));
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
            if($group->getOwner() == $user && $friend->getOwner() == $user){
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

    public static function delete($user, $groupId){
        try{
            $group = Group::build($groupId);
            if($group->getOwner() == $user){
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

    public static function removeGroupMember($user, $friendId, $groupId){
        try{
            $group = Group::build($groupId);
            $friend = Friend::build($friendId);
            $members = $group->getMembers()->getIterator();
            foreach($members as $member){
                if($member->getFriend() == $friend){
                    $member->delete();
                    return array("status" => 1, "message" => "Group member deleted", "payload" => "");
                }
            }
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

} 