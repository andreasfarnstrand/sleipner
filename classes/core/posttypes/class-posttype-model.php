<?php

	/**
	 * Class Model
	 * 
	 * This is the base class from custom post types.
	 * It helps with the creation and maintenance of 
	 * custom posttypes.
	 * 
	 * @package Sleipner
	 * @author Andreas Färnstrand <andreas@farnstranddev.se>
	 * 
	 * @todo  There is no reason to resave an old post's base fields 
	 * every time we save. That is already taken care of by WP.
	 * Just save the meta fields in that case. Only save the post when it is new.
	 * So rewrite the save function. How do I solve this on updating posts?
	 * 
	 * @todo  Need to add more validation rules to the function.
	 * 
	 * @todo  Think about containing the different form elements
	 * in a more structered array.
	 * 
	 * @todo  Make the meta data field info support character length
	 * on input fields.
	 * 
	 * @todo  Make validation validation into an own class?
	 * 
	 * @todo  Make errors into a class of it's own?
	 * 
	 * @todo  Update meta fields with the tried value if the validation fails.
	 * 
	 * IMPORTANT
	 * 
	 * @todo  Only allow saving meta data in the meta data info fields array 
	 */

	namespace Sleipner\Core\Posttypes;

	require_once( SLEIPNER_PATH . '/classes/core/posttypes/interface-posttype-model.php' );

	use Sleipner\Core\Posttypes\Interface_Posttype_Model;

	class Posttype_Model implements Interface_Posttype_Model {

		/* Properties */

		/**
		 * The post type of the object
		 */
		protected static $posttype;


		/**
		 * An array with meta data field info used
		 * for making validation when saving
		 */
		protected $meta_data_field_info = array();


		/**
		 * Metabox setup array.
		 * Set from the extending class
		 */
		protected static $meta_boxes;


		/**
		 * The post's fields
		 */
		protected $fields;


		/**
		 * The base fields of the post object
		 */
		protected $base_fields = array(
			'ID',
      'post_author',
      'post_date',
      'post_date_gmt',
      'post_content',
      'post_title',
      'post_excerpt',
      'post_status',
      'comment_status',
      'ping_status',
      'post_password', 
      'post_name',
      'to_ping', 
      'pinged', 
      'post_modified',
      'post_modified_gmt',
      'post_content_filtered',
      'post_parent',
      'guid',
      'menu_order',
      'post_type',
      'post_mime_type', 
      'comment_count',
      'filter',
		);


		/*
		|-----------------------------------
		| FUNCTIONS
		|-----------------------------------
		*/


		/**
		 * save
		 * 
		 * Save the post data and meta data
		 * 
		 */
		public function save( $force_sanitation = true ) {

			$wp_error = false;
			$post_fields = array();
			$meta_fields = array();
			$meta_fields_to_remove = array();
			$sanitized = true;

			foreach( $this->fields as $key => $value ) {

				// By default we sanitize the input data
				if( $force_sanitation ) {
					$value = sanitize_text_field( $value );
				}

				if( in_array( $key, $this->base_fields ) ) {
					
					$post_fields[$key] = $value;

				} else {

					try {

						$meta_fields[$key] = $this->sanitize( $key, $value );

					} catch( \Exception $sanitation_failed_exception ) {

						$sanitized = false;
						$meta_fields['sleipner_admin_notice'][$key]= array(
							'field' => $key,
							'text' =>$sanitation_failed_exception->getMessage(),
							'type' => 'error'
						);

					}
					
				}

			}

			if( $sanitized ) {

				delete_post_meta( $this->ID, 'sleipner_admin_notice' );

			} else {

				foreach( $meta_fields as $key => $value ) {

					if( $key != 'sleipner_admin_notice' ) {
						unset( $meta_fields[$key] );
					}

				}


			}




			if( empty( $this->ID ) ) {

				// Filter before creating a new post
				$fields = apply_filters( 'sleipner_pre_create_post', array( 'post_fields' => $post_fields, 'meta_fields' => $meta_fields ) );
				$post_fields = $fields['post_fields'];
				$meta_fields = $fields['meta_fields'];

				// Create the post 
				$id = wp_insert_post( $post_fields );

				apply_filters( 'sleipner_post_create_post', $post_fields, array( self::$posttype, $id ) );

				// Check if this is an error
				if( !is_wp_error( $id ) ) {
					$this->ID = $id;
				} else {
					$wp_error = true;
				}

			} else { // Update an existing post

				// Update post and meta data
				//wp_update_post( $post_fields );

			}

			// Update the meta data if there is any
			if( count( $meta_fields ) > 0 && !$wp_error ) {

				foreach( $meta_fields as $key => $value ) {

					// Update or remove the meta data
					if( isset( $value ) ) {

						// Filter to run before updating meta data value
						$value = apply_filters( 'sleipner_pre_update_meta_data', $value, $this->ID );

						update_post_meta( $this->ID, $key, $value );
						
					} else {

						delete_post_meta( $this->ID, $key );

					}

				}

			}

		}


		/**
		 * sanitize
		 * 
		 * Sanitize the meta data before saving it
		 * 
		 * @param  string $key
		 * @param  any $value
		 * 
		 * @return  $value
		 */
		private function sanitize( $key, $value = null ) {

			if( isset( $value ) && strlen( $value ) > 0 ) {

				if( in_array( $key, array_keys( $this->meta_data_field_info ) ) ) {

					$error_text = __( 'Failed to validate one or more fields.', SLEIPNER_TEXTDOMAIN );
					$error_text = apply_filters( 'sleipner_default_validation_error_text', $error_text, $key );
								
					switch( $this->meta_data_field_info[$key]['type'] ) {
						
						case 'email':
							$email = sanitize_email( $value );
							if( is_email( $email ) ) {
								
								return $email;
							
							} else {

								$error_text = isset( $this->meta_data_field_info[$key]['error_text'] ) ? $this->meta_data_field_info[$key]['error_text'] : $error_text; 
								throw new \Exception( $error_text );
								
							}

							break;
						
						case 'text':
						default:
							return sanitize_text_field( $value );
							break;

					}

				}

			}

			return $value;

		}


		/**
		 * delete
		 * 
		 * Force delete the post
		 */
		public function delete() {

			wp_delete_post( $this->ID, true );

		}


		/**
		 * trash
		 * 
		 * Move the post to trash bin
		 */
		public function trash() {

			wp_delete_post( $this->ID );

		}


		/**
		 * debug
		 * 
		 * Print the object
		 */
		public function debug() {
			
			echo '<pre>' . print_r( $this, true ) . '</pre>';

		}


		/**
		 * register
		 * 
		 * static interfaced function to use
		 * when registering the post type
		 */
		public static function register() {}

		/**
		 * register
		 * 
		 * static interfaced function to use
		 * when registering the post type
		 */
		public static function init() {}

		/**
		 * register
		 * 
		 * static interfaced function to use
		 * when registering the post type
		 */
		public static function meta_boxes() {}


		/**
		 * __get
		 * 
		 * Return the requested property
		 * 
		 * @param string $key
		 * @return unknown $value
		 */
		public function __get( $key ) {

			return isset( $this->fields[$key] ) ? $this->fields[$key] : null;

		}


		/**
		 * __set
		 * 
		 * @param string $key
		 * @param unknown $value
		 */
		public function __set( $key, $value ) {

			$this->fields[$key] = $value;

		}


		public static function admin_interface() {

			foreach( self::$meta_boxes as $box ) {

				add_meta_box( $box['id'], $box['title'], $box['callback'], $box['posttype'], $box['position'] );

			}

		}


    /**
     * fromPostObject
     * 
     * Static factory function to create an
     * event object from a wp post object
     * 
     * @param object post
     * @return object
     */
    public static function fromPostObject( $post ) {

      $meta = get_post_custom( $post->ID );

      $properties = get_object_vars( $post );

      $posttype = $post->post_type;
      
      // Set the file path to include the class
      $posttype_filename = SLEIPNER_PATH . '/classes/posttypes/class-' . str_replace( '_', '-', $posttype ) . '.php';

      // Capitalize first letter of posttype to match class name
      $splits = explode( '_', $posttype );
      if( count( $splits ) > 0 ) {
        foreach( $splits as $split_key => $split_value ) {
          $splits[$split_key] = ucfirst( $split_value );
        }
        $posttype = implode( '_', $splits );
      }

      // Require the class file if exists
      if( file_exists( $posttype_filename ) ) {
        
        require_once( $posttype_filename );

        // Create an object of the correct class
        $posttype = 'Sleipner\\Posttypes\\' . $posttype;
        $object = new $posttype();

        // Set all properites on the new object
        if( count( $properties ) > 0 ) {

          foreach( $properties as $key => $value ) {

            $object->$key = $value;

          }

        }


        // Set all meta data properties on the new object
        if( count( $meta ) > 0 ) {

          foreach( $meta as $key => $value ) {

            $object->$key = $value[0];

          }

        }

        return $object;
        
      }

    }



		/**
		 * fromId
		 * 
		 * Static factory function to create an
		 * object from the post id
		 * 
		 * @param int $id
		 * @return object
		 */
		public static function fromId( $id ) {

			$meta = get_post_custom( $id );
			$post = get_post( $id );

			$properties = get_object_vars( $post );

			$posttype = $post->post_type;
			
			// Set the file path to include the class
			$posttype_filename = SLEIPNER_PATH . '/classes/posttypes/class-' . str_replace( '_', '-', $posttype ) . '.php';

			// Capitalize first letter of posttype to match class name
			$splits = explode( '_', $posttype );
			if( count( $splits ) > 0 ) {
				foreach( $splits as $split_key => $split_value ) {
					$splits[$split_key] = ucfirst( $split_value );
				}
				$posttype = implode( '_', $splits );
			}

			// Require the class file if exists
			if( file_exists( $posttype_filename ) ) {
				
				require_once( $posttype_filename );

				// Create an object of the correct class
				$posttype = 'Sleipner\\Posttypes\\' . $posttype;
				$object = new $posttype();

				// Set all properites on the new object
				if( count( $properties ) > 0 ) {

					foreach( $properties as $key => $value ) {

						$object->$key = $value;

					}

				}


				// Set all meta data properties on the new object
				if( count( $meta ) > 0 ) {

					foreach( $meta as $key => $value ) {

						$object->$key = $value[0];

					}

				}

				return $object;
				
			}

		}


		/**
		 * matches_posttype
		 * 
		 * Check if the two given posttypes match names
		 * @param string $this_posttype
		 * @param  string $posttype
		 * 
		 * @return boolean
		 */
		public static function matches_posttype( $this_posttype, $posttype ) {

      if( $this_posttype == $posttype ) {
        return true;
      }

      return false;
    }


    /**
     * admin_notices
     * 
     * Gets any admin notices set as meta data
     * on the current post. It displays them
     * as an admin notice on the post type edit
     * screen. It also adds the field to the javascript
     * and highlights the field wich had the error in it.
     */
    public static function admin_notices(){
      
      global $post;

      if( isset( $post->ID ) ) {
 
      	$notices = get_post_meta( $post->ID, 'sleipner_admin_notice', true );
      	delete_post_meta( $post->ID, 'sleipner_admin_notice' );

      	if( isset( $notices ) && is_array( $notices ) ) {

      		$fields = array();

      		foreach( $notices as $notice ) {

      			extract( $notice );

      			if( isset( $text ) && strlen( $text ) > 0 ) {
      				echo '<div class="' . $type . '"><p>' . $text . '</p></div>';

      				$fields []= $field;

      			}

      		}

    			wp_enqueue_script( 'admin-notice-error', SLEIPNER_URL . 'assets/js/sleipner-validation.js', array( 'jquery' ), '', true );
    			wp_localize_script( 'admin-notice-error', 'SLEIPNER', array( 'validation' => $fields ) );

      	}

      }
      
    }


    /**
     * as_array
     * 
     * Return the object all properties as an array
     * 
     * @return  array
     */
    public function as_array() {

    	$properties = get_object_vars( $this );
    	if( is_array( $properties['fields'] ) ) {
    		return $properties['fields'];
    	}

    	return array();

    }


    /**
     * query
     * 
     * Wrapper function for WP Query
     * You can use exactly the same arguments
     * as WP Query.
     * 
     * @param array $args An array with arguments for the query
     * 
     * @return  The WP query object
     */
    public static function query( $args = array() ) {

    	$args = apply_filters( 'sleipner_pre_query_args', $args );
    	return new WP_Query( $args );

    }


    /**
     * authenticate_save
     * 
     * Check if a few basic things are met before
     * trying to save the post type data.
     * 
     * @param  $post the $_POST variable from child
     * 
     * @return  the result as a boolean.
     */
    public static function authenticate_save( $post ) {
    	if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return false;

      // Prevent quick edit from clearing custom fields
      if( defined('DOING_AJAX') && DOING_AJAX ) return false;

      // If there is no post type than break
      if( !isset( $post['post_type'] ) ) return false;

      return true;
    }


	}


?>