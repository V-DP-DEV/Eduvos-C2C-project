<?php 
require_once __DIR__ . "/../bootstrap.php";

try {
    
    echo Db::usingDbConnection(function () {
		$dropSql= "SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS userLogs;
DROP TABLE IF EXISTS authLog;
DROP TABLE IF EXISTS verificationRequest;
DROP TABLE IF EXISTS questionareUserAttempt;
DROP TABLE IF EXISTS questionareResponses;
DROP TABLE IF EXISTS questionareChoices;
DROP TABLE IF EXISTS questionareQuestions;
DROP TABLE IF EXISTS questionares;
DROP TABLE IF EXISTS subRatings;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS productReservations;
DROP TABLE IF EXISTS productImgs;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS buyerSellerInfo;
DROP TABLE IF EXISTS bankRecepients;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS images;
DROP TABLE IF EXISTS suburbs;
DROP TABLE IF EXISTS cities;

SET FOREIGN_KEY_CHECKS = 1;";
     	Db::insert($dropSql,[]);
        
        $sql = file_get_contents(__DIR__. "/../dbSetup/backup.sql");
		
        
        $conn= Db::getConnection();
		$queries = explode(';', $sql);

foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        Db::insert($query,[]);
    }
}
        
        
        return getJsonSuccess();
    });

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}
?>