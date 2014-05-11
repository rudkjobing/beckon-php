<?PHP
require_once "user.php";
require_once "friend.php";
require_once "group.php";

class UserTest extends PHPUnit_Framework_TestCase{

	protected static $users;
	protected static $user;
	protected static $friend;
	protected static $group;
		
	public static function setUpBeforeClass(){
		self::$user = New User();
		self::$friend = New Friend();
		self::$group = New Group();
		self::$users = array();
		self::$users['laura'] = New TestUser("laura@beckon.dk", "test1234", "Laura", "Croft");
		self::$users['james'] = New TestUser("james@beckon.dk", "test1234", "James", "Bond");
		self::$users['lex'] = New TestUser("lex@beckon.dk", "test1234", "Lex", "Luther");
		self::$users['harry'] = New TestUser("harry@beckon.dk", "test1234", "Harry", "Potter");
	}
	
	public static function tearDownAfterClass(){

	}

	public function testAccount(){
		fwrite(STDOUT, __METHOD__ . "\n");
		//Create laura
		$body = '{"email" : "'.self::$users['laura']->email.'", "firstname" : "'.self::$users['laura']->firstname.'", "lastname" : "'.self::$users['laura']->lastname.'", "phonenumber" : "61792979" , "countrycode" : "0045" , "password" : "'.self::$users['laura']->password.'"}';
		$decodebody = json_decode($body);
	 	$result = self::$user->add($decodebody);
		//Test if Laura was created successfully, according to the server
		if($result['success'] == 0){fwrite(STDOUT, $result['message'] . "\n");}
		$this->assertEquals(1, $result['success']);
		//Try to register "this" as a device with Laura's credentials
		$body = '{"email" : "'.self::$users['laura']->email.'", "password" : "'.self::$users['laura']->password.'", "device_type" : "IPhone" , "device_os" : "IOS 7" }';
		$decodebody = json_decode($body);
		$result = self::$user->registerDevice($decodebody);
		self::$users['laura']->auth_key = $result['payload']['auth_key'];
		self::$users['laura']->device_key = $result['payload']['device_key'];
		//Test if the server thinks this was successful
		$this->assertEquals(1, $result['success']);
		//Now test if the server stored the data correctly
		$this->assertEquals(self::$users['laura']->firstname, $result['payload']['firstname']);
		$this->assertEquals(self::$users['laura']->lastname, $result['payload']['lastname']);
		$this->assertEquals(self::$users['laura']->email, $result['payload']['email']);
		//Test that we can use the server
		$body = '{"email" : "'.self::$users['laura']->email.'" , "auth_key" : "'.self::$users['laura']->auth_key.'" , "device_key" : "'.self::$users['laura']->device_key.'" }';
		$decodebody = json_decode($body);
		$result = self::$user->ping($decodebody);	
		if($result['success'] == 0){fwrite(STDOUT, $result['message'] . "\n");}
		$this->assertEquals(1, $result['success']);		
		//Remove traces of the test
		$result = self::$user->delete(json_decode('{"email" : "'.self::$users['laura']->email.'" , "password" : "'.self::$users['laura']->password.'" , "auth_key" : "'.self::$users['laura']->auth_key.'" , "device_key" : "'.self::$users['laura']->device_key.'"}'));
		if($result['success'] == 0){fwrite(STDOUT, $result['message'] . "\n");}
		$this->assertEquals(1, $result['success']);
		//Test if the user is gone
		$result = self::$user->registerDevice($decodebody);
		$this->assertEquals(0, $result['success']);
	}

	public function testFriends(){
		fwrite(STDOUT, __METHOD__ . "\n");
		//Create laura
		$body = '{"email" : "'.self::$users['laura']->email.'", "firstname" : "'.self::$users['laura']->firstname.'", "lastname" : "'.self::$users['laura']->lastname.'", "phonenumber" : "61792979" , "countrycode" : "0045" , "password" : "'.self::$users['laura']->password.'"}';
		$decodebody = json_decode($body);
	 	$result = self::$user->add($decodebody);
		//Register device for Laura
		$body = '{"email" : "'.self::$users['laura']->email.'", "password" : "'.self::$users['laura']->password.'", "device_type" : "IPhone" , "device_os" : "IOS 7" }';
		$decodebody = json_decode($body);
		$result = self::$user->registerDevice($decodebody);
		self::$users['laura']->auth_key = $result['payload']['auth_key'];
		self::$users['laura']->device_key = $result['payload']['device_key'];
		//Create Lex
		$body = '{"email" : "'.self::$users['lex']->email.'", "firstname" : "'.self::$users['lex']->firstname.'", "lastname" : "'.self::$users['lex']->lastname.'", "phonenumber" : "61792979" , "countrycode" : "0045" , "password" : "'.self::$users['lex']->password.'"}';
		$decodebody = json_decode($body);
	 	$result = self::$user->add($decodebody);
		//Laura adds Lex as friend
		$body = '{"email" : "'.self::$users['laura']->email.'" , "auth_key" : "'.self::$users['laura']->auth_key.'" , "device_key" : "'.self::$users['laura']->device_key.'" , "friend_email" : "'.self::$users['lex']->email.'"}';
		$decodebody = json_decode($body);
	 	$result = self::$friend->add($decodebody);
		//Register device for Lex
		$body = '{"email" : "'.self::$users['lex']->email.'", "password" : "'.self::$users['lex']->password.'", "device_type" : "IPhone" , "device_os" : "IOS 7" }';
		$decodebody = json_decode($body);
		$result = self::$user->registerDevice($decodebody);
		self::$users['lex']->auth_key = $result['payload']['auth_key'];
		self::$users['lex']->device_key = $result['payload']['device_key'];
		//Get pending friend requests for Lex
		$body = '{"email" : "'.self::$users['lex']->email.'" , "auth_key" : "'.self::$users['lex']->auth_key.'" , "device_key" : "'.self::$users['lex']->device_key.'" }';
		$decodebody = json_decode($body);
		$result = self::$friend->getPending($decodebody);
		//We expect only 1 row with the request from Laura
		$this->assertEquals("laura@beckon.dk", $result['payload'][0]['email']);
		//Accept Laura's friend request
		$body = '{"email" : "'.self::$users['lex']->email.'", "auth_key" : "'.self::$users['lex']->auth_key.'" , "device_key" : "'.self::$users['lex']->device_key.'" , "friend_email" : "'.self::$users['laura']->email.'"}';
		$decodebody = json_decode($body);
		$result = self::$friend->accept($decodebody);
		$this->assertEquals(1, $result['success']);		
		//Verify that Laura is now a friend
		$body = '{"email" : "'.self::$users['lex']->email.'" , "auth_key" : "'.self::$users['lex']->auth_key.'" , "device_key" : "'.self::$users['lex']->device_key.'" }';
		$decodebody = json_decode($body);
		$result = self::$friend->getAll($decodebody);
		//We expect only 1 row with the request from Laura
		$this->assertEquals("laura@beckon.dk", $result['payload'][0]['email']);		
		//Verify that Lex is now a friend
		$body = '{"email" : "'.self::$users['laura']->email.'" , "auth_key" : "'.self::$users['laura']->auth_key.'" , "device_key" : "'.self::$users['laura']->device_key.'" }';
		$decodebody = json_decode($body);
		$result = self::$friend->getAll($decodebody);
		//We expect only 1 row with the request from Laura
		$this->assertEquals("lex@beckon.dk", $result['payload'][0]['email']);
		//Clean up
		self::$user->delete(json_decode('{"email" : "'.self::$users['laura']->email.'", "password" : "'.self::$users['laura']->password.'", "auth_key" : "'.self::$users['laura']->auth_key.'" , "device_key" : "'.self::$users['laura']->device_key.'"}'));
		self::$user->delete(json_decode('{"email" : "'.self::$users['lex']->email.'", "password" : "'.self::$users['lex']->password.'", "auth_key" : "'.self::$users['lex']->auth_key.'" , "device_key" : "'.self::$users['lex']->device_key.'"}'));
	}
	/*
	public function testAddGroup(){
		fwrite(STDOUT, __METHOD__ . "\n");
		foreach(self::$users as $user){
			$body = '{"email" : "'.self::$users['laura']->email.'", "auth_key" : "'.self::$users['laura']->auth_key.'" , "device_key" : "'.self::$users['laura']->device_key.'" , "group_name" : "Close friends" }';
			$decodebody = json_decode($body);
		 	$result = self::$group->add($decodebody);
		 	fwrite(STDOUT, $result['message'] . "\n");
		 	$this->assertEquals(1, $result['success']); 
		}				
	}
/*
	public function testGetGroups(){
		fwrite(STDOUT, __METHOD__ . "\n");
		foreach(self::$users as $user){
			$body = '{"email" : "'.self::$users['laura']->email.'", "auth_key" : "'.self::$users['laura']->auth_key.'" , "device_key" : "'.self::$users['laura']->device_key.'"}';
			$decodebody = json_decode($body);
		 	$result = self::$group->get($decodebody);
		 	fwrite(STDOUT, $result['message'] . "\n");
		 	$this->assertEquals(1, $result['success']); 
		 }
	}

	public function testAddFriendsToGroup(){
		fwrite(STDOUT, __METHOD__ . "\n");
		foreach(self::$users as $user){
			foreach(self::$users as $friend){
				if(self::$users['laura']->email != $friend->email){
					$body = '{"email" : "'.self::$users['laura']->email.'" , "auth_key" : "'.self::$users['laura']->auth_key.'" , "device_key" : "'.self::$users['laura']->device_key.'" , "group_name" : "Close Friends" , "friend_email" : "'.$friend->email.'"}';
					$decodebody = json_decode($body);
				 	$result = self::$group->addMember($decodebody);
				 	fwrite(STDOUT, $result['message'] . "\n");
				 	$this->assertEquals(1, $result['success']);
				 }
			}
		}	
	}

	public function testGetGroupMembers(){
		fwrite(STDOUT, __METHOD__ . "\n");
		foreach(self::$users as $user){
			$body = '{"email" : "'.self::$users['laura']->email.'", "auth_key" : "'.self::$users['laura']->auth_key.'" , "group_name" : "Close Friends" , "device_key" : "'.self::$users['laura']->device_key.'"}';
			$decodebody = json_decode($body);
		 	$result = self::$group->getMembers($decodebody);
		 	fwrite(STDOUT, $result['message'] . "\n");
		 	$this->assertEquals(1, $result['success']); 
		 }
	}
	*/
}	

class TestUser{
	public $email; 
	public $password; 
	public $firstname;
	public $lastname;
	public $auth_key;
	public $device_key;
	function __construct($email, $password, $firstname, $lastname) {
		$this->email = $email;
		$this->password = $password;
		$this->firstname= $firstname;
		$this->lastname = $lastname;
	}
	
}

?>