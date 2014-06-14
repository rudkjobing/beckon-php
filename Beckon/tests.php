<?php

include_once "Persistence.abstract.php";
include_once "UserManager.class.php";
include_once "FriendManager.class.php";
include_once "GroupManager.class.php";
include_once "Beckon.class.php";
include_once "User.class.php";
include_once "Friend.class.php";
include_once "Group.class.php";
include_once "GroupMember.class.php";
include_once "Cookie.class.php";
include_once "Collection.interface.php";

try{
    /*$cookie = UserManager::signUp("Bertil", "Andersen", "bertil@gmail.com", "test1234");
    $cookie2 = UserManager::signUp("Steffen", "Rudkjøbing", "steffen@gmail.com", "test1234");*/

    $bertil = UserManager::eatCookie(15, "05aacbea048f11056adfb52ecc680acba0a97a51");
    $steffen = UserManager::eatCookie(16, "74c99989d13cf1ce7d64b75a5695dcd0179013b3");

    //$friend = Friend::build(1);

    //$friend = FriendManager::addFriend($bertil, "steffen@gmail.com");

    //FriendManager::acceptFriendRequest($steffen, 2);

    //$group = Group::buildNew("The gang", $steffen);

    //GroupManager::addGroup($steffen, "The Gang");
    //GroupManager::addGroupMember($steffen,1,2);
    $date = date("Y-m-d H:i:s");
    $beckon = Beckon::buildNew("Å'en", $steffen, $date, $date);
    $beckon->setEnds($date + 1);
    $beckon->flush();
    $beckon->setEnds($date);
    $beckon->flush();

    //echo json_encode(FriendManager::getFriends($steffen),JSON_PRETTY_PRINT);
    echo json_encode($steffen->getJsonSerializedTree(),JSON_PRETTY_PRINT);

}
catch(Exception $e){
    echo $e->getMessage();
}





echo Persistence::getUsage();