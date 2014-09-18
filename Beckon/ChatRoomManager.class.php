<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 01/07/14
 * Time: 13:20
 */

class ChatRoomManager {
    public static function getChatRoomMessages(User &$user, $chatRoomId){
        try{
            $chatRoom = ChatRoom::build($chatRoomId);
            $messages = $chatRoom->getMessages()->getIterator();
            $chatRoomMember = ChatRoomMember::buildFromChatRoomAndUser($chatRoom, $user);
            $result = array();
            foreach($messages as $message){/* @var $message ChatMessage */
                $msg = array("id" => $message->getId(), "from" => $message->getOwner()->getFirstName() . " " . $message->getOwner()->getLastName(), "message" => $message->getMessage(), "date" => $message->getDate());
                if($message->getOwner() == $user){
                    $msg['fromMe'] = true;
                }
                else{
                    $msg['fromMe'] = false;
                }
                array_push($result, $msg);
            }
            $chatRoomMember->setHasUnreadMessages(0);
            $chatRoomMember->flush();
            return array("status" => 1, "message" => "Messages fetched", "payload" => array("messages" =>$result));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

    public static function putChatRoomMessage(User &$user, $chatRoomId, $message){
        try{
            ChatRoom::beginTransaction();
            $chatRoom = ChatRoom::build($chatRoomId);
            $chatMessage = ChatMessage::buildNew($chatRoom, $user, $message);
            $members = $chatRoom->getMembers()->getIterator();
            foreach($members as $member){/* @var $member ChatRoomMember */
                if($member->getUser() != $user){
                    $member->setHasUnreadMessages(1);
                    $member->flush();
                    Notification::buildNew($member->getUser(), "ChatRoom", $chatRoomId, $chatMessage->getOwner()->getFirstName() . ": " . $message);
                }
            }
            ChatRoom::commitTransaction();
            return array("status" => 1, "message" => "Message recieved", "payload" => "");
        }
        catch(Exception $e){
            ChatRoom::rollbackTransaction();
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }

} 