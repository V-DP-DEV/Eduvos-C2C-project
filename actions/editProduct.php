<?php 
	require_once __DIR__ ."/../bootstrap.php";
	if($_SERVER['REQUEST_METHOD'] !== "POST"){
        echo getJsonWrongMethod('POST');
        return;
    }
	try{
        $userId = $_SESSION['id']??"";
        if(!$userId===''){
            echo getJsonFailure("No user id specified");
            return;
        }
        $fieldsErrors = new FieldsErrors();
        
        
        $title = $_POST['title']??"";
        if(!$title){
            $fieldsErrors->addError('title','No title provided');
        }
        else if(strlen($title)>50){
            $fieldsErrors->addError('title','Title must be shorter than 50 characters');
        }
        $price = $_POST['price']??"";
        if(!$price){
            $fieldsErrors->addError('price','No price provided');
        }
        $categoryId = $_POST['categoryId']??"";
        if(!$categoryId){
            $fieldsErrors->addError('categoryId','No category selected');
        }
        $description = $_POST['description']??"";
        if(!$description){
            $fieldsErrors->addError('description','No description provided');
        }
        
        //decode to get an array
       	$existingImages = json_decode($_POST['existingImages'] ?? '[]', true) ?? [];
        $hasNewImages = isset($_FILES['images']) && $_FILES['images']['error'][0] !== 4;
        if (!$hasNewImages) {
    		if (count($existingImages) ===0) {
        		$fieldsErrors->addError('images', 'No images provided');
    		}
		}
        if($fieldsErrors->hasErrors()){
            echo getJsonWithFieldErrors('validation errors',$fieldsErrors->getErrors());
			exit;
        }
       
        $newImages=[];
        if($hasNewImages){
        	$count = count($_FILES['images']['name']);
        	for($i=0;$i<$count;$i++){
            	if(!checkFileInArray($_FILES['images'],$i)){
                	$fieldsErrors->addError('images','Not an image or not png or jpeg, upload '.($i+1));
            	}            
        	}    
        }
        
        
        if($fieldsErrors->hasErrors()){
            echo getJsonWithFieldErrors('validation errors',$fieldsErrors->getErrors());
			exit;
        }
         $id = $_POST['id']??"";
        if(!$id){
            echo getJsonFailure("No product id provided");
            return;
        }
        
        if($hasNewImages){
            $newImages=$_FILES['images']["tmp_name"];
        }
        
        Db::connect();
        $product = Product::find($id);
        if(!$product){
            echo getJsonFailure("Product doesnt exist");
            return;
        }
        if($product->getUserId()!==$userId){
            echo getJsonFailure("Not your product");
            return;
        }
        if($product->hasActiveOrder()){
            echo getJsonFailure("Cant be edited when a valid order is in process.");
            return;
        }
        $product->setTitle($title);
        
        $product->setPrice($price);
        $product->setDescription($description);
        $product->setCategoryId($categoryId);
        $product->update();
       	
        
        if(!$existingImages){
            $product->saveImages($newImages);
        }
        else{
            $product->reuploadImages($existingImages,$newImages);
            echo "fah";
        }
        
        
        Db::logUserAction($userId,$_SESSION['role'],"Update",'Product',$product->getId());
        echo getJsonSuccess();
        return;
    }
	catch(PDOException $e){
		Db::handleException($e);
    }
    catch(Exception $e){
		echo getJsonGeneralFailure();
    }
    
?>