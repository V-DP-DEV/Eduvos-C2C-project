<?php
    require_once __DIR__ .'/model.php';
	require_once __DIR__ ."/../bootstrap.php";

	class Order extends Model implements JsonSerializable{

    	protected static $table = 'orders';

        private $id;
        private $userId;
        private $productId;
        private $price;
        private $state;
        private $sellerId;
        private $title;
        private $userSummary;
        private $sellerSummary;
        private $hasReview;
        private $reservationId;
        
        // Build Order from database row
        protected static function fromArray(array $row,$prefix): Order {
        	$order = new Order();
        	$order->id = $row[$prefix.'id']??null;
        	$order->userId = $row[$prefix.'user_id']??null;
            $order->productId = $row[$prefix.'product_id']??null;
            $order->price = $row[$prefix.'product_price']??null;
            $order->sellerId = $row[$prefix.'seller_id']??null;
            $order->title = $row[$prefix.'product_title']??null;
            $order->state = $row[$prefix.'state']??null;
            $order->hasReview = $row[$prefix.'has_review']??null;
            $order->reservationId = $row[$prefix.'reservation_id']??null;

            if(Order::hasPrefixedData($row,'u_')){
                $order->setUserSummary(UserSummary::fromArray($row,"u_"));
            }
            if(Order::hasPrefixedData($row,'seller_')){
                $order->setSellerSummary(UserSummary::fromArray($row,"seller_"));
            }

        	return $order;
    	}
        
        // JSON output
        public function jsonSerialize(): mixed {
    		return [
        		'id' => $this->id,
        		'userId' => $this->userId,
        		'productId' => $this->productId,
        		'price' => $this->price,
        		'state' => $this->state,
        		'sellerId' => $this->sellerId,
        		'title' => $this->title,

        		'userSummary' => $this->userSummary,
        		'sellerSummary' => $this->sellerSummary,

        		'hasReview' => $this->hasReview,
        		'reservationId' => $this->reservationId
    		];
		}
        
        // Update order state
        public function updateState($state){
            $sql = "UPDATE orders SET state=? WHERE id=?";
            Db::update($sql,[$state,$this->id]);
        }
        
        // Get paid order for user
        public static function getPaidOrder($userId,$orderId){
            $orders = Order::where("user_id=? AND id = ? AND state='paid'",[$userId,$orderId]);
        	if($orders===null){
            	return null;
        	}
            return $orders[0];
        }
        
        // Get received order for seller
        public static function getReceivedOrder($userId,$orderId){
            $orders = Order::where("seller_id=? AND id = ? AND state='received'",[$userId,$orderId]);
        	if($orders===null){
            	return null;
        	}
            return $orders[0];
        }
        
        // Get orders for buyer
        public static function getMyOrders($userId,$filters){
            $sqlWith = "WITH u AS(". UserSummary::baseWithSuburb() .")";
   			$params=[$userId]; 

            $sql= $sqlWith." SELECT 
EXISTS (
    SELECT 1
    FROM reviews r
    WHERE r.product_id = o.product_id
) AS o_has_review,
o.id o_id,o.product_price as o_product_price,o.state as o_state,o.user_id as o_user_id,o.product_title as o_product_title,
u.id AS seller_id, u.username AS seller_username,u.verified as seller_verified,u.banned as seller_banned,u.total_reviews as seller_total_reviews,u.avg_rating as seller_avg_rating
FROM orders AS o
INNER JOIN u ON u.id = o.seller_id
WHERE o.user_id=?";

            $map = [
    			'state' => fn($v) => ['o.state = ?', $v],
			];

            $sql = Order::applyFilters($sql,$params,$filters,$map);
            $rows = Db::select($sql,$params);

            return Order::mapRows($rows,'o_');
        }
        
        // Get orders for seller
        public static function getSellerOrders($userId,$filters){
            $sqlWith = "WITH u AS(". UserSummary::baseWithSuburb() .")";
   			$params=[$userId]; 

            $sql= $sqlWith." SELECT 
o.id o_id,o.product_price as o_product_price,o.state as o_state,o.user_id as o_user_id,o.product_title as o_product_title,
u.id AS u_id, u.username AS u_username,u.verified as u_verified,u.banned as u_banned,u.total_reviews as u_total_reviews,u.avg_rating as u_avg_rating
FROM orders AS o
INNER JOIN u ON u.id = o.user_id
WHERE o.seller_id=?";

            $map = [
    			'state' => fn($v) => ['o.state = ?', $v],
			];

            $sql = Order::applyFilters($sql,$params,$filters,$map);
            $rows = Db::select($sql,$params);

            return Order::mapRows($rows,'o_');
        }
        
        // Create order from product
        public function save(){
            $sql = 'INSERT INTO orders (user_id,product_id,product_price,product_title,seller_id,reservation_id)
                    SELECT ?,?,?,title,user_id,? FROM products where id=?';

            Db::insert($sql,[
                $this->userId,
                $this->productId,
                $this->price,
                $this->reservationId,
                $this->productId
            ]);
        }
        
        // Getters / setters
        public function getId() { return $this->id; }
        public function setId($id) {$this->id =$id;}

        public function getState() {return $this->state;}

    	public function getUserId() { return $this->userId; }
    	public function setUserId($userId) { $this->userId = $userId; }

    	public function getProductId() { return $this->productId; }
    	public function setProductId($productId) { $this->productId = $productId; }

    	public function getPrice() { return $this->price; }
    	public function setPrice($price) { $this->price = $price; }
        
        public function setReservationId($reservationId) {$this->reservationId = $reservationId;}
        public function getReservationId() {return $this->reservationId;}
        
        public function getSellerId() { return $this->sellerId; }

		public function getTitle() { return $this->title; }
        
        public function getUserSummary() {return $this->userSummary;}
        public function setUserSummary($userSummary) {$this->userSummary=$userSummary;}
        
        public function getSellerSummary() {return $this->sellerSummary;}
        public function setSellerSummary($sellerSummary) {$this->sellerSummary=$sellerSummary;}
        
        public function hasReview() {return $this->hasReview;}
	}
?>