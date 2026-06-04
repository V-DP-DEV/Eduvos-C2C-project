<?php 
require_once __DIR__ . "/../bootstrap.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

try {

    $userId = $_SESSION['id'] ?? null;

    if (!$userId) {
        echo getJsonFailure("No user id specified");
        return;
    }

    $fieldsErrors = new FieldsErrors();

    $accountNumber = $_POST['accountNumber'] ?? "";
    $bankCode = $_POST['bankCode'] ?? "";

    if (!$accountNumber) {
        $fieldsErrors->addError('accountNumber', 'No account number provided');
    }

    if (!$bankCode) {
        $fieldsErrors->addError('bankCode', 'No bankCode provided');
    }

    if ($fieldsErrors->hasErrors()) {
        echo getJsonWithFieldErrors('validation errors', $fieldsErrors->getErrors());
        return;
    }

    $result = Paystack::resolveAccount($bankCode, $accountNumber);

    if (!$result['status']) {
        echo getJsonFailure("No credentials matched in banks.");
        return;
    }

    $accountName = $result['data']['account_name'];

    // recepient and transfer blocked on starter business/projects
    // $recepientCode = Paystack::createRecepientOnPaystack($accountName,$accountNumber,$bankCode);

    $recepientCode = 'RCP_' . strtoupper(bin2hex(random_bytes(6)));

    if ($recepientCode === null) {
        echo getJsonFailure("Something went wrong when trying to save your bank account.");
        return;
    }

    echo Db::usingTransactionDbConnection(function () use (
        $userId,
        $bankCode,
        $accountNumber,
        $accountName,
        $recepientCode
    ) {

        $recepient = BankRecepient::where("user_id=?", [$userId]);

        $isDefault = $recepient ? 0 : 1;

        $bankRecepient = new BankRecepient();
        $bankRecepient->setBankCode($bankCode);
        $bankRecepient->setIsDefault($isDefault);
        $bankRecepient->setRecipientCode($recepientCode);
        $bankRecepient->setAccountNumber($accountNumber);
        $bankRecepient->setUserId($userId);
        $bankRecepient->setAccountName($accountName);
        $bankRecepient->save();

        Db::logUserAction(
            $userId,
            $_SESSION['role'],
            "Create",
            'BankRecipient',
            $bankRecepient->getId()
        );

        return getJsonSuccess();
    });

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}
?>