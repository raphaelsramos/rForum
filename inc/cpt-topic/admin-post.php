<?php

	/***
	 *	2019-03-17
	 *	https://www.sitepoint.com/handling-post-requests-the-wordpress-way/
	 */

	if( !class_exists( 'rForum_CPT_AdminPost' ) ){

		class rForum_CPT_AdminPost {

			
			protected static $slug = 'topicos';


			public static function init(){

				add_action( 'admin_post_add_topic', 'rForum_CPT_AdminPost::process' );
				
				add_action( 'admin_post_approve_topic', 'rForum_CPT_AdminPost::approve' );
				
				add_action( 'admin_post_reprove_topic', 'rForum_CPT_AdminPost::reprove' );

			}
			
			
			public static function process(){
				
				$referer = esc_url( $_POST[ 'referer' ] );
				
				if( !isset( $_POST[ 'forum' ] ) || empty( $_POST[ 'forum' ] ) ){
					wp_redirect( $referer .'?error=missing_forum' );
					die();
				}

				if( !isset( $_POST[ 'title' ] ) || empty( $_POST[ 'title' ] ) ){
					wp_redirect( $referer .'?error=missing_title' );
					die();
				}
				
				if( !isset( $_POST[ 'content' ] ) || empty( $_POST[ 'content' ] ) ){
					wp_redirect( $referer .'?error=missing_content' );
					die();
				}
				
				if( !isset( $_POST[ 'add-topic-nonce' ] ) || !wp_verify_nonce( $_POST[ 'add-topic-nonce' ], 'r-forum-add-topic' ) ){
					wp_redirect( $referer .'?error=invalid_nonce' );
					die();
				}

				$forum_id = intval( $_POST[ 'forum' ] );
				#$forum = get_term( $forum_id, rForum_Tax::get_slug() );
				$moderators = (array) get_term_meta( $forum_id, 'moderator', 1 );
				
				$user = wp_get_current_user();
				$is_forum_mod = in_array( $user->ID, $moderators ) || in_array( 'administrator', $user->roles ) || in_array( 'editor', $user->roles );

				$data = [
					'post_title'	=> wp_strip_all_tags( $_POST[ 'title' ] ),
					'post_content'	=> $_POST[ 'content' ], 
					'post_status'	=> $is_forum_mod ? 'publish' : 'pending', 
					'post_type'		=> rForum_CPT::get_slug(),
				];
				
				$post_id = wp_insert_post( $data, true );
				if( is_wp_error( $post_id ) ){
					wp_redirect( referer .'?error=insert_post' );
					die();
				}

				wp_set_object_terms( $post_id, $forum_id, rForum_Tax::get_slug() );

				wp_redirect( $referer .'?success=true' );

			}
			
			
			public static function approve(){
				
				$referer = esc_url( $_GET[ 'referer' ] );
				
				if( !isset( $_GET[ 'topic_id' ] ) || empty( $_GET[ 'topic_id' ] ) ){
					wp_redirect( $referer .'?process_topic=missing_topic_id' );
					die();
				}

				$topic_id = intval( $_GET[ 'topic_id' ] );

				$forum_id = wp_get_post_terms( $topic_id, rForum_Tax::get_slug(), [ 'fields' => 'ids' ] );
				$moderators = (array) get_term_meta( $forum_id[ 0 ], 'moderator', 1 );
				
				$user = wp_get_current_user();
				if( !can_add_topic_to_forum( $forum_id[ 0 ] ) ){
					wp_redirect( $referer .'?process_topic=missing_permission' );
					die();
				}

				$update = wp_update_post( [ 'ID' => $topic_id, 'post_status' => 'publish' ], 1 );
				if( is_wp_error( $post_id ) ){
					wp_redirect( $referer .'?process_topic=update_post' );
					die();
				}

				wp_set_object_terms( $post_id, $forum_id, rForum_Tax::get_slug() );

				wp_redirect( $referer .'?approve_success=1' );
				die();

			}
			
			
			public static function reprove(){
				
				$referer = esc_url( $_GET[ 'referer' ] );
				
				if( !isset( $_GET[ 'topic_id' ] ) || empty( $_GET[ 'topic_id' ] ) ){
					wp_redirect( $referer .'?process_topic=missing_topic_id' );
					die();
				}

				$topic_id = intval( $_GET[ 'topic_id' ] );

				$forum_id = wp_get_post_terms( $topic_id, rForum_Tax::get_slug(), [ 'fields' => 'ids' ] );
				$moderators = (array) get_term_meta( $forum_id[ 0 ], 'moderator', 1 );
				
				$user = wp_get_current_user();
				if( !can_add_topic_to_forum( $forum_id[ 0 ] ) ){
					wp_redirect( $referer .'?process_topic=missing_permission' );
					die();
				}

				$update = wp_update_post( [ 'ID' => $topic_id, 'post_status' => 'draft' ], 1 );
				if( is_wp_error( $post_id ) ){
					wp_redirect( $referer .'?process_topic=update_post' );
					die();
				}

				wp_set_object_terms( $post_id, $forum_id, rForum_Tax::get_slug() );

				wp_redirect( $referer .'?reprove_success=1' );
				die();

			}
			
		}
	}
