<?php 
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

try {
	//get fields
    $email = $_POST['email'] ?? "";
    $password = $_POST['password'] ?? "";

    //create db connection and pass fields
    echo Db::usingDbConnection(function () use ($email, $password) {
        //get if email and password match
        $user = User::login($email, $password);
		
        //if a user is matched
        if ($user->id != -1) {
			
            //redirect to last clicked page
            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php?page=marketPage';
            unset($_SESSION['redirect_after_login']);
			
            //load into session
            $_SESSION['id'] = $user->id;
            $_SESSION['role'] = $user->role;
			
            //check if have to do questionare and set attempt in session if needed
            $questionare = Questionare::getActiveQuestionare();

            if ($questionare) {
                $hasAttempt = QuestionareAttempt::userHasAttempt(
                    $user->id,
                    $questionare->getId()
                );

                if (!$hasAttempt) {
                    $_SESSION['doQuestionare'] = $questionare->getId();
                }
            }

            //check if any reservation in progress and load in session
            $reservation = Reservation::getActiveUserReservation($user->id);

            if ($reservation) {
                $_SESSION['reserveId'] = $reservation->getId();
            }

            //logs valid authentication
            Db::logAuth($user->id, $user->role, "Login");
			
            //redirect json send
            return getJsonSuccessRedirect($redirect);
        }
		//send json failure due to incorrect password or email
        return getJsonFailure("Incorrect password or email");
    });

} 
//catches if pdo exception occured
catch (PDOException $e) {
    //let db class handle exception and send
    Db::handleException($e);
} 
//catches other errors
catch (Exception $e) {
    //returns general failure
    echo getJsonGeneralFailure();
}
?>