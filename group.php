<?PHP
require_once "groups.class.php";	
class Group{
	private $model;
	private	$success;
	private $message;
	private $payload;

	function __construct() {
		$this->model = New Groups();
		$this->success = "0";
		$this->message = "Operation failed";
		$this->payload = "";
	}

	function add($decodebody){
		try{
			$group = $this->model->put($decodebody->id, $decodebody->auth_key, $decodebody->device_key, $decodebody->group_name);
			$this->success = "1";
			$this->message = "Group added";
			$this->payload = "";
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
   		return $response;	
	}
	
	function remove($decodebody){
		try{
			$group = $this->model->delete($decodebody->id, $decodebody->auth_key, $decodebody->device_key, $decodebody->group_name);
			$this->success = "1";
			$this->message = "Group removed";
			$this->payload = "";
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
   		return $response;	
	}

	function get($decodebody){
		try{
			$groups = $this->model->get($decodebody->id, $decodebody->auth_key, $decodebody->device_key);
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

	function getMembers($decodebody){
		try{
			$members = $this->model->getMembers($decodebody->id, $decodebody->auth_key, $decodebody->device_key, $decodebody->group_id);
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

	function addMember($decodebody){
		try{
			$this->model->putMember($decodebody->id, $decodebody->auth_key, $decodebody->device_key, $decodebody->group_id,$decodebody->friend_id);
			$this->success = "1";
			$this->message = "Friend added to group";
			$this->payload = "";
		}
		catch(Exception $e){
			$this->success = "0";
			$this->message = $e->getMessage();
		}
		$response = array("success" => $this->success, "message" => $this->message, "payload" => $this->payload);
   		return $response;
	}

	function removeMember($decodebody){
		try{
			$this->model->removeMember($decodebody->id, $decodebody->auth_key, $decodebody->device_key, $decodebody->group_id,$decodebody->friend_id);
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