<?php
	require_once "user.php";
	require_once "friend.php";
	require_once "group.php";
	require_once "beckon.php";
	if(!isset($_SERVER['HTTP_APPKEY']) || $_SERVER['HTTP_APPKEY'] != "6752dad744e6ab1bd0e65dbf4f2ffc77"){
		foreach ($_SERVER as $key => $value) {
    		error_log($key . " -> " . $value . "\n", 3, "/var/www/html/errors.log");
		}
		header("HTTP/1.0 404 Not Found");
		exit;
	}

	$response = json_decode('{"success" : "0" , "message" : "fatal error" , "payload" : ""}');
	$body = file_get_contents('php://input'); 
	error_log($body, 3, "/var/www/html/errors.log");
	$decodebody = json_decode($body);
	if(isset($_SERVER['HTTP_USER'])){
		$user = New User();
		if($_SERVER['HTTP_USER'] == "testCredentials"){
			$response = $user->testCredentials($decodebody);
		}
		elseif($_SERVER['HTTP_USER'] == "put"){
			$response = $user->put($decodebody);
		}
		elseif($_SERVER['HTTP_USER'] == "registerDevice"){
			$response = $user->registerDevice($decodebody);
		}
		elseif($_SERVER['HTTP_USER'] == "updateNotificationKey"){
			$response = $user->updateNotificationKey($decodebody);
		}
	}
	elseif(isset($_SERVER['HTTP_BECKON'])){
		$beckon = New Beckon();
		if($_SERVER['HTTP_BECKON'] == "add"){
			$response = $beckon->add($decodebody);
		}
		elseif($_SERVER['HTTP_BECKON'] == "getAll"){
			$response = $beckon->getAll($decodebody);
		}
	}
	elseif(isset($_SERVER['HTTP_FRIEND'])){
		$friend = New Friend();
		if($_SERVER['HTTP_FRIEND'] == "add"){
			$response = $friend->add($decodebody);
		}
		elseif($_SERVER['HTTP_FRIEND'] == "getAll"){
			$response = $friend->getAll($decodebody);
		}
		elseif($_SERVER['HTTP_FRIEND'] == "getPending"){
			$response = $friend->getPending($decodebody);
		}
		elseif($_SERVER['HTTP_FRIEND'] == "accept"){
			$response = $friend->accept($decodebody);
		}
		elseif($_SERVER['HTTP_FRIEND'] == "remove"){
			$response = $friend->remove($decodebody);
		}
	}
	elseif(isset($_SERVER['HTTP_GROUP'])){
		$group = New Group();
		if($_SERVER['HTTP_GROUP'] == "add"){
			$response = $group->add($decodebody);
		}
		if($_SERVER['HTTP_GROUP'] == "remove"){
			$response = $group->remove($decodebody);
		}
		elseif($_SERVER['HTTP_GROUP'] == "getAll"){
			$response = $group->get($decodebody);
		}
		elseif($_SERVER['HTTP_GROUP'] == "getMembers"){
			$response = $group->getMembers($decodebody);
		}
		elseif($_SERVER['HTTP_GROUP'] == "addMember"){
			$response = $group->addMember($decodebody);
		}
		elseif($_SERVER['HTTP_GROUP'] == "removeMember"){
			$response = $group->removeMember($decodebody);
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

	echo json_encode($response);
	
?>