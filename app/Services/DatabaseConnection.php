<?php

namespace App\Services;

use MongoDB\Client;

class DatabaseConnection {

    protected $database;
    protected $connection;

    /**
     * constructor to create connection and Database .
     *
     * @return connection and database name
     */

    public function __construct() {
        $connection_string="mongodb://localhost:27017/?readPreference=primary&appname=MongoDB%20Compass&directConnection=true&ssl=false";
        $this->connection= new Client($connection_string);
        $this->database= $this->connection->PeopleAttach;
    }

    /**
     * Geter for Connection.
     *
     * @return database connection
     */


    public static function getConnection(){
        return $this->connection;
    }


    /**
     * Seter for Database.
     *
     * @return Database
     * ? do not use other value
     */

    public  function getDatabase(){
        return $this->database;
    }
}
