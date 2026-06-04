<?php
    require_once __DIR__ .'/model.php';
	require_once __DIR__ ."/../bootstrap.php";
	class Transaction extends Model implements JsonSerializable{
    	protected static $table = 'transactions';
        private $id;
        private $reservationId;
        private $orderId;
        private $type;
        private $amount;
        private $reference;
        private $refundId;
        private $relatedId;
        private $createdAt;
        private $affectedUserId;
        private $recepientCode;
        private $status;
        private $title;
        
        protected static function fromArray(array $row,$prefix): Transaction {
        	$transaction = new Transaction();
        	$transaction->id = $row[$prefix.'id']??null;
        	$transaction->reservationId = $row[$prefix.'reservation_id']??null;
            $transaction->orderId = $row[$prefix.'order_id']??null;
            $transaction->type = $row[$prefix.'type']??null;
            $transaction->amount = $row[$prefix.'amount']??null;
            $transaction->reference = $row[$prefix.'reference']??null;
            $transaction->createdAt = $row[$prefix.'created_at']??null;
            $transaction->refundId = $row[$prefix.'paystack_refund_id']??null;
            $transaction->relatedId= $row[$prefix.'related_transaction_id']??null;
            $transaction->affectedUserId = $row[$prefix.'affected_user_id']??null;
            $transaction->status = $row[$prefix.'status']??null;
            $transaction->title=$row[$prefix.'title']??null;
            $transaction->recepientCode=$row[$prefix.'recepientCode']??null;
        	return $transaction;
    	}
        
        public function jsonSerialize(): mixed {
    	return [
        	'id' => $this->id,
        	'reservationId' => $this->reservationId,
        	'orderId' => $this->orderId,
        	'type' => $this->type,
        	'amount' => $this->amount,
        	'reference' => $this->reference,
        	'refundId' => $this->refundId,
        	'relatedId' => $this->relatedId,
        	'createdAt' => $this->createdAt,
        	'affectedUserId' => $this->affectedUserId,
        	'status' => $this->status,
        	'title' => $this->title
    	];
}
        
        public function updateState($state){
            $sql = "UPDATE transactions SET status=? WHERE id=?";
            Db::update($sql,[$state,$this->id]);
        }
        
        public function getActiveUserTransactions($userId){
            
        }
        
        public function save(){
            //$sql='INSERT INTO orders (user_id,product_id,product_price,product_title,seller_id) SELECT ?,?,?,title,user_id FROM products where id=?';
            //Db::insert($sql,[$this->userId,$this->productId,$this->price,$this->productId]);
            $sql="INSERT INTO transactions (reservation_id,order_id,type,amount,reference,paystack_refund_id,related_transaction_id,affected_user_id,recepient_code) VALUES (?,?,?,?,?,?,?,?,?)";
            Db::insert($sql,[$this->reservationId,$this->orderId,$this->type,$this->amount,$this->reference,$this->refundId,$this->relatedId,$this->affectedUserId,$this->recepientCode]);
        }
        
                public static function getTransactions($userId,$filters,$orderBy,$dir){
   			$params=[]; 
            $params[] = $userId;
        $sql=" SELECT 
t.id as id,t.amount as amount,t.type as type,t.status as status,t.created_at as created_at,p.title as title
FROM transactions as t
INNER JOIN productReservations as r ON t.reservation_id=r.id
LEFT JOIN products as p ON r.product_id=p.id
WHERE t.affected_user_id=?";
            $map = [
    'status' => fn($v) => ['t.status = ?', $v],

    'type'   => fn($v) => ['t.type = ?', $v],
];
            $sql=Transaction::applyFilters($sql,$params,$filters,$map);
            $orderMap = [
        		'id'       => 't.id',
    		];
            $sql=Transaction::applySorting($sql,$orderBy,$orderMap,'t.id',$dir);
            $rows= Db::select($sql,$params);
            //var_dump($sql);
            return Transaction::mapRows($rows,'');
        }
        
        
        
        public function getId() { return $this->id; }

    public function getRefundId(){ return $this->refundId; } 
    public function setRefundId($refundId){ $this->refundId = $refundId; return $this; } 
        
    public function getRelatedId(){ return $this->relatedId; } 
    public function setRelatedId($relatedId){ $this->relatedId = $relatedId; return $this; }
        
    public function getReservationId() { return $this->reservationId; }
    public function setReservationId($reservationId) { $this->reservationId = $reservationId; }

    public function getOrderId() { return $this->orderId; }
    public function setOrderId($orderId) { $this->orderId = $orderId; }

    public function getType() { return $this->type; }
    public function setType($type) { $this->type = $type; }

    public function getStatus() {return $this->status;}    
        
    public function getAmount() { return $this->amount; }
    public function setAmount($amount) { $this->amount = $amount; }
        
    public function getTitle() { return $this->title; }

    public function getReference() { return $this->reference; }
    public function setReference($reference) { $this->reference = $reference; }
        
   	public function getAffectedUserId() { return $this->affectedUserId; }
	public function setAffectedUserId($affectedUserId) { $this->affectedUserId = $affectedUserId; }

    public function getCreatedAt() { return $this->createdAt; }
    public function getRecepientCode(){return $this->recepientCode;}
	public function setRecepientCode($recepientCode){$this->recepientCode=$recepientCode;}    
       
	}
?>