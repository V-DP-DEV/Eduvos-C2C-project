<?php 
    // Load bootstrap (DB connection, helpers, config, etc.)
    require_once __DIR__ ."/../bootstrap.php";

    // Base abstract Model class for all database models
    abstract class Model{

        // Table name defined in child classes
        protected static $table;
        
        // Find a single record by ID
        static function find($id){

            // Run base query wrapped as subquery, filter by id
            $item = Db::select(
                'SELECT * FROM ' . static::wrap(static::baseQuery()) . '  WHERE id = ?',
                [$id]
            );

            // Return mapped object if found
            if($item){
                return static::fromArray($item[0],'');
            }

            return null;
        }

        // Generic WHERE query
        static function where($condition,$parms){

            // Execute dynamic condition query
            $rows = Db::select(
                'SELECT * FROM ' . static::wrap(static::baseQuery()) . '  WHERE ' . $condition,
                $parms
            );

            // Map results to objects
            return static::mapRows($rows,'');
        }
        
        // Validate if ID exists in table
        static function validId($id){

            // Query record by id
            $rows = Db::select(
                'SELECT * FROM ' . static::wrap(static::baseQuery()) . ' WHERE id=?',
                [$id]
            );

            // Return true if record exists
            if($rows){
                return true;
            }

            return false;
        }

        // Fetch all records
        static function all(){

            // Execute base query
            $rows = Db::select(static::baseQuery(),[]);

            // Convert rows to objects
            return static::mapRows($rows,'');
        }
        
        // Delete record by ID
        static function delete($id){

            // Build delete query using table name
            $sql = "DELETE FROM ".static::$table ." WHERE id=?";

            // Execute delete
            Db::delete($sql,[$id]);
        }

        // Each model must define how to map DB row to object
        abstract protected static function fromArray(array $row,$prefix);

        // Convert multiple rows into object array
        protected static function mapRows(array $rows,$prefix){

            $objects =[];

            // Loop through rows and map each one
            foreach($rows as $row){
                $objects[] = static::fromArray($row,$prefix);
            }

            return $objects;
        }
        
        // Wrap subquery for safe SQL usage
        protected static function wrap($query) {
            return "(" . $query . ") AS t";
        }
        
        // Default base query (can be overridden in child classes)
        public static function baseQuery(){
            return 'SELECT * FROM ' . static::$table;
        }
        
        // Check if row has prefixed data (used for joins)
        public static function hasPrefixedData(array $row, string $prefix): bool {

            // Scan all keys for prefixed columns
            foreach ($row as $key => $value) {
                if (str_starts_with($key, $prefix) && $value !== null) {
                    return true;
                }       
            }

            return false;
        }
        
        // Apply ORDER BY clause safely using whitelist mapping
        public static function applySorting($sql, $orderBy,$orderMap,$default, $dir = 'ASC') {

            // Map requested column or fallback
            $column = $orderMap[$orderBy] ?? $default;

            // Normalize direction
            $dir = strtoupper($dir);

            // Validate direction input
            if (!in_array($dir, ['ASC', 'DESC'])) {
                $dir = 'ASC';
            }

            return $sql . " ORDER BY $column $dir";
        }
        
        // Apply filters dynamically with parameter binding
        public static function applyFilters($sql, &$params, $filters,$map) {

            // Loop through filter inputs
            foreach ($filters as $key => $value) {

                // Allow 0 values, ignore empty
                if (!empty($value) || $value === 0 || $value === '0') {

                    // Check if filter exists in map
                    if (isset($map[$key])) {

                        // Get SQL clause + parameter
                        [$clause, $p] = $map[$key]($value);

                        // Append condition
                        $sql .= " AND " . $clause;

                        // Add parameter safely
                        $params[] = $p;
                    }
                }
            }

            return $sql; 
        }
    }
?>