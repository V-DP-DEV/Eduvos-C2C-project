<?php
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    echo getJsonFailure("Invalid request method. GET required.");
    return;
}

$filters = [
    'status' => $_GET['status'] ?? null,
    'type'   => $_GET['type'] ?? null,
];

$orderBy = $_GET['orderBy'] ?? 'id';
$dir = $_GET['dir'] ?? 'ASC';

try {

    $userId = $_SESSION['id'] ?? null;

    if (!$userId) {
        echo getJsonFailure("No user id specified");
        return;
    }

    $transactions = Db::usingDbConnection(function () use ($userId, $filters, $orderBy, $dir) {
        return Transaction::getTransactions($userId, $filters, $orderBy, $dir);
    });

    if (!$transactions) {
        echo getJsonFailure('No transactions');
        return;
    }

    echo getJsonSuccessWithData($transactions);

} catch (PDOException $e) {
    Db::handleException($e);
    echo getJsonFailure('Error occured');
} catch (Exception $e) {
    echo getJsonFailure('Error occured');
}
?>