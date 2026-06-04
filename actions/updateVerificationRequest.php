<?php
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

$adminId = $_SESSION['id'] ?? null;
$role    = $_SESSION['role'] ?? null;

if (!$adminId) {
    echo getJsonFailure("No user id specified");
    return;
}

if ($role !== 'admin') {
    echo getJsonFailure("Not an admin");
    return;
}

try {

    $verificationId = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$verificationId) {
        echo getJsonFailure("No verification specified");
        return;
    }

    if (!$status) {
        echo getJsonFailure("No status given");
        return;
    }

    echo Db::usingTransactionDbConnection(function () use (
        $adminId,
        $role,
        $verificationId,
        $status
    ) {

        $verificationRequest = VerificationRequest::find($verificationId);

        if (!$verificationRequest) {
            return getJsonFailure("Not valid verification");
        }

        $user = User::find($verificationRequest->getUserId());

        if (!$user) {
            return getJsonFailure("Not valid user");
        }

        if (
            $status === "accepted" &&
            $verificationRequest->getUserSummary()->getBanned()
        ) {
            return getJsonFailure("User has been banned!");
        }

        if ($status === "accepted") {
            $user->updateVerified(1);
        } elseif ($status === "declined") {
            $user->updateVerified(0);
        }

        VerificationRequest::delete($verificationId);

        Db::logUserAction(
            $adminId,
            $role,
            $status,
            'Verification',
            $verificationId
        );

        return getJsonSuccess();
    });

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}
?>