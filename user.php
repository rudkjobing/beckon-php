<?php
require_once "model.php";	
class User{
	private $model;
	private $success;
	private $message;
	private $payload;
	function __construct() {
		$this->model = New Model();
		$this->success = "0";
		$this->message = "Operation failed";
		$this->payload = "";
	}
	
	function add($decodebody){
		try{
			$user = $this->model->userAdd($decodebody->email, $decodebody->firstname, $decodebody->lastname, $decodebody->phonenumber, $decodebody->countrycode, $decodebody->password);
			$this->success = "1";
			$this->message = "User has been created";
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
	    return $response;
	}
	
	function authenticate($decodebody){
		try{
			$user = $this->model->userGet($decodebody->email, $decodebody->password, $decodebody->device_key);
			$this->success = "1";
			$this->message = "User is authentic";
			$this->payload = $user;
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
	    return $response;	
	}
	
	function delete($decodebody){
		try{
			$user = $this->model->userDelete($decodebody->email, $decodebody->auth_key, $decodebody->device_key, $decodebody->password);
			$this->success = "1";
			$this->message = "User has been deleted";
			$this->payload = $user;
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
	    return $response;	
	}
	
	function forceConfirm($decodebody){
		try{
			$user = $this->model->ForcevalidateConfirmationKey($decodebody->email);
			$this->success = "1";
			$this->message = "User force deleted";
			$this->payload = $user;
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
	    return $response;	
	}
	
	function ping($decodebody){
		try{
			$user = $this->model->userPing($decodebody->email, $decodebody->auth_key, $decodebody->device_key);
			$this->success = "1";
			$this->message = "User is valid and is welcome to use the server";
			$this->payload = $user;
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
	    return $response;	
	}
	
	function registerDevice($decodebody){
		try{
			$user = $this->model->userRegisterDevice($decodebody->email, $decodebody->password, $decodebody->device_type, $decodebody->device_os);
			$this->success = "1";
			$this->message = "Device registered";
			$this->payload = $user;
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