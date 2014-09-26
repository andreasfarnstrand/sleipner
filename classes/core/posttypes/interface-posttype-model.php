<?php

	/**
	 * Interface_Model
	 * 
	 * Inteface for the Model class.
	 * It specifies a few necessary function for posttypes
	 * extending the model.
	 * 
	 * @package Sleipner
	 * @author Andreas Färnstrand <andreas@farnstranddev.se>
	 */

	namespace Sleipner\Core\Posttypes;

	interface Interface_Posttype_Model {

		public static function init();
		public static function register();
		public static function meta_boxes();

	}


?>