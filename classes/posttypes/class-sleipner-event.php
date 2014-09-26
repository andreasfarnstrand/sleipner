<?php

  /**
   * Sleipner_Event
   * 
   * Custom post type sleipner_event.
   * 
   * @package  Sleipner
   * @author Andreas Färnstrand <andreas@farnstranddev.se>
   * 
   * @todo  translate datepicker and timepicker via wp_localize_script
   * 
   * @todo  Might think of making the map_canvas id unique for an event
   * 
   */


	namespace Sleipner\Posttypes;

  require_once( SLEIPNER_PATH . '/classes/core/posttypes/class-posttype-model.php' );
  require_once( SLEIPNER_PATH . '/classes/core/class-html.php' );

	use Sleipner\Core\Posttypes\Posttype_Model;
  use Sleipner\Core\Posttypes\Interface_Posttype;
  use Sleipner\Core;


	class Sleipner_Event extends Posttype_Model {

    /*
    |-----------------------------------------
    | PROPERTIES
    |-----------------------------------------
     */

    // Posttype
    protected static $posttype = 'sleipner_event';
    
    // Used for validating meta data fields
    // Any fields not in this array will not
    // save.
    protected $meta_data_field_info = array();


    public function __construct() {

      $this->meta_data_field_info = array(
        'contact_name' => array(
          'type' => 'text',
        ),
        'contact_email' => array(
          'type' => 'email',
          'error_text' => __( 'The organizer contact email failed to validate.', SLEIPNER_TEXTDOMAIN ),
        ),
        'contact_phone' => array(
          'type' => 'text',
        ),
        'startdate' => array(
          'type' => 'text',
        ),
        'stopdate' => array(
          'type' => 'text',
        ),
        'starttime' => array(
          'type' => 'text',
        ),
        'stoptime' => array(
          'type' => 'text',
        )
      );

    }


    /**
     * init
     * 
     * Initializes the custom post type object.
     * It sets up the objects post type, registers
     * the custom post type and adds metaboxes to the
     * admin interface.
     */
		public static function init() {

      // Parent needs to know of the posttype
      parent::$posttype = self::$posttype;

      // Register the posttype
      self::register();

      // Setup the admin interface metaboxes
      self::meta_boxes();

		}


    /**
     * scripts
     * 
     * The function enqueues scripts used in WP frontend.
     */
    public function scripts() {

      global $post;

      $options = get_option( 'sleipner' );

      $event = Sleipner_Event::fromPostObject( $post );

      if( isset( $event ) && is_object( $event ) && $event->post_type == self::$posttype ) {

        wp_enqueue_script( 'google-maps', "https://maps.googleapis.com/maps/api/js?key=" . $options['google_maps_api_key'] . "&sensor=true", array(), false, true );
        
        if(is_single()) {
          wp_enqueue_script( 'sleipner-frontend', SLEIPNER_URL.'assets/js/sleipner-event.js', array( 'jquery' ), false, true );
          wp_enqueue_style( 'sleipner-frontend-style', SLEIPNER_URL . 'assets/css/sleipner-event.css' );

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
        }

      }

    }


    /**
     * admin_scripts
     * 
     * The function enqueues scripts used in WP admin
     * for the sleipner_event custom posttype.
     */
    public function admin_scripts() {
      
      global $post;

      if( isset( $post ) && is_object( $post ) && $post->post_type == self::$posttype ) {

        if( is_admin() ) {

          $options = get_option( 'sleipner' );
          $lat = isset( $options['map_default_lat'] ) ? $options['map_default_lat'] : null;
          $lng = isset( $options['map_default_lng'] ) ? $options['map_default_lng'] : null;
          $zoomlevel = isset( $options['map_default_zoomlevel'] ) ? $options['map_default_zoomlevel'] : null;

          // Enqueue scripts
          wp_enqueue_script( 'jquery-ui-datepicker', false, array('jquery'), false, true );
          wp_enqueue_script( 'jquery-ui-timepicker', SLEIPNER_URL.'assets/js/jquery.ui.timepicker.js', array( 'jquery' ), false, true );
          wp_enqueue_script( 'sleipner-event-admin', SLEIPNER_URL.'assets/js/sleipner-event-admin.js', array( 'jquery' ), false, true );
          wp_enqueue_script( 'google-maps', "https://maps.googleapis.com/maps/api/js?key=" . $options['google_maps_api_key'] . "&sensor=true", array(), false, true );      
          
          // Enqueue stylesheets
          wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
          wp_enqueue_style( 'jquery-timepicker-style', SLEIPNER_URL.'assets/css/jquery.ui.timepicker.css' );
          wp_enqueue_style( 'sleipner-event-admin', SLEIPNER_URL.'assets/css/sleipner-event-admin.css' );
          
          wp_localize_script( 'sleipner-event-admin', 'SLEIPNER_ADMIN', array(
            'map_default_lat' => $lat,
            'map_default_lng' => $lng,
            'map_default_zoomlevel' => $zoomlevel,
          ));

          // Load admin notices wit localization if needed
          $event = Sleipner_Event::fromPostObject( $post );
          $admin_notices = $event->sleipner_admin_notice;
          if( isset( $admin_notices ) && strlen( $admin_notices ) ) {
            
            $admin_notices = unserialize( $admin_notices );
            extract( $admin_notices );
            
            if( isset( $field ) ) {
              wp_enqueue_script( 'admin-notice-error', SLEIPNER_URL . 'assets/js/sleipner-validation.js', array( 'jquery' ), false, true );
              wp_localize_script( 'admin-notice-error', 'SLEIPNER', array(
                'validation' => array(
                  $field,
                )
              ));
            }

          }

        }

      }

    }


    /**
     * register
     * 
     * Register the class custom post type with the system
     */
    public static function register() {

      $options = get_option('sleipner');

      $posttype_labels = array(
        'name' => _x( 'Event', 'post type general name', SLEIPNER_TEXTDOMAIN ),
        'singular_name' => _x( 'Event', 'post type singular name', SLEIPNER_TEXTDOMAIN ),
        'add_new' => __( 'Create new', SLEIPNER_TEXTDOMAIN ),
        'menu_name' => __( 'Event', SLEIPNER_TEXTDOMAIN ),
        'add_new_item' => __( 'Create new event', SLEIPNER_TEXTDOMAIN ),
        'edit_item' => __( 'Edit Event', SLEIPNER_TEXTDOMAIN ),
        'new_item' => __( 'New Event', SLEIPNER_TEXTDOMAIN ),
        'all_items' => __('All Events', SLEIPNER_TEXTDOMAIN ),
        'view_item' => __('View Event', SLEIPNER_TEXTDOMAIN ),
        'search_items' => __('Search event', SLEIPNER_TEXTDOMAIN ),
        'not_found' =>  __('No events found', SLEIPNER_TEXTDOMAIN ),
        'not_found_in_trash' => __('No events found in bin', SLEIPNER_TEXTDOMAIN ),
      );

      // Apply the filter for changing posttype labels
      $posttype_labels = apply_filters( 'sleipner_pre_register_posttype', $posttype_labels );

      // Custom post type for pageaccountable
      register_post_type( self::$posttype, array(
        'labels' => $posttype_labels,
        'capability_type' => 'post',
        'public' => true,
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'show_ui' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'rewrite' => array(
          'slug' => 'event',
        ),
        'taxonomies' => array( 'sleipner_category' )
      ));


      // If the plugin is setup with the option to have event categories
      // than we need to register the taxonomy.
      if( !empty( $options['enable_categories'] ) && $options['enable_categories'] == true ) {

        $taxonomy_labels = array(
          'name'                       => _x( 'Event category', 'Taxonomy General Name', SLEIPNER_TEXTDOMAIN ),
          'singular_name'              => _x( 'Event category', 'Taxonomy Singular Name', SLEIPNER_TEXTDOMAIN ),
          'menu_name'                  => __( 'Categories', SLEIPNER_TEXTDOMAIN ),
          'all_items'                  => __( 'All event categories', SLEIPNER_TEXTDOMAIN ),
          'parent_item'                => __( 'Current event categories', SLEIPNER_TEXTDOMAIN ),
          'parent_item_colon'          => __( 'Current event categories:', SLEIPNER_TEXTDOMAIN ),
          'new_item_name'              => __( 'New event category', SLEIPNER_TEXTDOMAIN ),
          'add_new_item'               => __( 'Create new event category', SLEIPNER_TEXTDOMAIN ),
          'edit_item'                  => __( 'Edit event category', SLEIPNER_TEXTDOMAIN ),
          'update_item'                => __( 'Update event category', SLEIPNER_TEXTDOMAIN ),
          'separate_items_with_commas' => __( 'Separate event categories with a comma sign', SLEIPNER_TEXTDOMAIN ),
          'search_items'               => __( 'Search event categories', SLEIPNER_TEXTDOMAIN ),
          'add_or_remove_items'        => __( 'Add or remove event categooies', SLEIPNER_TEXTDOMAIN ),
          'choose_from_most_used'      => __( 'Chose among the most used event categories', SLEIPNER_TEXTDOMAIN ),
          'not_found'                  => __( 'Found no event categories', SLEIPNER_TEXTDOMAIN ),
        );

        $taxonomy_labels = apply_filters( 'sleipner_pre_register_taxonomy', $taxonomy_labels );
        

        // create a new taxonomy
        register_taxonomy(
          'sleipner_category',
          'sleipner_event',
          array(
            'labels' => $taxonomy_labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            //'show_tagcloud'              => true,
            'rewrite' => array( 'slug' => 'event-category' ),
          )
        );

      }

      //flush_rewrite_rules();

    }


    /**
     * map
     * 
     * Draw the map output div
     * 
     * @param  $data An array of css style parameters
     * to be applied to the map div.
     * 
     * @return  string A string represesnting the map
     * output div.
     */
    public function map( $data = '' ) {

      $style = null;
      if( count( $data ) > 0 ) {
        foreach( $data as $key => $value ) {
          $style .= $key . ': ' .$value . ';';
        }
      }

      if( isset( $style ) ) {
        return '<div id="map_canvas" style="' . $style . '"></div>';
      }

      return '<div id="map_canvas"></div>';

    }


    /**
     * meta_boxes
     * 
     * Setup the arrays containing the madmin
     * interface meta boxes.
     */
    public static function meta_boxes() {

      // Setup the meta boxes in admin interface
      self::$meta_boxes = array(
        array(
          'id' => 'contact',
          'title' => __( 'Organizer', SLEIPNER_TEXTDOMAIN ),
          'callback' => array( __CLASS__, 'metabox_organizer_callback' ),
          'posttype' => self::$posttype,
          'position' => 'side',
        ),
        array(
          'id' => 'time-and-date',
          'title' => __( 'Date and time', SLEIPNER_TEXTDOMAIN ),
          'callback' => array( __CLASS__, 'metabox_time_and_date_callback' ),
          'posttype' => self::$posttype,
          'position' => 'side',
        ),
        array(
          'id' => 'location',
          'title' => __( 'Location', SLEIPNER_TEXTDOMAIN ),
          'callback' => array( __CLASS__, 'metabox_location_callback' ),
          'posttype' => self::$posttype,
          'position' => 'normal'
        ),
      );

    }


    /**
     * save_post
     * 
     * action save_post saves data relating to the
     * event custom post type.
     */
    public static function save_post( $post_id ) {

      global $post;

      if( !self::authenticate_save( $_POST ) ) return false;

      // No need to continue if this is the wrong post type
      if( !self::matches_posttype( self::$posttype, $_POST['post_type'] ) ) return false;
      if( !is_int( $post->ID ) ) return false;

      // Remove the action while saving to avoid a loop
      remove_action( 'save_post', array( 'Sleipner\Posttypes\Sleipner_Event', 'save_post' ) );

      // Load the event object
      $event = Sleipner_Event::fromPostObject( $post );
      if( is_object( $event ) ) {

        // Update the event object
        $event->contact_name = $_POST['sleipner']['contact_name'];
        $event->contact_email = $_POST['sleipner']['contact_email'];
        $event->contact_phone = $_POST['sleipner']['contact_phone'];
        $event->startdate = $_POST['sleipner']['startdate'];
        $event->stopdate = $_POST['sleipner']['stopdate'];
        $event->starttime = $_POST['sleipner']['starttime'];
        $event->stoptime = $_POST['sleipner']['stoptime'];
        $event->location_name = $_POST['sleipner']['location_name'];
        $event->location_street_address = $_POST['sleipner']['location_street_address'];
        $event->location_zipcode = $_POST['sleipner']['location_zipcode'];
        $event->location_city = $_POST['sleipner']['location_city'];
        $event->location_country = $_POST['sleipner']['location_country'];
        $event->location_coordinates = $_POST['sleipner']['location_coordinates'];
        $event->location_zoomlevel = $_POST['sleipner']['location_zoomlevel'];
        $event->location_no_location = $_POST['sleipner']['no_location'];
        $event->save();

      }

      // Add back action for saving
      add_action( 'save_post', array( 'Sleipner\Posttypes\Sleipner_Event', 'save_post' ) );

    }


    /**
     * metabox_organizer_callback
     * 
     * This is the callback for outputing the organizer
     * metabox in the event admin interface.
     */
    public function metabox_organizer_callback() {
      
      global $post;

      // Load the event and save the meta data
      $event = Sleipner_Event::fromPostObject( $post );
      $event_contact_name = $event->contact_name;
      $event_contact_email = $event->contact_email;
      $event_contact_phone = $event->contact_phone;

      ?>
      <label><?php _e( 'Name', SLEIPNER_TEXTDOMAIN ); ?></label><br />
      <input type="text" name="sleipner[contact_name]" value="<?php echo $event_contact_name; ?>" /><br />
      <label><?php _e( 'Email', SLEIPNER_TEXTDOMAIN ); ?></label><br />
      <input type="text" name="sleipner[contact_email]" value="<?php echo $event_contact_email; ?>" /><br />
      <label><?php _e( 'Phone', SLEIPNER_TEXTDOMAIN ); ?></label><br />
      <input type="text" name="sleipner[contact_phone]" value="<?php echo $event_contact_phone; ?>" />
      <?php
    }


    /**
     * metabox_time_adn_date_callback
     * 
     * This is the callback method for the date
     * and time metabox in the event admin interface.
     */
    public function metabox_time_and_date_callback() {

      global $post;

      $event = Sleipner_Event::fromPostObject( $post );

      $event_startdate  = $event->startdate; 
      $event_stopdate   = $event->stopdate; 
      $event_starttime  = $event->starttime;
      $event_stoptime   = $event->stoptime

      ?>
      <label><?php _e( 'Start date', SLEIPNER_TEXTDOMAIN ); ?></label><br />
      <input type="text" class="event-date" name="sleipner[startdate]" value="<?php echo $event_startdate; ?>" readonly="readonly" /><br />
      <label><?php _e( 'End date', SLEIPNER_TEXTDOMAIN ); ?></label><br />
      <input type="text" class="event-date" name="sleipner[stopdate]" value="<?php echo $event_stopdate; ?>" readonly="readonly" />
      <br /><br />
      <label><?php _e( 'Start time', SLEIPNER_TEXTDOMAIN ); ?></label><br />
      <input type="text" class="event-time" name="sleipner[starttime]" value="<?php echo $event_starttime; ?>"  readonly="readonly" /><br />
      <label><?php _e( 'End time', SLEIPNER_TEXTDOMAIN ); ?></label><br />
      <input type="text" class="event-time" name="sleipner[stoptime]" value="<?php echo $event_stoptime; ?>" readonly="readonly" />
      <br /><br />
      <?php

    }


    /**
     * metabox_location_callback
     * 
     * This is the callback method for the location
     * metabox in the event admin interface.
     */
    public function metabox_location_callback() {

      global $post;
      
      $options = get_option( 'sleipner' );

      $event = Sleipner_Event::fromPostObject( $post );

      $location_name            = $event->location_name;
      $location_street_address  = $event->location_street_address;
      $location_zipcode         = $event->location_zipcode;
      $location_city            = $event->location_city;
      $location_country         = $event->location_country;
      $location_coordinates     = $event->location_coordinates;
      $location_zoomlevel       = $event->location_zoomlevel;
      $location_country         = $event->location_country;
      $no_location              = $event->location_no_location;


      if( empty( $location_coordinates ) ) {
        if( !empty( $options['map_default_lat'] ) && !empty( $options['map_default_lng'] ) ) {
          $location_coordinates = '(' . $options['map_default_lat'] . ',' . $options['map_default_lng'] . ')';
        }
      }


      ?>
      <label><input type="checkbox" id="no-location" name="sleipner[no_location]" <?php checked( $no_location, 'on' ); ?>/> <?php _e( 'This event has no location (no map or address fields will be shown on post)', SLEIPNER_TEXTDOMAIN ); ?></label><br /><br />
      <div id="location-input-wrapper"<?php echo $style; ?>>
        <label><?php _e('Location name', SLEIPNER_TEXTDOMAIN); ?></label><br />
        <input type="text" id="location-name" name="sleipner[location_name]" value="<?php echo $location_name; ?>" /><br />
        <label><?php _e('Street address', SLEIPNER_TEXTDOMAIN); ?></label><br />
        <input type="text" id="location-street-address" name="sleipner[location_street_address]" value="<?php echo $location_street_address; ?>" /><br />
        <label><?php _e('Zipcode', SLEIPNER_TEXTDOMAIN); ?></label><br />
        <input type="text" id="location-zipcode" name="sleipner[location_zipcode]" value="<?php echo $location_zipcode; ?>" /><br />
        <label><?php _e('City', SLEIPNER_TEXTDOMAIN); ?></label><br />
        <input type="text" id="location-city" name="sleipner[location_city]" value="<?php echo $location_city; ?>" /><br />
        
        <?php
        if( !empty( $options['enable_country'] ) && $options['enable_country'] == true ) {
          ?>
          <label><?php _e('Country', SLEIPNER_TEXTDOMAIN ); ?></label><br />
          <input type="text" id="location-country" name="sleipner[location_country]" value="<?php echo $location_country; ?>" /><br />
          <?php
        }
        ?>

        <br />
        <input type="hidden" id="location-coordinates" name="sleipner[location_coordinates]" value="<?php echo $location_coordinates; ?>" />
        <input type="hidden" id="location-zoomlevel" name="sleipner[location_zoomlevel]" value="<?php echo $location_zoomlevel; ?>" />
        <div id="map_canvas" style="width: 100%; height: 500px; border: 1px solid #000;"></div>
      </div>
      <?php
    }


    /**
     * single_template_output
     * 
     * Create the HTML output for a single event template
     * 
     * @return  The html
     */
    public function single_template_output() {

      // This is the array representing the single event
      $event_output = array(
        
        /* Event title */
        array(
          'type' => 'h1',
          'attributes' => array(
            'class' => 'sleipner-event-title'
          ),
          'value' => $this->post_title
        ),

        /* Event content */
        array(
          'type' => 'div',
          'attributes' => array(
            'class' => 'sleipner-event-content'
          ),
          'value' => $this->post_content
        ),

        /* Clearfix */
        array(
          'type' => 'div',
          'attributes' => array(
            'class' => 'clearfix'
          ),
          'value' => ''
        ),

        array(
          'type' => 'div',
          'attributes' => array(
            'class' => 'sleipner-event-section',
          ),
          'value' => array(
            
            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-section-title',
              ),
              'value' => __('Date and time', SLEIPNER_TEXTDOMAIN),
            ),

            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-label'
              ),
              'value' => __('Name', SLEIPNER_TEXTDOMAIN)
            ),

            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-text'
              ),
              'value' => $this->contact_name
            ),

            /* Contact email */
            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-label'
              ),
              'value' => __('Email', SLEIPNER_TEXTDOMAIN)
            ),

            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-text'
              ),
              'value' => $this->contact_email
            ),

            /* Contact phone */
            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-label'
              ),
              'value' => __('Phone', SLEIPNER_TEXTDOMAIN)
            ),

            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-text'
              ),
              'value' => $this->contact_phone
            ),

          )

        ),

        array(
          'type' => 'div',
          'attributes' => array(
            'class' => 'sleipner-event-section',
          ),
          'value' => array(
            
            /* Section title */
            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-section-title',
              ),
              'value' => __('Date and time', SLEIPNER_TEXTDOMAIN),
            ),

            /* Date and starttime */
            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-label'
              ),
              'value' => __('Starts', SLEIPNER_TEXTDOMAIN)
            ),

            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-text'
              ),
              'value' => $this->startdate . ' ' . $this->starttime,
            ),

            /* End date and time */
            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-label'
              ),
              'value' => __('Ends', SLEIPNER_TEXTDOMAIN),
            ),

            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-text'
              ),
              'value' => $this->stopdate . ' ' . $this->stoptime,
            ),

          )

        ),

        /* Location section */
        array(
           'type' => 'div',
          'attributes' => array(
            'class' => 'sleipner-event-section',
          ),
          'value' => array(

            /* Section title */
            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-section-title',
              ),
              'value' => __('Location', SLEIPNER_TEXTDOMAIN),
            ),

            /* Location name */
            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-label'
              ),
              'value' => __('Name', SLEIPNER_TEXTDOMAIN)
            ),

            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-text'
              ),
              'value' => $this->location_name,
            ),

            /* Location street address */
            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-label'
              ),
              'value' => __('Street address', SLEIPNER_TEXTDOMAIN)
            ),

            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-text'
              ),
              'value' => $this->location_street_address,
            ),

            /* Location zipcode */
            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-label'
              ),
              'value' => __('Zipcode', SLEIPNER_TEXTDOMAIN)
            ),

            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-text'
              ),
              'value' => (strlen($this->location_zipcode)>0) ? $this->location_zipcode : '&nbsp;',
            ),

             /* Location city */
            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-label'
              ),
              'value' => __('City', SLEIPNER_TEXTDOMAIN)
            ),

            array(
              'type' => 'div',
              'attributes' => array(
                'class' => 'sleipner-event-output-text'
              ),
              'value' => (strlen($this->location_city)>0) ? $this->location_city : '&nbsp;',
            ),

          )

        ),

        array(
          'type' => 'div',
          'attributes' => array(
            'class' => 'clearfix'
          ),
          'value' => ''
        ),

        array(
          'type' => 'div',
          'attributes' => array(
            'id' => 'sleipner-map-canvas'
          ),
          'value' => ''
        )

      );

      // Add filter to make it possible to change this array
      $event_output = apply_filters( 'post_event_output_array_setup', $event_output );

      // Create the actual HTML from the array
      $html = '';
      foreach( $event_output as $eo ) {
        $html .= \Sleipner\Core\HTML::fromArray( $eo );
      }

      
      // Apply filter to make it possible to change the html output
      return apply_filters( 'sleipner_single_template', $html );

    }




	}

?>