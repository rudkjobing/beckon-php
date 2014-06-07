<?php

require_once "notificationcenter.php";

class Model{

	private $SERVER_IP = "95.85.59.211";
	private $USERNAME = "app";
	private $PASSWORD = "AA2D43901A3D9F8C5730518DF92F6F0D";
	private $DATABASE = "main";
	protected $connection;
	
	function __construct() {
		mysqli_report(MYSQLI_REPORT_STRICT); 
		try{
			$this->connection = mysqli_connect($this->SERVER_IP, $this->USERNAME, $this->PASSWORD,$this->DATABASE);
		}
		catch(exception $e){
			throw $e;
		}
	}
	
	function ping($id, $authkey, $device_key){
		try{
			$this->userAuthenticate($id, $authkey, $device_key);
			return;				
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	function userAuthenticate($id, $authkey, $device_key){
		try{
			$q = mysqli_query($this->connection, "select user_id from beckon_device where user_id = {$id} and device_key = '{$device_key}' and auth_key = '{$authkey}'");
		}
		catch(Exception $e){
			throw $e;
		}
		if(mysqli_num_rows($q) > 0){
			return;
		}
		else{
			throw new Exception("Invalid authentication key. Please log in");	
		}
	}
	
	function validateConfirmationKey($key){
		$q = mysqli_query($this->connection, "select * from beckon_user where confirmation_key = '{$key}' and confirmed = 'N'");
		if(mysqli_num_rows($q) > 0){
			mysqli_query($this->connection, "update beckon_user set confirmed = 'Y' where confirmation_key = '{$key}'");
			return "User was confirmed and activated";
		}
		else{
			throw new Exception("Key could not be verified");
		}
	}
	
	function buildIntInString($array){
		$i = 0;
		$result = "(";
		foreach($array as $element){
			if($i > 0){
				$result = $result . "," . $element;
			}
			else{
				$result = $result . $element;
			}
			$i++;
		}
		$result = $result . ")";
		return $result;		
	}


	function __destruct() {
		mysqli_close($this->connection);
	}

}
	


?>
