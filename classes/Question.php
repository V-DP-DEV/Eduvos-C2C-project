<?php
    require_once __DIR__ .'/model.php';
	require_once __DIR__ ."/../bootstrap.php";

	class Question extends Model{

    	protected static $table = 'questionareQuestions';

        private $id;
        private $question;
        private $state;
        private $choices;
        
        // Build question from DB row
        protected static function fromArray(array $row,$prefix): Question {
        	$question = new Question();
        	$question->id = $row[$prefix.'id'];
        	$question->question = $row[$prefix.'question'];
        	return $question;
    	}

        // Load questions + their choices
        public static function withChoices($questionareId){
    		$questions = self::where("questionare_id = ?", [$questionareId]);

    		$ids = array_map(fn($q) => $q->getId(), $questions);

    		if (empty($ids)) return $questions;

    		$placeholders = implode(',', array_fill(0, count($ids), '?'));

    		$choices = QuestionareChoice::where("question_id IN ($placeholders)", $ids);

    		$grouped = [];
    		foreach ($choices as $c) {
        		$grouped[$c->getQuestionId()][] = $c;
    		}

    		foreach ($questions as $q) {
        		$q->setChoices($grouped[$q->getId()] ?? []);
    		}

    		return $questions;
		}
        
        // Get correct answer for a question
        public static function getCorrectChoice($questionId){
            $choice = QuestionareChoice::where("question_id=? and is_correct=1",[$questionId]);
    		return $choice[0];
		}
        
        // Getters / setters
        public function getId() { return $this->id; }
    	public function getQuestion() { return $this->question; }

        public function setChoices($choices){$this->choices = $choices;}
        public function getChoices(){return $this->choices;}
	}
?>