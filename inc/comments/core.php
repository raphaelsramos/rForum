<?php

	/***
	 *	2019-03-06
	 *	https://wordpress.stackexchange.com/questions/94817/add-category-base-to-url-in-custom-post-type-taxonomy/188834
	 *	https://wordpress.stackexchange.com/questions/108642/permalinks-custom-post-type-custom-taxonomy-post
	 */

	if( !class_exists( 'rForum_Comments' ) ){

		class rForum_Comments {

			public static function init(){
				// proccess form to add topic
				require_once( R_FORUM_DIR .'inc/comments/admin-post.php' );
				rForum_Comments_AdminPost::init();

			}

		}
	}
