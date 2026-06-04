<?php
    require_once __DIR__ .'/model.php';
	require_once __DIR__ ."/../bootstrap.php";

	class Questionare extends Model{

    	protected static $table = 'questionares';

        private $id;
        private $state;
        
        // Build object from DB row
        protected static function fromArray(array $row,$prefix): Questionare {
        	$questionare = new Questionare();
        	$questionare->id = $row[$prefix.'id'] ?? null;
        	$questionare->state = $row[$prefix.'state'] ?? null;
        	return $questionare;
    	}
        
        // Get active questionare
        public static function getActiveQuestionare(){
        	$questionare = self::where("state='active'",[]);
            if(!$questionare){
                return null;
            }
            return $questionare[0];
        }
        
        // Getters
        public function getId() { return $this->id; }
    	public function getState() { return $this->state; }
	}
?>