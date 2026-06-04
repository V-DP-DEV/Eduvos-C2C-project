<?php
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    echo getJsonFailure("Invalid request method. GET required.");
    return;
}

$orderBy = $_GET['orderBy'] ?? 'id';
$dir = $_GET['dir'] ?? 'ASC';

try {

    $verifications = Db::usingDbConnection(function () use ($orderBy, $dir) {
        return VerificationRequest::getVerifications($orderBy, $dir);
    });

    if (!$verifications) {
        echo getJsonFailure("No verifications at the moment");
        return;
    }

    echo getJsonSuccessWithData($verifications);

} catch (PDOException $e) {
    Db::handleException($e);
    echo getJsonFailure("Error occured");
} catch (Exception $e) {
    echo getJsonFailure("Error occured");
}
?>