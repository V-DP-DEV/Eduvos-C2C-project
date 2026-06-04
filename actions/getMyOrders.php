<?php
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    echo getJsonFailure("Request not made with get!");
    return;
}

try {

    $userId = $_SESSION['id'] ?? null;

    if (!$userId) {
        echo getJsonFailure("No user id specified");
        return;
    }

    $filters = [
        'state' => $_GET['state'] ?? null
    ];

    echo Db::usingDbConnection(function () use ($userId, $filters) {

        $orders = Order::getMyOrders($userId, $filters);

        if (!$orders) {
            return getJsonFailure("No orders currently!");
        }

        return getJsonSuccessWithData($orders);
    });

} catch (PDOException $e) {
    Db::handleException($e);
    echo getJsonFailure("Error occured!");
} catch (Exception $e) {
    echo getJsonFailure("Error occured!");
}
?>