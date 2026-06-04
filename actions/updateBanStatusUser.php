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

    $targetUserId = $_POST['userId'] ?? null;
    $isBanned = isset($_POST['isBanned']) ? (int)$_POST['isBanned'] : null;

    if ($targetUserId === null || $isBanned === null) {
        echo getJsonFailure("User or banned value not set");
        return;
    }

    echo Db::usingTransactionDbConnection(function () use ($adminId, $role, $targetUserId, $isBanned) {

        $user = User::find($targetUserId);

        if (!$user) {
            return getJsonFailure("User doesnt exist");
        }

        if ($user->getRole() === "admin") {
            return getJsonFailure("Error when banning user");
        }

        $user->updateBanned($isBanned);

        if ($isBanned) {
            Db::logUserAction($adminId, $role, 'Banned', 'User', $targetUserId);
        } else {
            Db::logUserAction($adminId, $role, 'Unbanned', 'User', $targetUserId);
        }

        return getJsonSuccess();
    });

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}
?>