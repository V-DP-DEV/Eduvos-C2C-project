<?php
    require_once __DIR__ .'/model.php';
	require_once __DIR__ ."/../bootstrap.php";

	class QuestionareResponse extends Model{

    	protected static $table = 'questionareResponses';

        private $id;
        private $userId;
        private $questionChoiceId;
        
        // Create response
        function __construct($userId = null, $questionChoiceId = null){
            $this->userId = $userId;
            $this->questionChoiceId = $questionChoiceId;
        }
        
        // Save response
        public function save(){
            $conn = new Db();
            $sql = "INSERT INTO questionareResponses (user_id,questionare_choice_id) VALUES (?,?)";
            Db::insert($sql,[$this->userId,$this->questionChoiceId]);
        }
        
        // Build from DB row
        protected static function fromArray(array $row,$prefix): QuestionareResponse {
        	$questionareResponse = new QuestionareResponse();
        	$questionareResponse->id = $row[$prefix.'id'];
        	$questionareResponse->userId = $row[$prefix.'user_id'];
            $questionareResponse->questionChoiceId = $row[$prefix.'questionare_choice_id'];
        	return $questionareResponse;
    	}
        
        // Getters
        public function getId() { return $this->id; }
    	public function getUserId() { return $this->userId; }
        public function getQuestionChoiceId() { return $this->questionChoiceId; }
	}
?>