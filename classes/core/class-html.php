<?php

  /**
   * HTML
   * 
   * The class converts an array into html
   * 
   * @package Sleipner
   * @author  Andreas Färnstrand <andreas@farnstranddev.se>
   */

  namespace Sleipner\Core;

  class HTML {

    /**
     * fromArray
     * 
     * A static function that traverses an array with
     * html elements and converts it into a HTML string.
     * 
     * @param  $array The array that contains 
     * the elements to be converted to HTML.
     * 
     * @return the html string to return
     */
    public static function fromArray( $array ) {

      // No type? Then don't parse the array element
      if( !isset( $array['type'] ) ) return '';

      $type = $array['type'];
      $attributes = '';
      $html = '';

      // Parse and setup element attributes
      if( isset( $array['attributes']) && count( $array['attributes'] ) > 0 ) {
        
        foreach( $array['attributes'] as $attribute_key => $attribute_value ) {
          
          $attributes .= sprintf( ' %s="%s" ', $attribute_key, $attribute_value );
        
        }

      }
      $html .= "<$type $attributes>";


      // Parse the value. Forward it to function if it is an array to be parsed
      if( isset( $array['value'] ) && is_array( $array['value'] ) && count( $array['value'] ) > 0 ) {

        foreach( $array['value'] as $element ) {
          
          $html .= self::fromArray( $element );

        }

      } else {

        // The value is not an array. Get the value or set it as null
        $html .= isset($array['value']) ? $array['value'] : NULL;

      }

      // Close the element
      $html .= "</$type>";

      return $html;

    }

  }