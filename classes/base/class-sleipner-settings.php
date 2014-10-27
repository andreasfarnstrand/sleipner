<?php

  /**
   * Settings
   * 
   * Settings class for Sleipner plugin
   * 
   * @author Andreas Färnstrand <andreas@farnstranddev.se>
   * 
   */

  namespace Sleipner\Base;

  class Sleipner_Settings {
    
    /**
     * The Sleipner option properties
     */
    protected $options;


    /**
     * __construct
     * 
     */
    public function __construct() {

      global $pagenow;

      $this->options = self::get_options();

      add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
      add_action( 'admin_init', array( $this, 'page_init' ) );

      if( $pagenow == 'options-general.php' ) {
        add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
      }

    }


    /**
     * get_options
     * 
     * Get the plugin options
     * 
     * @return array The array with the plugin options
     */
    public static function get_options() {

      return get_option( 'sleipner', array() );

    }


    /**
     * add_scripts
     * 
     * Add the js and css
     */
    public function add_scripts() {

      wp_register_script( 'google-maps', "https://maps.googleapis.com/maps/api/js?key="  . $this->google_maps_api_key . "&sensor=true", array(), false, true );
      wp_register_script( 'sleipner-settings', SLEIPNER_URL . 'assets/js/sleipner-settings.js', null, null, true );
      wp_enqueue_script( 'google-maps' );
      wp_enqueue_script( 'sleipner-settings' );

      wp_register_style( 'sleipner-settings', SLEIPNER_URL . 'assets/css/sleipner-settings.css' );
      wp_enqueue_style( 'sleipner-settings' );

      wp_localize_script( 'sleipner-settings', 'SLEIPNER', array(
        'map_default_lat' => (float) $this->map_default_lat,
        'map_default_lng' => (float) $this->map_default_lng,
        'map_default_zoomlevel' => (int) $this->map_default_zoomlevel,
        'map_hue' => $this->map_hue,
      ));

    }


    /**
     * add_options_page
     * 
     * Add options page to menu
     */
    public function add_plugin_page() {
      
      add_options_page(
          'Settings Admin', 
          __( 'Sleipner', SLEIPNER_TEXTDOMAIN ), 
          'manage_options', 
          'sleipner', 
          array( $this, 'create_admin_page' )
      );

    }

    /**
     * create_admin_pahe
     * 
     * Options page callback that actually 
     * creates the options page
     */
    public function create_admin_page() {
      ?>
      <div class="wrap">
          <?php screen_icon(); ?>
          <h2><?php _e( 'Sleipner Settings', SLEIPNER_TEXTDOMAIN ); ?></h2>           
          <form method="post" action="options.php">
          <?php
              // This prints out all hidden setting fields
              settings_fields( 'events_group' );   
              do_settings_sections( 'sleipner' );
              submit_button(); 
          ?>
          </form>
      </div>
      <?php

    }

    /**
     * page_init
     * 
     * Register and add settings sections and fields
     */
    public function page_init() {        
      
      register_setting(
        'events_group', // Option group
        'sleipner', //Option name
        array( $this, 'sanitize' ) // Sanitize
      );

      /* SECTION EVENTS */

      add_settings_section(
        'events', // ID
        __( 'Events', SLEIPNER_TEXTDOMAIN ), // Title
        array( $this, 'print_section_info' ), // Callback
        'sleipner' // Page
      );

      add_settings_field(
        'enable_country', // ID
        __( 'Enable display country on event location', SLEIPNER_TEXTDOMAIN ), // Title 
        array( $this, 'event_enable_country_callback' ), // Callback
        'sleipner', // Page
        'events' // Section           
      );

      add_settings_field(
        'google_maps_api_key', // ID
        __( 'Google maps API key', SLEIPNER_TEXTDOMAIN ), // Title 
        array( $this, 'event_google_maps_api_key_callback' ), // Callback
        'sleipner', // Page
        'events' // Section           
      );

      add_settings_field(
        'map_start_lat', // ID
        __( 'Set the default start location of the event map.', SLEIPNER_TEXTDOMAIN ), // Title 
        array( $this, 'event_map_default_start_lat_callback' ), // Callback
        'sleipner', // Page
        'events' // Section           
      );

      add_settings_field(
        'map_start_lng', // ID
        '', // Title 
        array( $this, 'event_map_default_start_lng_callback' ), // Callback
        'sleipner', // Page
        'events' // Section           
      );

      add_settings_field(
        'map_zoomlevel', // ID
        '',
        array( $this, 'event_map_default_zoomlevel_callback' ), // Callback
        'sleipner', // Page
        'events' // Section           
      );

      add_settings_field(
        'map_hue', // ID
        __( 'Set a hue to the map. Default is no hue.', SLEIPNER_TEXTDOMAIN ), // Title 
        array( $this, 'event_map_hue_callback' ), // Callback
        'sleipner', // Page
        'events' // Section           
      );

      add_settings_field(
        'output_template_css', // ID
        __( 'Enable default css for the event templates.', SLEIPNER_TEXTDOMAIN ), // Title 
        array( $this, 'output_template_css_callback' ), // Callback
        'sleipner', // Page
        'events' // Section           
      );


      /* SECTION CATEGORIES */

      add_settings_section(
        'categories', // ID
        __( 'Categories', SLEIPNER_TEXTDOMAIN ), // Title
        array( $this, 'categories_section_callback' ), // Callback
        'sleipner' // Page
      );

      add_settings_field(
        'enable_categories', // ID
        __( 'Enable categories on events', SLEIPNER_TEXTDOMAIN ), // Title 
        array( $this, 'event_enable_categories_callback' ), // Callback
        'sleipner', // Page
        'categories' // Section           
      );

    }


    /**
     * sanitize
     * 
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     * 
     * @return  $new_input The new sanitized input
     */
    public function sanitize( $input ) {

      $new_input = array();
      $new_input['enable_country'] = isset( $input['enable_country'] ) ? true : false;
      $new_input['enable_categories'] = isset( $input['enable_categories'] ) ? true : false;
      $new_input['output_template_css'] = isset( $input['output_template_css'] ) ? true : false;
      $new_input['google_maps_api_key'] = isset( $input['google_maps_api_key'] ) ? $input['google_maps_api_key'] : null;
      $new_input['map_default_lat'] = isset( $input['map_default_lat'] ) ? (float) $input['map_default_lat'] : null;
      $new_input['map_default_lng'] = isset( $input['map_default_lng'] ) ? (float) $input['map_default_lng'] : null;
      $new_input['map_default_zoomlevel'] = isset( $input['map_default_zoomlevel'] ) ? $input['map_default_zoomlevel'] : null;
      $new_input['map_hue'] = isset( $input['map_hue'] ) ? $input['map_hue'] : null;

      return $new_input;
      
    }

    /** 
     * Print the Section text
     */
    public function print_section_info() {
        
        //print __( 'Enter your settings below:', SLEIPNER_TEXTDOMAIN );

    }


    /** 
     * Print the Section text
     */
    public function categories_section_callback() {
        
        //print __( 'Enter your settings below:', SLEIPNER_TEXTDOMAIN );

    }


    /**
     * event_enable_country_callback
     * 
     * HTML Callback 
     */
    public function event_enable_country_callback() {

      $checked = ($this->enable_country == true) ? $this->enable_country : false;  
      echo '<input type="checkbox" name="sleipner[enable_country]"' . checked( $checked, true, false ) . '/><br />';

    }


    /**
     * event_google_maps_api_key_callback
     * 
     * HTML Callback
     */
    public function event_google_maps_api_key_callback() {
      echo '<input type="text" id="sleipner-google-maps-api-key" value="' . $this->google_maps_api_key . '" name="sleipner[google_maps_api_key]" />';      
    }


    /**
     * event_map_default_start_lat_callback
     * 
     * HTML Callback
     */
    public function event_map_default_start_lat_callback() {

      echo '<div id="map_canvas" style="width: 100%; height: 500px; background: #ffffff;"></div>';
      echo '<input type="hidden" id="sleipner-map-default-lat" value="' . $this->map_default_lat . '" name="sleipner[map_default_lat]" />';

    }

    /**
     * event_map_default_start_lng_callback
     * 
     * HTML Callback
     */
    public function event_map_default_start_lng_callback() {

      echo '<input type="hidden" id="sleipner-map-default-lng" value="' . $this->map_default_lng . '" name="sleipner[map_default_lng]" />';

    }


    /**
     * event_map_default_zoomlevel_callback
     * 
     * HTML Callback
     */
    public function event_map_default_zoomlevel_callback() {
      echo '<input type="hidden" id="sleipner-map-default-zoomlevel" value="' . $this->map_default_zoomlevel . '" name="sleipner[map_default_zoomlevel]" />';
    }


    /**
     * event_map_hue_callback
     * 
     * HTMLCallback
     */
    public function event_map_hue_callback() {

      $hue = isset( $this->options['map_hue'] ) ? $this->options['map_hue'] : null;
      echo '#<input type="text" id="sleipner-map-hue" value="' . $hue . '" name="sleipner[map_hue]" maxlength="6" />';

    }


    /**
     * output_css_callback
     * 
     * HTMLCallback
     */
    public function output_template_css_callback() {

      $checked = ($this->output_template_css == true) ? $this->output_template_css : false;
      echo '<input type="checkbox" name="sleipner[output_template_css]"' . checked( $checked, true, false ) . '/><br />';

    }


    /**
     * event_enable_categories_callback
     * 
     * HTML Callback
     */
    public function event_enable_categories_callback() {

      $checked = ($this->enable_categories == true) ? $this->enable_categories : false;
      echo '<input type="checkbox" name="sleipner[enable_categories]"' . checked( $checked, true, false ) . '/><br />';

    }


    /**
     * __get
     * 
     * Automagical get function
     * 
     * @param  $key The key that we want to get
     * 
     * @return  The value of the given key
     */
    public function __get( $key ) {

      return isset( $this->options[$key] ) ? $this->options[$key] : null;

    }


    /**
     * __set
     * 
     * Set the value of the key
     * 
     * @param  $key The key to save
     * @param  $value The value to save
     */
    public function __set( $key, $value ) {
      $this->options[$key] = $value;
    }


  }


?>