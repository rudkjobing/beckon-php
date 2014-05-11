<?php
require_once "model.php";	
class Beckon{
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
			$beckons = $this->model->beckonGet($decodebody->email, $decodebody->auth_key, $decodebody->device_key);
			$this->success = "1";
			$this->message = "Beckons fetched";
			$this->payload = $beckons;
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
			$beckons = $this->model->friendBeckon(	$decodebody->email, 
													$decodebody->auth_key, 
													$decodebody->device_key, 
													$decodebody->friends,
													$decodebody->groups,
													$decodebody->title,
													$decodebody->description,
													$decodebody->duedate,
													$decodebody->expires,
													$decodebody->recurring,
													$decodebody->recurring_pattern);
			$this->success = "1";
			$this->message = "Beckon added";
			$this->payload = $beckons;
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
			$this->payload = $friends;
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
	    return $response;
	}

	function forceaccept($decodebody){
		try{
			$friends = $this->model->friendForceAcceptRequest($decodebody->email);
			$this->success = "1";
			$this->message = "Friend accepted";
			$this->payload = $friends;
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