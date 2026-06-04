<?php 
function returnFallBack(){
    readfile("https://nftcalendar.io/storage/uploads/2022/02/21/image-not-found_0221202211372462137974b6c1a.png");
    exit;
}

require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    echo getJsonFailure("Invalid request method. GET required.");
    return;
}

try {

    $imageId = $_GET['id'] ?? null;

    if (!$imageId) {
        returnFallBack();
    }

    $image = Db::usingDbConnection(function () use ($imageId) {
        return Image::find($imageId);
    });

    if (ob_get_level()) ob_end_clean();

    if (!$image) {
        returnFallBack();
    }

    if ($image->getVisibility() === "admin_only") {
        $userId = $_SESSION['id'] ?? null;
        $role = $_SESSION['role'] ?? null;

        if (!$userId || $role !== "admin") {
            returnFallBack();
        }
    }

    header("Content-Type: " . $image->getMime());
    header("Content-Length: " . strlen($image->getData()));
    echo $image->getData();

} catch (PDOException $e) {
    returnFallBack();
} catch (Exception $e) {
    returnFallBack();
}
?>