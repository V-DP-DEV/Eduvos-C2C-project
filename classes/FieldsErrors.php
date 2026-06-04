<?php
class FieldsErrors{
	//to store errors
	private $errors = [];
	public function addError($field,$error){
		//check if value exists for the key
		if(!isset($this->errors[$field])){
			//add the key value pair with field to empty array
			$this->errors[$field] = [];
		}
		//add dynamically to the field array
		$this->errors[$field][] = $error;
	}
	public function getErrors(){
		return $this->errors;
	}
	public function hasErrors(){
		return !empty($this->errors);
	}
}
?>

