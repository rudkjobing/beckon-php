<?PHP

require_once "model.php";

if(isset($_GET['k'])){
	$model = new Model();
	try{
		$model->validateConfirmationKey($_GET['k']);
		echo "Account activated";
	}
	catch(Exception $e){
		sleep(1);
		echo "Invalid authentication credentials";
	}
}

?>