<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class EDSM{

    public $systemName;
    public $details;
    public $scanValues;

    // build edsm object
    public function __construct( $systemName ){

        include_once('species.php'); // load up the species array
        include_once('db.php'); // mYSQLi connection sequances
        
        $this->region = null; 
        $this->conn = $mysqli;
        $this->spanshAPIData = null;

        // configure for wanted materials
        $this->wantedMats       = ['Cadmium', 'Niobium','Yttrium','Tungsten','Polonium','Ruthenium','Tellurium','Technetium','Antimony','Nickel', 'Iron'];
        $this->terraformOptions = ['Candidate for terraforming' => '<a class="tooltips">(T)<span>Terraforming</span></a>', 'Not terraformable' => '', 'Terraformed' => '<a class="tooltips">(T)<span>Terraformed</span></a>','Terraforming' => '<a class="tooltips">(T)<span>Terraforming</span></a>' ];
        $this->maxHeliumLevel   = 28.2;
        $this->systemName       = $systemName;
        $this->species          = $species;
        $this->geologicals      = $geologicals;
        $this->fetchSystemData();
        $this->scanValues       = $this->fetchScanValues();
        $this->valuableBodies   = property_exists( $this->scanValues, 'valuableBodies') ? $this->scanValues->valuableBodies : false;
        $this->materialLevels   = $this->materialLevels();
        $this->landableTaters   = $this->sysTaters() ? count( $this->sysTaters()) : 0;
        
        $this->fetchSpanshData();


        // $this->preWrap( $this->carriers );

        // $this->signalCheck();
        // exit;
    }

    public function kmInLs( $km ){

        $kmInLS = 299800; // how many kilometers per light second
        $orbitalDistance = number_format( ($km / $kmInLS), 2);

        return $km;

    }

    public function sysTaters($tcount = 0, $aTaters = array() ){
        $system = property_exists( $this->details, 'bodies') ? $this->details->bodies : false;
        if($system) {
                foreach( $system as $body ){
                    if( $body->type == "Planet" && $body->isLandable && $body->radius < 300 ){
                        array_push( $aTaters, $body );
                        }
                    }
                    return $aTaters;
            }
        }

    
    public function materialLevels(){

        $maxLevels = [
            'helium'   => 29.9,
            'antimony' =>1.82,
            'arsenic' => 3.03,
            'cadmium' => 3.77,
            'carbon' => 33.91,
            'chromium' => 18.7,
            'germanium' => 6.54,
            'iron' => 48.85,
            'manganese' => 17.36,
            'mercury' => 2.13,
            'molybdenum' => 3.18,
            'nickel' => 36.94,
            'niobium' => 3.32,
            'phosphorus' => 21.71,
            'polonium' => 2.09,
            'ruthenium' => 2.97,
            'selenium' => 6.15,
            'sulphur' => 40.33,
            'technetium' => 1.74,
            'tellurium' => 1.75,
            'tin'       => 3.25,
            'tungsten' => 2.68,
            'vanadium' => 16.98,
            'yttrium' => 2.87,
            'zinc' => 12.29,
            'zirconium' => 5.58
        ];

        $avgLevels = [
            'antimony' => 0.91,
            'arsenic' => 1.97,
            'cadmium' => 1.33,
            'carbon' => 18.47,
            'chromium' => 7.73,
            'germanium' => 4.29,
            'iron' => 17.17,
            'manganese' => 7.12,
            'mercury' => 0.75,
            'molybdenum' => 1.12,
            'nickel' => 12.99,
            'niobium' => 1.17,
            'phosphorus' => 11.83,
            'polonium' => 0.48,
            'ruthenium' => 1.07,
            'selenium' => 3.69,
            'sulphur' => 21.97,
            'technetium' => 0.62,
            'tellurium' => 1.1,
            'tin' => 1.07,
            'tungsten' => 1.5,
            'vanadium' => 4.33,
            'yttrium' => 1.04,
            'zinc' => 4.77,
            'zirconium' => 2.1
        ];

        return ['maxlevels' => $maxLevels, 'avgLevels' => $avgLevels];

    }


    public function jsonWantedMats(){
        return json_encode( $this->wantedMats );
    }

    // get ringed bodies, not just GGs
    public function fetchRingedBodies($rb = 0 ){
        $system = $this->details->bodies;
        foreach( $system as $body ){
            if(property_exists($body, 'rings')){
                $rb ++;
            }
        }
        return $rb;
    }



    // get Gas Giants count in a system
    public function fetchGGs($gg = 0){
        $system = $this->details->bodies;
        foreach( $system as $body ){
            if($body->type == 'Planet'){
                if( strpos( $body->subType, 'giant') > 0 ){
                    $gg ++;
                }
            }
        }
        return $gg; 
    }

    // Check the bodies for a ring element, and return the count of the rings array
    // This checks all bodies with rings, regardless if it's a planet or a star

    public function ringsCount($rbs = 0){
        $system = $this->details->bodies;
        foreach( $system as $body ){
                if(property_exists($body, 'rings')){
                    $rbs += count($body->rings);
                }
            }
        return $rbs; 
    }

    // count terraformable bodies
    public function fetchTerraformables( $tt = 0){
        $system = $this->details->bodies;
        foreach( $system as $body ){
            if($body->type == 'Planet'){
                
                if( $body->terraformingState && $this->terraformOptions[$body->terraformingState] == $body->terraformingState ){
                    $tt ++;
                }
                
            }
        }
        return $tt; 
    }

    // retrieve count of landable bodies
    public function fetchLandables($landables = 0){

        if (property_exists( $this->details, 'bodies')) {
            $system = $this->details->bodies;

        foreach( $system as $body ){
            if($body->type == "Planet"){
                $landables += $body->isLandable;
            }
        }

        return $landables;
    }

    }

    // Parents Check and report
    // [materials] => Iron:20.47, Sulphur:20.06, Carbon:16.87, Nickel:15.48, Phosphorus:10.8, Zinc:5.56, Vanadium:5.03, Zirconium:2.38, Cadmium:1.59, Tin:1.22, Polonium:0.53

    public function materialsCheck( $mats, $list=array() ){

        foreach($mats as $key => $value ){
            
            // filter only wanted materials
            if(in_array($key, $this->wantedMats)){
                $list[$key] = $value;
            }
        }

        // array_walk the list, and convert to comma delimited string key:value
        // compare to $avgMatLevels  
        array_walk($list, 
        function(&$value, $key) {
            
            $value =  floatval( $this->materialLevels['avgLevels'][ strtolower($key) ]) <  floatval($value) ?  "<b>{$key}</b>:{$value}" : "{$key}:{$value}";
        
        });
        
        // convert array to string comma delimted
        $stringOut = implode(",", $list);

        return $stringOut;

    }
   
    
    /**
     * Array
(
    [0] => Array
        (
            [id64] => 2089679695220778425
            [body_name] => Shapsugabus 11 c
            [landmark] => stdClass Object
                (
                    [count] => 6
                    [subtype] => Fonticulua Campestris
                    [type] => Fonticulua
                    [value] => 1000000
                )

        )

    [1] => Array
        (
            [id64] => 2089679695220778425
            [body_name] => Shapsugabus 11 c
            [landmark] => stdClass Object
                (
                    [count] => 3
                    [subtype] => Bacterium Vesicula
                    [type] => Bacterium
                    [value] => 1000000
                )

        )

)
*/

    public function landmarkCheck( $body ){
        
        $output     = null;
        $landmarks  = property_exists($this, 'landmarks') ? $this->landmarks : null;
        $class      = "class=\"landmark\"";

        if($landmarks)
        {
            foreach($landmarks as $row){
                    if( $body == $row['body_name'] ){
                        $output .= sprintf("<div %s>%s : %d</div>", $class, $row['landmark']->subtype, $row['landmark']->count );
                    }                
            }
        }
        return $output;

    }


    public function geologicalCheck( $body ) 
    {

        // $this->preWrap( $body );
        $output = null;
        $geologicals = property_exists( $this, 'landmarks') ? $this->geologicals : null;
        $class      = "class=\"landmark\"";

        if( $geologicals ){
            foreach( $geologicals as $row ){
                if($body == $row['body_name']) {
                    $output .= sprintf("<div %s>%s : %d</div>", $class, $row['geological']->subtype, $row['geological']->count );
                }
            }
        }

        return $output;
    }

   
    // ================================================================================
    // rings check and report
    // Here, we want to send ring info to our database for collecting and cataloging. 
    // ================================================================================

    public function ringCheck( $body, $types=array(), $ringCount = 0 ){

        $types = [];

        if( property_exists( $body, 'rings') && is_array( $body->rings ) ){

            // $body->systemDB_id  = $this->systemDB_id; 
            $body->radius       = property_exists($body, 'radius') ? $body->radius : $body->solarRadius; 
            $body->reserveLevel = (property_exists($body, 'reserveLevel') && !empty($body->reserveLevel) ) ? $body->reserveLevel : "Pristine";

           // Insert our body into the body table, update on duplicates
            // $stmt = mysqli_prepare( $this->conn, "INSERT INTO body (system_id, id64, bodyName, radius, type, subtype, reserveLevel) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE bodyName=?, id=LAST_INSERT_ID(id);" );
            
            // if ( !$stmt ) {
            //     die('mysqli error: '.mysqli_error($this->conn));
            // }

            // mysqli_stmt_bind_param( $stmt, 'ddsdssss', $body->systemDB_id, $body->id64, $body->name, $body->radius, $body->type, $body->subType, $body->reserveLevel, $body->name ); 

            // if ( !mysqli_execute($stmt) ) {
            //     die( 'stmt error 273: '. mysqli_stmt_error($stmt) . $this->preWrap($body) );
            //   }
            
            // Get the body id and use it for reference in the rings table
            // $body->bodyId = $this->conn->insert_id;

            $ringData = $NewRingData = $ringRadius = "";
            
            // reserveLevels
            if( property_exists($body, 'reserveLevel') && !empty($body->reserveLevel)) $NewRingData .=  sprintf('<div class="%s">%s reserves</div>', $body->reserveLevel, $body->reserveLevel );
            
            $ringIdx = 0;

            foreach( $body->rings as $ringIdx => $ring){
                
                // set bodyId of this ring
                $ring->bodyId  = $body->bodyId; 

                // (F63/((3.14159265358979*(outerRadius)^2)-(3.14159265358979*(innerRadius)^2))) -- crazy calculation from Lovecraft, with help from CMDR Dharok Titan
                $ring->visibleValue = ( $ring->mass / ( 3.141592 * ( pow($ring->outerRadius, 2) )  - ( 3.141592 * ( pow($ring->innerRadius, 2) ) )));
                
                // A Ring inner: 140,030 km
                $ringRadID =  substr($ring->name, -6)[0];
                $rID = $ringRadID . str_replace(' ', '', $ring->name);
                
                $ringRadius .= "<div class='massContainer'><div class='ringMass'>Ring ". $ringRadID . " (" . $ring->type . ") mass: <span>" . $ring->mass . "</span></div>";
                $ringRadius .= "<div class='iRadius'>Inner radius: <span>" . number_format($ring->innerRadius,0,'.',',') . " km</span></div><div class='oRadius'>Outer radius: <span>" . number_format($ring->outerRadius,0,'.',',') ." km</span></div>";
                $ringRadius .= "<div class='massMath'>Mass math: <span>". $ring->visibleValue . "</span> "; 
                $ringRadius .= "<a class='tooltips massmathCopy' id=".$rID." data-visualValue='". $ring->visibleValue."'><span>Copy To clipboard</span></a></div></div>";
                
                
                if( property_exists($ring, 'signals') ){
                    $NewRingData .= "<div id='hotspots'>";
                    $NewRingData .= "<div class='signals-heading'><b>Ring ". $ringRadID . " signals (" . count($ring->signals). ")</b></div>";
                    
                    foreach( $ring->signals as $signal ){
                        $NewRingData .= "<div class='hotspot'>" . $signal['name'] . ":" . $signal['count'] . "</div>";
                    }

                    $NewRingData .= "</div>";
                }


            }
            
            $NewRingData .= $ringRadius;

            // Count our types, create commma delimited string of types
            $typeCount   = count($types);
            $ringData = $typeCount  > 1 ?  "<b>$typeCount rings</b>: " . implode(", ", $types) . "<br/>" : " <b>$typeCount ring</b>: " . implode(", ", $types) . "<br/>";
           
            // if reserveLevel value exists, append to rings string on newline
            return $NewRingData;


        }

    }


    public function signalCheck()
    {

        return;
       
    }



    /**
     * Fetch API call from EDSM with GET
     * @param systemName
     * @return array of objects
     */

    public function fetchSystemData()
    {
        $url  = "https://www.edsm.net/api-system-v1/bodies?systemName=" . urlencode( $this->systemName );
        $contents = file_get_contents($url);

        if( $contents != '' ){

            $data = json_decode($contents, false );
            $updateTime = date('Y-m-d h:i:s');

            // update systems table with system info or insert a new record if no duplicate
            $bodyCount  = !isset( $data->bodyCount ) ? 0 : $data->bodyCount;
            if($bodyCount > 0){

            $stmt       = mysqli_prepare( $this->conn, "INSERT INTO system (id64, name, url, bodyCount) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE id64=?, name=?;");
                          mysqli_stmt_bind_param( $stmt, 'dssdds', $data->id64, $data->name, $data->url, $bodyCount, $data->id64, $data->name ); 
            $this->systemDB_id          = $this->conn->insert_id; 
            
            // if we get id64 assign a Spansh url
            $this->jsonSpanshDumpUrl    = isset($data->id64) ? sprintf("https://spansh.co.uk/api/dump/%d", $data->id64): null;
            
            // grab the API dump from Spansh
            $spanshAPIResult = $this->apiSpansh($data->id64);

            if( $spanshAPIResult ){
                $this->spanshAPIData = $spanshAPIResult;
            }


             if (!$stmt->execute()) {
                echo "Error executing statement: " . $stmt->error;
                }
            }
            // // Capture the system db id for later use in our work
            $this->details              = $data;
            $this->jsonEDSMUrl          = $url;

        }
        

    }

    public function fetchScanValues()
    {
        
        $url = "https://www.edsm.net/api-system-v1/estimated-value?systemName=". urlencode($this->systemName);
        $data = json_decode( @file_get_contents($url), false );

        return $data;

    }

    // retrieve Spansh from api via id64 
    public function apiSpansh($id64){
        $url        = sprintf("https://spansh.co.uk/api/dump/%d", $id64);
        $data       = @file_get_contents($url);
        
        if( $data ){
            file_put_contents('./json/apiSpansh.json', $data );
            $out = json_decode($data, true);
            return $out;
        }
    }


    // Try to obtain the region name from Spansh api
    public function getRegionName( $systemName, $arr = array() )
    {
        $url        = sprintf("https://spansh.co.uk/api/search?q=%s" , urlencode( $systemName) );
        $contents   = file_get_contents($url);
        $data       = json_decode( $contents, false );

        // $this->preWrap( $this->spanshAPIData );
        
        // check if Spansh knows this system, because undiscovered systems are empty results.
        if(!$data->results) return (object) array("region" => "Undiscovered");
        
        // iterate through results gather type->system arrays
        foreach( $data->results as $rec ){
            if( $rec->type == 'system'){
                array_push($arr, array("id64" => $rec->record->id64, "name" => $rec->record->name, "region" => $rec->record->region ));
            }
        }
        /**
         * If we didn't get a region from $data->results, 
         * return our object undiscovered. 
         * Else, return our object with region data. 
         */

        if( !$arr ) {
            

            return  (object) array("region" => "Undiscovered");
        }else{
            return (object) $arr[0];
        }
    }


    // retrieve region name based on system name
    public function fetchSpanshData()
    {
        $spansh     = false;
        $landmarks  = $geologicals= $rings = $payload = array();
        $url        = sprintf("https://spansh.co.uk/api/search?q=%s" , urlencode( $this->systemName ) );
        $contents   = file_get_contents($url);
        $data       = json_decode( $contents, false );

        // $this->preWrap($data);
        // exit;

        $bases      = [];
        $testResult     = $data->results; 
        $matchedResult  = null; 
        
        // ==================================================================================================
        // Let's make sure we're getting the correct system by name, because Spansh passes back
        // several different records with name "Jura" returns "Jura, Jura Something, Jura something else"...
        // ==================================================================================================
        
        foreach($testResult as $resultRow )
            {
                if( $resultRow->type == 'system' && $resultRow->record->name == $this->systemName ){
                    $matchedResult = $resultRow;
                }
            }
            
            // =====================================================================
            // We found a match system name and verified it's a system type so we
            // can now proceed to process the matchedResult array of objects.
            // =====================================================================
            if( $matchedResult ) {
                
                $record         = $matchedResult->record;
                $this->id64     = $record->id64;

                // Here lets grab an apidump from Spansh based on id64

                
                
                unset($record->bodies, $record->minor_faction_presences, $record->synthesis_recipes);

                $current_dt                             = date_create( date('Y-m-d H:i:s') ) ;
                $update_dt                              = property_exists($matchedResult->record, 'updated_at') ? date_create($matchedResult->record->updated_at) : null;
                $diff                                   = date_diff($current_dt, $update_dt);
                
                if($update_dt){
                    $this->updated_at                   =  date_format($update_dt, 'y-m-d h:i:s');
                }

                $this->controlling_minor_faction        = property_exists($matchedResult->record, 'controllering_minor_faction') ?  $matchedResult->record->controlling_minor_faction : null;
                $this->controlling_minor_faction_state  = property_exists( $matchedResult->record, 'controlling_minor_faction_state') ? $matchedResult->record->controlling_minor_faction_state : null;
                
                // stations details in system (carriers)
                $aStations                              =  property_exists($matchedResult->record, 'stations') ? $matchedResult->record->stations : [];
                $outposts                               = $starports = $carriers  = $bsaes  = $settlements = $poutposts = [];
                
                
                foreach( $aStations as $row ){
                    
                    if( property_exists($row, 'type')){
                        // collect Planetary Outposts
                        if( $row->type == 'Planetary Outpost'){
                            $row->name      = str_replace("'", "`", $row->name ) . " (planetary)";
                            $poutposts[]    = $row; // for future references
                            $outposts[]     = $row;
                        }
                        
                        // collect Outposts
                        if( $row->type == 'Outpost'){
                            $row->name = str_replace("'", "`", $row->name );
                            $outposts[] = $row;
                        }
                        // collect carriers
                        if( strpos($row->type, 'Carrier') > 0  && property_exists( $row, 'controlling_minor_faction' ) ) {

                                $row->inaraLink = sprintf('https://inara.cz/elite/station/?search=%s', urlencode($row->name) );
                                $row->spanshAPIUrl = sprintf('https://spansh.co.uk/api/search?q=%s', $row->market_id);
                                $carriers[] = $row; 

                        }

                        // Starpots
                        if( strpos($row->type, 'Starport') > 0 ) {
                            
                            // Don's Inheritance -> Don`s Inheritance so it doesn't 
                            // throw JS errors when parsing
                            
                            $row->name = str_replace("'", "`", $row->name );
                            $starports[] = $row; 
                            
                            // $this->preWrap($starports);
                        }
                        // Asteroid base
                        if( $row->type == 'Asteroid base'){
                            $row->name = str_replace("'", "", $row->name );
                            $bases[] = $row;
                        }
                        
                        // settlements
                        if( $row->type == 'Settlement'){
                            $row->name = str_replace("'", "", $row->name );
                            $settlements[] = $row;
                        }
                    
                    }
                
                }
                
                // Station to station 
                $this->stations      = $aStations;
                $this->station_count = count( $this->stations );
                $this->carriers      = $carriers;
                $this->outposts      = $outposts;
                $this->starports     = $starports;
                $this->bases         = $bases;
                $this->settlements   = $settlements; 
                

                // Get the region name from spansh, then update our system record
                if( property_exists($matchedResult->record, 'system_region') ) $this->region = $matchedResult->record->system_region;
                if( property_exists($matchedResult->record, 'region') ) $this->region = $matchedResult->record->region;
                
                           
           
            }else{
            
            // Spansh Returned no data because it's not been collected yet from it's pull from EDSM 
            // so we'll try to use the getRegionName and if it fails, it's null 

            $this->region       = null;
            $this->updated_at   =  date('y-m-d h:i:s');
            return;
        }
        
        $bodies = property_exists( $matchedResult->record, 'bodies') ? $matchedResult->record->bodies : []; 
        
        foreach($bodies as $body ){
            

            // gather eaach body landmarks
            if(property_exists($body, 'subtype')){

                if( $body->type = "Planet" && strpos($body->subtype, 'Star') == 0 && property_exists($body, 'landmarks')){
                    
                    // just matching biological landmarks
                    foreach( $body->landmarks as $landmark ){
                        
                        // biological signals
                        if( in_array(  $landmark->subtype, $this->species )){
                            $landmarks[] = ['body_name' => $body->name,  'landmark' => $landmark ];
                        }
                        // geological signals
                        if( in_array( $landmark->type, $this->geologicals )){
                            $geologicals[] = ['body_name' => $body->name, 'geological' => $landmark ];
                        }
                        
                        
                    }
                }
            }
        }
        
        
        // add landmarks (future use)
        $this->landmarks = $landmarks;
        $this->geologicals = $geologicals; 
        
        // System Info (future use)
        $this->allegiance                   = property_exists($matchedResult->record, 'allegiance') ? $matchedResult->record->allegiance : null;
        $this->controlling_minor_faction    = property_exists($matchedResult->record, 'controlling_minor_faction') ? $matchedResult->record->controlling_minor_faction : null;
        $this->government                   = property_exists($matchedResult->record, 'government') ? $matchedResult->record->government : null;
        $this->primary_economy              = property_exists($matchedResult->record, 'primary_economy') ? $matchedResult->record->primary_economy : null;
        $this->security                     = property_exists($matchedResult->record, 'security' ) ? $matchedResult->record->security : null;
        $this->population                   = property_exists($matchedResult->record,'population') ? $matchedResult->record->population : null;
        $this->controlling_power            = property_exists($matchedResult->record, 'controlling_power') ? $matchedResult->record->controlling_power: null;
        $this->controlling_minor_faction    = property_exists($matchedResult->record,'controlling_minor_faction') ? $matchedResult->record->controlling_minor_faction : null;
        $this->controlling_minor_faction_state = property_exists($matchedResult->record, 'controlling_minor_faction_state') ? $matchedResult->record->controlling_minor_faction_state : null;
        $this->power_state                  = property_exists($matchedResult->record, 'power_state') ? $this->systemState( $matchedResult->record->power_state ) .'('. $matchedResult->record->power_state . ')' : null;
        $this->minor_faction_presences  = property_exists($matchedResult->record, 'minor_faction_presences') ? count( $matchedResult->record->minor_faction_presences) : 0;
        // Stations
        $this->stations         =  property_exists($matchedResult->record, 'stations') ? count($matchedResult->record->stations) : null;
        $this->stations_details = property_exists($matchedResult->record, 'stations') ? $matchedResult->record->stations : null;
        // coords
        $this->coordinates = array(
            'x' => $matchedResult->record->x,
            'y' => $matchedResult->record->y,
            'z' => $matchedResult->record->z
        );


         // -------------- Update our system table record with the region -----------//
        $stmt = mysqli_prepare( $this->conn, "UPDATE system set region=?, allegiance=?, controlling_minor_faction = ?, government = ?, population = ?, primary_economy = ?, security = ?, updated_at = ? WHERE id64 = ?");
            mysqli_stmt_bind_param( $stmt, 'ssssdsssd', $this->region, $this->allegiance, $this->controlling_minor_faction, $this->government, $this->population, $this->primary_economy, $this->security, $this->updated_at, $record->id64 ); 
                
        if (!$stmt->execute()) {
            echo "Error executing statement: " . $stmt->error;
        }

        // ==========================================================
        //  The Lord Of The Rings
        // ==========================================================

        $ringData   = array();
        $payload    = array();
        $idx=0;

        $systemName = $matchedResult->record->name;

        foreach( $data->results as $record ){
            if( $record->type == 'body'){
                foreach( $record as $row ){
                    if(property_exists($row, 'rings') ){
                        foreach($row->rings as $ring){
                            if( property_exists($ring, 'signals')){
                                $ringData[$idx]['body_name']         = $row->name;
                                $ringData[$idx]['ring_name']         = $ring->name;
                                $ringData[$idx]['ring_type']         = $ring->type;
                                $ringData[$idx]['reserve_level']     = property_exists($row, 'reserve_level') ? $row->reserve_level : null;
                                $ringData[$idx]['hotspot_count']     = $ring->signal_count;
                                
                                foreach($ring->signals as $signal)  {
                                    $ringData[$idx]['hotspots'][] = ['name' => $signal->name, 'count' => $signal->count];
                                }
                    
                            }
                            $idx++;
        
                        }
                    }
        
                }
            }
        
        }
        $this->hotspots = $ringData;

    }

    // The system state formatted out in human times.
    public function systemState( $state ){

        $out = "";

        switch($state){
            case "Uncontrolled":
            case "Expansion":
            case "Contested":
                $out = "Aquisition";
                break;
            case "Exploited":
            case "Fortified":
            case "Stronghold":
                $out = "Reinforcement";
                break;
        }

        return $out;

    }


    public function bodyCount()
    {
        return $this->details->bodyCount;

    }

    public function terraformableCheck( $body ){
              
        if( property_exists( $body, 'terraformingState') && $body->type == 'Planet' && $body->terraformingState ) {

            return $this->terraformOptions[ $body->terraformingState ];
        }

    }

    public function ageOfStar( $body ){
        if ($body->type == "Star"){
            return property_exists($body, 'age') ? "Age:" . number_format($body->age) . " my" : null;
        }

    }


function timeAgoInYears(string $pastDateString): string
{
    $now        = new DateTime(); // Current date and time
    $pastDate   = new DateTime($pastDateString); // Convert the input string to a DateTime object
    $interval   = $now->diff($pastDate); // Calculate the difference
    $years      = $interval->y; // Get the number of years from the DateInterval object

    if ($years === 0) {
        return "";
    } elseif ($years === 1) {
        return "1yr ago";
    } else {
        return $years . "yrs ago";
    }

    return $years;
}

public function discoveries( $bodies, $hotspots, $pack=[] )
    {

        foreach( $bodies as $body )
        {
            if($hotspots){
                foreach($hotspots as $hotspot){
                    if( $body->name == $hotspot['body_name']){
                        foreach($body->rings as $body_ring){
                            if($hotspot['ring_name'] == $body_ring->name ){
                                $body_ring->signals = $hotspot['hotspots'];
                            }                            
                        }
                    }
                }
            }

            // check for MainStar 
            $isMainStar            = property_exists($body, 'isMainStar') ? ($body->isMainStar == 'true' ? ' (Main) ' : null) : null;
            // major axis rounded
            $semiMajorAxis          = property_exists($body, 'semi_major_axis') ? round($body->semi_major_axis, 2) : null;
            // surfaceTemperature
            $surfaceTemperature     = property_exists($body, 'surfaceTemperature') ? $body->surfaceTemperature : null; 
            
            // gravity rounded
            $gravity                = property_exists($body, 'gravity') ? round($body->gravity, 2) : 0;
            // atmosphereComposition and solidComposition values
            $solidComposition       = property_exists($body, 'solidComposition') ? $body->solidComposition: null; 
            $atmosphereComposition  = property_exists($body, 'atmosphereComposition') ? $body->atmosphereComposition : null;

            // sometime we don't get discovery data
            $discovery              = property_exists($body, 'discovery') ? $body->discovery : null;
            // initialize variables for later
            $discoveryDt            = null;
            $commander              = null;

            // format commander discovery info
            if($discovery){
                // get the date of the system discovery
                $discoveryDt        = property_exists($discovery, 'date') ? date_create( $body->discovery->date ) : null;
                $now                = new DateTime();
                // how many years ago was the discovery?
                $diff               = $discoveryDt->diff($now);
                // format string to display when discovery was and how long ago if it's more than a year ago
                $when               = $diff->y >0 ? ' (' .$diff->y . 'yr' . ($diff->y > 1 ? 's' : '') . ' ago)': ' (recently)';
                // add 986 years to co-incide with the game year
                $discoveryDt->add(new DateInterval('P986Y'));
                // format mm-yyyy
                $discoveryDt        = date_format($discoveryDt, 'm-Y');
                // CMDR Scott Fleming 12-3011
                $commander          = property_exists($discovery, 'commander') ?  sprintf('<div>%s</div><small>%s</small>', $discovery->commander , $discoveryDt . $when ) : null;
            }

            
            /**
             // TODO: The object that gets appended to the system array of bodies. Should prob clean this up, sometime.
             */
            $myObj = (object) [
                "bodyName"       => $body->name,
                "bodyType"       => $body->subType . $isMainStar.  $this->terraformableCheck($body) . " ". $this->ageOfStar($body),
                "isLandable"     => property_exists($body, 'isLandable') ? $body->isLandable : 0,
                "radius"         => $body->type == "Star" ? $body->solarRadius : $body->radius,
                "gravity"        => ( property_exists($body, 'gravity') && $body->isLandable == 1 ) ? round( $body->gravity, 2 ) : null,
                "atmosphere"     => isset( $atmosphereComposition ) ? $this->maxHeliumLevel( $atmosphereComposition) : null,
                "materials"      => property_exists($body, 'materials') ? $this->materialsCheck($body->materials): null,
                "Rings" => property_exists( $body, 'rings') ? $this->ringCheck( $body ) : null,
                "Exobiology"    => $this->getBiologicals($body),
                "Geological"     => $this->getGeologicals( $body ),
                "Discovered"  => $commander,
                "EDSM"           => $body->bodyId > 0 ? $this->details->url . '/details/idB/'. $body->id . '/nameB/' . $body->name . ' ' . $body->bodyId : $this->details->url,
            ];
            
            // append the surfaceTemperature to the subType element on landable bodies.
            // 800K or higher is too hot, color it red and strong

            $class = $surfaceTemperature > 800 ? "hot": "ok";
            if($myObj->isLandable == 1) $myObj->bodyType .= sprintf('</br/><small>Surface Temp: <span class="%s">%sK</span></small>', $class, $surfaceTemperature );
            
            
            // add to the pack array
            $pack[] = $myObj;
        }
        

        return $pack;

    }

    // biolgicals per body from SpanshJSON, not the EDSM json
    public function getBiologicals( $body ) {
        
        // if we don't have data, just return empty.
        if(!isset($this->spanshAPIData['system'])) return;
        
        $spanshBodies = $this->spanshAPIData['system']['bodies'];
        $genusList = "";
        
        foreach( $spanshBodies as $spanshBody){
            if($spanshBody['bodyId'] == $body->bodyId){
                if(isset($spanshBody['signals']['genuses'])){
                    
                        foreach($spanshBody['signals']['genuses'] as $genus) {
                            $genusFiltered = substr($genus, 11, -12);
                            $genusList .= $genusFiltered . "<br/>";
                        }
                        
                        return $genusList;
                        
                    }
                }
            }
        }

        // Count of Geologicals from SPANSHJson, not EDSM JSON
        // Over time the genuses will populate the json results, so let's 
        // work them into the display when they're available. 

        public function getGeologicals( $body, $strGenus = "" ){
            
            // if we don't have data, just return empty.
            if(!isset($this->spanshAPIData['system'])) return;
            
            $spanshBodies = $this->spanshAPIData['system']['bodies'];
            
            foreach( $spanshBodies as $spanshBody){
                // make sure we're on the right body
                if( $spanshBody['bodyId'] == $body->bodyId ){
                    
                    if( isset($spanshBody['signals']['genuses']) )
                        {
                            foreach($spanshBody['signals']['genuses']  as $genus)
                                {
                                    $strGenus .= sprintf('%s<br/>', $genus);
                                    // $this->preWrap( $genus );
                                }
                            }
                        if(isset($spanshBody['signals']['signals']['$SAA_SignalType_Geological;'] )){
                            /**
                             * 
                               2 geo signals
                               Volcanism:Major Metallic Magma
                               2025-12-14 03:00:22+00
                             *                               
                             */

                            return sprintf('%d %s <br/><strong>Volcanism</strong>:%s<br/><small>%s</small>',  $spanshBody['signals']['signals']['$SAA_SignalType_Geological;'], ' geo signals', $spanshBody['volcanismType'], $spanshBody['updateTime']) ;
                    }
                }
            }
    }




    // Helium maxlevel check
    public function maxHeliumLevel( $obj, $strOut = '' ){


        foreach( $obj as $key => $value ){
            
            if($key == "Helium" && $value > $this->materialLevels['maxlevels']['helium'] ){
                $key = "<span class='strong'>" . $key . "</span>";
            }

            $strOut .= $key . ":" . round($value) . "%<br/>"; 
        }

        return $strOut;
    }



    public function checkValuableBody( $bodyName, $bodyValue = "" ){

        if(isset($this->scanValues)){
            $valueBodies = $this->scanValues->valuableBodies;
            foreach($valueBodies as $body){
                $bodyValue = $bodyName == $body->name ? number_format($body->valueMax) : "";
            }
            return $bodyValue;
        }

    }
    
    
    public function preWrap( $d )
    {
        echo "<pre>", print_r($d), "</pre>";
    }

    public function jSonIfy( $array ){
        return json_encode($array, false );
    }

    // Export to csv file, with system name as filename
    public function csvOut($systemName, $data, $header=FALSE )
    {
        $result = [];
        foreach ($data as $key => $value)
        {
            $result[$key] = (is_array($value) || is_object($value)) ? object_to_array($value) : $value;
        }

        $fp = fopen('./csv/'.$systemName.'.csv', 'w');

        foreach ($result as $value) {
            if (!$header)
            {   
                fputcsv($fp, array_keys($value));
                $header=TRUE;
            }
            // fputcsv( $fp, get_object_vars( $value ));
            fputcsv($fp, $value );
        }
        
        fclose($fp);

    }

    public function planetCount() {
        $c = 0;
        
        // $this->prewrap( $this->getDetails()->bodies);

        foreach( $this->getDetails()->bodies as $row ){
            if($row->type == 'Planet' || $row->type === 'Star') $c++;
        }

        return $c;

    }

    public function getDetails(){
        return $this->details;
        
    }

    public function headerTable($html = "", $type="Planet", $wantedAvgLevels = array() ){

        foreach ( $this->wantedMats as $mat) {
              array_push( $wantedAvgLevels, strtolower($mat) .":". $this->materialLevels['avgLevels'][strtolower($mat)] );
            }
        
        $html = sprintf("<div class=\"overviewContainer\"><h3>%s <a class=\"tooltips\"><i class=\"far fa-copy\" id=\"copyRef\"></i><span>page url clipboard</span></a><a class='tooltips headLink' href='%s' target='_blank'>SJ<span>Spansh JSON data</span></a><span>%s</span></h3>
        <div class=\"parent\">
            <div class=\"child1\">
                <div><span>Region</span>: %s</div>
                %s
                %s
                %s
                %s
                %s
                %s
                %s
                %s
                %s
                <div><span>Total Bodies</span>: %d</div>
                <div><span>Landable bodies</span>: %d</div>
                <div><span>High Value Bodies</span>: %d</div>
                %s
                %s
                %s
                %s
                %s
                </div>
            <div class=\"child2\">%s%s%s%s%s</div>
            <div class=\"child3\"></div>
            <div class=\"child4\"></div>

        </div><!--parent-->
        </div><!--overview-->", 
        $this->scanValues->name,
        // urlencode($this->scanValues->name),
        isset($this->jsonSpanshDumpUrl) ? $this->jsonSpanshDumpUrl : '', '<a class="tooltips headLink" target="_blank" href="' . $this->jsonEDSMUrl. '">EDJ<span>EDSM Json data</span></a>',
        
        // if region is null, run getRegionName else use region from $this
        
        $this->region == null ? $this->getRegionName( $this->systemName )->region : $this->region,

        property_exists($this, 'updated_at') ? '<div><span>Last update</span>: ' . $this->updated_at . '</div>' : '',
        (property_exists($this, 'allegiance') && $this->allegiance )  ? '<div><span>Allegiance</span>:' . $this->allegiance . '</div>' : '' ,
        (property_exists($this, 'controlling_power') && $this->controlling_power )  ? '<div><span>Controlling Power</span>: ' . $this->controlling_power . '</div>' : '',
        (property_exists($this, 'power_state') && $this->power_state )  ? '<div><span>Power State</span>: ' . $this->power_state . '</span></div>' : '',
        (property_exists($this, 'controlling_minor_faction') && $this->controlling_minor_faction) ? '<div><span>Controlling Minor Faction</span>: ' . $this->controlling_minor_faction . '</div>' :'',
        (property_exists($this, 'controlling_minor_faction_state') && $this->controlling_minor_faction_state) ? '<div><span>Controlling Minor Faction State</span>: ' . $this->controlling_minor_faction_state . '</div>': '',
        (property_exists($this, 'minor_faction_presences') && $this->minor_faction_presences) ? '<div><span>Minor Factions Present</span>: ' . $this->minor_faction_presences .'</div>': '',
        (property_exists($this, 'government' ) && $this->government != 'None') ? '<div><span>Government</span>:' . $this->government . '</div>' : '' ,
        (property_exists($this, 'security') && $this->security ) ? '<div><span>Security</span>:' . $this->security . '</div>' : '' ,
        $this->planetCount(),
        $this->fetchLandables(),
        count($this->scanValues->valuableBodies), 
        (property_exists($this, 'population') && $this->population > 0) ? '<div><span>Population</span>:' . number_format( $this->population, 0, '.', ',' ) . '</div>' : '' ,
        $this->fetchGGs() > 0 ? '<div><span>Gas Giants</span>: '. $this->fetchGGs() . '</div>' : '',
        $this->fetchRingedBodies() > 0 ? '<div><span>Ringed Bodies</span>: ' . $this->fetchRingedBodies() . '</div>' : '',
        $this->ringsCount() > 0 ? '<div><span>Total Rings</span>:' . $this->ringsCount() . '</div>' : '',
        $this->landableTaters > 0 ? '<div><span>Landable Space Taters</span>: '. $this->landableTaters . '</div>' : '',
        (property_exists($this, 'carriers') && count($this->carriers) > 0 ) ? '<div><a class="tooltips" id="carriers">Carriers<span>View list</span></a></div>' : '',
        (property_exists($this, 'outposts') && count($this->outposts) > 0 )? '<div><a class="tooltips" id="outposts">Outposts<span>View list</span></a>:' . count($this->outposts) . "</div>" : '',
        (property_exists($this, 'starports') && count($this->starports) > 0) ? '<div><a class="tooltips" id="starports">Starports<span>View list</span></a>:' . count($this->starports) . "</div>" : '',
        (property_exists($this, 'bases') && count($this->bases) > 0) ? "<div><a class=\"tooltips\" id=\"bases\">Bases<span>View list</span></a>: " . count( $this->bases ) . "</div>" : '',
        (property_exists($this, 'settlements') && count($this->settlements) > 0) ? '<div><a class="tooltips" id="settlements">Settlements<span>View list</span></a>:' . count($this->settlements) . "</div>" : ''
    );
    
        return $html;
        
    }
    
}


/**
* @param array|object $data
* @return array
*/


function object_to_array($data) {
    $result = [];
    foreach ($data as $key => $value)
    {
        $result[$key] = (is_array($value) || is_object($value)) ? object_to_array($value) : $value;
    }
    return $result;
}

function renderMenu()
    {

        return '<div class="topnav">
                <a class="active" href="index.php">Home</a>
                <a href="https://edastro.com/" target="_blank">ED Astro</a>
                <a href="https://inara.cz/elite/" target="_blank">Inara</a>
                <a href="https://www.spansh.co.uk/fleet-carrier" target="_blank">Spansh</a>
                <a href="https://docs.google.com/spreadsheets/d/1hY52cdHU4CDKwaHaOWrEl23ZRW6zl0p8t1FCzGs41oo/edit?gid=0#gid=0" target="_blank">Minerals</a>
                <a href="https://edtools.cc/hotspot" target="_blank">Hotspots</a>
                <div class="itemRight"></div>
                <div class="lastItem"></div>
                </div>';

    }

