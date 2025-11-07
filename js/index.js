// click to copy 
$(document).ready(function(){

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


    
    
    $('#carriers').click(function(x){
        
        
        $(".child3").empty().show().html('<div id="carrierHeading">Carriers<span>[X]</span></div>');
        
        var carriers        = $("#carriersData").attr('data-carriers');
        var jsoncarriers    = JSON.parse(carriers)
        
        $.each( jsoncarriers, function( key, row ){
            
            // console.log( row );

                var large_pads = row.has_large_pad == true ? " LG " : " ";
                var has_shipyard = row.has_shipyard == true ? "SY " : " ";
                var has_market = row.has_market == true ? "MK " : " ";
                var has_outfitting = row.has_outfitting == true ? "O " : " ";
                // var distance_to_arrival = row.distance_to_arrival != null ? Math.round(row.distance_to_arrival) + 'ls' : 0;

                // write data to container
                $(".child3").append('<div>' + row.type.replace('Carrier','') + " " + row.name + large_pads + has_shipyard + has_market + has_outfitting + "</div>");
                
            });

        // close .child3 container listener. Must be initiated inside the click function
        // in order to fire properly. Otherwise, it cannot "see" the container.

        $('#carrierHeading span').click(function(q){
            console.log('click');
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

                $(".child3").append("<div class='outpost' data-marketId='"+ row.market_id + "'>" + row.name + "<span>" + large_pads + med_pads + sm_pads + has_market + has_outfitting + has_shipyard +"</span></div>");
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
            
                $('.child4').html('<div class="sName">' + result.sName + '<span>[X]</span></div>').show();

                var commoditiesCount = result.commodities !=undefined ? result.commodities.length :  "No";
                $('.child4').append('<div>' + commoditiesCount + ' commodities</div>');
                    $('.child4').append('<div><a target="_blank" href="'+ result.url + '">View EDSM Details</a></div>');
        
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
        const outposts = document.querySelector('.outpost');

        elements.forEach(element => {
            element.addEventListener('mouseover', function() {
            // Code to execute when mouse enters the element
                // console.log('Mouse entered element with class:', this.className);
            //    this.classList.add('hovered'); // Example: add a class for styling
               this.classList.add('blueBackground');
               
            });

            element.addEventListener('mouseout', function() {
            // Code to execute when mouse leaves the element
            // console.log('Mouse left element with class:', this.className);
            // this.classList.remove('hovered'); // Example: remove the class
            this.classList.remove('blueBackground');

            });
        });

});
