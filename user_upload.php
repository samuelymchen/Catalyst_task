<?php
// We assume the csv file is called users.csv, unless being specified
$file_name = 'users.csv';
$dry_run = false;
$create_mode = false;

if ($argc > 1) {
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
            $create_mode = true;
        } else if ($argv[$i] === '--dry_run') {
            $dry_run = true;
        } else if ($argv[$i] === '--help') {

        } else {
            // Check if -u, -p, -h is supplied
            $get_parameter = substr($argv[$i], 0, 2);
            if ($get_parameter === '-u' || $get_parameter === '-p' || $get_parameter === '-h') {
                $parameter_val = substr($argv[$i], 3);

                if ($parameter_val !== '' && strlen($parameter_val) !== 0) {
                    switch ($get_parameter) {
                        case '-u':
                            $user = $parameter_val;
                            break;
                        case '-p':
                            $password = $parameter_val;
                            break;
                        case '-h':
                            $host = $parameter_val;
                            break;
                    }
                } else {
                    $message = "Error: Database user, password or host are not supplied correctly, please run $ php user_upload.php --help for more information\n";
                    die($message);
                }

            } else {
                // If other parameters are given, exit and show the help page
                $message = "Error: '".$argv[$i]."' is not a valid parameter, please run $ php user_upload.php --help for more information\n";
                die($message);
            }
        }
    }

    if (!isset($user)) {
        $message = "Error: User is not supplied\n";
        die($message);
    }

    if (!isset($password)) {
        $message = "Error: Password is not supplied\n";
        die($message);
    }

    if (!isset($host)) {
        $message = "Error: Host is not supplied\n";
        die($message);
    }

    $conn = connect_db($user, $password, $host);

//    $conn = connect_db('samuel', 'testtest', '127.0.0.1');

    // If --create_table is presented, create table only and no more action
    if ($create_mode) {
        create_table($conn);
        exit();
    }

    // If nothing goes wrong, read csv file and store
    read_csv($file_name, $conn, $dry_run);
} else {
    $message = "Error: No parameter is presented, please run $ php user_upload.php --help for more information\n";
    die($message);
}

function connect_db($user, $password, $host) {
    try{
        // create a PostgreSQL database connection
        $conn = new PDO("pgsql:host=$host;dbname=mydatabasename;user=$user;password=$password");
        // allow PDO to throw exceptions
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // display a message if connected to the PostgreSQL database successfully
        if($conn){
            fwrite(STDOUT, "Connect to database successfully!\n");
        }

        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $conn;
    }catch (PDOException $err){
        // report error message
        $message = "Error: Fail to connect to database, beacuse: \n".$err->getMessage()."\n";
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

// Read csv file (ignore first line) and store into db one by one
function read_csv($file_name, $conn, $dry_run) {
    // Check if file is a valid .csv file and exists in current directory
    try {
        if (substr($file_name, -4) !== '.csv') {
            throw new Exception("Error: File is not a .csv file\n");
        }

        if (!file_exists($file_name)) {
            throw new Exception("Error: File not found\n");
        }

        $file = fopen($file_name, 'r');

        // Skip the first line
        fgets($file);

    } catch (Exception $err) {
        die($err->getMessage()."\n");
    }

    // Iterate through the csv file row by row
    for ($i = 0; $row = fgetcsv($file); ++$i) {
        fwrite(STDOUT, "============================================\n");
        if ($row !== array(null)) {
            // format data
            $user = format_data($row);

            if ($user[0] === '') {
                // First name is empty
                $message = "Error: On row ". ($i+2) ." name cannot be empty"."\n";
                fwrite(STDOUT, $message);
                continue;
            }

            if ($user[1] === '') {
                // Surname is empty
                $message = "Error: On row ". ($i+2) ." surname cannot be empty"."\n";
                fwrite(STDOUT, $message);
                continue;
            }

            if ($user[1] === '') {
                // Email is empty
                $message = "Error: On row ". ($i+2) ." email cannot be empty"."\n";
                fwrite(STDOUT, $message);
                continue;
            }

            // Check if email is valid
            if (filter_var($user[2], FILTER_VALIDATE_EMAIL)) {

                if ($dry_run) {
                    $message = 'Found user '.$user[0].' '.$user[1]." (".$user[2].")\n";
                    fwrite(STDOUT, $message);
                } else {
                    // Insert into db
                    $insert_query = 'INSERT INTO "user" (name, surname, email) VALUES (:name, :surname, :email)';

                    try {
                        $stmt = $conn->prepare($insert_query);

                        $stmt->bindValue(':name', $user[0]);
                        $stmt->bindValue(':surname', $user[1]);
                        $stmt->bindValue(':email', $user[2]);

                        $result = $stmt->execute();
                    } catch(Exception $err) {
                        $message = 'Error: Unable to inset user '.$user[0].' '.$user[1]."(".$user[2]."), because:\n".$err->getMessage()."\n";
                        fwrite(STDOUT, $message);
                        continue;
                    }

                    $message = 'User '.$user[0].' '.$user[1]."(".$user[2].") is inserted into database successfully\n";
                    fwrite(STDOUT, $message);
                }

            } else {
                // Email is invalid
                $message = "Error: On row ". ($i+2) ." Invalid email address: ".$row[2].", user not inserted\n";
                fwrite(STDOUT, $message);
                continue;
            }
        }
    }

    fclose($file);
}

function format_data($user) {
    // Remove unexpected spaces/tabs from the beginning and end of name string
    $user[0] = trim($user[0], " \t\n\r\0\x0B");
    $user[1] = trim($user[1], " \t\n\r\0\x0B");
    // Remove all whitespaces from email
    $user[2] = str_replace(' ', '', $user[2]);

    // Capitalise name and surname
    $user[0] = ucwords(strtolower($user[0]));
    $user[1] = ucwords(strtolower($user[1]));

    // Make email address to lower case
    $user[2] = strtolower($user[2]);

    return $user;
}