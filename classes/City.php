<?php
require_once __DIR__ .'/model.php';
require_once __DIR__ ."/../bootstrap.php";

class City extends Model implements JsonSerializable{

    private $id;
    private $name;
    private $suburbs = [];

    protected static $table = 'cities';
    
    // Create object from database row
    protected static function fromArray(array $row,$prefix): City {
        $city = new City();
        $city->id = $row[$prefix.'id'] ?? null;
        $city->name = $row[$prefix.'name'] ?? null;
        return $city;
    }
    
    // Load related suburbs
    public function loadSuburbs(){
        $this->suburbs = Suburb::where('city_id=?',[$this->id]);
        echo var_dump($this->suburbs);
    }
    
    // JSON output format
    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getSuburbs() { return $this->suburbs; }
}
?>