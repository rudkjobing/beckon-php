<?PHP
require_once "model.php";	
class Group{
	private $model;
	private	$success;
	private $message;
	private $payload;

	function __construct() {
		$this->model = New Model();
		$this->success = "0";
		$this->message = "Operation failed";
		$this->payload = "";
	}

	public function add($decodebody){
		try{
			$group = $this->model->groupAdd($decodebody->email, $decodebody->auth_key, $decodebody->device_key, $decodebody->group_name);
			$this->success = "1";
			$this->message = "Group added";
			$this->payload = $group;
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
   		return $response;	
	}

	public function get($decodebody){
		try{
			$groups = $this->model->groupGet($decodebody->email, $decodebody->auth_key, $decodebody->device_key);
			$this->success = "1";
			$this->message = "Groups fetched";
			$this->payload = $groups;
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
   		return $response;
	}

	public function getMembers($decodebody){
		try{
			$members = $this->model->groupGetMembers($decodebody->email, $decodebody->auth_key, $decodebody->device_key, $decodebody->group_name);
			$this->success = "1";
			$this->message = "Members fetched";
			$this->payload = $members;
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
   		return $response;
	}

	public function addMember($decodebody){
		try{
			$this->model->groupAddFriend($decodebody->email, $decodebody->auth_key, $decodebody->device_key, $decodebody->group_name,$decodebody->friend_email);
			$this->success = "1";
			$this->message = "Friend added to group";
			$this->payload = null;
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
   		return $response;
	}

	public function removeMember($decodebody){
		try{
			$this->model->removeFriendFromGroup($decodebody->email, $decodebody->auth_key, $decodebody->device_key, $decodebody->group_name,$decodebody->friend_email);
			$this->success = "1";
			$this->message = "Friend removed from group";
			$this->payload = null;
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