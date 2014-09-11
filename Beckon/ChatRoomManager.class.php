<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 01/07/14
 * Time: 13:20
 */

class ChatRoomManager {
    public static function getChatRoomMessages(&$user, $chatRoomId){
        try{
            $chatRoom = ChatRoom::build($chatRoomId);
            $messages = $chatRoom->getMessages()->getIterator();
            $result = array();
            foreach($messages as $message){
                $msg = array("id" => $message->getId(), "from" => $message->getOwner()->getFirstName() . " " . $message->getOwner()->getLastName(), "message" => $message->getMessage(), "date" => $message->getDate());
                if($message->getOwner() == $user){
                    $msg['fromMe'] = true;
                }
                else{
                    $msg['fromMe'] = false;
                }
                array_push($result, $msg);
            }
            return array("status" => 1, "message" => "Messages fetched", "payload" => array("messages" =>$result));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function putChatRoomMessage(&$user, $chatRoomId, $message){
        try{
            $chatRoom = ChatRoom::build($chatRoomId);
            ChatMessage::buildNew($chatRoom, $user, $message);
            $members = $chatRoom->getMembers()->getIterator();
            foreach($members as $member){
                if($member->getUser() != $user){
                    Notification::buildNew($member->getUser(), "ChatRoom", $chatRoomId, $message);
                }
            }
            return array("status" => 1, "message" => "Message recieved", "payload" => "");
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

} 