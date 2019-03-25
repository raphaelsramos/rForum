<?php

	/***
	 *	2019-02-18
	 *
	 *	Refs:
	 *		https://www.isitwp.com/add-remove-wordpress-user-roles/
	 *		https://codex.wordpress.org/Roles_and_Capabilities
	 */

	if( !class_exists( 'rForum_UserRoles' ) ){

		class rForum_UserRoles {

			public static function init(){

				#add_action( 'init', 'rForum_UserRoles::manage' );

				#add_action( 'init', 'rForum_UserRoles::create' );

				add_action( 'init', 'rForum_UserRoles::remove' );

			}

			/*
			public static function manage(){
				#global $wp_roles;

				#if( ! isset( $wp_roles ) ){
				#	$wp_roles = new WP_Roles();
				#}

				//You can list all currently available roles like this...
				//$roles = $wp_roles->get_names();
				//print_r($roles);

				
				//You can replace "administrator" with any other role "editor", "author", "contributor" or "subscriber"...
				#$wp_roles->roles[ 'editor' ][ 'name' ] =		__( 'Manager', 'extranet-seven-capital' );
				#$wp_roles->role_names[ 'editor' ] =				__( 'Manager', 'extranet-seven-capital' );
				
				#$wp_roles->roles[ 'subscriber' ][ 'name' ] =	__( 'Shareholder', 'extranet-seven-capital' );
				#$wp_roles->role_names[ 'subscriber' ] =			__( 'Shareholder', 'extranet-seven-capital' );
				
				// add "see_all_menus" to this role object
				$admin = get_role( 'administrator' );
					$admin->add_cap( 'moderate_forum' );
					$admin->add_cap( 'moderate_topic' );
					$admin->add_cap( 'set_topic_moderator' );
				
				$editor = get_role( 'editor' );
					$editor->add_cap( 'moderate_forum' );
					$editor->add_cap( 'moderate_topic' );
					$editor->add_cap( 'set_topic_moderator' );

			}
			
			public static function create(){
				add_role(
					'moderator_forum',
					__( 'Forum Moderator', 'r-forum' ),
					[
						'moderate_forum',
						'moderate_topic',
						'set_topic_moderator',
						'moderate_comments',
					]
				);
				
				add_role(
					'moderator_topic',
					__( 'Topic Moderator', 'r-forum' ),
					[
						'moderate_topic',
						'moderate_comments',
					]
				);
			}
			*/
			
			public static function remove(){
				#remove_role( 'editor' );
				#remove_role( 'author' );
				#remove_role( 'contributor' );
				#remove_role( 'subscriber' );
				remove_role( 'moderator_forum' );
				remove_role( 'moderator_topic' );
			}

		}
	}