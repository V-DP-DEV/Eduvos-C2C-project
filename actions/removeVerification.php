<?php 
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

try {

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

    $userId = $_POST['id'] ?? null;

    if (!$userId) {
        echo getJsonFailure("No user specified");
        return;
    }

    echo Db::usingTransactionDbConnection(function () use ($adminId, $role, $userId) {

        $user = User::find($userId);

        if (!$user) {
            return getJsonFailure("Not valid user");
        }

        $user->updateVerified(0);

        Db::logUserAction(
            $adminId,
            $role,
            "Remove verification",
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