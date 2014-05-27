<?php
require_once "friends.class.php";
class Friend{
	private $model;
	private	$success;
	private $message;
	private $payload;
	
	function __construct() {
		$this->model = New Friends();
		$this->success = "0";
		$this->message = "Operation failed";
		$this->payload = "";
	}
	
	function getAll($decodebody){
		try{
			$friends = $this->model->getAll($decodebody->email, $decodebody->auth_key, $decodebody->device_key);
			$this->success = "1";
			$this->message = "Friends fetched";
			$this->payload = $friends;
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
	    return $response;
	}
	
	function getPending($decodebody){
		try{
			$friends = $this->model->getPending($decodebody->email, $decodebody->auth_key, $decodebody->device_key);
			$this->success = "1";
			$this->message = "Pending friend requests fetched";
			$this->payload = $friends;
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
	    return $response;
	}
	
	function add($decodebody){
		try{
			$friends = $this->model->put($decodebody->email, $decodebody->auth_key, $decodebody->device_key, $decodebody->friend_email);
			$this->success = "1";
			$this->message = "Friend added";
			$this->payload = "";
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
	    return $response;
	}
	
	function remove($decodebody){
		try{
			$friends = $this->model->delete($decodebody->email, $decodebody->auth_key, $decodebody->device_key, $decodebody->friend_email);
			$this->success = "1";
			$this->message = "Friend removed";
			$this->payload = "";
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
	    return $response;
	}

	function accept($decodebody){
		try{
			$friends = $this->model->acceptRequest($decodebody->email, $decodebody->auth_key, $decodebody->device_key, $decodebody->friend_email);
			$this->success = "1";
			$this->message = "Friend accepted";
			$this->payload = "";
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
	    return $response;
	}

}	
?>