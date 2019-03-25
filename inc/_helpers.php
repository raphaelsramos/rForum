<?php

	/***
	 *	2019-02-15
	 */
	
	if( !class_exists( 'Extranet_SC_Files_Helpers' ) ){
		
		class Extranet_SC_Files_Helpers {

			public static function enc_post_id( $post_id ){
				$post_id = Extranet_SC_Files_CPT::get_slug() .'-'. $post_id;
				$post_id = base64_encode( $post_id );
				$post_id = str_replace( '=', '', $post_id );
				$post_id = strrev( $post_id );
				return $post_id;
			}
			
			
			public static function dec_post_id( $post_id ){
				$post_id = strrev( $post_id );
				$post_id = base64_decode( $post_id );
				$post_id = str_replace( Extranet_SC_Files_CPT::get_slug() .'-', '', $post_id );
				return $post_id;
			}
			
			
			public static function formatBytes( $bytes, $precision = 2 ){ 
				$units = array( 'B', 'KB', 'MB', 'GB', 'TB' ); 

				$bytes = max( $bytes, 0 ); 
				$pow = floor( ( $bytes ? log( $bytes ) : 0) / log( 1024 ) ); 
				$pow = min( $pow, count( $units ) - 1 ); 

				// Uncomment one of the following alternatives
				// $bytes /= pow(1024, $pow);
				$bytes /= (1 << (10 * $pow)); 

				return round( $bytes, $precision ) . ' ' . $units[ $pow ]; 
			} 

		}
	}
