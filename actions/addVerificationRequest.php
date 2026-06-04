<?php 
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

function validate($files) {
    $errors = new FieldsErrors();
    $data = [];

    // Required files
    if (!isset($files['idPhoto'])) {
        $errors->addError('idPhoto', 'Id photo not provided');
    }

    if (!isset($files['selfiePhoto'])) {
        $errors->addError('selfiePhoto', 'No photo provided');
    }

    if ($errors->hasErrors()) {
        return [$errors, $data];
    }

    // File validation
    if (!checkFile($files['idPhoto'])) {
        $errors->addError('idPhoto', 'File uploaded not an image or not png/jpeg');
    }

    if (!checkFile($files['selfiePhoto'])) {
        $errors->addError('selfiePhoto', 'File uploaded not an image or not png/jpeg');
    }

    if ($errors->hasErrors()) {
        return [$errors, $data];
    }

    $data['idPhoto'] = $files['idPhoto'];
    $data['selfiePhoto'] = $files['selfiePhoto'];

    return [$errors, $data];
}

try {
    $userId = $_SESSION['id'] ?? "";

    if (!$userId) {
        echo getJsonFailure("No user id specified");
        return;
    }

    $files = $_FILES;

    [$errors, $data] = validate($files);

    if ($errors->hasErrors()) {
        echo getJsonWithFieldErrors('validation errors', $errors->getErrors());
        return;
    }

    echo Db::usingTransactionDbConnection(function () use ($userId, $files) {

        if (!UserSummary::validForVerification($userId)) {
            return getJsonFailure("You are not valid for a verification");
        }

        $verificationRequest = new VerificationRequest();
        $verificationRequest->saveImages(
            $files['idPhoto']['tmp_name'],
            $files['selfiePhoto']['tmp_name']
        );

        $verificationRequest->setUserId($userId);
        $verificationRequest->save();

        Db::logUserAction(
            $userId,
            $_SESSION['role'],
            "Add",
            'Verification',
            $verificationRequest->getId()
        );

        return getJsonSuccess();
    });

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}