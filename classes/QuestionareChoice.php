<?php
    require_once __DIR__ .'/model.php';
	require_once __DIR__ ."/../bootstrap.php";

	class QuestionareChoice extends Model{

    	protected static $table = 'questionareChoices';

        private $id;
        private $questionId;
        private $choice;
        private $isCorrect;
        
        // Build choice from DB row
        protected static function fromArray(array $row,$prefix): QuestionareChoice {
        	$questionareChoice = new QuestionareChoice();
        	$questionareChoice->id = $row[$prefix.'id'];
        	$questionareChoice->questionId = $row[$prefix.'question_id'];
            $questionareChoice->choice = $row[$prefix.'choice'];
            $questionareChoice->isCorrect = $row[$prefix.'is_correct'];
        	return $questionareChoice;
    	}
        
        // Getters
        public function getId() { return $this->id; }
    	public function getQuestionId() { return $this->questionId; }
        public function getChoice() { return $this->choice; }
        public function isCorrect() { return $this->isCorrect; }
	}
?>