<?php
	//used to create connection, handle errors and sql query execution
	class Db{
        //set on construct and removed on deconstruct
        //this is to ensure the connection to db gets closed safely in try catch blocks.
		static private $conn;
    
		function __construct(){
            //default connection
			$host = $_ENV['dbHost'];
			$dbname = $_ENV['dbName'];
			$username = $_ENV['dbUsername'];
			$password = $_ENV['dbPassword'];
			Db::$conn = new PDO("mysql:host=$host;dbname=$dbname", $username,$password);
			Db::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            //ensures only utf8 characters can be used,constraints are applied
            //and division rules are enforced
			Db::$conn->exec("
			SET SESSION sql_mode =
			'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';SET NAMES utf8mb4;
			");
		}
    
        //alias to construct, hiding away how the db and conn actually works
    	static public function connect(){
            return new Db();
        }
    
    	static public function getConnection(){
            return self::$conn;
        }
    
    	static public function disconnect(){
            Db::$conn = null;
        }
    	
    	static public function usingDbConnection(callable $fn){
            try{
                Db::connect();
                return $fn();
            }
            finally{
                Db::disconnect();
            }
        }
    
    	static public function usingTransactionDbConnection(callable $fn){
            try{
                Db::connect();
                //Db::$conn->beginTransaction();
                $result = $fn();
                //Db::$conn->commit();
                return $result;
            }
            catch(Throwable $e){
                //Db::$conn->rollBack();
                throw $e;
            }
            finally{
                Db::disconnect();
            }
        }
    

 
   		//used to handle errors thrown by PDOException
    	static public function handleException($e){
            //get the state and driver code
            $sqlState = $e->errorInfo[0] ?? $e->getCode();
            $driverCode = $e->errorInfo[1] ?? null;
          	
            //check if connection error
            if(str_starts_with($sqlState,'08')){
                echo getJsonFailure("Could not connect to the db");
            }
            
            //check if constraint error
            if($sqlState === '23000'){
                //loop through the entire constraint map
                foreach (ConstraintMap::MAP as $constraint => $info) {
                    //if constraint name in string 
    				if (strpos($e->errorInfo[2], $constraint) !== false) {
                        $fieldsErrors = new FieldsErrors();
                        $fieldsErrors->addError($info['field'],$info['message']);
        				//return field error + constraint
                        echo getJsonWithFieldErrors('Db constraints violated',$fieldsErrors->getErrors());
        				exit;
    				}
				}
            }
            
            echo getJsonFailure("Something went wrong with the db" . $e);
        }
		
        //used as alias to always returns the records
		static function select($query,$params = []){
			$stmt = static::$conn->prepare($query);
			$stmt->execute($params);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
    	
        //used to return the last inserted id
    	static function lastInsertId(){
            return static::$conn->lastInsertId();
        }
    	
        //used as alias to make calling and management easier
    	static function insert($sql,$params){
            $stmt = static::$conn->prepare($sql);
			$stmt->execute($params);
        }
        
        //used as alias to make calling and management easier
    	static function update($sql,$params){
            $stmt = static::$conn->prepare($sql);
			$stmt->execute($params);
        }
    
    	static function delete($sql,$params){
            $stmt = static::$conn->prepare($sql);
			$stmt->execute($params);
        }
    	
        //function to easily log authentication
		static public function logAuth($userId,$role,$action){
            try{
            	$sql = "INSERT INTO authLog (user_id,role,action) VALUES (?,?,?)";
				static::insert($sql,[$userId,$role,$action]);    
            }
            catch(Exception $e){
                // silently fail
            }
           
		}
    
    	
        
        //function to easily log important actions
		static public function logUserAction($userId,$role,$action,$targetType,$targetId){
            try{
                $sql = "INSERT INTO userLogs (user_id,role,action,target_type,target_id) VALUES (?,?,?,?,?)";
				static::insert($sql,[$userId,$role,$action,$targetType,$targetId]);
            }
            catch (Exception $e){
                // silently fail
            }
		}
	}
?>