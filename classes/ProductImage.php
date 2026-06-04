<?php
    require_once __DIR__ .'/model.php';
	require_once __DIR__ ."/../bootstrap.php";

	class ProductImage extends Model implements JsonSerializable{

    	protected static $table = 'productImgs';

        private $id;
        private $imageId;
        private $productId;
        private $isPrimary;
        
        // Build image object from DB row
        protected static function fromArray(array $row,$prefix): ProductImage {
        	$productImg = new ProductImage();
        	$productImg->id = $row[$prefix.'id'] ?? null;
        	$productImg->imageId = $row[$prefix.'img_id'] ?? null;
            $productImg->productId = $row[$prefix.'product_id'] ?? null;
            $productImg->isPrimary = $row[$prefix.'is_primary'] ?? null;
        	return $productImg;
    	}
        
        // JSON output
        public function jsonSerialize(): mixed {
        	return [
            	'imageId' => $this->getImageId(),
        	];
    	}
        
        // Save image + DB record
        public function save($file){
            $image = Image::prepareImage($file,800,800,80);
            $image->setVisibility('public');
            $image->save();

            $this->imageId = $image->getId();

            $sql = "INSERT INTO productImgs (img_id,product_id,is_primary) VALUES (?,?,?)";
            Db::insert($sql,[$image->getId(),$this->productId,$this->isPrimary]);

            $this->id = Db::lastInsertId();
        }
        
        // Get all images for product
        public static function getAllImagesFromProduct($productId){
            $images = self::where("product_id=?", $productId);
            if(!$images){
                return null;
            }
            return $images;
        }
        
        // Getters / setters
        public function getId() { return $this->id; }
		public function getImageId() { return $this->imageId; }

		public function getProductId() { return $this->productId; }
        public function setProductId($productId) { $this->productId=$productId; }

		public function getIsPrimary() { return $this->isPrimary; }
        public function setIsPrimary($isPrimary) { $this->isPrimary=$isPrimary; }
	}
?>