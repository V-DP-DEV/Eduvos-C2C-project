<?php
require_once __DIR__ . "/../bootstrap.php";
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo getJsonWrongMethod('POST');
    return;
}

try {

    $userId = $_SESSION['id'] ?? null;

    $answers = $_POST['answers'] ?? [];

    if (!$answers || !is_array($answers)) {
        echo getJsonFailure("No answers provided");
        return;
    }

    echo Db::usingTransactionDbConnection(function () use ($userId, $answers) {

        $questionare = Questionare::getActiveQuestionare();

        if (!$questionare) {
            return getJsonFailure("No active questionare");
        }

        $attempt = new QuestionareAttempt();
        $attempt->setUserId($userId);
        $attempt->setQuestionareId($questionare->getId());
        $attempt->save();

        unset($_SESSION['doQuestionare']);

        $data = [];

        foreach ($answers as $questionId => $choiceId) {

            $response = new QuestionareResponse($userId, $choiceId);

            $correctChoice = Question::getCorrectChoice($questionId);

            $data[$questionId] = [
                'correct'  => $correctChoice->getId(),
                'selected' => $choiceId
            ];

            $response->save();
        }

        $redirect = $_SESSION['redirect_after_questionare'] ?? 'index.php?page=marketPage';
        unset($_SESSION['redirect_after_questionare']);

        return getJsonSuccessWithDataRedirect($data, $redirect);
    });

} catch (PDOException $e) {
    Db::handleException($e);
} catch (Exception $e) {
    echo getJsonGeneralFailure();
}
?>