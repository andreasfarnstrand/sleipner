<?php

	namespace Sleipner\Core;

	class Util {

		public static function debug() {

			$args = func_get_args();

			if( count( $args ) > 0 ) {

				foreach( $args as $arg ) {

					echo '<pre>' . print_r( $arg, true ) . '</pre>';

				}

			}
 

		}


		public static function sanitize_text_field( $value = null ) {

			if( !empty( $value ) ) {

				return sanitize_text_field( $value );

			}

			return null;

		}

	}

?>