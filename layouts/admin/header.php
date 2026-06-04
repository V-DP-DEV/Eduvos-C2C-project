<!DOCTYPE html>
<html>
    <head>
        <title>HTML Forms</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="styles/base.css">
        <link rel="stylesheet" href="styles/layout.css">
        <link rel="stylesheet" href="styles/input.css">
        <link rel="stylesheet" href="styles/table.css">
        <link rel="stylesheet" href="styles/header.css">
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
                    <li><a href="./index.php?page=adminUserTable">Users</a></li>
                    <li><a href="./index.php?page=adminVerificationTable">Verifications</a></li>
                    <li><a href="./actions/logout.php">Logout</a></li>
                    <li><a href="./actions/resetDb.php">Reset db</a></li>
                </ul>
            </nav>
        </header>