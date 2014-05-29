<?php
require_once "users.class.php";	
class User{
	private $model;
	private $success;
	private $message;
	private $payload;
	function __construct() {
		$this->model = New Users();
		$this->success = "0";
		$this->message = "Operation failed";
		$this->payload = "";
	}
	
	function put($decodebody){
		try{
			$user = $this->model->put($decodebody->email, $decodebody->firstname, $decodebody->lastname, $decodebody->phonenumber, $decodebody->countrycode, $decodebody->password);
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
	
	function testCredentials($decodebody){
		try{
			$this->model->ping($decodebody->email, $decodebody->auth_key, $decodebody->device_key);
			$this->success = "1";
			$this->message = "User is authentic";
			$this->payload = "";
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
	    return $response;	
	}
	
	function updateNotificationKey($decodebody){
		try{
			$this->model->updateNotificationKey($decodebody->email, $decodebody->auth_key, $decodebody->device_key, $decodybody->notification_key);
			$this->success = "1";
			$this->message = "Notification key updated";
			$this->payload = "";
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
			$user = $this->model->delete($decodebody->email, $decodebody->auth_key, $decodebody->device_key, $decodebody->password);
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
			$user = $this->model->ping($decodebody->email, $decodebody->auth_key, $decodebody->device_key);
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