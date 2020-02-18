<?php
// We assume the csv file is called users.csv, unless being specified
$file_name = 'users.csv';
$dry_run = false;

if ($argc > 1) {
    $conn = connect_db('samuel', 'testtest', '127.0.0.1');
    // Start from the second index
    for ($i = 1; $i < $argc; $i++) {
        // Skip current iteration if it is a file name which is surrounded by []
        if (substr($argv[$i], 0, 1) === '[' && substr($argv[$i], -1) === ']') {
            continue;
        }
        if ($argv[$i] === '--file') {
            // If --file is presented, a [file name] must be presented right after
            if (array_key_exists($i+1, $argv)) {
                if (substr($argv[$i+1], 0, 1) === '[' && substr($argv[$i+1], -1) === ']') {
                    // extract file name from string
                    $file_name = substr($argv[$i+1], 1, -1);
                    echo '================'.$file_name.'================'."\n";
                } else {
                    $message = "Error: File name is not supplied, please include file name after --file (e.g. --file [users.csv])\n";
                    die($message);
                }
            } else {
                $message = "Error: File name is not supplied, please include file name after --file (e.g. --file [users.csv])\n";
                die($message);
            }
        } else if ($argv[$i] === '--create_table') {
            create_table($conn);
            exit();
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

function create_table($conn) {
    // Drop table if exist
    try {
        $drop_script = 'DROP TABLE IF EXISTS "user"';
        $conn->exec($drop_script);
    } catch (Exception $err) {
        $message = "Error: Error dropping existing user table, because:\n".$err->getMessage()."\n";
        die($message);
    }
    fwrite(STDOUT, "Drop user table successfully\n");

    // Create user table
    try {
        $create_script = 'CREATE TABLE "user" (
            id SERIAL PRIMARY KEY,
            name TEXT NOT NULL,
            surname TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL
        )';

        $conn->exec($create_script);
    } catch (Exception $err) {
        $message = "Error: Error creating user table, because:\n".$err->getMessage()."\n";
        die($message);
    }

    fwrite(STDOUT, "Create user table successfully\n");
}