<?php 
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

try {

    $userId = $_SESSION['id'] ?? null;

    if (!$userId) {
        echo getJsonFailure("No user id specified");
        return;
    }

    $productId = $_POST['productId'] ?? null;

    if (!$productId) {
        echo getJsonFailure("No product id provided");
        return;
    }

    echo Db::usingTransactionDbConnection(function () use ($userId, $productId) {

        $product = Product::find($productId);

        if (!$product) {
            return getJsonFailure("Product doesnt exist");
        }

        if ($product->getUserId() !== $userId) {
            return getJsonFailure("Not your product");
        }

        if ($product->hasActiveOrder()) {
            return getJsonFailure("Cant be deleted when a valid order is in process.");
        }

        Image::deleteProductImages($product->getId());

        $product->delete($product->getId());

        Db::logUserAction(
            $userId,
            $_SESSION['role'],
            "Delete",
            'Product',
            $product->getId()
        );

        return getJsonSuccess();
    });

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}
?>