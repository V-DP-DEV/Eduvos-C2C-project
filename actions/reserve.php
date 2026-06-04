<?php 
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

try {

    $userId = $_SESSION['id'] ?? null;

    if (!$userId) {
        echo getJsonFailure("Not logged in");
        return;
    }

    $productId = $_POST['productId'] ?? null;

    if (!$productId) {
        echo getJsonFailure("Product id not specified");
        return;
    }

    echo Db::usingTransactionDbConnection(function () use ($userId, $productId) {

        $product = Product::find($productId);

        if (!$product) {
            return getJsonFailure("Product not valid");
        }

        if ($product->getState() !== "available") {
            return getJsonFailure("Product not available");
        }

        $activeReservation = Reservation::getActiveProductReservation($productId);

        if ($activeReservation !== null) {
            return getJsonFailure("Reservation already made for this product");
        }

        $userReservation = Reservation::getActiveUserReservation($userId);

        if ($userReservation) {
            return getJsonFailure("Finish your other reservation first");
        }

        if (BuyerSellerInfo::isBanned($product->getUserId())) {
            return getJsonFailure("Seller has been banned");
        }

        $reservation = new Reservation();
        $reservation->setUserId($userId);
        $reservation->setProductId($productId);
        $reservation->save();

        $_SESSION['reserveId'] = $reservation->getId();

        $transaction = new Transaction();
        $transaction->setReservationId($reservation->getId());
        $transaction->setAmount($product->getPrice());
        $transaction->setType('payment');
        $transaction->setReference("TXN_" . time() . "_" . rand(1000, 9999));
        $transaction->setAffectedUserId($userId);
        $transaction->save();

        $data = Paystack::createPayment(
            $product->getPrice(),
            $transaction->getReference()
        );

        $url = $data['data']['authorization_url'];

        $_SESSION['paystack_url'] = $url;

        return getJsonSuccessRedirect($url);
    });

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}
?>