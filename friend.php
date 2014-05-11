<?php
require_once "model.php";	
class Friend{
	private $model;
	private	$success;
	private $message;
	private $payload;
	
	function __construct() {
		$this->model = New Model();
		$this->success = "0";
		$this->message = "Operation failed";
		$this->payload = "";
	}
	
	function getAll($decodebody){
		try{
			$friends = $this->model->friendGetAll($decodebody->email, $decodebody->auth_key, $decodebody->device_key);
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
			$friends = $this->model->friendGetPending($decodebody->email, $decodebody->auth_key, $decodebody->device_key);
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
	
	function add($decodebody){
		try{
			$friends = $this->model->friendAdd($decodebody->email, $decodebody->auth_key, $decodebody->device_key, $decodebody->friend_email);
			$this->success = "1";
			$this->message = "Friend added";
			$this->payload = $friends;
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
			$friends = $this->model->friendAcceptRequest($decodebody->email, $decodebody->auth_key, $decodebody->device_key, $decodebody->friend_email);
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