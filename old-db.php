<?phpfunction getActiveListeners() {
    global $mysqli;
     try{
         if( !$result = $mysqli->query("SELECT * FROM activeListeners") ) throw new Exception( $mysqli->error );
             $payload = $result->fetch_all();
             
    } catch( Exception $e){
        $payload = $e->getMessage();
    }

    
    return json_encode( $payload, true );
}

//   A better query, get todays listeners, then use jscript to sort the actives/inactives
function mapListenersToday(){
    global $mysqli;
    
    
    try{
        setlocale(LC_ALL,'en_US');
        
        if( !$result = $mysqli->query("SELECT * FROM mapListenersToday") ) throw new Exception( $mysqli->error );
        
        // Convert shitty characters in international strings
        
        $payload = $result->fetch_all();
        
        foreach( $payload as $idx => $row ){
            // Prevent shitty characters from breaking out shit!
            $payload[$idx][1] = iconv("ISO-8859-1", "ASCII//TRANSLIT", $payload[$idx][1]);
            $payload[$idx][2] = iconv("ISO-8859-1", "ASCII//TRANSLIT", $payload[$idx][2]);
            $payload[$idx][8] = mapFormatAgent( $row[8] );
        }
        
        // preWrap($payload);

    } catch( Exception $e){
        $payload = json_encode( $e->getMessage() );
   }



   return json_encode($payload, true);

  }

  function GetUseragents($out = array()){
      global $mysqli;
      if( !$result = $mysqli->query( "SELECT * FROM useragents" ) ) throw new Exception( $mysqli->error );
        $payload = $result->fetch_all();
      return $payload;
  }



function agentKeys(){
    global $agents;

    $payload = "";
    $knownKeys = array();

    foreach( $agents as $key => $icon ){
        if(!in_array($icon, $knownKeys)){
            $payload .= sprintf("<i class='%s'></i><span class='agentkey'>%s</span>", $icon, $key);
            array_push($knownKeys, $icon);
        }
    }

    return $payload;
}

// Renove duplicate keys from the array return unique.
function unique_multidim_array($array, $key) {

    $temp_array = array();
    $i = 0;
    $key_array = array();

    foreach($array as $val) {

        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $temp_array[$i] = $val;
        }

        $i++;
    }

    return $temp_array;
}


function human_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}


function nowPlaying(){
    try{
    
        global $mysqli;
        if( !$result = $mysqli->query( "SELECT * FROM nowplaying" ) ) throw new Exception( $mysqli->error );
        return $result->fetch_object();

    } catch( Exception $e){
        return  $e->getMessage() ;
    }
}


function listeners_today( $mObj, $since  ){
    
    try{

        global $mysqli;
        global $useragents;

        $plays      = plays();
        $totals     = listeners_total();
        $nowplaying = nowPlaying();
        $out        = $coords = [];

		$result    = $mysqli->query("select * from listeners_today");

		if(!$result) throw new Exception( $mysqli->error );       

		if( $result->num_rows > 0 ){
			while($row = $result->fetch_object() ){ 
                
                // Remove unwanted fields from our output array. 
                array_push( $coords, array("city" => $row->city, "lat" => $row->lat, "lng" => $row->lng ) );
				unset( $row->timestamp,  $row->lat, $row->lng );
				array_push( $out, $row); 

			}
            
            // echo "<pre>", print_r($out), "</pre>";
            
			$header = array_keys( get_object_vars( $out[0] ) );
			$cols   = $data ='';

            // header filters to remove from display
            $filters = ['pretty_country', 'hostname', 'referer' ];



			foreach( $header as $col ){
				
                // remove pretty_country header we dont want to display that
                // but we're going to use the data in our display later. 

                if((in_array($col, $filters))) continue;

                // if($col == 'pretty_country') continue;
                // if($col == 'hostname') continue;


				switch($col){
                    
                    case 'state':
                        $col = 'Region';
                        break;
					case 'fconnect':
						$col = '1st<br/>Visit';
						break;
					case 'dtime':
						$col = 'Disconnect';
						break;
                    case 'useragent':
                        $col = 'Agent';
                        break;
                    case 'duration':
                        $col = 'Duration';
                        break;
                    case 'connection_count':
                        $col = 'Visits';
                        break;
                    case 'last_time':
                        $col = 'Last<br/>Disconnect';
                        break;
                    case 'connecttime':
                        $col = 'Last<br/>Duration';
					default:
						break;
				}

				$cols .= sprintf('<th>%s</th>', $col );
			}
        
			foreach($out as $id => $row ){
				
                // echo "<pre>", print_r( $coords[$id] ), "</pre>";

                $now      =  new DateTime('now', new DateTimezone('America/Chicago'));
				$fconnect = convertStamp( $row->fconnect );             
				$now      = convertStamp( $now->format('Y-m-d'));

				sscanf( $row->duration, "%d:%d:%d", $hours, $minutes, $seconds);

				$time_seconds = isset($hours) ? $hours * 3600 + $minutes * 60 + $seconds : $minutes * 60 + $seconds;
				
				// gt 1min connected filters
				if( $time_seconds > -1 || $row->dtime == '0000-00-00 00:00:00'){
				
					$data .=  sprintf( "<tr id='$id' data-lat='%s' data-lng='%s' %s data-hostname='%s' data-pretty-country='%s'>", $coords[$id]['lat'], $coords[$id]['lng'], $fconnect == $now ? 'class="firsttimer"' : '' , $row->hostname, $row->pretty_country );

					foreach($row as $key => $datacol ){          
                        
                        if((in_array($key, $filters))) continue;
                        // if($key == 'pretty_country') continue;
                        // if($key == 'hostname') continue;
                         

                        if($key == 'country'){
                            $datacol = sprintf('<span class="flag-icon flag-icon-%s flag-icon-squared"></span> %s', strtolower($datacol), $row->pretty_country);
                        }

                        // display last connecttime ago standards
                        if( $key == 'last_time' && $datacol !== '0000-00-00 00:00:00' ){
                            $datacol = time_elapsed_string($datacol);
                        }
                        if( $key == 'last_time' && $datacol == '0000-00-00 00:00:00'){
                            $datacol = 'Today';
                        }
						if($key == 'fconnect'){
							$pretty = new DateTime( $datacol );
							$datacol = $pretty->format('m-d-y');
						}

						if($key == 'dtime'){
							if($datacol == '0000-00-00 00:00:00') {
							   $datacol = 'Listening';
							}else{
							   $pretty = new DateTime( $datacol );
							   $datacol = $pretty->format('h:i A');
							}
						}

                        if($key == 'useragent'){
                            $datacol = formatAgent( $datacol );
                        }

                        // Check for Unknown or Masked and set to Masked
                        
						$data .= sprintf('<td %s>%s %s</td>', 
						$datacol == 'Listening' ? 'class=connected' : '',                        
                        $datacol == 'Masked' ? '<span class="lookup flag-icon flag-icon-unknown"></span>'  : '',
                        $key == 'disconnect' ? ( $datacol == 'Listening' ? 'Connected' : $datacol ) : ($datacol == 'null' ? 'Masked' : $datacol ) 
                        
                        ); 
					
                        
                    
                    }  
				
					$data .= "</tr>";

				} // if time > 60 
			}
    
            // render table with data nuggets

        //    echo "<pre>", print_r( $out ), "</pre>";

            $todays_listeners = count( $out );
			
            return sprintf("<div>
                                <table class='first table table-bordered '>
                                <caption>
                                    <div class='nowplaying'>Now Playing on Hawkwynd Radio: %s</div>
                                    <div class='stats'><span>Played: %s</span><span>%s listeners: %d</span><span>Grand Total: %s</span></div>
                                    <div class='text-center nowplaying' style='margin-bottom: 6px'>User Agents Tracking</div>
                                    <div class='agentContainer'>%s</div>
                                </caption>
							   <thead class='thead-dark'><tr>%s</tr></thead>
                               <tbody>%s</tbody>
                            </table></div>", 
							$nowplaying->cursong,
							number_format($plays->plays), 
    
							$mObj->MonthName, 
							$mObj->count, 
							number_format( $totals->totalListeners ), 
                            agentKeys(),
							$cols, 
							$data
                            
						);  
		}else{

			return('<div>No Listeners! Something surely must be wrong for me to show this! You better check everything again to see what is going on.</div>');

		} // if result > 0 
    
    }catch (Exception $e){
        return $e->getMessage();
    }
}

function convertStamp( $stamp ){
    $dt = date_create($stamp);
    return $dt->format('m-d');
}

// returns the fontawesome code for map use only.
function mapFormatAgent($agent, $myClass = 'flac-icon flag-icon-bot'){
    global $agents;


    foreach($agents as $key => $class){
        if( stripos($agent, $key) !== false ){
            $myClass = $class;
        }
    }

    return '<i class="'.$myClass.'" data-type="'.$agent.'"></i>';

}

// Proper formatting of useragent string for icons

function formatAgent($agent, $myClass = 'flag-icon flag-icon-bot'){

    global $agents;

    // Distinct user agents from useragents view
    // $useragents = GetUseragents(); 


    foreach($agents as $key => $class){
        if( stripos($agent, $key) !== false ){
            $myClass = $class;
        }
    }

    return '<div class="agentContainer" data-agent="'.$agent.'"><i class="'.$myClass.'" data-type="'.$agent.'"></i></div>';

}

function yesterday_details( $datapak = array() ){
    
    global $mysqli;
    $result    = $mysqli->query("SELECT * FROM listeners_yesterday ORDER BY country DESC");

    while( $row = $result->fetch_object() )
    {
        array_push($datapak, $row );
    }

    // return $datapak;
    render_yesterday_details($datapak);

}


function render_yesterday_details($data, $html='')
{
    // echo "<pre>", print_r($data), "</pre>";
    if($data)
    {
        echo "<div class='yesterday_details_container'>";
        foreach( $data as $row ) {
            $html .= '<div>'. $row->city . ', ' . $row->state .'<br/>' . $row->country . ' - ' . $row->connecttime .'</div>';
        }
        echo $html;
        echo "</div>";

    }
}




function listeners_yesterday() {

    global $mysqli;
    $out       = $countries = [];
    // $result    = $mysqli->query("SELECT * FROM listeners_yesterday WHERE country IS NOT NULL ORDER BY connecttime DESC");
    $result    = $mysqli->query("SELECT * FROM listeners_yesterday ORDER BY connecttime DESC");
    $cols      = $cPack = $data ='';

    while($row = $result->fetch_object() ){        
        
        // drop timestamp and disconnect from array
        // unset( $row->TIMESTAMP, $row->disconnect );
        array_push($out, $row); 
        
        // collect countries array
		array_push($countries, $row->country );

    }

    if(!$out) return false;

    $header = array_keys( get_object_vars( $out[0] ) );
    
    foreach($header as $col){
        $col = $col == 'state' ? 'Region' : $col; 
        $cols .= sprintf('<th>%s</th>', $col == 'connecttime' ? 'Duration' : $col );
    }
    
    // count of countries listening
    $countryCount = array_count_values( $countries );
    $cPack = "<div class='stats'>";

    foreach($countryCount as $k => $v ){
        $cPack .= sprintf('<span><span class="flag-icon flag-icon-%s flag-icon-squared"></span> %s:%d</span>', strtolower($k), $k, $v);
    }
    $cPack .= "</div>";
    
    foreach($out as $id => $row ){      
        
        sscanf($row->connecttime, "%d:%d:%d", $hours, $minutes, $seconds);
        $time_seconds = isset($hours) ? $hours * 3600 + $minutes * 60 + $seconds : $minutes * 60 + $seconds;
        
        // filter out rows < 120 seconds listening time
        // gt 5min connected
        // if( $time_seconds > 300 ){
            $data .= "<tr>";     

            foreach($row as $key => $datacol ){       
                $data .= sprintf('<td>%s</td>', $datacol == 'null' ? '' : $datacol ); 
            }  
            $data .= "</tr>";
        // }
    }
   
   return sprintf("<div>
                    <table class='second table table-bordered'>
                        <caption>
                            Yesterday total listeners: %d   
                            <div> %s </div>
                        </caption>
                    </table>
                  </div>", 
                count($out), $cPack);  
}



function current_month_count(){
	global $mysqli;
	$q = "SELECT MONTHNAME(CURRENT_DATE) as `MonthName`, COUNT(*) AS `count` FROM `listeners_this_month`";
	$result = $mysqli->query($q) or die( 'this_month_count error: ' . mysqli_error($mysqli));
	return $result->fetch_object();
}



function analytics(){
    global $mysqli;

    $monthName = date('F');

    $result = $mysqli->query(
        "SELECT FORMAT(a.connection_count, 0) `total visits`, l.city, l.state, l.country, DATE_FORMAT(l.first_connect, '%m-%d-%y') 'first visit', 
        DATE_FORMAT(l.disconnect, '%m-%d-%y') 'last visit'
         FROM analytics a
         JOIN listeners l on l.id=a.id 
         WHERE l.timestamp BETWEEN (CURRENT_DATE() - INTERVAL 1 MONTH) AND CURRENT_DATE()
         ORDER BY a.connection_count DESC LIMIT 10"
    );

    $out=[];

    while($row = $result->fetch_object() ){
        array_push($out, $row);
    }

    // return $out;
    $header = array_keys( get_object_vars($out[0]) );
    $cols   = $data ='';
   
    foreach($header as $col){
        $col = $col == 'state' ? 'Region' : $col; 
        $cols .= sprintf('<th>%s</th>', $col == 'connecttime' ? 'Duration' : $col );
    }
   
    foreach($out as $id => $row ){      
       $data .= "<tr>";     
       foreach($row as $key => $datacol ){       
           $data .= sprintf('<td>%s</td>', $datacol);
                //    $key == 'disconnect' ? 'class=disconnect':'',  
                //    $key == 'disconnect' ? ($datacol == '0000-00-00 00:00:00' ? 'Listening' : $datacol) : $datacol == 'null' ? 'Unknown' : $datacol ); 
       }  
   
       $data .= "</tr>";
    }
   
   return sprintf("<div><table class='table table-responsive'><caption>Top 10 %s Listeners by Visits</caption>
                    <thead class='thead-dark'><tr>%s</tr></thead><tbody>%s</tbody></table></div>", $monthName, $cols, $data);  
}


// last 72hr listeners
function last72(){
    global $mysqli;

    $result = $mysqli->query(
        "SELECT * FROM `last72_hr_listeners`"
    );

    $out = [];

    while( $row = $result->fetch_object() ){
        array_push($out, $row);
    }
    $header = array_keys( get_object_vars($out[0]) );
    $cols   = $data ='';
   
    // build headers
    foreach($header as $col){
       $cols .= sprintf('<th>%s</th>', $col );
    }
    foreach($out as $id => $row ){      
        $data .= "<tr>";     
        foreach($row as $key => $datacol ){       
            
            switch($key){
                case "useragent":
                    $agent = explode('/', $datacol);
                    $d     = explode(',',$agent[0]);
                    $datacol = $d[0];

            }

            $data .= sprintf('<td>%s</td>', $datacol );
        }  
    
        $data .= "</tr>";
    }

    return sprintf("<div><table class='table table-responsive'><caption>Last 72hrs by Duration</caption>
                    <thead class='thead-dark'><tr>%s</tr></thead><tbody>%s</tbody></table></div>", $cols, $data); 
}


// Top US listeners

function top_US_listeners(){
    global $mysqli;
    $result = $mysqli->query(
        "SELECT
        l.city,
        l.state 'state or name',
        FORMAT(a.connection_count,0) visits
    FROM
        `listeners` l
    JOIN analytics a ON
        a.id = l.id
    WHERE
        l.country IN('US')
    GROUP BY
        l.city
    ORDER BY
        a.connection_count
    DESC
    LIMIT 10"
    );

    $out = [];

    while( $row = $result->fetch_object() ){
        array_push($out, $row);
    }
    $header = array_keys( get_object_vars($out[0]) );
    $cols   = $data ='';
   
    // build headers
    foreach($header as $col){
       $cols .= sprintf('<th>%s</th>', $col );
    }
    foreach($out as $id => $row ){      
        $data .= "<tr>";     
        foreach($row as $key => $datacol ){       
            $data .= sprintf('<td>%s</td>', $datacol );
        }  
    
        $data .= "</tr>";
    }

    return sprintf("<div><table class='table table-responsive'><caption>Top 10 All Time US by Visits</caption>
                    <thead class='thead-dark'><tr>%s</tr></thead><tbody>%s</tbody></table></div>", $cols, $data); 
}

// Top Jarvis Song Plays
function top_jarvis_plays(){
    global $mysqli;
    $result = $mysqli->query(
        "SELECT * FROM `top_song_plays` limit 14"
    );
    $out = [];

    while( $row = $result->fetch_object() ){
        array_push($out, $row);
    }
    $header = array_keys( get_object_vars($out[0]) );
    $cols   = $data ='';
   
    // build headers
    foreach($header as $col){
       $cols .= sprintf('<th>%s</th>', $col );
    }
    foreach($out as $id => $row ){      
        $data .= "<tr>";     
        foreach($row as $key => $datacol ){       
            $data .= sprintf('<td>%s</td>', $datacol );
        }  
    
        $data .= "</tr>";
    }

    return sprintf("<div><table class='table table-responsive'><caption>Jarvis All Time Most Played Songs </caption>
                    <thead class='thead-dark'><tr>%s</tr></thead><tbody>%s</tbody></table></div>", $cols, $data); 
}

// Last Hour plays 
function last_hour_plays(){
    global $mysqli;
    $result = $mysqli->query(
        "SELECT DATE_FORMAT(last_played, '%h:%i%p') played, artist, song, plays FROM `plays_today`
        where last_played >  now() - interval 1 hour"
    );
    $out = [];

    while( $row = $result->fetch_object() ){
        array_push($out, $row);
    }
    $header = array_keys( get_object_vars($out[0]) );
    $cols   = $data ='';
   
    // build headers
    foreach($header as $col){
       $cols .= sprintf('<th>%s</th>', $col );
    }
    foreach($out as $id => $row ){      
        $data .= "<tr>";     
        foreach($row as $key => $datacol ){       
            // $datacol = $datacol == "0" ? "1": $datacol;
            $data .= sprintf('<td>%s</td>', $datacol );
        }  
    
        $data .= "</tr>";
    }

    return sprintf("<div><table class='table table-responsive'><caption>Last Hour Jarvis Plays</caption>
                    <thead class='thead-dark'><tr>%s</tr></thead><tbody>%s</tbody></table></div>", $cols, $data); 
}


function top10byCountry_jarbot(){
    global $mysqli;

    $monthName = date('F');

    $result = $mysqli->query(
        "SELECT * FROM `top-10-countries-listeners`"
    );
    $out = [];

    while( $row = $result->fetch_object() ){
        array_push($out, $row);
    }
    
    return json_encode( $out );
    
}


// Top 10 listeners by Country
function top_10_by_country(){
    global $mysqli;

    $monthName = date('F');

    $result = $mysqli->query(
        "SELECT * FROM `top-10-countries-listeners`"
    );
    $out = [];

    while( $row = $result->fetch_object() ){
        array_push($out, $row);
    }
    $header = array_keys( get_object_vars($out[0]) );
    $cols   = $data ='';
   
    // build headers
    foreach($header as $col){
       $cols .= sprintf('<th>%s</th>', $col );
    }
    foreach($out as $id => $row ){      
        $data .= "<tr>";     
        foreach($row as $key => $datacol ){       
            $data .= sprintf('<td>%s</td>', $datacol );
        }  
    
        $data .= "</tr>";
    }

    return sprintf("<div><table class='table table-responsive'><caption>%s Top 10 Countries</caption>
                    <thead class='thead-dark'><tr>%s</tr></thead><tbody>%s</tbody></table></div>", $monthName, $cols, $data); 
}


// top_ten listeners from view
function top_10(){

	global $mysqli;
    $monthName = date('F');

	$result = $mysqli->query("SELECT * FROM `listeners_top_10_connections`");
	$out       = [];

    while($row = $result->fetch_object() ){ 
        
        // drop timestamp from array, for now as we dont want them.
        unset($row->timestamp, $row->referer);
       array_push($out, $row); 
    }

    $header = array_keys( get_object_vars($out[0]) );
    $cols   = $data ='';
   
    foreach($header as $col){
        $col = $col == 'state' ? 'Region' : $col; 
       $cols .= sprintf('<th>%s</th>', $col == 'connecttime' ? 'Duration' : $col );
    }
   
    foreach($out as $id => $row ){      
       $data .= "<tr>";     
       foreach($row as $key => $datacol ){       
           $data .= sprintf('<td %s>%s</td>', 
                   $key == 'disconnect' ? 'class=disconnect':'',  
                   $key == 'disconnect' ? $datacol == '0000-00-00 00:00:00' ? 'Listening' : $datacol : ($datacol == 'null' ? 'Masked' : $datacol) ); 
       }  
   
       $data .= "</tr>";
    }
   
   return sprintf("<div><table class='table table-responsive'><caption>Top 10 %s Listeners by Duration</caption>
                    <thead class='thead-dark'><tr>%s</tr></thead><tbody>%s</tbody></table></div>",$monthName, $cols, $data);  

}

/**
 * @return 
 * date: firstConnect
 * date: lastConnect 
 * integer: totalListeners
 */


function time_elapsed_string($datetime, $full = false) {
	
	$now = new DateTime;
	$now->setTimezone(new DateTimeZone('America/Chicago'));
    $ago = new DateTime($datetime);
	
	// Subtract time from datetime
	$ago->modify("+6 hours");

    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hr',
        'i' => 'min',
        's' => 'sec',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    

	$output = $string ? implode(', ', $string) . ' ago' : 'just now';

	return  $output;
}

function listeners_total(){
	global $mysqli;
	$result = $mysqli->query("SELECT * FROM `listeners_total_count`");
	return $result->fetch_object();
}


function listeners_since(){
	global $mysqli;
	$result = $mysqli->query("SELECT COUNT(hostname) total, MIN(timestamp) since  FROM `listeners`") or die("listeners_since error: " . mysqli_error($mysqli));
	return $result->fetch_object();
}

function totalTime( $tabl ){
    global $mysqli;

    $result = $mysqli->query( "SELECT TIME_FORMAT( SEC_TO_TIME(SUM(TIME_TO_SEC(connecttime))), '%T') AS TotalTime FROM $tabl"  );   
    $out = $result->fetch_object();
    return $out->TotalTime;
}

// Get list of agents from listeners table (DISTINCT)
// --------------------------------------------------
function allAgents(){
    global $mysqli;
    $out = [];

    try{
        $sql = "SELECT * FROM AllAgentsLong";
        $result = $mysqli->query($sql);
        if(!$result) throw new Exception( $mysqli->error() );

        while( $row = $result->fetch_object() ){
            array_push($out, $row);
        }
    
        return $out;
    }
    catch ( Exception $e ){
        return $e->getMessage();
    }
}

// Filter known agents from allAgents list to expose unknown agents
function filterAgents(){

    global $agents;
    $allAgents = allAgents();

    $out = ['count' => count($allAgents), 'agents' => $agents];

    foreach($allAgents as $allagent){

        if( !array_key_exists( $allagent->useragent, $agents )){
            array_push($out, $allagent);
        }
    }

    return $out;
}



function plays(){
    global $mysqli;

    try{
        $sql = "SELECT SUM(plays) plays from recording";
        $result = $mysqli->query( $sql );
        if(!$result) throw new Exception( $mysqli->error() );

        $plays = $result->fetch_object();
        return $plays;
    }
    catch( Exception $e ){
        return $e->getMessage(); 
    }
}

function getListener( $hostname ){
    global $mysqli;

    try{
        $result = $mysqli->query(
            "SELECT * from listeners WHERE hostname='$hostname'"
        );

        // return  json_encode( $result->fetch_object() );
        
        updateListener( $result->fetch_object() );

    } catch( Exception $e) {
        return $e->getMessage();
    }

}

/**
 * Update listeners record in the database.
 */

function updateListener( $data, $message = [] ){
    global $mysqli;

    $current    = $data;
    $hostname   = $data['ip'];
    $city       = $data['city'] == '' ? 'Masked' : $data['city'];
    $state      = $data['region_name'] == '' ? 'Masked' : $data['region_name'];
    $country    = $data['country_code'] == '' ? 'Masked' : $data['country_code'];
    $lat        = $data['latitude'];
    $lng        = $data['longitude'];
    
    $sql = "UPDATE listeners SET city='$city', state='$state',country='$country', lat=$lat,lng=$lng WHERE hostname IN('$hostname')";
    
    try{

        
        $update = $mysqli->query( $sql );

        $message = array(
            'ip'          => $data['ip'],
            'city'          => $city,
            'state'         => $state,
            'country_code'  => $country,
            'latitude'      => $lat,
            'longitude'     => $lng
        );


    } catch( Exception $e ){
        return $e-getMessage();
    }

    return json_encode( $message ); 

    // exit;

}
 
function preWrap($d){
    echo "<pre>", print_r($d), "</pre>";
}


function geoLook($ip){

    $GEO = new Geoip();

    $payload = (object) [
        'city'      => null,
        'state'     => null,
        'country'   => null,
        'ip'        => null,
    ];

    $geoClassStuff = $GEO->getGeo($ip);


    $google = $GEO->GoogleGeo($geoClassStuff->latitude, $geoClassStuff->longitude );

    $payload->city = $google->address_components[1]->long_name; // city
    $payload->state = $google->address_components[3]->short_name; // state
    $payload->country =  $google->address_components[4]->short_name; // country
    $payload->ip    = $ip;

    return json_encode( $payload );

}

function normalizeChars( $str ) {
    $unwanted_array = array( 
        'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
        'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ü' => 'u'
    );

    $fixed = strtr( $str, $unwanted_array );
    echo $fixed . "<br/>";

    return $fixed;

}