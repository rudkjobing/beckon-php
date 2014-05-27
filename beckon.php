<?php
require_once "beckons.class.php";	
class Beckon{
	private $model;
	private	$success;
	private $message;
	private $payload;
	
	function __construct() {
		$this->model = New Beckons();
		$this->success = "0";
		$this->message = "Operation failed";
		$this->payload = "";
	}
	
	function add($decodebody){
		try{
			$beckons = $this->model->put(	$decodebody->email, 
											$decodebody->auth_key, 
											$decodebody->device_key, 
											$decodebody->friend_ids,
											$decodebody->group_ids,
											$decodebody->title,
											$decodebody->description,
											$decodebody->duedate);
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

}	
?>