<?php
	require_once __DIR__ .'/model.php';

	class Review extends Model {

    	protected static $table = 'reviews';

        private $id;
        private $productId;
        private $userId;
        private $userSummary;
        private $rating;
        private $picture;
        private $comments;
        
    	private $subRatings = [];
    
        // Build review from DB row
    	protected static function fromArray(array $row,$prefix): Review {
        	$review = new Review();
        	$review->id = $row[$prefix.'id'];
            $review->productId = $row[$prefix.'product_id'];
        	$review->userId = $row[$prefix.'user_id'];
            $review->rating = $row[$prefix.'rating'];
            $review->picture = $row[$prefix.'img_id'];
            $review->comments = $row[$prefix.'comments'];
        	return $review;
    	}
    
    	// Load reviews with sub ratings
    	public static function withSubRatings($productId){
    		$reviews = self::where("product_id = ?", [$productId]);

            if(!$reviews){
                return null;
            }
             
            $review = $reviews[0];

            $userSummary = UserSummary::find($review->getUserId());
            $review->setUserSummary($userSummary);

    		$subRatings = SubRating::where("review_id=?", [$review->getId()]);
            $review->setSubRatings($subRatings);

    		return $reviews;
		}
        
        // Load reviews grouped with subratings (bulk)
        public static function reviewsWithSubRatings($userId){
    		$reviews = self::where("product_id = ?", [$productId]);
            
    		$ids = array_map(fn($r) => $r->getId(), $reviews);

    		if (empty($ids)) return $reviews;

    		$placeholders = implode(',', array_fill(0, count($ids), '?'));

    		$subRatings = SubRating::where("review_id IN ($placeholders)", $ids);

    		$grouped = [];
    		foreach ($subRatings as $s) {
        		$grouped[$s->getReviewId()][] = $s;
    		}

    		foreach ($reviews as $r) {
        		$r->setSubRatings($grouped[$r->getId()] ?? []);
    		}

    		return $reviews;
		}
        
        // Save review
        public function save($photoPath){
            $image = Image::prepareImage($photoPath,500,500,80);
            $image->setVisibility('public');
            $image->save();

            $this->picture = $image->getId();

            $sql = "INSERT INTO reviews (product_id,user_id,rating,comments,img_id) VALUES (?,?,?,?,?)";
            Db::insert($sql,[$this->productId,$this->userId,$this->rating,$this->comments,$this->picture]);

            $this->id = Db::lastInsertId();
        }
        
        // Check if user already reviewed product
        public static function hasUserLeftReviewOnProduct($productId,$userId){
            $sql = "SELECT * FROM reviews WHERE user_id=? and product_id=?";
            $row = Db::select($sql,[$userId,$productId]);

            return (bool)$row;
        }

    	// Getters / setters
    	public function getId() { return $this->id; }
    	
        public function getProductId() { return $this->productId; }
        public function setProductId($productId) {$this->productId=$productId;}
    	
        public function getUserId() {return $this->userId;}
        public function setUserId($userId) {$this->userId=$userId;}
        
        public function getRating() {return $this->rating;}
        public function setRating($rating) {$this->rating=$rating;}
        
        public function getPicture() {return $this->picture;}
        
        public function getComments() {return $this->comments;}
        public function setComments($comments) {$this->comments=$comments;}
        
        public function setSubRatings($subRatings){$this->subRatings= $subRatings;}
        public function getSubRatings(){return $this->subRatings;}
        
        public function setUserSummary($userSummary){$this->userSummary=$userSummary;}
        public function getUserSummary(){return $this->userSummary;}
	}
?>