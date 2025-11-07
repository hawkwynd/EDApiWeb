<?php 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



class EDJournal{

    public function __construct(){
        $this->mysqli = new mysqli('localhost', 'hawkwynd', 'Sc00tre1');
        $this->mysqli->query("SET time_zone = 'America/Chicago'");

        // Check connection
        if ($this->mysqli->connect_error) {
            die("Connection failed: " . $this->mysqli->connect_error);
        }
    }

        public function preWrap($s)
        {
            echo "<pre>", print_r($s), "</pre>";
        }
}