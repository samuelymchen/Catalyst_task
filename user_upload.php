<?php
// We assume the csv file is called users.csv, unless being specified
$file_name = 'users.csv';
$dry_run = false;

if ($argc > 1) {
    $conn = connect_db('samuel', 'testtest', '127.0.0.1');
    // Start from the second index
    for ($i = 1; $i < $argc; $i++) {

        if ($argv[$i] === '--file') {
            echo 'file';
        } else if ($argv[$i] === '--create_table') {
            echo 'create_table';
        } else if ($argv[$i] === '--dry_run') {
            $dry_run = true;
            echo 'dry run';
        } else if ($argv[$i] === '--help') {
            echo 'help';
        } else {
            // Check if -u, -p, -h is supplied
        }
    }
} else {
    $message = "Error: No parameter is presented, please run $ php user_upload.php --help for more information\n";
    die($message);
}

function connect_db($user, $password, $host) {
    try{
        // create a PostgreSQL database connection
        $conn = new PDO("pgsql:host=$host;dbname=mydatabasename;user=$user;password=$password");

        // display a message if connected to the PostgreSQL successfully
        if($conn){
            echo "Connect to database successfully!\n";
        }
        // allow PDO to throw exceptions
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $conn;
    }catch (PDOException $err){
        // report error message
        $message = "Error: Cannot connect to database, because: \n".$err->getMessage()."\n";
        die($message);
    }
}