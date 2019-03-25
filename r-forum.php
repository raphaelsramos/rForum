<?php
/*
Plugin Name: rForum
Plugin URI: https://www.raphaelramos.com.br/wp/plugins/r-forum/
Description: A Forum plugin for WP
Version: 0.1.0
Author: Raphael Ramos
Author URI: https://www.raphaelramos.com.br/
Requires at least: 4.8
Tested up to: 3.4.2
*/


	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;

	
	// plugin path
	if( !defined( 'R_FORUM_DIR' ) ){
		define( 'R_FORUM_DIR', plugin_dir_path( __FILE__ ) );
	}

	// plugin url
	if( !defined( 'R_FORUM_URL' ) ){
		define( 'R_FORUM_URL', plugin_dir_url( __FILE__ ) );
	}
	
	// plugin path
	if( !defined( 'R_FORUM_PATH' ) ){
		define( 'R_FORUM_PATH', dirname( plugin_basename( __FILE__ ) ) );
	}

	// plugin version
	if( !defined( 'R_FORUM_NAME' ) ){
		define( 'R_FORUM_NAME', 'r-forum' );

	}

	// plugin version
	if( !defined( 'R_FORUM_VERSION' ) ){
		define( 'R_FORUM_VERSION', '0.1.0' );

	}

	// base class
	require_once( R_FORUM_DIR .'inc/core.php' );
	
	/***
	 *	The code that runs during plugin activation.
	 *	This action is documented in includes/class-plugin-name-activator.php
	 */
	function activate_rforum() {
		do_action( 'r/forum/activate' );
	}
	register_activation_hook( __FILE__, 'activate_rforum' );


	/***
	 *	The code that runs during plugin deactivation.
	 *	This action is documented in includes/class-plugin-name-deactivator.php
	 */
	function deactivate_rforum() {
		do_action( 'r/forum/deactivate' );
	}
	register_deactivation_hook( __FILE__, 'deactivate_rforum' );


	/***
	 *	Begins execution of the plugin.
	 */
	rForum_Core::init();
