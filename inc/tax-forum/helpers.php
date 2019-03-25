<?php

	/***
	 *	2019-03-17
	 */

	function get_forum_moderators( $id = false ){
		$args = [
			'taxonomy' => rForum_Tax::get_slug(),
			'count' => true,
			'hide_empty' => false,
			'fields' => 'ids',
		];
		
		if( !!$id ){
			$foruns = get_term( $id, rForum_Tax::get_slug() );
		}
		else {
			$foruns = get_terms( $args );
		}
		
		#echo "<pre>";
		#var_dump( [ 'foruns' => $foruns ] );
		#echo "</pre>";
		
		if( empty( $foruns ) || is_wp_error( $foruns ) ){
			return [];
		}

		$list = [];
		
		foreach( $foruns as $forum_id ){
			$moderator = (array) get_term_meta( $forum_id, 'moderator', 1 );
			$list = array_merge( $list, $moderator );
		}
		
		$list = array_unique( $list );
		
		#echo "<pre>";
		#var_dump( [ 'list' => $list ] );
		#echo "</pre>";
		
		return $list;

	}

	
	function count_user_comments( $user_id ) {
		global $wpdb;
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( comment_ID ) FROM {$wpdb->comments} WHERE user_id = %d", $user_id ) );
		return $count;
	}

	
	function can_add_topic_to_forum( $forum_id ) {
		$moderators = (array) get_term_meta( $forum_id, 'moderator', 1 );
		$user = wp_get_current_user();
		$can = in_array( $user->ID, $moderators ) || in_array( 'administrator', $user->roles ) || in_array( 'editor', $user->roles );
		$can = apply_filters( 'can_add_topic_to_forum', $can, $forum_id );
		return $can;
	}
	
	function can_add_comment_to_topic( $topic_id ){
		$moderators = (array) get_term_meta( $topic_id, '_moderator', 1 );
		$user = wp_get_current_user();
		$can = in_array( $user->ID, $moderators ) || in_array( 'administrator', $user->roles ) || in_array( 'editor', $user->roles );
		$can = apply_filters( 'can_add_topic_to_forum', $can, $forum_id );
		return $can;
	}
