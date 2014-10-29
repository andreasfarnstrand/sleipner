/*
This is the main frontend js file for the custom post type sleipner event
@author Andreas Färnstrand <andreas@farnstranddev.se> 
 */
jQuery( document ).ready( function($) {

  var map = null;
  var marker = null;

  if( SLEIPNER.lat.length > 0 ) {
    initialize( SLEIPNER.lat, SLEIPNER.lon, SLEIPNER.zoomlevel );
  }


  // fix for bootstrap tab, need a reload.
  $("#map-tab").on('shown.bs.tab', function() {
   initialize( SLEIPNER.lat, SLEIPNER.lon, SLEIPNER.zoomlevel );
  });
  
  function initialize(lat, lon, zoomlevel) {

    var mapOptions = {
      center: new google.maps.LatLng(lat,lon),
      zoom: parseInt( zoomlevel ),
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    map = new google.maps.Map(document.getElementById("sleipner-map-canvas"), mapOptions);

    if( marker == null ) {
      marker = new google.maps.Marker({
        position: map.getCenter(),
        title: SLEIPNER.name
      });
    }

    if( SLEIPNER.hue != null && SLEIPNER.hue.length > 0 ) {

      var styles = [
        {
          "stylers": [
            { "hue": SLEIPNER.hue }
          ]
        }
      ];

      map.setOptions( {styles:styles} );

    }

    marker.setMap(null);
    marker.setMap( map );
    
  }

  /*$('.call-to-event-registration').click( function() {
    
    if( confirm( 'Är du säker på att du vill anmäla dig till denna aktivitet?' ) ) {
        
      $(this).hide();

      $(this).parent().find('.SLEIPNER-registration-ajax').fadeIn();

      $.post( SLEIPNER.ajaxurl, {
        
        action: 'register_member_to_event',
        id: $('#event-registration-id').val(),
        nonce: $('#event-registration-nonce').val(),
        form: $('#event-registration-form').serializeArray()
      
      }, function( response ) {
        
        if( response.result ) {

          if( response.redirect != undefined ) {
            if( response.redirect.length > 0 ) 
              document.location = response.redirect;
          }

          $('.SLEIPNER-registration-ajax').empty();
          $('.SLEIPNER-registration-ajax').append( response.user_message );

        } else {

          $('.SLEIPNER-registration-ajax').empty();
          $('.SLEIPNER-registration-ajax').append( response.message ); 
          $('.SLEIPNER-registration-ajax').addClass('error');

        }

      }, 'json' );

    }


  });*/


  /*$('.call-to-event-unregistration').click( function() {
    
    if( confirm( 'Är du säker på att du vill avanmäla dig från denna aktivitet?' ) ) {
        
      $(this).hide();

      $(this).parent().find('.SLEIPNER-registration-ajax').fadeIn();

      $.post( SLEIPNER.ajaxurl, {
        
        action: 'unregister_member_from_event',
        id: $('#event-unregistration-id').val(),
        nonce: $('#event-unregistration-nonce').val()
      
      }, function( response ) {
        
        if( response.result ) {

          $('.SLEIPNER-registration-ajax').empty();
          $('.SLEIPNER-registration-ajax').append( response.user_message );
          if( response.redirect.length ) {
            document.location = response.redirect;
          }

        } else {

          $('.SLEIPNER-registration-ajax').empty();
          $('.SLEIPNER-registration-ajax').append( response.message );
          $('.SLEIPNER-registration-ajax').addClass('error');

        }

      }, 'json' );

    }


  });*/


  /*$('#extra-registrations .add-row').click(function( event ) {
    event.preventDefault();
    var clone = $('#template-registration').clone();
    clone.find('label').remove();
    clone.find('input:text').removeAttr('readonly');
    clone.find('input:text').val('');
    $('#extra-registrations #first-row div').first().after( clone );

  });*/



});