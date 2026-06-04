<?php 
    // Load base model class
    require_once 'model.php';
    // Load bootstrap (likely DB/config/autoload)
    require_once __DIR__ ."/../bootstrap.php";

    // User model extending base Model class
    class User extends Model{

        // User properties
        private $id;
        private $username;
        private $email;
        private $phone;
        private $role;
        private $hashPassword;
        private $buyerSellerInfo;
        
        // Database table name for this model
        protected static $table = 'users';

        // Create User object from DB row using alias prefix
        protected static function fromArray(array $row,$prefix):User{
            $user = new User();

            // Map DB columns to object properties
            $user->id = $row[$prefix."id"];
            $user->username = $row[$prefix."username"];
            $user->email = $row[$prefix."email"];
            $user->phone = $row[$prefix."phone"];
            $user->role = $row[$prefix."role"];

            // Optional field (commented out in current schema)
            //$user->suburbId = $row[$prefix."suburbId"];

            return $user;
        }

        // Insert new user into database
        public function save(){
            $sqlMain = "INSERT INTO users (username,email,phone,role,password) VALUES (?,?,?,?,?)";
            
            // Execute insert query
            Db::insert($sqlMain,[
                $this->username,
                $this->email,
                $this->phone,
                $this->role,
                $this->hashPassword
            ]);

            // Store generated ID
            $this->id = Db::lastInsertId();
        }
        
        // Fetch user with buyer/seller related info joined
        public static function withBuyerSellerInfo($userId){

            $sql = "SELECT 
                        u.id as u_id,
                        u.email as u_email,
                        u.phone as u_phone,
                        u.username as u_username,
                        u.role as u_role,
                        u.since as u_since,
                        b.id b_id,
                        b.suburb_id as b_suburb_id,
                        b.user_id as b_user_id,
                        b.verified as b_verified,
                        b.verification_timeOut as b_verification_timeOut,
                        b.img_id as b_img_id,
                        c.id as b_city_id
                    FROM users as u
                    LEFT JOIN buyerSellerInfo as b on u.id=b.user_id 
                    LEFT JOIN suburbs as s on s.id=b.suburb_id
                    LEFT JOIN cities as c on c.id=s.city_id
                    WHERE u.id=?";

            // Run query
            $row = Db::select($sql,[$userId]);

            // If no user found, return null
            if(!$row){
                return null;
            }

            // Build base user object
            $user = User::fromArray($row[0],"u_");

            // (Redundant line: overwritten next line)
            $buyerInfo = BuyerSellerInfo::where("user_id=?",[$user->getId()]);

            // Build buyer/seller info from joined row
            $buyerInfo = BuyerSellerInfo::fromArray($row[0],"b_");

            // Attach buyer/seller info to user object
            $user->setBuyerSellerInfo($buyerInfo);

            return $user;
        }
        
        // Update basic user fields
        public function update(){
            $sqlMain = "UPDATE users SET username=?,email=?,phone=? WHERE id=?";
            
            Db::update($sqlMain,[
                $this->username,
                $this->email,
                $this->phone,
                $this->id
            ]);
        }
        
        // Login validation method
        static function login($email,$password){        

            // Fetch user credentials and banned status
            $sql = 'SELECT users.id as id,users.password as password,users.role as role,b.banned as banned 
                    FROM users 
                    LEFT JOIN buyerSellerInfo as b on b.user_id=users.id  
                    WHERE users.email = ?';

            $row = Db::select($sql,[$email]);
            
            // Default response object
            $user = new stdClass();
            $user->id = -1;
            $user->role = 'user';
            $user->correct = false;
            
            // If user exists
            if($row){

                // Check if banned
                if($row[0]['banned']){
                    return $user;
                }

                $hashedPassword = $row[0]["password"];
                
                // Verify password
                if(!password_verify($password,$hashedPassword)){
                    return $user;
                }

                // Successful login
                $user->id = $row[0]['id'];
                $user->role = $row[0]['role'];
                $user->correct = true;
                
                return $user;
            }
            
            // Return failed login
            return $user;           
        }
        
        // Update banned status in buyer/seller table
        public function updateBanned($banned){
            $sql="UPDATE buyerSellerInfo SET banned=? WHERE user_id=?";
            Db::update($sql,[$banned,$this->id]);
        }
        
        // Update verification status and expiry date
        public function updateVerified($verified){
            $sql="UPDATE buyerSellerInfo SET verified=?,verification_timeOut=CURRENT_DATE()+INTERVAL 365 DAY WHERE user_id=?";
            Db::update($sql,[$verified,$this->id]);
        }
        
        // Set buyer/seller info object
        public function setBuyerSellerInfo($buyerSellerInfo){
            $this->buyerSellerInfo = $buyerSellerInfo;
        }

        // Get buyer/seller info object
        public function getBuyerSellerInfo(){
            return $this->buyerSellerInfo;
        }
        
        // Getter and setter methods

        public function getId() { return $this->id; }
        public function setId($id) { $this->id = $id; }

        public function getUsername() { return $this->username; }
        public function setUsername($username) { $this->username = $username; }

        public function getEmail() { return $this->email; }
        public function setEmail($email) { $this->email = $email; }

        public function getPhone() { return $this->phone; }
        public function setPhone($phone) { $this->phone = $phone; }

        public function getRole() { return $this->role; }
        public function setRole($role) { $this->role = $role; }

        public function getHashPassword() { return $this->hashPassword; }

        // Hash and set password securely
        public function setPassword($rawPassword) {
            $this->hashPassword = password_hash($rawPassword, PASSWORD_DEFAULT);
        }
    }
?>