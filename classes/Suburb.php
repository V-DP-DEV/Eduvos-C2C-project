<?php 
	require_once __DIR__ .'/model.php';

	class Suburb extends Model implements JsonSerializable{

		protected static $table = 'suburbs';

		// fields
		private $id;
		private $name;
        private $city;

		// Build suburb from DB row
		protected static function fromArray(array $row,$prefix): Suburb{
			$suburb = new Suburb();
			$suburb->id = $row[$prefix.'id'] ?? null;
			$suburb->name = $row[$prefix.'name'] ?? null;
            return $suburb;
		}
        
        // Load suburb with city join
        public static function withCity($suburbId){
            $sql = 'SELECT suburbs.id as s_id,suburbs.name as s_name,
                           cities.id as c_id,cities.name as c_name 
                    FROM suburbs 
                    INNER JOIN cities ON cities.id=suburbs.city_id
                    WHERE suburbs.id=?';
            
            $rows = Db::select($sql,[$suburbId]);

            if(!$rows){
                return null;
            }

            $suburb = self::fromArray($rows[0],'s_');
            $city = City::fromArray($rows[0],'c_');

            $suburb->setCity($city);

            return $suburb;
        }
        
        // JSON output
        public function jsonSerialize(): mixed {
            if($this->city){
                return [
                    'id' => $this->id,
                    'name' => $this->name,
                    'city' => $this->city
                ];
            }

        	return [
            	'id' => $this->id,
            	'name' => $this->name
        	];
    	}
        
        // City relation
        public function setCity($city){
            $this->city = $city;
        }
        
        public function getCity(){
            return $this->city;
        }
        
        // Getters
        public function getId() { return $this->id; }
    	public function getName() { return $this->name; }
	}
?>