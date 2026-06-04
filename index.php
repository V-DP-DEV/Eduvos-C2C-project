<?php 
	require_once "./bootstrap.php";
	//gets page requested or loads default if none specified
	$page = $_GET['page'] ?? 'pages/marketPage.php';
	
	//check page + enforce proper autentication and authorisation
	$view = 'pages/marketPage.php';
	switch($page){
		case 'howToBuy':
			$view = 'pages/howToBuy.php';
			break;
		case 'howToSell':
			$view = 'pages/howToSell.php';
			break;
		case 'loginPage':
			$view = 'pages/loginPage.php';
			break;
		case 'signupPage':
			$view = 'pages/signupPage.php';
			break;
		case 'marketPage':
			$view = 'pages/marketPage.php';
			break;
		case 'verificationRequestPage':
			requireLogin();
            requireQuestionare();
			$view = 'pages/verificationRequestPage.php';
			break;
		case 'viewMyProfile':
			requireLogin();
			$view = 'pages/viewMyProfile.php';
			break;
        case 'viewProfile':
			$view = 'pages/viewProfile.php';
			break;
		case 'productPage': 									
			$view = 'pages/productPage.php';
            requireQuestionare();
			break;
       	case 'createProductPage': 
            requireLogin();
            requireQuestionare();
			$view = 'pages/createProductPage.php';
			break;
        case 'editProduct':
            requireLogin();
            requireQuestionare();
            $view = "pages/editProduct.php";
            break;
		case 'questionare':
			requireLogin();
			$view = 'pages/questionare.php';
		break;
		case 'adminUserTable':
			requireLogin();
            requireAdmin();
			$view = 'pages/adminUserTable.php';
		break;
		case 'adminVerificationTable':
			requireLogin();
            requireAdmin();
			$view = 'pages/adminVerificationTable.php';
		break;
		case 'adminVerificationRequest':
			requireLogin();
            requireAdmin();
			$view = 'pages/adminVerificationRequest.php';
		break;
		case 'myListings':
			requireLogin();
			$userId = $_SESSION['id'];
    		header("Location: index.php?page=market&userId=" . $userId);
		break;
		case 'leaveReview':
            requireLogin();
            requireQuestionare();
			$view = 'pages/leaveReview.php';
		break;
        case 'bankDetails':
            requireLogin();
            requireQuestionare();
			$view = 'pages/bankDetails.php';
		break;
		case 'yourOrders':
            requireLogin();
			$view = 'pages/yourOrders.php';
		break;
        case 'sellerOrders':
            requireLogin();
			$view = 'pages/sellerOrders.php';
            
		break;
       	case 'transactions':
            requireLogin();
			$view = 'pages/transactionsTable.php';
		break;
	}

	//loads user id if the user authenticated is default user
	$layout ='user';
	if(isset($_SESSION['id'])){
        //switches to admin layout if adminn
		if($_SESSION['role'] === 'admin' ){
			$layout = 'admin';
		}
	}

	//construct the webpage by header, page and footer.
	require_once "./layouts/$layout/header.php";
	require_once $view;
	require_once "./layouts/$layout/footer.php";
//Vian
?>