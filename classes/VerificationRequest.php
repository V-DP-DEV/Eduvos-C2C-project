<?php 
    // Load base model + dependencies
    require_once 'model.php';
    require_once 'Image.php';
    require_once 'UserSummary.php';

    // VerificationRequest model handles user verification submissions
    class VerificationRequest extends Model implements JsonSerializable{

        // Database table for this model
        protected static $table = 'verificationRequest';

        // Core fields
        private $id;
        private $userId;
        private $imgId;
        private $imgSelfie;
        private $userSummary;
        
        // Map DB row into object using prefix-based column naming
        protected static function fromArray(array $row,$prefix):VerificationRequest{
            $verificationRequest = new VerificationRequest();

            // Basic field mapping
            $verificationRequest->id = $row[$prefix."id"]??null;
            $verificationRequest->userId = $row[$prefix."user_id"]??null;
            $verificationRequest->imgId = $row[$prefix."img_id_card_id"]??null;
            $verificationRequest->imgSelfie = $row[$prefix."img_selfie_id"]??null;

            // Nested user summary object (joined data)
            $verificationRequest->setUserSummary(
                UserSummary::fromArray($row,'user_summ_')
            );

            return $verificationRequest;
        }
        
        // JSON representation of verification request
        public function jsonSerialize(): mixed {
            return [
                'id' => $this->id,
                'userId' => $this->userId,
                'imgId' => $this->imgId ?? null,
                'imgSelfie' => $this->imgSelfie ?? null,

                // Nested object included in API output
                'userSummary' => $this->userSummary
            ];
        }   

        // Save verification request to DB
        public function save(){

            // TODO: ensure suburbID handling is correct
            // TODO: ensure user image update logic is handled elsewhere

            $sqlMain = "INSERT INTO verificationRequest (user_id,img_id_card_id,img_selfie_id) VALUES (?,?,?)";

            // Insert record
            Db::insert($sqlMain,[
                $this->userId,
                $this->imgId,
                $this->imgSelfie
            ]);

            // Store generated ID
            $this->id = Db::lastInsertId();
        }
        
        // Get all verification requests with sorting
        public static function getVerifications($orderBy,$dir){

            // Use CTE for user summary aggregation
            $sqlWith = "WITH u AS(". UserSummary::baseQuery() .")";

            // Join verification requests with user summary
            $sql = $sqlWith . "SELECT 
                        v.id as id,
                        v.user_id as user_id,
                        u.username as user_summ_username,
                        u.verified as user_summ_verified,
                        u.id as user_summ_id,
                        u.since as user_summ_since,
                        u.avg_rating as user_summ_avg_rating,
                        u.total_reviews as user_summ_total_reviews,
                        u.banned as user_summ_banned
                    FROM verificationRequest AS v
                    INNER JOIN u on v.user_id=u.id";

            // Sorting rules
            $orderMap = [
                'since'     => 'u.since',
                'avgRating' => 'u.avg_rating',
            ];

            // Apply safe sorting
            $sql = self::applySorting($sql,$orderBy,$orderMap,'v.id',$dir);

            // Execute query
            $rows = Db::select($sql,[]);

            // No data found
            if(!$rows){
                return null; 
            }

            // Map results into objects
            return self::mapRows($rows,'');
        }
        
        // Base query for verification requests with user summary
        public static function baseQuery(){

            $sqlWith = "WITH u AS(". UserSummary::baseQuery() .")";

            return $sqlWith . "SELECT 
                        v.id as id,
                        v.user_id as user_id,
                        v.img_id_card_id as img_id_card_id,
                        v.img_selfie_id as img_selfie_id,
                        u.username as user_summ_username,
                        u.verified as user_summ_verified,
                        u.id as user_summ_id,
                        u.avg_rating as user_summ_avg_rating,
                        u.total_reviews as user_summ_total_reviews,
                        u.banned as user_summ_banned
                    FROM verificationRequest AS v
                    INNER JOIN u on v.user_id=u.id";
        }
        
        // Save and process uploaded images for verification
        public function saveImages($imageId,$imageSelfie){

            // Compress and prepare ID image
            $imageCompressed = Image::prepareImage($imageId,700,394,85);
            $imageCompressed->setVisibility("admin_only");
            $imageCompressed->save();

            // Store generated image ID
            $this->imgId = Db::lastInsertId();

            // Compress and prepare selfie image
            $imageCompressed2 = Image::prepareImage($imageSelfie,700,394,85);
            $imageCompressed2->setVisibility("admin_only");
            $imageCompressed2->save();

            // Store generated selfie image ID
            $this->imgSelfie = Db::lastInsertId();
        }
        
        // Getters and setters

        public function getId() { return $this->id; }

        public function getUserId() { return $this->userId; }
        public function setUserId($userId) { $this->userId = $userId; }

        public function setUserSummary($userSummary){
            $this->userSummary = $userSummary;
        }

        public function getUserSummary(){
            return $this->userSummary;
        }

        public function getImageId() { return $this->imgId; }

        public function getImageSelfie() { return $this->imgSelfie; }
    }
?>