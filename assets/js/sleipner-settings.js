jQuery( document ).ready( function( $ ) {

	var default_lat = 9.0405704;
	if( SLEIPNER.map_default_lat != undefined ) default_lat = SLEIPNER.map_default_lat;

	var default_lng = 54.8639127;
	if( SLEIPNER.map_default_lng != undefined ) default_lng = SLEIPNER.map_default_lng;

	var default_zoomlevel = 2;
	if( SLEIPNER.map_default_zoomlevel != undefined ) default_zoomlevel = SLEIPNER.map_default_zoomlevel;


  var default_map_options = {
    center: new google.maps.LatLng( default_lat, default_lng ),
    zoom: parseInt( default_zoomlevel ),
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };

	var map = new google.maps.Map( document.getElementById("map_canvas"), default_map_options );
	if( SLEIPNER.map_hue != null && SLEIPNER.map_hue.length > 0 ) {
		var styles = [{"stylers": [{ "hue": '#' + SLEIPNER.map_hue }] }];
    map.setOptions( {styles:styles} );
  }

  $('#sleipner-map-hue').keyup( function() {
    if( $(this).val().length == 6 ) {
      map.set( 'styles', [{stylers: [{hue: '#' + $(this).val()}]}] );
    }
  });

	/*
  |------------------------------------------
  | Map events
  |------------------------------------------
  */

  google.maps.event.addListener( map, 'center_changed', function() {
  	$('#sleipner-map-default-lat').val( map.getCenter().lat() );
  	$('#sleipner-map-default-lng').val( map.getCenter().lng() );
  });

  google.maps.event.addListener( map, 'zoom_changed', function() {
  	$('#sleipner-map-default-lat').val( map.getCenter().lat() );
  	$('#sleipner-map-default-lng').val( map.getCenter().lng() );
  	$('#sleipner-map-default-zoomlevel').val( map.getZoom() );
  });


});

