<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require('species.php');

$url        = sprintf("https://spansh.co.uk/api/search?q=%s" , urlencode( "BD+56 581" ) );
$json       = @file_get_contents($url);
$data       = json_decode($json, false)       ;
$ringData   = array();
$payload    = array();

$results    = $data->results;

echo $json;
exit;

foreach( $results as $result ){
    
    echo "Type: " . $result->type. "<br/>";
    echo $result->type == "system" ? "System: " . $result->record->name . "</br/>" : '';
    
    echo "Updated: " . $result->record->updated_at . "<br/>";

    if( $result->type == 'system') {
        if( property_exists($result->record, 'bodies')){
            echo "<pre>", print_r($result->record->bodies), "</pre>";

        }
    }


}

exit;















$bodies = $data->results[0]->record->bodies;

foreach( $bodies as $body ){
    if( property_exists($body, 'landmarks') ){
        foreach($body->landmarks as $landmark){
            if( in_array($landmark->subtype, $species) ){
                echo "<div>", $body->name, ": ",  $landmark->subtype, " - ", $landmark->count, " Val: ", $landmark->value, "</div>";
            }
        }
    }
}

// unset($data->results[0]);

// foreach( $data->results as $record ){
//     // echo "<pre>", print_r($record), "</pre>";
//     if( $record->type == 'body'){
//         foreach( $record as $row ){
//             if(property_exists($row, 'rings') ){
//                 foreach($row->rings as $ring){
//                     // echo "<pre>",print_r($row), "</pre>";
//                     if( property_exists($ring, 'signals')){
//                         $ringData[$idx]['body_name']         = $row->name;
//                         $ringData[$idx]['ring_name']         = $ring->name;
//                         $ringData[$idx]['ring_type']         = $ring->type;
//                         $ringData[$idx]['reserve_level']     = $row->reserve_level;
//                         $ringData[$idx]['hotspot_count']      = $ring->signal_count;
                        
//                         foreach($ring->signals as $signal)  {
//                             $ringData[$idx]['hotspots'][] = ['name' => $signal->name, 'count' => $signal->count];
//                         }
            
//                     }
//                     $idx++;

//                 }
//             }

//         }
//     }

// }

// echo "<pre>", print_r( $ringData ), "</pre>";
// echo json_encode($ringData); 
