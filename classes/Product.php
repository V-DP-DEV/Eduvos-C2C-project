<?php
    require_once __DIR__ .'/model.php';
	require_once __DIR__ ."/../bootstrap.php";

	class Product extends Model{

    	protected static $table = 'products';

        private $id;
        private $title;
        private $description;
        private $price;
        private $state;
        private $userId;
        private $category;
        private $categoryId;
        private $reviews;
        private $userSummary;
        private $productImgs = [];
        private $suburb;
        
        // Build product from DB row
        protected static function fromArray(array $row,$prefix): Product {
        	$product = new Product();
        	$product->id = $row[$prefix.'id'];
        	$product->title = $row[$prefix.'title'];
            $product->description = $row[$prefix.'description'];
            $product->price = $row[$prefix.'price'];
            $product->state = $row[$prefix.'state'];
            $product->userId = $row[$prefix."user_id"] ?? null;
            $product->categoryId = $row[$prefix."category_id"] ?? null;
        	return $product;
    	}
        
        // Update product state
        public static function updateProductState($productId,$state){
            $sql="UPDATE products SET state=? WHERE id=?";
            Db::update($sql,[$state,$productId]);
        }
        
        // Save new product
        public function save(){
            $sql="INSERT INTO products (title,price,description,category_id,user_id) VALUES (?,?,?,?,?)";
            Db::insert($sql,[$this->title,$this->price,$this->description,$this->categoryId,$this->userId]);
            $this->id = Db::lastInsertId();
        }
        
        // Update existing product
        public function update(){
            $sql="UPDATE products SET title=?,price=?,description=?,category_id=? WHERE id=?";
            Db::insert($sql,[$this->title,$this->price,$this->description,$this->categoryId,$this->id]);
        }
        
        // Save product images
        public function saveImages($files){
            $isPrimary = true;
            foreach($files as $file){
                $productImg = new ProductImage();
                $productImg->setProductId($this->id);
                $productImg->setIsPrimary($isPrimary);

                var_dump($isPrimary);
                $productImg->save($file);
                var_dump($productImg);

                if($isPrimary){
                    $isPrimary = 0;
                }
            }
        }
        
        // Load full product view
        public static function viewProduct($productId){
            $sqlWith = "WITH u AS(". UserSummary::baseWithSuburb() .")";
           
            $sql = $sqlWith."SELECT 
p.id AS p_id, p.title AS p_title, p.description AS p_description, p.price AS p_price, p.state AS p_state,
cat.id AS cat_id, cat.name AS cat_name,
u.id AS u_id, u.username AS u_username,u.verified as u_verified,u.total_reviews as u_total_reviews,u.avg_rating as u_avg_rating,
cit.id AS cit_id, cit.name AS cit_name,
s.id AS s_id, s.name AS s_name
FROM products AS p
INNER JOIN categories AS cat ON p.category_id = cat.id 
INNER JOIN u ON u.id = p.user_id 
INNER JOIN suburbs AS s ON s.id = u.suburb_id
INNER JOIN cities AS cit ON cit.id = s.city_id
WHERE p.id = ?;";

            $rows = Db::select($sql,[$productId]);
            if(!$rows){
                return null;
            }
            
            $product = Product::fromArray($rows[0],"p_");
            
            $category = Category::fromArray($rows[0],"cat_");
            $product->setCategory($category);
            
            $city = City::fromArray($rows[0],"cit_");
            $suburb = Suburb::fromArray($rows[0],"s_");
            $suburb->setCity($city);
            $product->setSuburb($suburb);
            
            $userSummary = UserSummary::fromArray($rows[0],"u_");
            $product->setUserSummary($userSummary);
            
            $productImgs = ProductImage::where("product_id =?",[$productId]);
            $product->setImgs($productImgs);
            
            $reviews = Review::withSubRatings($productId);
            if($reviews){
               $product->setReviews($reviews[0]); 
            }
            
            return $product;
        }
        
        // Load product with images and reviews only
        public static function withImgAndReviews($productId){
            $rows = self::where("id = ?",[$productId]);
            if(!$rows){
                return null;
            }

            $product = $rows[0];
            
            $productImgs = ProductImage::where("product_id =?",[$productId]);
            $product->setImgs($productImgs);
            
            $reviews = Review::withSubRatings($productId);
            if($reviews){
               $product->setReviews($reviews[0]); 
            }
            
            return $product;
        }
        
        // Replace product images
        public function reuploadImages($existingImgs,$newFiles){
            $placeholders = implode(',', array_fill(0, count($existingImgs), '?'));
        	$sql = "DELETE FROM productImgs WHERE product_id = ? AND img_id NOT IN ($placeholders)";

        	$params = array_merge([$this->id], $existingImgs);
        	Db::delete($sql, $params);
            
            foreach($newFiles as $file){
                $productImg = new ProductImage();
                $productImg->setProductId($this->id);
                $productImg->setIsPrimary(0);
                $productImg->save($file);
            }
        }
        
        // Check if product has active order
        public function hasActiveOrder(){
            $row = Order::where("product_id=? AND (state='completed' OR state='paid')",[$this->id]);
            
            return (bool)$row;
        }
        
        // Getters / setters
        public function getId() { return $this->id; }
		public function getTitle() { return $this->title; }
        public function setTitle($title) { $this->title=$title; }

		public function getDescription() { return $this->description; }
        public function setDescription($description) { $this->description=$description; }

		public function getPrice() { return $this->price; }
        public function setPrice($price) { $this->price=$price; }

		public function getState() { return $this->state; }

		public function getCategoryId() { return $this->categoryId; }
        public function setCategoryId($categoryId) { $this->categoryId=$categoryId; }

        public function setUserId($userId){$this->userId=$userId;}
        public function getUserId(){return $this->userId;}
        
        public function setImgs(array $productImgs){$this->productImgs =$productImgs;}
        public function getImgs(){return $this->productImgs;}

        public function setReviews($productReviews){$this->reviews = $productReviews;}
        public function getReviews(){return $this->reviews;}
        
        public function getCategory(){return $this->category;}
        public function setCategory($category){$this->category=$category;}
        
        public function getSuburb(){return $this->suburb;}
        public function setSuburb($suburb){$this->suburb=$suburb;}
        
        public function getUserSummary(){return $this->userSummary;}
        public function setUserSummary($userSummary){$this->userSummary=$userSummary;}
	}
?>