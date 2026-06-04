<?php
	require_once __DIR__ . "/../bootstrap.php";
	if($_SERVER['REQUEST_METHOD'] !== "GET"){
        getJsonWrongMethod('GET');
        return;
    }
	try{
		$fieldsErrors = new FieldsErrors();
		
        $accountNumber = $_GET['accountNumber']??"";
		if(!$accountNumber){
			$fieldsErrors->addError('accountNumber','Account number required');
		}
		
        $bankCode = $_GET['bankCode']??"";
		if(!$bankCode){
			$fieldsErrors->addError('bankCode','Bank required');
		}
        
		if($fieldsErrors->hasErrors()){
			echo getJsonWithFieldErrors('validation errors',$fieldsErrors->getErrors());
			exit;
		}
        
        $result = Paystack::resolveAccount($bankCode,$accountNumber);   
        if($result['status']){
            echo json_encode(['success'=>true,'accountName'=>$result['data']['account_name']]);
            return;
        }
		else{
            echo getJsonFailure("No result matched");
        }      
    }
	catch(PDOException $e){
    	Db::handleException($e);
    }
	catch(Exception $e){
    	echo getJsonGeneralFailure();
    }
?>