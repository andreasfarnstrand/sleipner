<?php

	/**
	 * Sleipner
	 * 
	 * This is the base class for the Sleipner plugin
	 * 
	 * @package Sleipner
	 * @author  Andreas Färnstrand <andreas@farnstranddev.se>
	 */

	namespace Sleipner\Base;

	use Sleipner\Core\Posttypes;
	use Sleipner\Posttypes\Sleipner_Event;

	class Sleipner_Base {

		/**
		 * __construct
		 * 
		 * Constructor for the Sleipner plugin.
		 * Here we define all hooks for the plugin
		 */
		public function __construct() {

			// Setup the custom posttype Sleipner_Event
			add_action( 'init', array( 'Sleipner\Posttypes\Sleipner_Event', 'init' ) );

			// Check if we need to add the frontend javascripts 
			add_action( 'wp_enqueue_scripts', array( 'Sleipner\Posttypes\Sleipner_Event', 'scripts' ) );

			// Filter to override the default templating for the custom post type
			add_filter( 'template_include', array( $this, 'template_include' ), 99 );

			// If this is an admin page
			if( is_admin() ) {
				add_action( 'add_meta_boxes', array( 'Sleipner\Posttypes\Sleipner_Event', 'admin_interface' ) );	
				add_action( 'save_post', array( 'Sleipner\Posttypes\Sleipner_Event', 'save_post' ) );
				add_action( 'admin_notices', array( 'Sleipner\Posttypes\Sleipner_Event', 'admin_notices' ) );

				add_action( 'admin_enqueue_scripts', array( 'Sleipner\Posttypes\Sleipner_Event', 'admin_scripts' ) );

				$this->settings();
			}

		}


		/**
		 * template_include
		 * 
		 * The filter callback to get the template for the events
		 * 
		 * @param  $template The template path
		 * 
		 * @return string The template path to return
		 */
		public function template_include( $template ) {
 
			global $post;

			$template_type = pathinfo( $template, PATHINFO_FILENAME );

			if( $post->post_type == 'sleipner_event' && $template_type == 'single' ) {

				// Get the template slug
		    $template_slug = rtrim( $template, '.php' );
		    $template_file = $template_type . '-sleipner_event.php';

		    // Check if a custom template exists in the theme folder, if not, load the plugin template file
		    if( $template_result = locate_template( array( 'plugins/sleipner/templates/' . $template_file ) ) ) {

		        return $template_result;

		    } else if( file_exists( SLEIPNER_PATH . '/templates/' . $template_file ) ) {
		    	
		    	return SLEIPNER_PATH . '/templates/' . $template_file;

		    }

			}

			if( $post->post_type == 'sleipner_event' && $template_type == 'archive' ) {

				// Get the template slug
		    $template_slug = rtrim( $template, '.php' );
		    $template_file = $template_type . '-sleipner_event.php';

		    // Check if a custom template exists in the theme folder, if not, load the plugin template file
		    if( $template_result = locate_template( array( 'plugins/sleipner/templates/' . $template_file ) ) ) {

		        return $template_result;

		    } else if( file_exists( SLEIPNER_PATH . '/templates/' . $template_file ) ) {
		    	
		    	return SLEIPNER_PATH . '/templates/' . $template_file;

		    }

			}

			return $template;

		}



		public function settings() {

			require_once( 'class-sleipner-settings.php' );
			$settings = new Sleipner_Settings();

		}

	}

?>