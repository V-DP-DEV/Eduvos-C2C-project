<?php 
	require_once 'model.php';
	require_once __DIR__ ."/../bootstrap.php";

	// Stores buyer/seller profile information
	class BuyerSellerInfo extends Model{

        private $id;
        private $userId;
        private $verified;
        private $verificationTimeOut;
        private $suburbId;
        private $cityId;
        private $suburb;
        private $imageId;
       
        protected static $table = 'buyerSellerInfo';

		// Build object from database row
		protected static function fromArray(array $row,$prefix):BuyerSellerInfo{
			$buyerSellerInfo = new BuyerSellerInfo();
            $buyerSellerInfo->id = $row[$prefix."id"];
            $buyerSellerInfo->userId = $row[$prefix."user_id"];
            $buyerSellerInfo->verified = $row[$prefix."verified"];
            $buyerSellerInfo->verificationTimeOut = $row[$prefix."verification_timeOut"];
            $buyerSellerInfo->suburbId = $row[$prefix."suburb_id"];
            $buyerSellerInfo->cityId = $row[$prefix.'city_id']??null;
            $buyerSellerInfo->imageId = $row[$prefix."img_id"];

            if($buyerSellerInfo->suburbId){
               $buyerSellerInfo->setSuburb(Suburb::withCity($buyerSellerInfo->suburbId));
            }

            return $buyerSellerInfo;
		}

		// Insert new record
		public function save(){
        	$sqlInfo = "INSERT INTO buyerSellerInfo (user_id,suburb_id,img_id) VALUES (?,?,?)";
        	Db::insert($sqlInfo,[$this->userId,$this->suburbId,$this->imageId]);
		}
        
        // Update existing record
        public function update(){
            if($this->imageId){
                $sql="UPDATE buyerSellerInfo SET img_id=?,suburb_id=? WHERE user_id=?";
                Db::update($sql,[$this->imageId,$this->suburbId,$this->userId]);
            }
            else{
              	$sql="UPDATE buyerSellerInfo SET suburb_id=? WHERE user_id=?";
                Db::update($sql,[$this->suburbId,$this->userId]);  
            }
        }
        
        // Check if user is banned
        public static function isBanned($userId){
            $sql = 'SELECT banned FROM buyerSellerInfo WHERE user_id = ?';
            $rows = Db::select($sql,[$userId]);

            if(!$rows){
                return false;
            }
            else{
                return $rows[0]['banned'];
            }
        }
        
        // Getters and setters
        public function getId() { return $this->id; }
        public function setId($id) { $this->id = $id; }

        public function setUserId($userId) {$this->userId=$userId; }
        public function getUserId() { return $this->userId; }

        public function getVerificationTimeOut() { return $this->verificationTimeOut; }

        public function getSuburbId() { return $this->suburbId; }
        public function setSuburbId($suburbId) { $this->suburbId = $suburbId; }
        
        public function getCityId() { return $this->cityId; }
        public function setCityId($cityId) { $this->cityId = $cityId; }

        public function getVerified() { return $this->role; }

        public function getImageId() { return $this->imageId; }
        public function setImageId($imageId) { $this->imageId = $imageId; }

        public function getSuburb(){ return $this->suburb; }
        public function setSuburb($suburb){ $this->suburb=$suburb; }
	}
?>