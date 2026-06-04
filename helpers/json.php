<?php
    //json for if succesful and redirect page specified
    function getJsonSuccessRedirect($redirect){
		return json_encode(['success' => true, 'redirect' => $redirect]);
	}

	//json for general success
	function getJsonSuccess(){
        if (ob_get_level()) ob_end_clean();
		return json_encode(['success' => true]);
	}
    
	//json for general errors
	function getJsonGeneralFailure(){
		return json_encode(['success' => false,'error'=> ['general_error'=>'Unexpected error occured']]);
	}

	//json for general success with data
	function getJsonSuccessWithData($data){
        return json_encode(['success' => true,'data'=> $data]);
    }
	
	//json for general success with data and redirect
	function getJsonSuccessWithDataRedirect($data,$redirect){
        return json_encode(['success' => true,'redirect'=>$redirect,'data'=> $data]);
    }
    
	//json for failure
	function getJsonFailure($errorMessage){
		return json_encode(['success' => false,'error'=> ['general_error'=>$errorMessage]]);
	}
    
    //json for wrong request method used
	function getJsonWrongMethod($requiredMethod){
		return json_encode(['success' => false,'error'=> ['general_error'=>'Request was not made with '.$requiredMethod]]);
	}
    
    //json for specific field errors
	function getJsonWithFieldErrors($generalMessage,$maps){
		return json_encode(['success' => false,'error'=> ['general_error'=>$generalMessage ,'fields'=>$maps]]);
	}
?>