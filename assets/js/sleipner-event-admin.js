/* Swedish initialisation for the jQuery UI date picker plugin. */
/* Written by Anders Ekdahl ( anders@nomadiz.se). */
jQuery(function($){
    $.datepicker.regional['sv'] = {
                closeText: 'Stäng',
        prevText: '&laquo;Förra',
                nextText: 'Nästa&raquo;',
                currentText: 'Idag',
        monthNames: ['Januari','Februari','Mars','April','Maj','Juni',
        'Juli','Augusti','September','Oktober','November','December'],
        monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun',
        'Jul','Aug','Sep','Okt','Nov','Dec'],
                dayNamesShort: ['Sön','Mån','Tis','Ons','Tor','Fre','Lör'],
                dayNames: ['Söndag','Måndag','Tisdag','Onsdag','Torsdag','Fredag','Lördag'],
                dayNamesMin: ['Sö','Må','Ti','On','To','Fr','Lö'],
                weekHeader: 'Ve',
        dateFormat: 'yy-mm-dd',
                firstDay: 1,
                isRTL: false,
                showMonthAfterYear: false,
                yearSuffix: ''};
    $.datepicker.setDefaults($.datepicker.regional['sv']);
});

jQuery( document ).ready( function( $ ) {

  /*
  |------------------------------------------
  | Events
  |------------------------------------------
  */

  $('#export-event-participants-button').click( function() {
    
    $('#participants-export').slideToggle( 400, function() {
      $('#participants-table').slideToggle();
    });
  });

  $('.event-time').timepicker();

  $('.event-reccurance').change( function() {
    switch( $(this).val() ) {
      case 'weekly':
        $('.recurrance-interval').hide(300, function(){
          $('#recurrance-weekly').show(300);  
        });
        break;
      case 'monthly':
        $('.recurrance-interval').hide(300, function(){
          $('#recurrance-weekly').show(300);
          $('#recurrance-monthly').show(300);
        });
        break;
      case 'yearly':
        break;
      case 'never':
      default:
        $('.recurrance-interval').hide(300);
        break;
    }
  });

  $('#post').submit( function( event ) {
    $('#location-zoomlevel').val( getZoomLevel() );
  });

  /*
  |------------------------------------------
  | Location
  |------------------------------------------
  */

  var address = '';
  var map = null;
  var geocoder = new google.maps.Geocoder();
  var zoomlevel = 8;
  var marker = null;
  var markers = [];

  
  initialize();

  // Only do this if there is something to lookup
  if( $('#location-street-address').val().length || $('#location-zipcode').val().length || $('#location-city').val().length ) {
    getLocation( createLocationQuery() );
  }


  $('#location-street-address, #location-zipcode, #location-city').blur( function() {
    getLocation( createLocationQuery() );
  });

  /*$('#no-location').change( function() {
    
    var inputs = $('#location').find('input[type=text]');

    if( $(this).is(':checked') ) {  
      $.each( inputs, function( i, element ) {
        $(element).attr('readonly', 'readonly');
      });
    } else {
      $.each( inputs, function( i, element ) {
        $(element).removeAttr('readonly');
      });
    }
  });*/


  $('#event-has-registration').change( function() {
    if( $(this).is(':checked') ) {
      $('#event-registration-wrapper').slideDown();
    } else {
      $('#event-registration-wrapper').slideUp();
    }
  });

  $('#event-registration-startdate input[type=text]').datepicker({
    dateFormat: 'yy-mm-dd',
    firstDay: 1 // Start with Monday
  });
  $('#event-registration-stopdate input[type=text]').datepicker({
    dateFormat: 'yy-mm-dd',
    firstDay: 1 // Start with Monday
  });

  $('.event-date').datepicker({
    dateFormat: 'yy-mm-dd',
    firstDay: 1 // Start with Monday
  });

  //$.datepicker.regional['en'];

  function initialize() {

    var mapOptions = {
      center: new google.maps.LatLng( SLEIPNER_ADMIN.map_default_lat, SLEIPNER_ADMIN.map_default_lng ),
      zoom: parseInt( SLEIPNER_ADMIN.map_default_zoomlevel ),
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);

  }

  function getZoomLevel() {
    if( map != null ) {
      return map.getZoom();
    }
  }

  function createLocationQuery() {

    var address = '';
    var street_address = $('#location-street-address').val().trim().split(" ").join( "+" );
    var zipcode = $('#location-zipcode').val().trim().replace( ' ', '+' );
    
    if( $('#location-city').val().trim().length > 0 ) {
      var city = $('#location-city').val().trim().replace( ' ', '+' );
    } else {
      var city = 'Sundsvall'.trim().replace( ' ', '+' );
    }
    var country = 'sverige';
    if( $('#location-country').length ) {
      country = $('#location-country').val().trim().replace( ' ', '+' );
    }
    var tmp_zoomlevel = 8;
    var tmp_zoom_level_changed = false;

    if( street_address.length > 0 ) {
      address += street_address;
      tmp_zoomlevel = 14;
      tmp_zoom_level_changed = true;
    }
    if( zipcode.length > 0 ) address += ',' + zipcode;
    if( city.length > 0 ) address += ',' + city;
    if( country.length > 0 ) address += ',' + country;

    zoomlevel = parseInt( $('#location-zoomlevel').val() );//tmp_zoomlevel;

    return address;
  }

  function getLocation( address ) {

    geocoder.geocode( { 'address': address }, function(results, status) {
      
      if( status == google.maps.GeocoderStatus.OK ) {

        map.panTo( results[0].geometry.location );
        map.setCenter( results[0].geometry.location );
        map.setZoom( zoomlevel );
        
        // Remove previous markers
        clearMarkers();

        // Create a new marker
        marker = new google.maps.Marker({
          position: results[0].geometry.location,
          map: map,
          title:"Hello World!"
        });

        markers.push( marker );

        // Add marker to map
        marker.setMap(null);
        marker.setMap( map );

        $('#location-coordinates').val( results[0].geometry.location );

      } else {
        //alert("Geocode was not successful for the following reason: " + status);
      }

    });
  }


  function clearMarkers() {

    for( var i = 0; i < markers.length; i++ ) {
      markers[i].setMap( null );
    }
    markers.length = 0;

  }

});