<?php
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

function validate($post, $files) {
    $errors = new FieldsErrors();

    if (empty($post['title'])) {
        $errors->addError('title', 'No title provided');
    }
    else if(strlen($post['title'])>50){
    	$errors->addError('title','Title must be shorter than 50 characters');
   	}

    if (empty($post['price'])) {
        $errors->addError('price', 'No price provided');
    }

    if (empty($post['categoryId'])) {
        $errors->addError('categoryId', 'No category selected');
    }

    if (empty($post['description'])) {
        $errors->addError('description', 'No description provided');
    }

    if (!isset($files) || $files['error'][0] === 4) {
        $errors->addError('images', 'No images provided');
    }
    else{
        $count = count($files['name']);
        for($i=0;$i<$count;$i++){
            if(!checkFileInArray($files,$i)){
                $errors->addError('images','Not an image or not png or jpeg, upload '.($i+1));
            }            
        }
    }

    return $errors;
}

try {

    $userId = $_SESSION['id'] ?? null;

    if (!$userId) {
        echo getJsonFailure("No user id specified");
        return;
    }

    $images = $_FILES['images'];  // capture before the closure
    $errors = validate($_POST, $images);

    if ($errors->hasErrors()) {
        echo getJsonWithFieldErrors('validation errors', $errors->getErrors());
        return;
    }

echo Db::usingTransactionDbConnection(function () use ($userId, $images) {

    $product = new Product();
    $product->setTitle($_POST['title']);
    $product->setPrice($_POST['price']);
    $product->setDescription($_POST['description']);
    $product->setUserId($userId);
    $product->setCategoryId($_POST['categoryId']);
    $product->save();

    $product->saveImages($images['tmp_name']); // use captured variable

    Db::logUserAction(
        $userId,
        $_SESSION['role'],
        "Create",
        'Product',
        $product->getId()
    );

    return getJsonSuccess();
});

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}
?>