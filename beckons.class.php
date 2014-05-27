<?PHP
require_once "model.class.php";

class Beckons extends Model{

	function __construct() {
		parent::__construct();
	}

	function getAll($email, $authkey, $device_key){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "select beckon_beckon.id, beckon_beckon.title from beckon_beckon inner join beckon_beckon_invitation on beckon_beckon.id = beckon_beckon_invitation.beckon_id inner join beckon_user on beckon_beckon_invitation.invitee = beckon_user.id where beckon_user.id = '$id'");
			if(mysqli_error($this->connection)){
				throw new Exception(mysqli_error($this->connection));
			}
			$beckons = array();
			if(mysqli_num_rows($q) > 0){
				while($r = mysqli_fetch_assoc($q)){
					$beckon = array("id" => $r['id'], "title" => $r['title']);
					array_push($beckons, $beckon);
				}
				
			}
			$q = mysqli_query($this->connection, "select beckon_beckon.id, beckon_beckon.title from beckon_beckon where beckon_beckon.owner = '$id'");
			if(mysqli_num_rows($q) > 0){
				$beckons = array();
				while($r = mysqli_fetch_assoc($q)){
					$beckon = array("id" => $r['id'], "title" => $r['title']);
					array_push($beckons, $beckon);
				}
				
			}
			if(count($beckons) == 0){
				throw new Exception("There are currently no Beckons"); 
			}
			return $beckons;	
		}
		catch(Exception $e){
			throw $e;
		}
	}
			
	function put($email, $authkey, $device_key, $friend_ids, $group_ids, $title, $description, $duedate){
		try{
			$id = $this->userAuthenticate($email, $authkey, $device_key);
			$q = mysqli_query($this->connection, "insert into beckon_beckon (owner, title, description, duedate) values ({$id}, '{$title}', '{$description}', '{$duedate}')");
			if(mysqli_error($this->connection)){
				throw new Exception(mysqli_error($this->connection));
			}
			$beckon_id = mysqli_insert_id($this->connection);
			$friends = $friend_ids;
			foreach($group_ids as $group_id){
				$q = mysqli_query($this->connection, "select member from beckon_group_member where group_id = {$group_id}");
				while($r = mysqli_fetch_assoc($q)){
					array_push($friends, $r['member']);
				}
			}
			$friends = array_unique($friends);
			mysqli_query($this->connection, "BEGIN");
			foreach($friends as $friend){
				$q = mysqli_query($this->connection, "insert into beckon_beckon_invitation (beckon_id, invitee) values ({$beckon_id}, {$friend})");
			}
			mysqli_query($this->connection, "COMMIT");
		}
		catch(Exception $e){
			throw $e;
		}
	}
}

?>