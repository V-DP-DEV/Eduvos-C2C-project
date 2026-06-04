<?php 
	require_once 'model.php';
	//TO:DO!!   fix db reference
	class Image extends Model{
		//add fields
        private $id;
        private $data;
        private $mime;
        private $visibility;
        protected static $table = 'images';

		protected static function fromArray(array $row,$prefix):Image{
			$image = new Image();
            $image->id = $row[$prefix.'id'];
            $image->data= $row[$prefix.'raw_data'];
            $image->mime= $row[$prefix.'mime_type'];
            $image->visibility =$row[$prefix.'visibility'];
            return $image;
		}

		public function save(){
            //FIX suburbID insert
            //FIX userImg update
            $sqlMain = "INSERT INTO images (raw_data,mime_type,visibility) VALUES (?,?,?)";
            Db::insert($sqlMain,[$this->data,$this->mime,$this->visibility]);
            $this->id=Db::lastInsertId();
		}
        
        public static function deleteProductImages($productId){
            $sql="DELETE FROM images
				WHERE id IN (
  					SELECT img_id FROM productImgs WHERE product_id=?
				);";
            Db::delete($sql,[$productId]);
        }
		
        public static function prepareImage($sourcePath, $maxWidth, $maxHeight, $quality = 75){
    		$info = getimagesize($sourcePath);

    		if ($info === false) return false;

    		list($width, $height, $type) = $info;

    		// Resize calculation
    		$ratio = min($maxWidth / $width, $maxHeight / $height, 1);
    		$newWidth = (int)($width * $ratio);
    		$newHeight = (int)($height * $ratio);

    		// Create source
    		switch ($type) {
        		case IMAGETYPE_JPEG:
            		$src = imagecreatefromjpeg($sourcePath);
            		break;
        		case IMAGETYPE_PNG:
            		$src = imagecreatefrompng($sourcePath);
            		break;
        		default:
            		return false;
    		}

    		$dst = imagecreatetruecolor($newWidth, $newHeight);

    		// PNG transparency
    		if ($type == IMAGETYPE_PNG) {
        		imagealphablending($dst, false);
        		imagesavealpha($dst, true);
        		$transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        		imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
    		}

    		imagecopyresampled($dst, $src, 0, 0, 0, 0,
        		$newWidth, $newHeight, $width, $height
    		);

    		// Capture output as blob
    		ob_start();

    		if ($type == IMAGETYPE_JPEG) {
        		imagejpeg($dst, null, $quality);
        		$mime = 'image/jpeg';
    		} else {
        		$pngQuality = (int) round((100 - $quality) / 10);
        		imagepng($dst, null, $pngQuality);
        		$mime = 'image/png';
    		}

    		$imageData = ob_get_clean();

    		imagedestroy($src);
    		imagedestroy($dst);
	
    		$image= new Image();
    		$image->data = $imageData;
   			$image->mime=$mime;
    		return $image;
		}
        
        public function getId() { return $this->id; }
        public function setVisibility($visibility) {$this->visibility=$visibility;}
        public function getVisibility() {return $this->visibility;}
        public function getData(){return $this->data;}
        public function getMime(){return $this->mime;}
    }
?>