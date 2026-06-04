<?php 
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

try {
    $orderId = $_POST['orderId'] ?? "";
    if (!$orderId) {
        echo getJsonFailure("No order id specified");
        return;
    }

    $userId = $_SESSION['id'] ?? "";
    if (!$userId) {
        echo getJsonFailure("No user id specified");
        return;
    }

    echo Db::usingTransactionDbConnection(function () use ($userId, $orderId) {

        $buyer = User::find($userId);
        if (!$buyer) {
            return getJsonFailure("Buyer doesnt exist");
        }

        $order = Order::getPaidOrder($userId, $orderId);
        if ($order === null) {
            return getJsonFailure("No order matched with your id and order id");
        }

        $transactionActiveRefund = Transaction::where(
            "reservation_id=? AND type='refund' AND (status='pending' OR status='success')",
            [$order->getReservationId()]
        );

        if ($transactionActiveRefund) {
            return getJsonFailure("Already refunded or in progress");
        }

        $transactions = Transaction::where(
            "reservation_id=? AND type='payment' AND status='success'",
            [$order->getReservationId()]
        );

        if (!$transactions) {
            return getJsonGeneralFailure();
        }

        $transaction = $transactions[0];

        $response = Paystack::refundTransaction($transaction->getReference());

        if ($response['status']) {

            $refundId = $response['data']['id'];
            $transactionId = $response['data']['transaction']['id'];

            $transactionRefund = new Transaction();
            $transactionRefund->setReservationId($transaction->getReservationId());
            $transactionRefund->setOrderId($order->getId());
            $transactionRefund->setAmount($transaction->getAmount());
            $transactionRefund->setType('refund');
            $transactionRefund->setReference("TXN_" . time() . "_" . rand(1000, 9999));
            $transactionRefund->setRelatedId($transactionId);
            $transactionRefund->setRefundId($refundId);
            $transactionRefund->setAffectedUserId($userId);
            $transactionRefund->save();

            return getJsonSuccess();
        } else {
            return getJsonGeneralFailure();
        }
    });

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}