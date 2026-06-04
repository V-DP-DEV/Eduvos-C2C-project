<?php
    //loads environment variables from file
function loadEnv($path) {
    //check if file exists
    if (!file_exists($path)) return;
	
    //converts to lines
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    //for each line 
    foreach ($lines as $line) {
        //trim each line
        $line = trim($line);
        //cif not emppty
        if ($line === '') continue;
		//map key and value by seperating according to = 
        [$key, $value] = explode('=', $line, 2);
		//load into env global fields
        $_ENV[trim($key)] = trim($value);
    }
}
?>