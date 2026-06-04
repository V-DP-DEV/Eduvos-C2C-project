<?php
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    echo getJsonFailure("Invalid request method. GET required.");
    return;
}

$filters = [
    'minPrice' => $_GET['minPrice'] ?? null,
    'maxPrice' => $_GET['maxPrice'] ?? null,
    'category' => $_GET['category'] ?? null,
    'suburbId' => $_GET['suburbId'] ?? null,
    'cityId'   => $_GET['cityId'] ?? null,
    'search'   => $_GET['search'] ?? null,
    'userId'   => $_GET['userId'] ?? null,
];

if ($filters['userId'] === null) {
    $filters['notUserId'] = $_SESSION['id'] ?? null;
    $filters['banned'] = 0;
}

$orderBy = $_GET['orderBy'] ?? 'id';
$dir = $_GET['dir'] ?? 'ASC';

try {

    $productSummaries = Db::usingDbConnection(function () use ($filters, $orderBy, $dir) {
        return ProductSummary::getAllProducts($filters, $orderBy, $dir);
    });

    if (!$productSummaries) {
        echo getJsonFailure("No products currently!");
        return;
    }

    echo getJsonSuccessWithData($productSummaries);

} catch (PDOException $e) {
    Db::handleException($e);
    echo getJsonFailure('Something went wrong when retrieving products');
} catch (Exception $e) {
    echo getJsonFailure('Something went wrong when retrieving products');
}
?>