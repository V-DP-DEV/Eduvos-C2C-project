<?php
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    echo getJsonFailure("Invalid request method. GET required.");
    return;
}

$filters = [
    'state' => $_GET['state'] ?? null
];

try {

    $userId = $_SESSION['id'] ?? null;

    if (!$userId) {
        echo getJsonFailure("No user id specified");
        return;
    }

    $orders = Db::usingDbConnection(function () use ($userId, $filters) {
        return Order::getSellerOrders($userId, $filters);
    });

    if (!$orders) {
        echo getJsonFailure("No orders currently!");
        return;
    }

    echo getJsonSuccessWithData($orders);

} catch (PDOException $e) {
    Db::handleException($e);
    echo getJsonFailure("An error occured!");
} catch (Exception $e) {
    echo getJsonFailure("An error occured!");
}
?>