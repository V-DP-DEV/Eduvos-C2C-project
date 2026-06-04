<?php

//if not authenticated dont continue executing rest of the script
//and redirect to login page
    
function requireLogin() {
		if (!isset($_SESSION['id'])) {
				//sets redirect page to not just send them to market page
				$_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
				header("Location: index.php?page=loginPage");
				exit();
		}
}

//if outstanding questionare questions dont continue executing rest of the script
//and redirect to questionare page
//didQuestionare is assigned on in login and signup
function requireQuestionare(){
		if(isset($_SESSION['doQuestionare'])){
				$_SESSION['redirect_after_questionare'] = $_SERVER['REQUEST_URI'];
				header("Location: index.php?page=questionare");
				exit();
		}
}

//if not authenticated as admin
//redirect to 
function requireAdmin() {
		if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
				http_response_code(403);
				header("Location: index.php?page=loginPage");
				exit();
		}
}

//if no bank recepiet redirect to bankDetails
function requireBankRecepient() {
    	Db::connect();
    	$recepient = BankRecepient::where(" user_id=?",[$_SESSION['id']]);
		if (!$recepient===null) {
				http_response_code(403);
				header("Location: index.php?page=bankDetails");
				exit();
		}
}

function loadPage(){
	if(!defined('APP_RUNNING')){
		die('Direct access not allowed');
		exit;
	}
}

?>