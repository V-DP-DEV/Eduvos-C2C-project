<?php 
    ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
    
    //sets session to only be accesible via http and not js
	ini_set('session.cookie_httponly', 1);
	//protects against fixation or false cookie id
	ini_set('session.use_strict_mode', 1);

	//if session secure use https cookies
	if(isset($_SESSION['HTTPS'])){
		ini_set('session.cookie_secure', 1);
	}
	//start session
	//prevents session errors breaking
	if (session_status() === PHP_SESSION_NONE) {
    	session_save_path(__DIR__ . '/sessions');
    	session_start();
	}
    
	//loads all helper files
    foreach(glob(__DIR__ ."/helpers/*php") as $file){
    	require_once $file;
	}

	if (!isset($_ENV['_LOADED'])) {
    	loadEnv(__DIR__ . '/.env');
    	$_ENV['_LOADED'] = true;
	}
    
	require_once __DIR__."/classes/FieldsErrors.php";
	require_once __DIR__ ."/classes/Db.php";

	//auto loads class when used
	spl_autoload_register(function ($class) {
    	$file = __DIR__ . '/classes/' . $class . '.php';
    	if (file_exists($file)) {
        	require_once $file;
    	}
	});

	if(isset($_SESSION['reserveId'])){
        try{
        	Db::connect();   
            $reservation = Reservation::getReservationIfActive($_SESSION['reserveId']);
        	
            
       		if($reservation===null){
                Reservation::updateState($_SESSION['reserveId'],'expired');
            	unset($_SESSION['reserveId']);
                unset($_SESSION['paystack_url']);
        	}
            
        }
        catch(PDOException $e){
    		Db::handleException($e);
    	}
		catch(Exception $e){
    		echo getJsonGeneralFailure();
    	}
    }

	require_once __DIR__ ."/dbSetup/setup.php";
?>