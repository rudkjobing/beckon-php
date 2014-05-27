<?php

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
	
	function ping($email, $authkey, $device_key){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			return;				
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	function userAuthenticate($email, $authkey, $device_key){
		try{
			$q = mysqli_query($this->connection, "select beckon_user.id from beckon_user inner join beckon_device on beckon_user.id = beckon_device.user_id where email = '{$email}' and device_key = '{$device_key}' and auth_key = '{$authkey}'");
		}
		catch(Exception $e){
			throw $e;
		}
		if(mysqli_num_rows($q) > 0){
			$r = mysqli_fetch_assoc($q);
			return $r['id'];
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
	
	/*
	function userGet($email, $password, $device_key){
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
	
	function userAuthenticate($email, $authkey, $device_key){
		try{
			$q = mysqli_query($this->connection, "select beckon_user.id from beckon_user inner join beckon_device on beckon_user.id = beckon_device.user_id where email = '{$email}' and device_key = '{$device_key}' and auth_key = '{$authkey}'");
		}
		catch(Exception $e){
			throw $e;
		}
		if(mysqli_num_rows($q) > 0){
			$r = mysqli_fetch_assoc($q);
			return $r['id'];
		}
		else{
			throw new Exception("Invalid authentication key. Please log in");	
		}
	}
	
	function userAdd($email, $firstname, $lastname, $phonenumber, $countrycode, $unhashed_password){
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
	
	function userRegisterDevice($email, $password, $device_type, $device_os){
		$s = strtoupper(md5(uniqid(rand(),true)));
		$device_key = substr($s,0,8) . '-' . substr($s,8,4) . '-' . substr($s,12,4). '-' . substr($s,16,4). '-' . substr($s,20);
		mysqli_query($this->connection, "insert into beckon_device (user_id, device_key, device_type, device_os) values ((select id from beckon_user where email = '{$email}' limit 1), '{$device_key}', '{$device_type}', '{$device_os}')");
		$user = $this->userGet($email, $password, $device_key);
		$user['device_key'] = $device_key;
		return $user;		
	}
	
	function userDelete($email, $authkey, $device_key, $password){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$user = $this->userGet($email, $password, $device_key);
			$q = mysqli_query($this->connection, "delete from beckon_user where id = {$id}");			
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	
	function userPing($email, $authkey, $device_key){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			return;				
		}
		catch(Exception $e){
			throw $e;
		}
	}		
	
	function friendGetAll($email, $authkey, $device_key){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select beckon_friend.nickname, them.email email, them.firstname firstname, them.lastname lastname from beckon_friend inner join beckon_user me on me.id = beckon_friend.inviter inner join beckon_user them on them.id = beckon_friend.invitee where me.email = '{$email}' and accepted = 'Y'");
			if(mysqli_num_rows($q) > 0){
				$friends = array();
				while($r = mysqli_fetch_assoc($q)){
					$friend = array("nickname" => $r['nickname'], "email" => $r['email'], "firstname" => $r['firstname'], "lastname" => $r['lastname']);
					array_push($friends, $friend);
				}
				return $friends;
			}
			else{
				throw new Exception("You have no friends");	
			}	
		}
		catch(Exception $e){
			throw $e;
		}
	}

	function friendGetPending($email, $authkey, $device_key){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select beckon_friend.nickname, them.email email, them.firstname firstname, them.lastname lastname from beckon_friend inner join beckon_user me on me.id = beckon_friend.invitee inner join beckon_user them on them.id = beckon_friend.inviter where me.email = '{$email}' and accepted = 'P'");
			if(mysqli_num_rows($q) > 0){
				$friends = array();
				while($r = mysqli_fetch_assoc($q)){
					$friend = array("nickname" => $r['nickname'], "email" => $r['email'], "firstname" => $r['firstname'], "lastname" => $r['lastname']);
					array_push($friends, $friend);
				}
				return $friends;
			}
			else{
				throw new Exception("You have no pending friend requests");	
			}	
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	function friendGet($email, $authkey, $device_key, $friend_email){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select beckon_friend.nickname, them.email email, them.firstname firstname, them.lastname lastname from beckon_friend inner join beckon_user me on me.id = beckon_friend.inviter inner join beckon_user them on them.id = beckon_friend.invitee where them.email = '{$friend_email}' and me.email = '{$email}' and accepted = 'Y'");
			if(mysqli_num_rows($q) > 0){
				$r = mysqli_fetch_assoc($q);
				$friend = array("nickname" => $r['nickname'], "email" => $r['email'], "firstname" => $r['firstname'], "lastname" => $r['lastname']);
				return $friend;
			}
			else{
				throw new Exception("You have no friend with this email");	
			}	
		}
		catch(Exception $e){
			throw $e;
		}
	}
			
	function friendAdd($email, $authkey, $device_key, $friend_email){
		try{
			if($email == $friend_email){
				throw new Exception("You cannot be friends with yourself");
			}
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select them.email from beckon_friend inner join beckon_user me on me.id = beckon_friend.inviter inner join beckon_user them on them.id = beckon_friend.invitee where me.email = '{$email}' and them.email = '{$friend_email}'");
			$q2 = mysqli_query($this->connection, "select them.email from beckon_friend inner join beckon_user me on me.id = beckon_friend.invitee inner join beckon_user them on them.id = beckon_friend.inviter where me.email = '{$email}' and them.email = '{$friend_email}'");
			if(mysqli_num_rows($q) > 0){
				throw new Exception("You are already friends with this person");
			}
			elseif(mysqli_num_rows($q2) > 0){
				throw new Exception("You are already friends with this person");
			}
			$q = mysqli_query($this->connection, "select id, firstname, lastname from beckon_user where email = '{$friend_email}'");
			if(mysqli_num_rows($q) > 0){
				$r = mysqli_fetch_assoc($q);
				$nickname = $r['firstname']." ".$r['lastname'];
				$q = mysqli_query($this->connection, "insert into beckon_friend (nickname, inviter, invitee) values ('{$nickname}', {$id}, {$r['id']})");
				mysqli_commit($this->connection);
				#return $this->friendGet($email, $authkey);
			}
			else{
				throw new Exception("User not recognized");	
			}
			
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	function friendAcceptRequest($email, $authkey, $device_key, $friend_email){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select beckon_friend.id from beckon_friend where invitee = {$id} and accepted = 'P' and inviter = (select id from beckon_user where email = '{$friend_email}' limit 1)");
			if(mysqli_num_rows($q) > 0){
				$r = mysqli_fetch_assoc($q);
				mysqli_query($this->connection, "update beckon_friend set accepted = 'Y' where id = {$r['id']}");
				$q = mysqli_query($this->connection, "select id, firstname, lastname from beckon_user where email = '{$friend_email}'"); 
				$r = mysqli_fetch_assoc($q);
				$nickname = $r['firstname']." ".$r['lastname'];
				mysqli_query($this->connection, "insert into beckon_friend (nickname, inviter, invitee, accepted) values ('{$nickname}', {$id}, {$r['id']}, 'Y')");
			}
			else{
				throw new Exception("No requests pending from this person");
			}
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	function friendForceAcceptRequest($email){
		try{
			$q = mysqli_query($this->connection, "select beckon_friend.id, beckon_friend.inviter from beckon_friend where invitee = (select id from beckon_user where email = '{$email}' limit 1) and accepted = 'P'");
			if(mysqli_num_rows($q) > 0){
				$r = mysqli_fetch_assoc($q);
				mysqli_query($this->connection, "update beckon_friend set accepted = 'Y' where id = {$r['id']}");
				$nickname = "Laura";
				mysqli_query($this->connection, "insert into beckon_friend (nickname, inviter, invitee, accepted) values ('{$nickname}', (select id from beckon_user where email = '{$email}' limit 1), {$r['inviter']}, 'Y')");
			}
			else{
				throw new Exception("No requests pending from this person");
			}
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	function friendRemove($email, $authkey, $device_key, $friend_email){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select id from beckon_user where email = '{$friend_email}'");
			$r = mysqli_fetch_assoc($q);
			$friend_id = $r['id'];
			$q = mysqli_query($this->connection, "delete from beckon_friend where invitee = {$id} and inviter = {$friend_id}");
			$q = mysqli_query($this->connection, "delete from beckon_friend where invitee = {$friend_id} and inviter = {$id}");
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	function groupAdd($email, $authkey, $device_key, $group_name){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select * from beckon_group inner join beckon_user on beckon_group.owner = beckon_user.id where beckon_user.email = '{$email}' and beckon_group.name = '{$group_name}'");
			if(mysqli_num_rows($q) > 0){
				throw new Exception("Group already exists");
			}
			else{
				$q = mysqli_query($this->connection, "insert into beckon_group (owner, name) values ({$id}, '{$group_name}')");
			}
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	function groupRemove($email, $authkey, $device_key, $group_name){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select id from beckon_group where beckon_group.owner = '{$id}' and beckon_group.name = '{$group_name}'");
			if(mysqli_num_rows($q) > 0){
				$r = mysqli_fetch_assoc($q);
				$q = mysqli_query($this->connection, "delete from beckon_group where beckon_group.id = {$r['id']}");
			}
			else{
				throw new Exception("Group does not exist");
			}
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	function groupAddFriend($email, $authkey, $device_key, $group_name, $friend_email){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select * from beckon_group inner join beckon_user on beckon_group.owner = beckon_user.id where beckon_user.email = '{$email}' and beckon_group.name = '{$group_name}'");
			if(mysqli_num_rows($q) > 0){
				$q = mysqli_query($this->connection, "select beckon_friend.id from beckon_friend inner join beckon_user on beckon_friend.invitee = beckon_user.id where beckon_user.email = '{$friend_email}' and accepted = 'y'");
				if(mysqli_num_rows($q) > 0){
					$r = mysqli_fetch_assoc($q);
					$q = mysqli_query($this->connection, "select * from beckon_group_member inner join beckon_group on beckon_group_member.group_id = beckon_group.id where beckon_group.id = (select beckon_group.id from beckon_group inner join beckon_user on beckon_group.owner = beckon_user.id where beckon_user.email = '{$email}' and beckon_group.name = '{$group_name}' limit 1) and beckon_group_member.member = {$r['id']}");
					if(mysqli_num_rows($q) > 0){
						throw new Exception("This person is already a member of the group");
					}
					mysqli_query($this->connection, "insert into beckon_group_member (group_id, member) values ((select beckon_group.id from beckon_group inner join beckon_user on beckon_group.owner = beckon_user.id where beckon_user.email = '{$email}' and beckon_group.name = '{$group_name}' limit 1), {$r['id']})");
				}
				else{
					throw new Exception("You are not friends with this person");
				}
			}
			else{
				throw new Exception("You have no group with that name");
			}
		}
		catch(Exception $e){
			throw($e);
		}
	}
	
	function groupGet($email, $authkey, $device_key){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select name from beckon_group inner join beckon_user on beckon_user.id = beckon_group.owner where email = '{$email}'");
			if(mysqli_num_rows($q) > 0){
				$groups = array();
				while($r = mysqli_fetch_assoc($q)){
					$group = array("name" => $r['name']);
					array_push($groups, $group);
				}
				return $groups;
			}
			else{
				throw new Exception("You have no groups");	
			}
			
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	function groupGetMembers($email, $authkey, $device_key, $group_name){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select nickname, firstname, lastname, email from beckon_group inner join beckon_group_member on beckon_group_member.group_id = beckon_group.id inner join beckon_friend on beckon_group_member.member = beckon_friend.id inner join beckon_user on beckon_friend.invitee = beckon_user.id where beckon_group.owner = {$id} and beckon_group.name = '{$group_name}'");
			if(mysqli_num_rows($q) > 0){
				$members = array();
				while($r = mysqli_fetch_assoc($q)){
					$member = array("nickname" => $r['nickname'], "firstname" => $r['firstname'], "lastname" => $r['lastname'], "email" => $r['email']);
					array_push($members, $member);
				}
				return $members;
			}
			else{
				throw new Exception("There are no members of this group");	
			}
			
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	function groupRemoveMember($email, $authkey, $device_key, $group_name, $friend_email){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select beckon_group_member.id from beckon_group_member inner join beckon_group on beckon_group_member.group_id = beckon_group.id inner join beckon_friend on beckon_group_member.member = beckon_friend.id inner join beckon_user on beckon_friend.invitee = beckon_user.id where owner = {$id} and beckon_group.name = '{$group_name}' and beckon_user.email = '{$friend_email}'");
			if(mysqli_num_rows($q) > 0){
				$r = mysqli_fetch_assoc($q);
				mysqli_query($this->connection, "delete from beckon_group_member where id = {$r['id']}");
			}
			else{
				throw new Exception("This person is not a member of the group");
			}
		}
		catch(Exception $e){
			throw($e);
		}
	}
	
	function beckonCreate($email, $authkey, $device_key, $title, $description, $duedate, $expires, $friends, $groups, $recurring){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "insert into beckon_beckon (owner, title, description, duedate, expires, created, recurring, recurring_pattern) values ({$id}, '{$title}', '{$description}', '{$duedate}', '{$expires}', '{$recurring}', '{recurring_pattern}'");
			$beckon_id = $mysqli->insert_id;
			foreach($friends as $friend_mail){
				$friend = this.friendGet();
			}
		}
		catch(Exception $e){
			throw $e;
		}	
	}*/



	function __destruct() {
		mysqli_close($this->connection);
	}

}
	


?>
