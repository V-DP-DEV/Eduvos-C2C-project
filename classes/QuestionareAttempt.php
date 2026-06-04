<?php
    require_once __DIR__ .'/model.php';
	require_once __DIR__ ."/../bootstrap.php";

	class QuestionareAttempt extends Model{

    	protected static $table = 'questionareUserAttempt';

        private $id;
        private $questionareId;
        private $userId;
        
        // Build object from DB row
        protected static function fromArray(array $row,$prefix): QuestionareAttempt {
        	$questionareAttempt = new QuestionareAttempt();
        	$questionareAttempt->id = $row[$prefix.'id'] ?? null;
        	$questionareAttempt->questionareId = $row[$prefix.'questionare_id'] ?? null;
            $questionareAttempt->userId = $row[$prefix.'user_id'] ?? null;
        	return $questionareAttempt;
    	}
        
        // Check if user already attempted
        public static function userHasAttempt($userId,$questionareId){
            $attempt = self::where("questionare_id=? and user_id=?",[$questionareId,$userId]);
            if(!$attempt){
            	return null;  
            }
            return $attempt[0];
        }
        
        // Save attempt
        public function save(){
            $sql="INSERT INTO questionareUserAttempt (user_id,questionare_id) VALUES (?,?)";
            Db::insert($sql,[$this->userId,$this->questionareId]);
            $this->id = Db::lastInsertId();
        }
        
        // Getters / setters
        public function getId() { return $this->id; }
    	public function getQuestionareId() { return $this->questionareId; }
        public function setQuestionareId($questionareId) {$this->questionareId=$questionareId;}

        public function getUserId() { return $this->userId; }
        public function setUserId($userId) {$this->userId=$userId;}
	}
?>