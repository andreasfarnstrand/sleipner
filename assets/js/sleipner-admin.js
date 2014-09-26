jQuery( document ).ready( function( $ ) {

	var default_lat = 62.3850746;
  if( SLEIPNER_ADMIN.map_default_lat.length ) default_lat = SLEIPNER_ADMIN.map_default_lat;

	var default_lon = 17.3038078;
  if( SLEIPNER_ADMIN.map_default_lng.length ) default_lon = SLEIPNER_ADMIN.map_default_lng;
	
  var default_zoomlevel = 13;
  if( SLEIPNER_ADMIN.map_default_zoomlevel ) default_zoomlevel = SLEIPNER_ADMIN.map_default_zoomlevel;

	var default_map_options = {
    center: new google.maps.LatLng( default_lat, default_lon ),
    zoom: parseInt( default_zoomlevel ),
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };

	var map = new google.maps.Map( document.getElementById("map_canvas"), default_map_options );


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

  // Setup the map, on load
  getLocation( createLocationQuery() );

  $('#location-street-address, #location-zipcode, #location-city').blur( function() {
    getLocation( createLocationQuery() );
  });


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
      var city = 'Heby'.trim().replace( ' ', '+' );
    }
    var country = 'sverige'; //$('#location-country').val().trim().replace( ' ', '+' );
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

    zoomlevel = tmp_zoomlevel;

    return address;
  }

  function getLocation( address ) {

    geocoder.geocode( { 'region': 'se', 'address': address }, function(results, status) {
      
      if( status == google.maps.GeocoderStatus.OK ) {
        map.panTo( results[0].geometry.location );
        map.setCenter( results[0].geometry.location );
        map.setZoom( zoomlevel );
        
        // Remove previous markers
        clearMarkers();

        // Create a new marker
        marker = new google.maps.Marker({
          position: results[0].geometry.location,
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

