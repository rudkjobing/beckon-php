<?PHP
require_once "model.class.php";

class Groups extends Model{
		
	function __construct() {
		parent::__construct();
	}
	
	function put($email, $authkey, $device_key, $group_name){
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
	
	function delete($email, $authkey, $device_key, $group_name){
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
	
	function putMember($email, $authkey, $device_key, $group_name, $friend_email){
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
	
	function get($email, $authkey, $device_key){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select beckon_group.id, name from beckon_group inner join beckon_user on beckon_user.id = beckon_group.owner where email = '{$email}'");
			if(mysqli_num_rows($q) > 0){
				$groups = array();
				while($r = mysqli_fetch_assoc($q)){
					$group = array("id" => $r['id'], "name" => $r['name']);
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
	
	function getMembers($email, $authkey, $device_key, $group_name){
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
	
	function deleteMember($email, $authkey, $device_key, $group_name, $friend_email){
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
}