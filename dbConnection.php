<?php
class dbConnection {

//    private $user = '';
//    private $password = '';
//    private $host = '';
//    private $databse = '';
    private $conn;

    public function __construct($user, $password, $host, $database) {
        try{
            // create a PostgreSQL database connection
            $this->conn = new PDO("pgsql:host=$host;dbname=$database;user=$user;password=$password");
            // allow PDO to throw exceptions
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // display a message if connected to the PostgreSQL database successfully
            if($this->conn){
                fwrite(STDOUT, "Connect to database successfully!\n");
            }

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

//            return $this->conn;
        }catch (PDOException $err){
            // report error message
            $message = "Error: Fail to connect to database, because: \n".$err->getMessage()."\n";
            die($message);
        }
    }

    public function connection(){
        return $this->conn;
    }

    function __destruct(){
        $this->conn = null;
        fwrite(STDOUT, "Connection close\n");
    }

}