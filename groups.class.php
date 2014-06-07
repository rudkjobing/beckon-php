<?PHP
require_once "model.class.php";

class Groups extends Model{
		
	function __construct() {
		parent::__construct();
	}
	
	function put($id, $authkey, $device_key, $group_name){
		try{
			$this->userAuthenticate($id, $authkey, $device_key);
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
	
	function delete($id, $authkey, $device_key, $group_name){
		try{
			$this->userAuthenticate($id, $authkey, $device_key);
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
	
	function putMember($id, $authkey, $device_key, $group_id, $friend_id){
		try{
			$this->userAuthenticate($id, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select beckon_group.id from beckon_group where beckon_group.id = {$group_id} and beckon_group.owner = {$id}");
			if(mysqli_num_rows($q) > 0){
				$q = mysqli_query($this->connection, "select beckon_friend.id from beckon_friend where beckon_friend.id = {$friend_id} and beckon_friend.inviter = {$id} and accepted = 'y'");
				if(mysqli_num_rows($q) > 0){
					$r = mysqli_fetch_assoc($q);
					$q = mysqli_query($this->connection, "select * from beckon_group_member inner join beckon_group on beckon_group_member.group_id = beckon_group.id where beckon_group.id = {$group_id} and beckon_group_member.member = {$friend_id}");
					if(mysqli_num_rows($q) > 0){
						throw new Exception("This person is already a member of the group");
					}
					mysqli_query($this->connection, "insert into beckon_group_member (group_id, member) values ({$group_id}, {$friend_id})");
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
	
	function removeMember($id, $authkey, $device_key, $group_id, $friend_id){
		try{
			$this->userAuthenticate($id, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select beckon_group_member.id from beckon_group_member inner join beckon_group on beckon_group_member.group_id = beckon_group.id where beckon_group.owner = {$id} and beckon_group_member.member = {$friend_id} and beckon_group.id = {$group_id}");
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
	
	function get($id, $authkey, $device_key){
		try{
			$this->userAuthenticate($id, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select beckon_group.id, beckon_group.name from beckon_group where owner = {$id}");
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
	
	function getMembers($id, $authkey, $device_key, $group_id){
		try{
			$this->userAuthenticate($id, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select beckon_friend.id, nickname, firstname, lastname, email from beckon_group inner join beckon_group_member on beckon_group_member.group_id = beckon_group.id inner join beckon_friend on beckon_group_member.member = beckon_friend.id inner join beckon_user on beckon_friend.invitee = beckon_user.id where beckon_group.owner = {$id} and beckon_group.id = {$group_id}");
			if(mysqli_num_rows($q) > 0){
				$members = array();
				while($r = mysqli_fetch_assoc($q)){
					$member = array("id" => $r['id'], "nickname" => $r['nickname'], "firstname" => $r['firstname'], "lastname" => $r['lastname'], "email" => $r['email']);
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
}