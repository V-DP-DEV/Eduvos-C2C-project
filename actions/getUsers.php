<?php
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    echo getJsonFailure("Invalid request method. GET required.");
    return;
}

$filters = [
    'verified' => $_GET['verified'] ?? null,
    'banned'   => $_GET['banned'] ?? null,
    'search'   => $_GET['search'] ?? null
];

$orderBy = $_GET['orderBy'] ?? 'id';
$dir = $_GET['dir'] ?? 'ASC';

try {

    $users = Db::usingDbConnection(function () use ($filters, $orderBy, $dir) {
        return UserSummary::getNormalUsers($filters, $orderBy, $dir);
    });

    if (!$users) {
        echo getJsonFailure("No users!");
        return;
    }

    echo getJsonSuccessWithData($users);

} catch (PDOException $e) {
    Db::handleException($e);
    echo getJsonFailure("Error seemed to occur!");
} catch (Exception $e) {
    echo getJsonFailure("Error seemed to occur!");
}
?>