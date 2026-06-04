<?php 
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

try {

    $orderId = $_POST['orderId'] ?? null;
    $userId  = $_SESSION['id'] ?? null;

    if (!$orderId) {
        echo getJsonFailure("No order id specified");
        return;
    }

    if (!$userId) {
        echo getJsonFailure("No user id specified");
        return;
    }

    echo Db::usingTransactionDbConnection(function () use ($orderId, $userId) {

        $seller = User::find($userId);

        if (!$seller) {
            return getJsonFailure("Seller doesnt exist");
        }

        $order = Order::getPaidOrder($userId, $orderId);

        if ($order === null) {
            return getJsonFailure("No order matched with your id and order id");
        }

        $order->updateState('received');

        return getJsonSuccess();
    });

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}
?>