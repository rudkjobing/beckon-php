<?PHP
require_once "model.class.php";

class Friends extends Model{

	function __construct() {
		parent::__construct();
	}

	function getAll($id, $authkey, $device_key){
		try{
			$this->userAuthenticate($id, $authkey, $device_key);
			//$q = mysqli_query($this->connection, "select beckon_friend.id, beckon_friend.nickname, them.email email, them.firstname firstname, them.lastname lastname from beckon_friend inner join beckon_user me on me.id = beckon_friend.inviter inner join beckon_user them on them.id = beckon_friend.invitee where me.id = {$id} and accepted = 'Y'");
			$q = mysqli_query($this->connection, "select beckon_friend.id, beckon_friend.nickname, them.email email, them.firstname firstname, them.lastname lastname from beckon_friend inner join beckon_user them on them.id = beckon_friend.invitee where inviter = {$id} and accepted = 'Y'");
			if(mysqli_num_rows($q) > 0){
				$friends = array();
				while($r = mysqli_fetch_assoc($q)){
					$friend = array("id" => $r['id'], "nickname" => $r['nickname'], "email" => $r['email'], "firstname" => $r['firstname'], "lastname" => $r['lastname']);
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

	function getRequests($id, $authkey, $device_key){
		try{
			$this->userAuthenticate($id, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select beckon_friend.nickname, them.email email, them.firstname firstname, them.lastname lastname from beckon_friend inner join beckon_user me on me.id = beckon_friend.invitee inner join beckon_user them on them.id = beckon_friend.inviter where me.id = {$id} and accepted = 'P'");
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
	
	function get($id, $authkey, $device_key, $friend_email){
		try{
			$this->userAuthenticate($id, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select beckon_friend.nickname, them.email email, them.firstname firstname, them.lastname lastname from beckon_friend inner join beckon_user me on me.id = beckon_friend.inviter inner join beckon_user them on them.id = beckon_friend.invitee where them.email = '{$friend_email}' and me.id = {$id} and accepted = 'Y'");
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
			
	function put($id, $authkey, $device_key, $friend_email){
		try{
			if($email == $friend_email){
				throw new Exception("You cannot be friends with yourself");
			}
			$this->userAuthenticate($id, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select them.email from beckon_friend inner join beckon_user me on me.id = beckon_friend.inviter inner join beckon_user them on them.id = beckon_friend.invitee where me.id = {$id} and them.email = '{$friend_email}'");
			$q2 = mysqli_query($this->connection, "select them.email from beckon_friend inner join beckon_user me on me.id = beckon_friend.invitee inner join beckon_user them on them.id = beckon_friend.inviter where me.id = {$id} and them.email = '{$friend_email}'");
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
				//Send notification
				$nc = new NotificationCenter();
				mysqli_commit($this->connection);
				$nc->dispatchNotification(array($r['id']), "Friend request", 1, array("freq" => 1));
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
	
	function acceptRequest($id, $authkey, $device_key, $friend_email){
		try{
			$this->userAuthenticate($id, $authkey, $device_key);
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
	
	function delete($id, $authkey, $device_key, $friend_email){
		try{
			$this->userAuthenticate($id, $authkey, $device_key);
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
}

?>