<?php
	require_once __DIR__ .'/model.php';
	require_once __DIR__ ."/../bootstrap.php";

	class SubRating extends Model {

    	protected static $table = 'subRatings';

        private $id;
        private $reviewId;
        private $subReviewCat;
        private $rating;

    	// Build sub rating from DB row
    	protected static function fromArray(array $row,$prefix): SubRating {
        	$subRating = new SubRating();
        	$subRating->id = $row[$prefix.'id'];
            $subRating->reviewId = $row[$prefix.'review_id'];
        	$subRating->subReviewCat = $row[$prefix.'sub_review_cat'];
            $subRating->rating = $row[$prefix.'rating'];
        	return $subRating;
    	}
        
        // Save sub rating
        public function save(){
            $sql = "INSERT INTO subRatings (review_id,rating,sub_review_cat) VALUES (?,?,?)";
            Db::insert($sql,[$this->reviewId,$this->rating,$this->subReviewCat]);
        }

    	// Getters / setters
    	public function getId() { return $this->id; }
        
    	public function getReviewId() { return $this->reviewId; }
        public function setReviewId($reviewId) {$this->reviewId=$reviewId;}
        
        public function getRating() { return $this->rating; }
        public function setRating($rating) {$this->rating=$rating;}
        
    	public function getSubReviewCat() {return $this->subReviewCat;}
        public function setSubReviewCat($subReviewCat) {$this->subReviewCat=$subReviewCat;}
	}
?>