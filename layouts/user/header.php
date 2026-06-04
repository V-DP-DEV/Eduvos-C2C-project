<?php
require_once __DIR__ . "/../../bootstrap.php";

$reservation = null;

if (isset($_SESSION['reserveId'])) {

    try {

        $reservation = Db::usingDbConnection(function () {
            return Reservation::getActiveUserReservation($_SESSION['id']);
        });

    } catch (PDOException $e) {
        Db::handleException($e);
    } catch (Exception $e) {
        getJsonGeneralFailure();
    }
}

$userId = $_SESSION['id'] ?? "";
?>

<!DOCTYPE html>
<html>
    <head>
        <title>HTML Forms</title>
        <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="styles/base.css">
        <link rel="stylesheet" href="styles/layout.css">
        <link rel="stylesheet" href="styles/header.css">
        <link rel="stylesheet" href="styles/input.css">
        <link rel="stylesheet" href="styles/table.css">
        <link rel="stylesheet" href="styles/footer.css">
        <link rel="stylesheet" href="styles/component.css">
    </head>

    <body>
        <header>
            <nav class="navbar">
                <div class="logo">SafeSwap</div>

                <!-- Hamburger for mobile -->
                <input type="checkbox" id="menu-toggle" hidden />
                <label for="menu-toggle" class="hamburger">&#9776;</label>

                <!-- Menu links -->
                <ul class="nav-links">
                    <li><a href="./index.php?page=marketPage">Market</a></li>

                    <?php if ($userId != "") { ?>
                        <li class="dropdown">
                            <button class="headerButton">Listings ▾</button>
                            <ul class="dropdown-menu">
                                <li><a href="./index.php?page=myListings">My listings</a></li>
                                <li><a href="./index.php?page=createProductPage">Create product</a></li>
                            </ul>
                        </li>

                        <li class="dropdown">
                            <button class="headerButton">Orders ▾</button>
                            <ul class="dropdown-menu">
                                <li><a href="./index.php?page=yourOrders">My Orders</a></li>
                                <li><a href="./index.php?page=sellerOrders">Seller Orders</a></li>
                                <li><a href="./index.php?page=transactions">Transactions</a></li>
                            </ul>
                        </li>

                        <?php if ($reservation) { ?>
                            <li><a href="<?= $_SESSION['paystack_url'] ?>">Finish payment</a></li>
                        <?php } ?>

                        <li class="dropdown">
                            <button class="headerButton">Profile ▾</button>
                            <ul class="dropdown-menu">
                                <li><a href="./index.php?page=viewMyProfile">Profile</a></li>
                                <li><a href="./index.php?page=bankDetails">Bank recepient</a></li>
                                <li><a href="./actions/logout.php">Logout</a></li>
                                
                            </ul>
                        </li>
                    <?php } ?>

                    <?php if ($userId === "") { ?>
                        <li><a href="./index.php?page=loginPage" class="mainLink">Login</a></li>
                    <?php } ?>
                </ul>
            </nav>
        </header>