<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('EDSMclass.php');

$systemName = $_GET['sysName'];

if(!isset($_GET['sysName']) || empty($_GET['sysName'])  ) {
    header("location: index.php");
    exit;
}
// header('Content-Type: application/json; charset=utf-8');
$edsm           = new EDSM( $systemName );

// attempt to get the Region name from Spansh
$regionArray    = $edsm->getRegionName( $systemName );
$regionName     = isset($regionArray->region) ? $regionArray->region : "Undiscovered Region";


// $spanshDump     = $edsm->apiSpansh( $edsm->details->id64 ); 
// $edsm->preWrap( $spanshDump );
// exit;

$carriers       = property_exists($edsm, 'carriers') ? $edsm->carriers : null;
$outposts       = property_exists($edsm, 'outposts') ? $edsm->outposts : null;
$starports      = property_exists($edsm, 'starports') ? $edsm->starports : null; 
$settlements    = property_exists($edsm, 'settlements') ? $edsm->settlements : null;
$bases          = property_exists($edsm, 'bases') ? $edsm->bases : null;
$hotspots       = property_exists($edsm, 'hotspots') ? $edsm->hotspots : null;

$materialLevels = $edsm->materialLevels();

// maximum known levels of materials
$maxMatLevels   = $materialLevels['maxlevels'];

// array of average known levels of materials - use this to compare to material values
// to highlight if greater than average levels are on the body
$avgMatLevels   = $materialLevels['avgLevels'];

?>
<html>
<header>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
    <?php printf('<title>ED System Display : %s %s</title>', $regionName , $systemName ); ?>
     <link rel="stylesheet" href="./css/style.css">
     <!-- font awesome -->
     <link rel="stylesheet" type="text/css" href="//use.fontawesome.com/releases/v5.7.2/css/all.css">
     
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
         <script src="./js/index.js"></script>
    </header>    
    <body>
    

<?php echo renderMenu(); ?>

<div class="formContainer">
    <form action="results.php">
        <div class="form_group">
            <label>Search system name</label>
            <?php printf('<input type="text" name="sysName" value="%s">', $systemName ); ?>
            <span><button type="submit">Engage!</button></span>
            </div>
    </form>
</div><!--formContainer -->

<!-- <div id="jsonContainer">Hello</div> -->

<?php

// No details? Spew a message that we got's nothin from EDSM.
if( !isset( $edsm->details->bodies ) ){
    echo "<div class=\"failContainer\"><div class=\"warning\">EDSM has no records of this system</div><div>Go visit this system, and get your name on it!</div></div>";
    exit;
}


$bodies = $edsm->details->bodies;
$result = $edsm->discoveries( $bodies, $hotspots );

// $edsm->preWrap($result);
// exit;

if( !empty($result) ) {
    
// spew system values

//  $edsm->prewrap( $edsm->spanshAPIData );
//  exit;

   echo $edsm->headerTable();

    // headers
    $headers = (array) $result[0];
    unset($headers['isLandable']);

    echo "<table><tr>";

    foreach( array_keys($headers) as $col ){
        printf("<th>%s</th>", $col);
    }
    
    
    foreach( $result as $row ){
        
        $colstrng   = "";
        
        // landable bodies are green backgrounded
        $c = $row->isLandable == 1 ? "class=\"landable\"" : ''; 
        unset($row->isLandable);
        printf("<tr %s>", $c );
        
        
        foreach($row as $key => $col ){
            
            // data holder
            $data       = $col;
            // class for use with the star element in bodyType
            $class      = "";

            // // Convert DateTime
            if($key == 'updateTime' || $key =='discoveredDt'){
                $dt = date_create($col);
                $col = date_format($dt, 'm/d/Y');
            }
            
            // EDSM url as link
            if($key == 'EDSM'){
                $col = "<a href='$col' target='_blank' class='tooltips edsmlink'><span>EDSM Info</span></a>";
            }

            // ===========================================
            // If our radius is less than 300 km, mark it 
            // as a Space Tater! 
            // ===========================================
            
            if($key == 'radius' && $col){
                
                $rad = intval(str_replace(',','', $col));


                if(  $rad < 300 && $c == "landable" ) {
                    $col = sprintf("%s km <strong>(tater)</strong>", $rad );
                } else{
                    $col = sprintf("%s km", number_format($rad) );
                }
            }

            // materials formatting and comparison 
            if($key == 'materials'){
                if(strlen( $col ) > 0){
                    $mats = explode(",", $col);
                    foreach( $mats as $material ){
                        $colstrng .= $material . "<br/>";
                    }
                    $col = $colstrng;
                }
               
            }
            
            if( $key == 'semiMajorAxis' ){
               $col = $edsm->kmInLs( $col );
            }


            if( $key == 'bodyType' ){

                // Look for Age in string
                $position = strpos($col, "Age");

                // if we find Age, trim it into a seperate string for our tooltip content
                if($position) {
                    $data = substr($col, $position, strlen($col)); // get the Age 
                    $col = substr($col, 0, $position); // strip out the age string
                    $col = "<a href='#' class='tooltips'>" . $col . "<span>". $data . "</span></a>"; // append the 
                    $class = "bodyType";
                }

            }

            // print the td element 
            echo "<td id='". $key."' class='".$class."'>" . $col . "</td>";
       
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // $edsm->preWrap($result);

    
}else{
    echo '<div class="failContainer"><div class="warning">EDSM has no records of this system</div><div>Please go splore and visit this system and claim it for your records!</div>';
}

echo "<div id='carriersData' data-carriers='" . json_encode($carriers) . "'></div>";
echo "<div id='starportsData' data-starports='" . json_encode($starports) . "'></div>";
echo "<div id='outpostsData' data-outposts='" . json_encode($outposts) . "'></div>";
echo "<div id=\"basesData\" data-bases='" . json_encode($bases) . "'></div>"; 
echo "<div id='settlementsData' data-settlements='" . json_encode( $settlements ) . "'></div>";


?> 
</body></html>
