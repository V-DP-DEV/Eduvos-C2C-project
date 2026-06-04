<?php
    // Load base Model class
    require_once __DIR__ .'/model.php';

    // UserSummary model for aggregated user data + JSON output
    class UserSummary extends Model implements JsonSerializable{

        // Core user summary fields
        private $id;
        private $name;
        private $verified;
        private $banned;
        private $avgRatings;
        private $totalReviews;
        private $since;
        private $suburbId;
        private $verificationTimeOut;
    
        // Map DB row into UserSummary object using column prefix
        protected static function fromArray(array $row,$prefix):UserSummary { 
            $userSummary = new UserSummary();

            // Safe mapping using null coalescing
            $userSummary->id = $row[$prefix.'id']??null;
            $userSummary->name = $row[$prefix.'username']??null;
            $userSummary->verified = $row[$prefix.'verified']??null;
            $userSummary->avgRatings = $row[$prefix.'avg_rating']??null;
            $userSummary->totalReviews = $row[$prefix."total_reviews"]??null;
            $userSummary->since = $row[$prefix."since"]??null;
            $userSummary->banned = $row[$prefix."banned"]??null;
            $userSummary->suburbId = $row[$prefix."suburb_id"]??null;
            $userSummary->verificationTimeOut = $row[$prefix."verification_timeOut"]??null;

            return $userSummary;
        }
    
        // Define how object is converted to JSON
        public function jsonSerialize(): mixed {
            return [
                'id' => $this->getId(),
                'username' => $this->getUsername(),
                'verified' => $this->getVerified(),
                'banned' => $this->getBanned(),
                'averageReviews' => $this->getAverageReviews(),
                'totalReviews' => $this->getTotalReviews(),
                'since' => $this->since
            ];
        }
    
        // Base aggregation query for user stats
        public static function baseQuery(){
            return "SELECT 
                        u.id AS id,
                        u.username AS username,
                        u.since as since,
                        i.verified AS verified,
                        i.banned as banned,
                        COUNT(r.id) AS total_reviews,
                        COALESCE(AVG(r.rating), 0) AS avg_rating
                    FROM users AS u
                    JOIN buyerSellerInfo AS i on u.id=i.user_id
                    LEFT JOIN products as p on p.user_id=u.id
                    LEFT JOIN reviews as r on p.id=r.product_id
                    GROUP BY 
                        u.id, u.username, u.since, i.verified";
        }
    
        // Base query including suburb information
        public static function baseWithSuburb(){
            return "SELECT 
                        u.id AS id,
                        u.username AS username,
                        u.since as since,
                        i.verified AS verified,
                        i.banned as banned,
                        i.suburb_id as suburb_id,
                        COUNT(r.id) AS total_reviews,
                        COALESCE(AVG(r.rating), 0) AS avg_rating
                    FROM users AS u
                    JOIN buyerSellerInfo AS i on u.id=i.user_id
                    LEFT JOIN products as p on p.user_id=u.id
                    LEFT JOIN reviews as r on p.id=r.product_id
                    GROUP BY 
                        u.id, u.username, u.since, i.verified,i.suburb_id";
        }
    
        // Check if a user is eligible for verification
        public static function validForVerification($userId){
        
            $sql = "SELECT 
                        u.id AS id,
                        u.username AS username,
                        i.verification_timeOut as verification_timeOut,
                        u.since as since,
                        i.verified AS verified,
                        i.banned as banned,
                        i.suburb_id as suburb_id,
                        COUNT(r.id) AS total_reviews,
                        COALESCE(AVG(r.rating), 0) AS avg_rating
                    FROM users AS u
                    JOIN buyerSellerInfo AS i on u.id=i.user_id
                    LEFT JOIN products as p on p.user_id=u.id
                    LEFT JOIN reviews as r on p.id=r.product_id
                    WHERE i.verification_timeOut < CURRENT_TIME() AND u.id=?
                    GROUP BY 
                        u.id, u.username, u.since, i.verified,i.suburb_id,i.verification_timeOut";

            // Execute query
            $rows = Db::select($sql,[$userId]);

            // No user found or not eligible
            if(!$rows){
                return false;
            }

            // Convert DB row to object
            $user = self::fromArray($rows,"");

            // Must be not verified and not banned to qualify
            if(!$user->getVerified() && !$user->getBanned()){
                return true;
            }
        }
    
        // Fetch filtered, sorted user summaries
        public static function getNormalUsers($filters,$orderBy,$dir){

            // Wrap base query for filtering
            $sql = "SELECT * FROM (".UserSummary::baseQuery().") as u WHERE 1=1";
            $params = [];

            // Filter mapping rules
            $map = [
                'verified' => fn($v) => ['u.verified = ?', $v],
                'banned'   => fn($v) => ['banned = ?', $v],
                'search'   => fn($v) => ['u.username LIKE ?', "%$v%"],
            ];

            // Apply filters dynamically
            $sql = UserSummary::applyFilters($sql,$params,$filters,$map);

            // Sorting map for safe ordering
            $orderMap = [
                'id'        => 'u.id',
                'avgRating' => 'u.avg_rating',
            ];

            // Apply sorting
            $sql = UserSummary::applySorting($sql,$orderBy,$orderMap,'u.id',$dir);
        
            // Execute query
            $rows = Db::select($sql,$params);
        
            // No results
            if(!$rows){
                return null;
            }

            // Convert rows into objects
            return UserSummary::mapRows($rows,'');
        }

        // Getters (read-only accessors)

        public function getId() { return $this->id; }
        public function getUsername() { return $this->name; }
        public function getVerified() { return $this->verified; }
        public function getTotalReviews() { return $this->totalReviews; }
        public function getAverageReviews(){ return $this->avgRatings; }
        public function getBanned() { return $this->banned; }
        public function getSince() { return $this->since; }
    }
?>