<?php
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

try {

    $fieldsErrors = new FieldsErrors();

    $username = $_POST['username'] ?? "";
    if (!$username) {
        $fieldsErrors->addError('username', 'Username required');
    } elseif (strlen($username) > 25) {
        $fieldsErrors->addError('username', 'Username must be shorter than 25 characters');
    }

    $phoneNumber = $_POST['phoneNumber'] ?? "";
    if (!$phoneNumber) {
        $fieldsErrors->addError('phoneNumber', 'Phone number required');
    } else {
        if (!ctype_digit($phoneNumber)) {
            $fieldsErrors->addError('phoneNumber', 'Phone number must only contain digits');
        }
        if (strlen($phoneNumber) != 10) {
            $fieldsErrors->addError('phoneNumber', 'Phone number must contain 10 digits');
        }
    }

    $email = $_POST['email'] ?? "";
    if (!$email) {
        $fieldsErrors->addError('email', 'Email required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fieldsErrors->addError('email', 'Email incorrect format');
    }

    $password = $_POST['password'] ?? "";
    if (!$password) {
        $fieldsErrors->addError('password', 'Password required');
    } elseif (strlen($password) < 8) {
        $fieldsErrors->addError('password', 'Password must be at least 8 characters');
    }

    $confirmPassword = $_POST['confirmPassword'] ?? "";
    if (!$confirmPassword) {
        $fieldsErrors->addError('confirmPassword', 'Confirm password required');
    } elseif ($confirmPassword != $password) {
        $fieldsErrors->addError('confirmPassword', 'Passwords doesnt match');
    }

    $suburb = $_POST['suburb'] ?? "";
    if (!$suburb) {
        $fieldsErrors->addError('suburb', 'No suburb selected');
    } elseif (!Suburb::validId($suburb)) {
        $fieldsErrors->addError('suburb', 'Not valid suburb');
    }

    if ($fieldsErrors->hasErrors()) {
        echo getJsonWithFieldErrors('validation errors', $fieldsErrors->getErrors());
        return;
    }

    echo Db::usingTransactionDbConnection(function () use (
        $username,
        $phoneNumber,
        $email,
        $password,
        $suburb
    ) {

        $user = new User();
        $user->setUsername($username);
        $user->setPhone($phoneNumber);
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setRole('user');
        $user->save();

        $buyerSellerInfo = new BuyerSellerInfo();
        $buyerSellerInfo->setUserId($user->getId());
        $buyerSellerInfo->setSuburbId($suburb);
        $buyerSellerInfo->setImageId(1);
        $buyerSellerInfo->save();

        $redirect = $_SESSION['redirect_after_login'] ?? 'index.php?page=marketPage';
        unset($_SESSION['redirect_after_login']);

        $_SESSION['role'] = $user->getRole();
        $_SESSION['id'] = $user->getId();

        $questionare = Questionare::getActiveQuestionare();

        if ($questionare) {
            $hasAttempt = QuestionareAttempt::userHasAttempt(
                $user->getId(),
                $questionare->getId()
            );

            if (!$hasAttempt) {
                $_SESSION['doQuestionare'] = $questionare->getId();
            }
        }

        Db::logAuth($user->getId(), $user->getRole(), "Sign up");

        return getJsonSuccessRedirect($redirect);
    });

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}
?>