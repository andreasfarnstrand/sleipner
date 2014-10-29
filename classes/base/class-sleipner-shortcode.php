<?php

	/**
	 * Shortcode
	 * 
	 * This class has all the shortcodes for Sleipner Plugin.
	 * 
	 * @package Sleipner
	 */

	namespace Sleipner\Base;

	use Sleipner\Posttypes\Sleipner_Event as Event;
	use Sleipner\Base\Sleipner_Settings as Settings;

	class Shortcode {

		/**
		 * event
		 * 
		 * Shortcode for displaying a single event.
		 * 
		 * @param array $attributes This should contain id of the event to be displayed.
		 * 
		 * @return string|false The html for output or a false.
		 */
		public function event( $attributes ) {

			$a = shortcode_atts( 
				array(
        	'id' => 0
    		), 
    		$attributes 
    	);

			if( isset( $a['id'] ) && $a['id'] > 0 ) {

				$options = Settings::get_options();

				wp_enqueue_script( 'sleipner-frontend', SLEIPNER_URL.'assets/js/sleipner-event.js', array( 'jquery' ), false, true );
				wp_enqueue_script( 'google-maps', "https://maps.googleapis.com/maps/api/js?key=" . $options['google_maps_api_key'] . "&sensor=true", array(), false, true );

        // Check if css should be loaded for template
        if( isset( $options['output_template_css'] ) && $options['output_template_css'] == true ) {
          wp_enqueue_style( 'sleipner-frontend-style', SLEIPNER_URL . 'assets/css/sleipner-event.css' );
        }

				$event = Event::fromId( $a['id'] ); 

				if( is_object( $event ) ) {

					$coordinates = $event->location_coordinates;
	        $coordinates_string = $coordinates;
	        $coordinates = str_ireplace('(', '', $coordinates);
	        $coordinates = str_ireplace(')', '', $coordinates);
	        $coordinates = explode( ',', $coordinates );
	        $lat = $coordinates[0];
	        $lon = $coordinates[1];
	        $zoomlevel = $event->location_zoomlevel;
	        if( !isset( $zoomlevel ) ) $zoomlevel = 12;
	        $hue = isset( $options['map_hue'] ) ? '#' . $options['map_hue'] : null;

	        $location_name = $event->location_name;

	        wp_localize_script( 'sleipner-frontend', 'SLEIPNER', 
	          array(
	            'lat' => $lat, 
	            'lon' => $lon, 
	            'coordinates' => $coordinates_string, 
	            'name' => $location_name,
	            'zoomlevel' => $zoomlevel,
	            'hue' => $hue,
	            'ajaxurl' => admin_url( 'admin-ajax.php' ),
	            'images_dir' => SLEIPNER_URL . '/images/'
	          )
	        );

	        return $event->single_template_output();

				}

			}

			return false;    	

		}

	}

?>