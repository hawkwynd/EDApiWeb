<?php 

require './include/config.php';

$mysqli = new mysqli( $mysql_host, $mysql_user, $mysql_pwd, $mysql_database );
$mysqli->query("SET time_zone = 'America/Chicago'");

 if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
  }


  