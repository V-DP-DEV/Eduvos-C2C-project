<?php
    require_once __DIR__ .'/model.php';
	require_once __DIR__ ."/../bootstrap.php";

	class Reservation extends Model{

    	protected static $table = 'productReservations';

        private $id;
        private $userId;
        private $productId;
        private $price;
        private $reservedUntil;
        
        // Build reservation from DB row
        protected static function fromArray(array $row,$prefix): Reservation {
        	$reservation = new Reservation();
        	$reservation->id = $row[$prefix.'id'];
        	$reservation->userId = $row[$prefix.'user_id'];
            $reservation->productId = $row[$prefix.'product_id'];
            $reservation->reservedUntil = $row[$prefix.'reserved_until'];
            $reservation->price = $row[$prefix.'product_price'];
        	return $reservation;
    	}
        
        // Create reservation
        public function save(){
            $sql='INSERT INTO productReservations (user_id,product_id,product_price) VALUES (?,?,(SELECT price FROM products WHERE id=?))';
            Db::insert($sql,[$this->userId,$this->productId,$this->productId]);
            $this->id = Db::lastInsertId();
        }
        
        // Update reservation state
        public static function updateState($reserveId,$state){
            $sql = 'UPDATE productReservations SET state=? WHERE id =?';
            Db::update($sql,[$state,$reserveId]);
        }
        
        // Get active reservation for product
        public static function getActiveProductReservation($productId){
            $sql="SELECT * FROM productReservations 
                  WHERE product_id=? 
                  AND reserved_until > CURRENT_TIME() 
                  AND state='active' 
                  ORDER BY reserved_until DESC 
                  LIMIT 1";

            $row = Db::select($sql,[$productId]);

            if($row){
                return self::fromArray($row[0],'');
            }
            return null;
        }
        
        // Get active reservation for user
        public static function getActiveUserReservation($userId){
            $sql="SELECT * FROM productReservations 
                  WHERE user_id=? 
                  AND reserved_until > CURRENT_TIME() 
                  AND state='active' 
                  ORDER BY reserved_until DESC 
                  LIMIT 1";

            $row = Db::select($sql,[$userId]);

            if($row){
                return self::fromArray($row[0],'');
            }
            return null;
        }
        
        // Get active reservation by id
        public static function getReservationIfActive($id){
            $sql="SELECT * FROM productReservations 
                  WHERE id=? 
                  AND reserved_until > CURRENT_TIME() 
                  AND state='active' 
                  ORDER BY reserved_until DESC 
                  LIMIT 1";

            $row = Db::select($sql,[$id]);

            if($row){
                return static::fromArray($row[0],'');
            }
            return null;
        }
        
        // Getters / setters
        public function getId() { return $this->id; }

    	public function getUserId() { return $this->userId; }
    	public function setUserId($userId) { $this->userId = $userId; }

    	public function getProductId() { return $this->productId; }
    	public function setProductId($productId) { $this->productId = $productId; }

    	public function getReservedUntil() { return $this->reservedUntil; }

        public function getPrice() {return $this->price;}
	}
?>