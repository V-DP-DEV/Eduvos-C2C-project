<?php
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

$userId = $_SESSION['id'] ?? null;

if (!$userId) {
    echo getJsonFailure("No user id specified");
    return;
}

try {

    $orderId = $_POST['orderId'] ?? null;

    if (!$orderId) {
        echo getJsonFailure("No order id specified");
        return;
    }

    $fieldsErrors = new FieldsErrors();

    $rating = $_POST['rating'] ?? "";
    if ($rating === "") {
        $fieldsErrors->addError('rating', 'Rating required');
    }

    $communicationRating = $_POST['communicationRating'] ?? "";
    if ($communicationRating === "") {
        $fieldsErrors->addError('communicationRating', 'Communication rating required');
    }

    $conditionRating = $_POST['conditionRating'] ?? "";
    if ($conditionRating === "") {
        $fieldsErrors->addError('conditionRating', 'Condition rating required');
    }

    $comments = $_POST['comments'] ?? "";
    if ($comments === "") {
        $fieldsErrors->addError('comments', 'Comments required');
    }

    $files = $_FILES;

    if (!fileProvided($files['picture'] ?? null)) {
        $fieldsErrors->addError('photo', 'No photo provided');
    }

    if ($fieldsErrors->hasErrors()) {
        echo getJsonWithFieldErrors('validation errors', $fieldsErrors->getErrors());
        return;
    }

    if (!checkFile($files['picture'])) {
        echo getJsonFailure("Photo not an image or not a jpeg or png");
        return;
    }

    $filePath = $files['picture']['tmp_name'];

    echo Db::usingTransactionDbConnection(function () use (
        $userId,
        $orderId,
        $rating,
        $communicationRating,
        $conditionRating,
        $comments,
        $filePath
    ) {

        $order = Order::find($orderId);

        if (!$order) {
            return getJsonFailure("No valid order");
        }

        if ($order->getUserId() !== $userId) {
            return getJsonFailure("This is not your order!");
        }

        if (Review::hasUserLeftReviewOnProduct($order->getProductId(), $userId)) {
            return getJsonFailure("You already reviewed this product");
        }

        $review = new Review();
        $review->setUserId($userId);
        $review->setProductId($order->getProductId());
        $review->setRating($rating);
        $review->setComments($comments);
        $review->save($filePath);

        $communicationSub = new SubRating();
        $communicationSub->setReviewId($review->getId());
        $communicationSub->setRating($communicationRating);
        $communicationSub->setSubReviewCat("communication");
        $communicationSub->save();

        $conditionSub = new SubRating();
        $conditionSub->setReviewId($review->getId());
        $conditionSub->setRating($conditionRating);
        $conditionSub->setSubReviewCat("condition");
        $conditionSub->save();

        return getJsonSuccess();
    });

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}
?>