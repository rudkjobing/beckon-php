<?PHP
require_once "model.class.php";

class Users extends Model{

	function __construct() {
		parent::__construct();
	}


	function get($email, $password, $device_key){
		$q = mysqli_query($this->connection, "select * from beckon_user where email = '{$email}'");
		if(mysqli_num_rows($q) > 0){
			$r = mysqli_fetch_assoc($q);
			if(password_verify($password, $r['hash'])){
				$s = strtoupper(md5(uniqid(rand(),true)));
				$authkey = substr($s,0,8) . '-' . substr($s,8,4) . '-' . substr($s,12,4). '-' . substr($s,16,4). '-' . substr($s,20);
				$q = mysqli_query($this->connection, "select id from beckon_device where device_key = '{$device_key}'");
				if(mysqli_num_rows($q) > 0){
					$re = mysqli_fetch_assoc($q);
					mysqli_query($this->connection, "update beckon_device set auth_key = '{$authkey}' where id = {$re['id']}");
					mysqli_commit($this->connection);
				}
				else{
					throw new Exception("Device not recognized, please register device");
				}
				return array("email" => $r['email'], "auth_key" => $authkey, "firstname" => $r['firstname'], "lastname" => $r['lastname'], "phonenumber" => $r['phonenumber'], "countrycode" => $r['countrycode']);
			}
			else{
				throw new Exception("Invalid credentials");
			}
		}
		else{
			throw new Exception("Invalid credentials");	
		}
	}

	function put($email, $firstname, $lastname, $phonenumber, $countrycode, $unhashed_password){
		if($email == ""){
			throw new Exception("Email is invalid");
		}
		if($firstname == ""){
		   throw new Exception("Firstname is invalid");
		}
		if($lastname == ""){
			throw new Exception("Lastname is invalid");
		}
		if($unhashed_password == ""){
			throw new Exception("Password is invalid");
		}
		$emailExists = mysqli_query($this->connection, "select * from beckon_user where email = '{$email}'");
		if(mysqli_num_rows($emailExists) > 0){
			throw new Exception("Email address is already registered");
		}
		else{
			$hash = password_hash($unhashed_password, PASSWORD_DEFAULT);
			try{
				$s = strtoupper(md5(uniqid(rand(),true)));
				$k = substr($s,0,8) . '-' . substr($s,8,4) . '-' . substr($s,12,4). '-' . substr($s,16,4). '-' . substr($s,20);
				$headers = "";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
				$headers .= "From: 'Welcome to Beckon' <auth@beckon.dk>\r\n";
				$headers .= "Reply-To: slyngel@gmail.com\r\n";
				$headers .= "X-Mailer: PHP/" . phpversion();
				mail($email, "Confirm your email", "Hello ".$firstname."!<br><br>To begin using Beckon, please confirm your email address by following this </br><a href='http://gateway.beckon.dk/confirm.php?k=".$k."'>link</a>"  , $headers);
				$q = mysqli_query($this->connection, "insert into beckon_user (email, firstname, lastname, phonenumber, countrycode, hash, confirmation_key) values ('{$email}', '{$firstname}', '{$lastname}', '{$phonenumber}', '{$countrycode}', '{$hash}', '{$k}')");
				return;
			}
			catch(Exception $e){
				throw $e;
			}
		}
	}

	

	function delete($email, $authkey, $device_key, $password){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$user = $this->userGet($email, $password, $device_key);
			$q = mysqli_query($this->connection, "delete from beckon_user where id = {$id}");			
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	function userRegisterDevice($email, $password, $device_type, $device_os){
		$s = strtoupper(md5(uniqid(rand(),true)));
		$device_key = substr($s,0,8) . '-' . substr($s,8,4) . '-' . substr($s,12,4). '-' . substr($s,16,4). '-' . substr($s,20);
		mysqli_query($this->connection, "insert into beckon_device (user_id, device_key, device_type, device_os) values ((select id from beckon_user where email = '{$email}' limit 1), '{$device_key}', '{$device_type}', '{$device_os}')");
		mysqli_query($this->connection, "commit");
		$user = $this->get($email, $password, $device_key);
		$user['device_key'] = $device_key;
		return $user;		
	}
	
	function updateNotificationKey($email, $authkey, $device_key, $notification_key){
		$id = $this->userAuthenticate($email, $authkey, $device_key);
		$q = mysqli_query($this->connection, "update beckon_device set notification_key = '{$notification_key}' where user_id = {$id} and device_key = '{$device_key}'");
		return;
	}

}

?>