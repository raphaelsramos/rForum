<?php

	/**
	 * 2019-02-15
	 *
	 *	Refs:
	 *		https://wordpress.stackexchange.com/questions/77658/custom-columns-for-taxonomy-list-table
	 **/
	
	if( ! class_exists( 'rForum_Tax_Columns' ) ) {

		class rForum_Tax_Columns {
 
			/***
			 *	Initialize the class and start calling our hooks and filters
			 */
			public static function init(){
				
				$tax = rForum_Tax::get_slug();
				
				add_filter( "manage_edit-{$tax}_columns", 'rForum_Tax_Columns::manage_columns');
				
				add_filter( "manage_{$tax}_custom_column", 'rForum_Tax_Columns::column_content', 10, 3 );
			}
			
			
			public static function manage_columns( $cols ){
				$cols[ 'moderators' ] = __( 'Moderators', 'r-forum' );	// add
				$cols[ 'posts' ] = __( 'Topics', 'r-forum' );			// rename
				unset( $cols[ 'description' ], $cols[ 'slug' ] );		// remove
				return $cols;
			}
			
			
			public static function column_content( $content, $column_name, $term_id ){
				switch( $column_name ){
					case 'moderators':
						$content = '-';
						if( $moderators = (array) get_term_meta( $term_id, 'moderator', 1 ) ){
							$users = [];
							foreach( $moderators as $user_id ){
								$user = get_user_by( 'id', $user_id );
								$users[] = '<a href="'. get_edit_user_link( $user_id ) .'">'. $user->display_name .'</a>';
							}
							$content = implode( ', ', $users );
						}
						break;
        
					default:
						break;
				}
				return $content;
			}

		}

	}
