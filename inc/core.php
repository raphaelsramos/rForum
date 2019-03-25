<?php

	/***
	 *	2019-03-06
	 */

	if( !class_exists( 'rForum_Core' ) ){
		
		class rForum_Core {

			// init process
			public static function init(){

				self::load_dependencies();
				
				self::load_textdomain();

			}

			
			// plugin name get
			public static function get_name() {
				return R_FORUM_NAME;
			}

			
			// plugin version get
			public static function get_version() {
				return R_FORUM_VERSION;
			}


			// load dependencies
			public static function load_dependencies(){

				require_once( R_FORUM_DIR .'inc/tax-forum/core.php' );
				rForum_Tax::init();

				require_once( R_FORUM_DIR .'inc/cpt-topic/core.php' );
				rForum_CPT::init();
				
				require_once( R_FORUM_DIR .'inc/comments/core.php' );
				rForum_Comments::init();
				
				require_once( R_FORUM_DIR .'inc/menus.php' );
				rForum_Menus::init();

			}


			// load textdomain
			public static function load_textdomain() {
				load_plugin_textdomain(
					self::get_name(),
					false,
					R_FORUM_PATH .'/lang'
				);
			}


		}

	}
