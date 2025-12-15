
async function fetchJSONCarrier( marketID ) {
  try {
    const apiEndpoint = 'https://www.afourthdimension.com/projects/eliteDangerous/edsm/fetchCarrier.php';

    // ?action=fetchCarrier&marketID=3702002944
    const queryParams = {
        action: 'fetchCarrier',
        marketID: marketID
    };

    // Convert the parameters object into a properly formatted query string
    const queryString = new URLSearchParams(queryParams).toString();

    // Combine the API endpoint with the query string
    const fullUrl = `${apiEndpoint}?${queryString}`;

    // Wait for the initial fetch request to complete and get the Response object
    const response = await fetch(fullUrl);

    // Check if the request was successful (status 200-299)
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    // Wait for the response body to be parsed as a JavaScript object
    const data = await response.json();
    // create inarURL in data
    data.inaraURL = "https://inara.cz/elite/station/?search=" + data.query;

    // console.log('Here is data',  data );
    return data; 

  } catch (error) {
    console.error('Error fetching JSON data:', error);
    // You can handle the error as needed
    throw error;
  }
}





// click to copy 
function getFormattedDatePlus986Years() {
    // 1. Get the current date
    const today = new Date(); // e.g., Dec 11 2025 09:21:00

    // 2. Subtract 10 years using setFullYear()
    // getFullYear() returns the four-digit year (e.g., 2025)
    today.setFullYear(today.getFullYear() + 986); // get game year

    // 3. Format the date into YYYY-MM-DD format manually
    const year = today.getFullYear();
    // getMonth() returns 0-indexed month (0 for Jan, 11 for Dec), so add 1
    const month = (today.getMonth() + 1).toString().padStart(2, '0');
    // getDate() returns the day of the month (1-31)
    const day = today.getDate().toString().padStart(2, '0');

    // Return the formatted string
    // return `${year}-${month}-${day}`;
    return `${month}-${day}-${year}`;
}


function getFormattedTimePlus6Hours(){
    const now = new Date();
    
    now.setHours(now.getHours() + 6)

    return `${(now.getHours()< 10 ?"0":"") + now.getHours() }`+ ":" + `${now.getMinutes()}` + ":" + `${(now.getSeconds()< 10 ?"0":"") + now.getSeconds()}`
}



$(document).ready(function(){

    // get the game date, which is 986 years in the future.
    const datePlus986 = getFormattedDatePlus986Years();
    // append our date into the navbar container.
    $('.itemRight').append(datePlus986);
    
    // display current time plus 6hrs to match game time.
    $('.lastItem').append( getFormattedTimePlus6Hours());
    // console.log( getFormattedTimePlus6Hours() );

    var currentURL = window.location.href;

    $('#copyRef').click(function(e){
        e.preventDefault();
        navigator.clipboard.writeText(currentURL);
        // alert('Got it');
        $(this).hide();

    })

    if ($('.child2').text().trim() == "") {
        $('.child2').hide();
    }


    $(".click .copy").click(function(event){
    var $tempElement = $("<input>");
        $("body").append($tempElement);
        $tempElement.val($(this).closest(".click").find("span").text()).select();
        document.execCommand("Copy");
        $tempElement.remove();
    });

    
    $('.massmathCopy').click(function(event){

        // bold the link, and change text to copied
        $(this).text('Copied').css('background-image', 'none').css('color', 'green');

        $(this).closest('div').find('span').css('background-color', '#04aa6d').css('color', '#fff');

        var copyMassmathContent = $(this).data('visualvalue');
        
        navigator.clipboard.writeText( copyMassmathContent );
        
        console.log( $(this).closest('div').find('span').text() );
        
         $(this).closest('div').find('span').focus().select();

    });


    /**
     * Open carriers panel, and display list of carriers in the system
     */
    
    $('#carriers').click(function(x){
        
        
        $(".child3").empty().show().html('<div id="carrierHeading">Carriers<span>[X]</span></div>');
        
        var carriers        = $("#carriersData").attr('data-carriers');
        var jsoncarriers    = JSON.parse(carriers)
        
        $.each( jsoncarriers, function( key, row ){
            
                // Call for carrier name and api from spansh by market_id
                fetchJSONCarrier( row.market_id )
                  .then(data =>{
                    var dataResult = data.results[0];

                    // validate we have a record element, and in that record, contains the carrier_name element
                    if( dataResult.hasOwnProperty('record')){
                        var record = dataResult.record;
                        if ( record.hasOwnProperty('carrier_name')){
                            var carrier_name = data.results[0].record.carrier_name.toUpperCase();
                          //   append to carrier panel with info and Inara Link for this carrier
                            $(".child3").append('<div><a class="tooltips" target="_blank" href="' + data.inaraURL + '">'  + carrier_name  + '<span>Go to Inara</span></a> ' + row.name + '</div>');
                        }else{
                            console.log(record);
                        }
                    }


                  })
                  .catch(err => {
                    console.error( 'In here, flow:', err )
                  });


            });

        // close .child3 container listener. Must be initiated inside the click function
        // in order to fire properly. Otherwise, it cannot "see" the container.

        $('#carrierHeading span').click(function(q){
            // console.log('click');
            $(".child3").empty().hide();
        });


    });

    

    // Outposts listing panel

    $('#outposts').click(function(x){
        
        $(".child3").empty().show().html('<div id="outpostsHeading">Outposts <span>[X]</span></div>');
        
        var outposts = $("#outpostsData").attr('data-outposts');
        var jsonOutposts = JSON.parse(outposts)
        
        // Iterate outputs, show padsize NumPads
        $.each( jsonOutposts, function( key, row ){
                
                var large_pads = row.has_large_pad == true ?  row.large_pads +"L " : " ";
                var med_pads = row.medium_pads > 0 ? row.medium_pads + "M ": "";
                var sm_pads  = row.small_pads > 0 ? row.small_pads + "S " : "";

                var has_shipyard = row.has_shipyard == true ? "SY " : " ";
                var has_market = row.has_market == true ? "MK " : " ";
                var has_outfitting = row.has_outfitting == true ? "O " : " ";
                var distance_to_arrival = row.distance_to_arrival != null ? Math.round(row.distance_to_arrival) + 'ls' : 0;

                $(".child3").append("<div class='outpost' data-marketId='"+ row.market_id + "'><a class='tooltips'>" + row.name + "<span>" + large_pads + med_pads + sm_pads + has_market + has_outfitting + has_shipyard +"</span></a></div>");
            })
        
        // close .child3 container listener. Must be initiated inside the click function
        // in order to fire properly. Otherwise, it cannot "see" the container.
        
        $('#outpostsHeading span').click(function(q){
            $(".child3").empty().hide();
        });


        $('.outpost').click(function(o){
            var marketId = $(this).attr('data-marketId');

            // fetch marketId from edsm
            // https://www.edsm.net/api-system-v1/stations/market?marketid=1231232314
            $.getJSON("https://www.edsm.net/api-system-v1/stations/market?marketId=" + marketId, function(result){
            
                $('.child4').html('<div class="sName">' + result.sName + '<span> [X]</span></div>').show();

                var commoditiesCount = result.commodities !=undefined ? result.commodities.length :  "No";
                $('.child4').append('<div>' + commoditiesCount + ' commodities</div>');

                    $('.child4').append('<div><a class="tooltips" target="_blank" href="'+ result.url + '">View<span>EDSM Detail</span></a></div>');
                    
                    $('.sName span').click(function(q){
                        $(".child4").empty().hide();
                    });
              
                });


        });

    });

    
  

    // starports display 

    $('#starports').click(function(x){
        $(".child3").empty().show().html('<div id="starportsHeading">StarPorts<span>[X]</span></div>').css("background-color", "#ffebcd");
        
        var starports = $("#starportsData").attr('data-starports');
        var jsonstarports = JSON.parse(starports)
        
        $.each( jsonstarports, function( key, row ){

                var large_pads = row.has_large_pad == true ? " LG " : " ";
                var has_shipyard = row.has_shipyard == true ? "SY " : " ";
                var has_market = row.has_market == true ? "MK " : " ";
                var has_outfitting = row.has_outfitting == true ? "O " : " ";
                var distance_to_arrival = row.distance_to_arrival != null ? Math.round(row.distance_to_arrival) + 'ls' : 0;
                var starport_type = row.type.replace('Starport', '').trim() + ":";

                $(".child3").append('<div class="starport ' + key + '"><span>' + starport_type + '</span>' + row.name + '<span>' + large_pads + has_market + has_outfitting + has_shipyard + '</span></div>');
        });

        // close .child3 container listener. Must be initiated inside the click function
        // in order to fire properly. Otherwise, it cannot "see" the container.
        $('#starportsHeading span').click(function(q){
            $(".child3").empty().hide();
        });

    });
    // bases
    $('#bases').click(function(x){
        
        $(".child3").empty().show().html('<div style=\"margin-bottom:4px\"><b>Bases</b></div>').css("background-color", "#ffebcd");

        var bases = $("#basesData").attr('data-bases');
        var jsonBases = JSON.parse( bases )
        $.each( jsonBases, function( key, row ){
                $(".child3").append("<div>" + row.name + "</div>");
        })
    });

    // settlements
    $('#settlements').click(function(x){
        $(".child3").empty().show().html('<div id="settlementHeading">Settlements <span>[X]</span></div>');
       
        var settlements = $("#settlementsData").attr('data-settlements');
        var jsonsettlements = JSON.parse( settlements )
        
        $.each( jsonsettlements, function( key, row ){
                var large_pads = row.has_large_pad == true ? " LG " : " ";
                var has_shipyard = row.has_shipyard == true ? "SY " : " ";
                var has_market = row.has_market == true ? "MK " : " ";
                var has_outfitting = row.has_outfitting == true ? "O " : " ";
                var distance_to_arrival = row.distance_to_arrival != null ? Math.round(row.distance_to_arrival) + 'ls' : 0;
                var type = row.type;

                $(".child3").append('<div class="settlement ' + key + '" data-marketId="' + row.market_id + '">' + row.name + '<span>' + large_pads + has_market + has_outfitting + has_shipyard + '</span></div>');

            });
            // close the child panel
            $('#settlementHeading span').click(function(q){
                $(".child3").empty().hide();
            });

           
    });


        // hover shit to change background
        const elements = document.querySelectorAll('.bodyType');

        // menu a href elements mouseover background change
        const menuElements = document.querySelectorAll('.topNav a')
        menuElements.forEach(element => {
            element.addEventListener('mouseover', function(){
                this.classList.add('blueBackground')
            })
            element.addEventListener('mouseout', function(){
                this.classList.remove('blueBackground')
            })
        })

        const outposts = document.querySelector('.outpost');
        elements.forEach(element => {
            element.addEventListener('mouseover', function() {
               this.classList.add('blueBackground');
            });
            element.addEventListener('mouseout', function() {
            this.classList.remove('blueBackground');

            });
        });

});
