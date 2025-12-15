<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

if( isset($_GET) && isset($_GET['action']) && $_GET['action']== "fetchCarrier" && isset($_GET['marketID'])){
    $marketID = $_GET['marketID'];
    $url        = sprintf("https://spansh.co.uk/api/search?q=%s" , $marketID );
    $json       = @file_get_contents($url);
    
    if($json === FALSE ){
        $json = json_encode("Error - file_get_contents failed");
    }
        
    echo $json;
}



