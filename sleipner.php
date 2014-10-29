<?php

	/*
	Plugin Name: Sleipner
	Description: Sleipner is an event administration plugin. It is the base event plugin with some basic event functionality. The plan is to have a range of plugins to extend it's functionality.
	Version: 1.0
	Author: Andreas Färnstrand <andreas@farnstranddev.se>
	Author URI: http://www.farnstranddev.se
  Text Domain: sleipner
	 */
	
  /*  Copyright 2014  Andreas Färnstrand  (email : andreas@farnstranddev.se)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/
	
	/*
	|---------------------------------------
	| AVAILABLE SLEIPNER FILTERS
	|---------------------------------------

	* sleipner_pre_create_post
	* sleipner_post_create_post

	* sleipner_default_validation_error_text

	* sleipner_pre_query_args
	* sleipner_pre_register_posttype
	* sleipner_pre_register_taxonomy

	* sleipner_single_event_output_array_setup
	* sleipner_archive_event_output_array_setup

	* sleipner_single_template
	* sleipner_get_the_event_excerpt
	* sleipner_archive_template
	*/

	
	namespace Sleipner;

	// Don't allow direct access
	if ( !defined( 'ABSPATH' ) ) exit;

	// Setup the constants
	define( 'SLEIPNER_PATH', dirname( __FILE__ ) );
	define( 'SLEIPNER_URL', plugin_dir_url( __FILE__ ) );
	define( 'SLEIPNER_TEXTDOMAIN', 'sleipner' );
	define( 'SLEIPNER_TEXTDOMAIN_PATH', dirname( plugin_basename( __FILE__) ) .'/languages/' );
	define( 'SLEIPNER_VERSION', 1.0 );


	// Load all needed classes
	require_once( 'classes/core/class-util.php' );
	require_once( 'classes/base/class-sleipner-base.php' );
	require_once( 'classes/posttypes/class-sleipner-event.php' );

	use Sleipner\Base\Sleipner_Base;

	// Init plugin
	if( class_exists( 'Sleipner\\base\\Sleipner_Base' ) ) {

		// Start magic
		$sleipner = new Sleipner_Base();

	}

	
	/**
	 * sleipner_class_autoloader
	 * 
	 * Implements a class autoloader routine
	 * for the Sleipner plugin.
	 */
 	if( !function_exists('sleipner_class_autoloader' ) ) {
   	function sleipner_class_autoloader( $class ){
   		
   		$class = strtolower($class);
    	$class_file = SLEIPNER_PATH . 'classes/posttypes/class-'.$class.'.php';
    	$class_file = str_replace( '_', '-', $class_file);

    	if( is_file( $class_file ) && !class_exists( $class ) ) {
    		include_once( $class_file );
    	}
        
    }
  }
  spl_autoload_register('Sleipner\sleipner_class_autoloader');


?>