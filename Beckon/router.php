<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 16/06/14
 * Time: 19:23
 */

include_once "Persistence.abstract.php";
include_once "UserManager.class.php";
include_once "FriendManager.class.php";
include_once "GroupManager.class.php";
include_once "BeckonManager.class.php";
include_once "DeviceManager.class.php";
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

	if(!isset($_SERVER['HTTP_APPKEY']) || $_SERVER['HTTP_APPKEY'] != "6752dad744e6ab1bd0e65dbf4f2ffc77"){
        foreach ($_SERVER as $key => $value) {
            error_log($key . " -> " . $value . "\n", 3, "/var/www/html/errors.log");
        }
        header("HTTP/1.0 404 Not Found");
        exit;
    }

	$response = array("status" => 0, "message" => "fatal error", "payload" => "");
	$body = file_get_contents('php://input');
    error_log(date("Y-m-d H:i:s"). "\n" . json_encode($_SERVER), 3, "/var/www/html/errors.log");
	error_log(date("Y-m-d H:i:s"). "\n" . $body, 3, "/var/www/html/errors.log");
	$client = json_decode($body);
    if(isset($client->cookie)){
        try{
            $user = UserManager::eatCookie($client->cookie->id, $client->cookie->cookie);
        }
        catch(Exception $e){
            if(!isset($_SERVER['HTTP_USER'])){
                echo json_encode(array("status" => 0, "message" => "Invalid Cookie", "payload" => $client->cookie->id));
                exit;
            }
        }
    }
	if(isset($_SERVER['HTTP_USER'])){
        if($_SERVER['HTTP_USER'] == "getState"){
            $response = UserManager::getState($user);
        }
        elseif($_SERVER['HTTP_USER'] == "signUp"){
            $response = UserManager::signUp($client->firstName, $client->lastName, $client->email, $client->password);
        }
        elseif($_SERVER['HTTP_USER'] == "signIn"){
            $response = UserManager::signIn($client->email, $client->password);
        }
    }
    elseif(isset($_SERVER['HTTP_NOTIFICATION'])){
        if($_SERVER['HTTP_NOTIFICATION'] == "getNotification"){
            $response = NotificationManager::getNotification($user, $client->notificationId);
        }
    }
    elseif(isset($_SERVER['HTTP_CHATROOM'])){
        if($_SERVER['HTTP_CHATROOM'] == "getChatRoomMessages"){
            $response = ChatRoomManager::getChatRoomMessages($user, $client->chatRoomId);
        }
        elseif($_SERVER['HTTP_CHATROOM'] == 'putChatRoomMessage'){
            $response = ChatRoomManager::putChatRoomMessage($user, $client->chatMessage->chatRoom, $client->chatMessage->message);
        }
    }
    elseif(isset($_SERVER['HTTP_BECKON'])){
        if($_SERVER['HTTP_BECKON'] == "addBeckon"){
            $response = BeckonManager::addBeckon($user, $client->beckon->title, $client->beckon->begins, $client->beckon->ends, array(), $client->beckon->friends);
        }
    }
    elseif(isset($_SERVER['HTTP_FRIEND'])){
        if($_SERVER['HTTP_FRIEND'] == "getFriends"){
            $response = FriendManager::getFriends($user);
        }
        elseif($_SERVER['HTTP_FRIEND'] == "addFriend"){
            $response = FriendManager::addFriend($user, $client->friendEmailAddress);
        }
    }
    elseif(isset($_SERVER['HTTP_GROUP'])){
        if($_SERVER['HTTP_GROUP'] == "addGroup"){
            $response = GroupManager::addGroup($user, $client->group->name);
        }
        elseif($_SERVER['HTTP_GROUP'] == "delete"){
            $response = GroupManager::delete($user, $client->group->id);
        }
        elseif($_SERVER['HTTP_GROUP'] == "addGroupMember"){
            $response = GroupManager::addGroupMember($user, $client->groupId, $client->friendId);
        }
        elseif($_SERVER['HTTP_GROUP'] == "removeGroupMember"){
            $response = GroupManager::removeGroupMember($user, $client->friendId, $client->groupId);
        }
        elseif($_SERVER['HTTP_GROUP'] == "getGroups"){
            $response = GroupManager::getGroups($user);
        }
        elseif($_SERVER['HTTP_GROUP'] == "getGroupMembers"){
            $response = GroupManager::getGroupMembers($user, $client->groupId);
        }
    }
    elseif(isset($_SERVER['HTTP_DEVICE'])){
        if($_SERVER['HTTP_DEVICE'] == "registerDevice"){
            $response = DeviceManager::registerDeviceForNotifications($user, $client->type, $client->notificationKey);
        }
    }
    else{
        error_log(json_encode($decodebody) . "\n", 3, "/var/www/html/errors.log");
        foreach ($_SERVER as $key => $value) {
            error_log($key . " -> " . $value . "\n", 3, "/var/www/html/errors.log");
        }
        header("HTTP/1.0 404 Not Found");
        exit;
    }
    error_log(date("Y-m-d H:i:s"). "\n" . json_encode($response), 3, "/var/www/html/errors.log");
	echo json_encode($response);