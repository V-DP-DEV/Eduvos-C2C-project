<?php
    require_once __DIR__ .'/model.php';

    class Category extends Model{

        protected static $table = 'categories';

        private $id;
        private $name;
        
        // Create object from database row
        protected static function fromArray(array $row,$prefix): Category {
        	$category = new Category();
        	$category->id = $row[$prefix.'id'] ?? null;
        	$category->name = $row[$prefix.'name'] ?? null;
        	return $category;
    	}
        
        public function getId() { return $this->id; }
        public function setId($id) { $this->id = $id; }
    	public function getName() { return $this->name; }
	}
?>