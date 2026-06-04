<?php
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

$userId = $_SESSION['id'] ?? null;

if (!$userId) {
    echo getJsonFailure("No user id specified");
    return;
}

try {

    $fieldsErrors = new FieldsErrors();

    $files = $_FILES;

    $username = $_POST['username'] ?? "";
    if (!$username) {
        $fieldsErrors->addError('username', 'Username required');
    } elseif (strlen($username) > 25) {
        $fieldsErrors->addError('username', 'Username must be shorter than 25 characters');
    }

    $phoneNumber = $_POST['phoneNumber'] ?? "";
    if (!$phoneNumber) {
        $fieldsErrors->addError('phoneNumber', 'Phone number required');
    } else {
        if (!ctype_digit($phoneNumber)) {
            $fieldsErrors->addError('phoneNumber', 'Phone number must only contain digits');
        }
        if (strlen($phoneNumber) != 10) {
            $fieldsErrors->addError('phoneNumber', 'Phone number must contain 10 digits');
        }
    }

    $email = $_POST['email'] ?? "";
    if (!$email) {
        $fieldsErrors->addError('email', 'Email required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fieldsErrors->addError('email', 'Email incorrect format');
    }

    $suburb = $_POST['suburb'] ?? "";
    if (!$suburb) {
        $fieldsErrors->addError('suburb', 'No suburb selected');
    } elseif (!Suburb::validId($suburb)) {
        $fieldsErrors->addError('suburb', 'Not valid suburb');
    }

    $image = null;

    if (fileProvided($files['profilePic'] ?? null)) {

        if (!checkFile($files['profilePic'])) {
            $fieldsErrors->addError(
                'profilePic',
                'File uploaded not an image or not a png or jpeg'
            );
        }
    }

    if ($fieldsErrors->hasErrors()) {
        echo getJsonWithFieldErrors('validation errors', $fieldsErrors->getErrors());
        return;
    }

    echo Db::usingTransactionDbConnection(function () use (
        $userId,
        $username,
        $phoneNumber,
        $email,
        $suburb,
        $files
    ) {

        $image = null;

        if (fileProvided($files['profilePic'] ?? null)) {

            $image = Image::prepareImage(
                $files['profilePic']['tmp_name'],
                300,
                300,
                80
            );

            $image->setVisibility('public');
            $image->save();
        }

        $user = new User();
        $user->setId($userId);
        $user->setUsername($username);
        $user->setPhone($phoneNumber);
        $user->setEmail($email);
        $user->update();

        $buyerSellerInfo = new BuyerSellerInfo();
        $buyerSellerInfo->setSuburbId($suburb);
        $buyerSellerInfo->setUserId($userId);

        if ($image) {
            $buyerSellerInfo->setImageId($image->getId());
        }

        $buyerSellerInfo->update();

        Db::logUserAction(
            $userId,
            $_SESSION['role'],
            "Update",
            'User',
            $userId
        );

        return getJsonSuccess();
    });

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}
?>