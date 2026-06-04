<?php 
	require_once 'model.php';
	require_once __DIR__ . "/../bootstrap.php";

	/* Bank recipient model */
	class BankRecepient extends Model {
        private $id;
        private $userId;
        private $bankCode;
        private $accountNumber;
        private $accountName;
        private $isDefault;
        private $recipientCode;
       
        protected static $table = 'bankRecepients';

		/* Create object from database row */
		protected static function fromArray(array $row, $prefix): BankRecepient {
			$bankRecepient = new BankRecepient();
            $bankRecepient->id = $row[$prefix."id"] ?? null;
            $bankRecepient->userId = $row[$prefix."user_id"] ?? null;
            $bankRecepient->bankCode = $row[$prefix."bank_code"] ?? null;
            $bankRecepient->accountNumber = $row[$prefix."account_number"] ?? null;
            $bankRecepient->accountName = $row[$prefix."account_name"] ?? null;
            $bankRecepient->isDefault = $row[$prefix."is_default"] ?? null;
            $bankRecepient->recipientCode = $row[$prefix."recipient_code"] ?? null;
            return $bankRecepient;
		}

		/* Save recipient to database */
		public function save() {
        	$sqlInfo = "INSERT INTO bankRecepients (user_id,bank_code,account_number,account_name,is_default,recipient_code) VALUES (?,?,?,?,?,?)";
        	Db::insert($sqlInfo, [$this->userId, $this->bankCode, $this->accountNumber, $this->accountName, $this->isDefault, $this->recipientCode]);
            $this->id = Db::lastInsertId();
		}
        
        // Getters and setters
        public function getId() { return $this->id; }
		public function setId($id) { $this->id = $id; }

		public function getUserId() { return $this->userId; }
		public function setUserId($userId) { $this->userId = $userId; }

		public function getBankCode() { return $this->bankCode; }
		public function setBankCode($bankCode) { $this->bankCode = $bankCode; }
	
		public function getAccountNumber() { return $this->accountNumber; }
		public function setAccountNumber($accountNumber) { $this->accountNumber = $accountNumber; }

		public function getAccountName() { return $this->accountName; }
		public function setAccountName($accountName) { $this->accountName = $accountName; }

		public function getIsDefault() { return $this->isDefault; }
		public function setIsDefault($isDefault) { $this->isDefault = $isDefault; }
        
        public function getRecipientCode() { return $this->recipientCode; }
		public function setRecipientCode($recipientCode) { $this->recipientCode = $recipientCode; }
	}
?>