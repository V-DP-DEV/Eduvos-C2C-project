<?php 
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

try {

    $orderId = $_POST['orderId'] ?? "";
    $userId = $_SESSION['id'] ?? "";

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

        $order = Order::getReceivedOrder($userId, $orderId);
        if ($order === null) {
            return getJsonFailure("No order matched with your id and order id");
        }

        if ($order->getState() !== "received") {
            return getJsonFailure("Order in incorrect state");
        }

        $bankRecepient = BankRecepient::where(
            "user_id=? AND is_default=1",
            [$userId]
        );

        if (count($bankRecepient) === 0) {
            return getJsonFailure("You dont have a bank recpient specified yet!");
        }

        $transaction = Transaction::where(
            "order_id=? AND affected_user_id=? AND type='payout' and status<>'failed'",
            [$orderId, $userId]
        );

        if ($transaction) {
            return getJsonFailure("You already made a payout request!");
        }

        $transaction = new Transaction();
        $transaction->setReservationId($order->getReservationId());
        $transaction->setOrderId($order->getId());
        $transaction->setAmount($order->getPrice());
        $transaction->setType('payout');
        $transaction->setReference("TXN_" . time() . "_" . rand(1000, 9999));
        $transaction->setAffectedUserId($userId);
        $transaction->setRecepientCode($bankRecepient[0]->getRecipientCode());
        $transaction->save();

        return getJsonSuccess();
    });

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}
?>