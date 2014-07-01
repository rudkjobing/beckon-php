<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 01/07/14
 * Time: 13:20
 */

class ChatRoomManager {
    public static function getChatRoomMessages($chatRoomId){
        try{
            $chatRoom = ChatRoom::build($chatRoomId);
            return array("status" => 1, "message" => "Messages fetched", "payload" => array("messages" => $chatRoom->getMessages()->jsonSerialize()));
        }
        catch(Exception $e){
            return array("status" => 0, "message" => $e->getMessage(), "payload" => "");
        }
    }
} 