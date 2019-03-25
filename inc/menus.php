<?php

	/***
	 *	2019-03-06
	 *
	 *	https://stackoverflow.com/questions/9847718/removing-dashboard-menu-options-for-the-user-role-editor
	 *	https://wordpress.stackexchange.com/questions/9211/changing-admin-menu-labels
	 */

	if( !class_exists( 'rForum_Menus' ) ){

		class rForum_Menus {

			public static function init(){

				add_action( 'admin_menu', 'rForum_Menus::add_reference_page' );
				
				add_action( 'admin_menu', 'rForum_Menus::rename_forum' );

			}

			
			public static function rename_forum(){
				
				global $menu;
				global $submenu;

				$menu[ 6 ][ 0 ] = __( 'Forums', 'r-forum' );

				$submenu[ 'edit.php?post_type='. rForum_CPT::get_slug() ][ 5 ][ 0 ] = __( 'Topics', 'r-forum' );
			}

			
			public static function add_reference_page(){
				add_submenu_page(
					'edit.php?post_type='. rForum_CPT::get_slug(),
					__( 'References', 'r-forum' ),
					__( 'References', 'r-forum' ),
					'manage_options',
					'forums-references',
					'rForum_Menus::render_reference_page'
				);
			}
			
			public static function render_reference_page(){
?>
    <div class="wrap">
        <h1><?php _e( "Forum's References", 'r-forum' ); ?></h1>
		<p><?php _e( "Here you'll find all the references about Forum, Topics, its Comments, Shortcodes and infos about rForum.", 'r-forum' ); ?></p>
<?php
	do_action( 'r/forum/pages/references' );
?>
    </div>
<?php
			}
		}
	}