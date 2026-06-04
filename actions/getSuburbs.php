<?php
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    echo getJsonFailure("Invalid request method. GET required.");
    return;
}

try {

    $cityId = $_GET["cityId"] ?? null;

    if (!$cityId) {
        echo getJsonFailure("No city id provided");
        return;
    }

    $suburbs = Db::usingDbConnection(function () use ($cityId) {
        return Suburb::where("city_id=?", [$cityId]);
    });

    echo getJsonSuccessWithData(['suburbs' => $suburbs]);

} catch (PDOException $e) {
    Db::handleException($e);
    echo getJsonGeneralFailure();
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}
?>