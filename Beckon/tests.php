<?php

include_once "Persistence.abstract.php";
include_once "UserManager.class.php";
include_once "FriendManager.class.php";
include_once "GroupManager.class.php";
include_once "BeckonManager.class.php";
include_once "ChatRoomManager.class.php";
include_once "NotificationManager.class.php";
include_once "Beckon.class.php";
include_once "User.class.php";
include_once "Friend.class.php";
include_once "Group.class.php";
include_once "GroupMember.class.php";
include_once "Cookie.class.php";
include_once "Collection.interface.php";
include_once "BeckonMember.class.php";
include_once "Notification.class.php";
include_once "ChatRoom.class.php";
include_once "ChatRoomMember.class.php";
include_once "ChatMessage.class.php";
include_once "DeviceManager.class.php";

try{
    //$cookie = UserManager::signUp("Bertil", "Andersen", "bertil@gmail.com", "test1234");/*
    $cookie2 = UserManager::signUp("Steffen", "Rudkjøbing", "steffen@gmail.com", "test1234");

    //$bertil = UserManager::eatCookie(15, "05aacbea048f11056adfb52ecc680acba0a97a51");
   // $steffen = UserManager::eatCookie(16, "74c99989d13cf1ce7d64b75a5695dcd0179013b3");

    //$res = UserManager::signIn("slyngel@gmail.com", "test1234");

    $res = UserManager::eatCookie(4, "e257c39f059d908cf0b58786cfe7250eadeb3952");
    //User::buildNew("Steffen", "R", "asd", "asd");
    //$notification = NotificationManager::getNotification($res, 1);
    BeckonManager::addBeckon($res, "Kejser2", "2014-07-07 15:57:12 +0000", "2014-07-07 15:57:12 +0000", array(), array(2, 4));

    //echo json_encode($res->getJsonSerializedTree());
    //echo json_encode($notification);
    //echo json_encode($notification->jsonSerialize());

    //$friend = Friend::build(1);

    //$friend = FriendManager::addFriend($res, "bertil@gmail.com");

    //FriendManager::acceptFriendRequest($steffen, 2);
    /*$chatRoom = ChatRoom::buildNew($steffen);
    ChatRoomMember::buildNew($chatRoom, $steffen);
    ChatRoomMember::buildNew($chatRoom, $bertil);
    ChatMessage::buildNew($chatRoom, $steffen, "Hej Bertil, klar på druk?");
    ChatMessage::buildNew($chatRoom, $bertil, "Hej Steffen, klart!");*/
    //$group = Group::buildNew("The gang", $steffen);

    //GroupManager::addGroup($steffen, "The Gang");
    //GroupManager::addGroupMember($steffen,1,2);
    //$date = date("Y-m-d H:i:s");
    //BeckonManager::addBeckon($bertil, "Pool", $date, $date, array(), array(1));

    //echo json_encode(FriendManager::getFriends($steffen),JSON_PRETTY_PRINT);
    /*echo json_encode($steffen->getChatRooms()->jsonSerialize(),JSON_PRETTY_PRINT);
    echo json_encode($bertil->getChatRooms()->jsonSerialize(),JSON_PRETTY_PRINT);*/

}
catch(Exception $e){
    echo $e->getMessage();
}





echo Persistence::getUsage();