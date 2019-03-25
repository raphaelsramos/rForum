<?php

	/***
	 *	2019-03-17
	 *	https://www.sitepoint.com/handling-post-requests-the-wordpress-way/
	 */

	if( !class_exists( 'rForum_Comments_AdminPost' ) ){

		class rForum_Comments_AdminPost {


			public static function init(){

				add_action( 'admin_post_mod_mng_comment', 'rForum_Comments_AdminPost::manage' );

			}
			
			
			public static function manage(){
				
				#echo "<pre>";
				#var_dump( $_REQUEST );
				#echo "</pre>";
				
				// referer
				if( !isset( $_REQUEST[ 'referer' ] ) || empty( $_REQUEST[ 'referer' ] ) ){
					wp_redirect( home_url() .'?mod_mng_comment_error=missing_referer' );
					die();
					#die( 'missing referer' );
				}
				$referer = esc_url( $_REQUEST[ 'referer' ] );
				
				// cid
				if( !isset( $_REQUEST[ 'c_id' ] ) || empty( $_REQUEST[ 'c_id' ] ) ){
					wp_redirect( $referer .'?mod_mng_comment_error=missing_cid' );
					die();
					#die( 'missing c_id' );
				}
				$comment_id = intval( $_REQUEST[ 'c_id' ] );

				// status
				if( !isset( $_REQUEST[ 'st' ] ) || empty( $_REQUEST[ 'st' ] ) ){
					wp_redirect( $referer .'?mod_mng_comment_error=missing_st' );
					die();
					#die( 'missing st' );
				}
				if( !in_array( $_REQUEST[ 'st' ], [ 'approve', 'trash' ] ) ){
					wp_redirect( $referer .'?mod_mng_comment_error=invalid_st' );
					die();
					#die( 'invalid st' );
				}
				$status = $_REQUEST[ 'st' ];

				// get comment
				$comment = get_comment( $comment_id );
				if( is_null( $comment ) ){
					wp_redirect( $referer .'?mod_mng_comment_error=invalid_cid' );
					die();
					#die( 'invalid cid' );
				}
				
				// already approved / disapproved
				if(
					( $status == 'approve' && $comment->comment_approved != '0' )
					|| ( $status == 'disapprove' && $comment->comment_approved == '0' )
				){
					wp_redirect( $referer .'?mod_mng_comment_success=true' );
					die();
					#die( 'status already '. $status );
				}
				
				// check if user can manage comments
				$user = wp_get_current_user();
				$moderators = (array) get_post_meta( $comment->comment_post_ID, '_moderator', 1 );
				if( !in_array( $user->ID, $moderators ) && !in_array( 'administrator', $user->roles ) && !in_array( 'editor', $user->roles ) ){
					wp_redirect( $referer .'?mod_mng_comment_error=missing_permissions' );
					die();
					#die( 'missing permissions' );
				}

				// change status
				if( !wp_set_comment_status( $comment_id, $status ) ){
					wp_redirect( $referer .'?mod_mng_comment_error=change_status' );
					die();
					#die( 'error on set status' );
				}
				
				wp_redirect( $referer .'?mod_mng_comment_success='. $status );
				die();
				#die( 'success' );

			}


		}
	}
