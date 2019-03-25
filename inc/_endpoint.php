<?php

	/***
	 *	2019-02-21
	 *
	 *	REF:
	 *		http://www.coderrr.com/create-an-api-endpoint-in-wordpress/
	 *		https://www.media-division.com/php-download-script-with-resume-option/
	 */

	if( !class_exists( 'Extranet_SC_Downloads_EP' ) ){
		
		class Extranet_SC_Downloads_EP {
	
			public static function init(){
				
				add_action( 'init', 'Extranet_SC_Downloads_EP::add_endpoint', 0 );
				
				add_filter( 'query_vars', 'Extranet_SC_Downloads_EP::add_query_vars', 0 );
				
				add_action( 'parse_request', 'Extranet_SC_Downloads_EP::sniff_requests', 0 );
				
				add_action( 'Extranet_SC/activate', 'flush_rewrite_rules' );
	
			}


			public static function add_endpoint(){
				add_rewrite_rule( '^download/([^/]*)/?', 'index.php?__extranet_sc=1&esc_file_id=$matches[1]', 'top' );
			}


			public static function add_query_vars($vars){
				$vars[] = '__extranet_sc';
				$vars[] = 'esc_file_id';
				return $vars;
			}


			public static function sniff_requests(){
				global $wp;
				if( isset( $wp->query_vars[ '__extranet_sc' ] ) ){
					self::handle_request();
					exit;
				}
			}


			protected static function handle_request(){
				
				// file_id foi fornecido?
				global $wp;
				$post_id = $wp->query_vars[ 'esc_file_id' ];
				
				$post_id = apply_filters( 'Extranet_SC/download/post_id_dec', $post_id );
				
				if( !$post_id ){
					header( "X-Handle-Reason: post_id missing" );
					header( $_SERVER[ "SERVER_PROTOCOL" ]." 404 Not Found", true, 404 );
					exit;
				}

				
				// post existe?
				$post = get_post( $post_id );
				
				if( !$post ){
					header( "X-Handle-Reason: post not found" );
					header( $_SERVER[ "SERVER_PROTOCOL" ]." 404 Not Found ", true, 404 );
					exit;
				}
				
				
				// post existe?
				$post = get_post( $post_id );
				
				if( Extranet_SC_Files_CPT::get_slug() !== get_post_type( $post_id ) ){
					header( "X-Handle-Reason: post is not a arquivo" );
					header( $_SERVER[ "SERVER_PROTOCOL" ]." 404 Not Found ", true, 404 );
					exit;
				}
				
				
				$company_tax = Extranet_SC_Companies_Tax::get_slug();
				
				// usuário possui acesso ao post?
				$user_company = (int) get_user_meta( get_current_user_id(), '_company', 1 );
				$post_company = get_the_terms( $post_id, $company_tax )[ 0 ]->term_id;
				
				$companies = [ $post_company ];
				
				if( $post_company[ 0 ]->parent !== '0' ){
					$companies = array_merge( $companies, get_ancestors( $post_company, $company_tax ) );
				}
				
				if( $company_childs = get_terms( [ 'taxonomy' => $company_tax, 'fields' => 'ids', 'child_of' => $post_company ] ) ){
					$companies = array_merge( $companies, $company_childs );
				}

				if( !in_array( $user_company, $companies ) ){
					header( "X-Handle-Reason: user company not allowed - ". $user_company ." / ". implode( ',', $companies ) );
					header( $_SERVER[ "SERVER_PROTOCOL" ]." 404 Not Found ", true, 404 );
					exit;
				}
				
				
				// get file
				// $post_file = get_field( 'arquivo', $post_id );
				$post_file = get_post_meta( $post_id, '_file', true );
				if( !$post_file ){
					header( "X-Handle-Reason: post has not file" );
					header( $_SERVER[ "SERVER_PROTOCOL" ]." 404 Not Found ", true, 404 );
					exit;
				}
				$post_file = json_decode( $post_file, 1 );
				
				
				// check if file exists
				$file_path = get_attached_file( $post_file[ 'id' ] );
				if( !$file_path ){
					header( "X-Handle-Reason: file dont exists" );
					header( $_SERVER[ "SERVER_PROTOCOL" ]." 404 Not Found", true, 404 );
					exit;
				}
				
				if( !is_file( $file_path ) ){
					header( "X-Handle-Reason: file is missing" );
					header( $_SERVER[ "SERVER_PROTOCOL" ]." 500 Internal Server Error", true, 500 );
					exit;
				}
				
				
				// turn off compression on the server
				if( function_exists( 'apache_setenv' ) ){
					@apache_setenv( 'no-gzip', 1 );
				}
				@ini_set( 'zlib.output_compression', 'Off' );
				
				
				// get file data
				$path_parts = pathinfo( $file_path );
				
				$file_name  = $path_parts[ 'basename' ]; 
				
				$file_ext = $path_parts[ 'extension' ];
				
				$file_size  = filesize( $file_path );
	
				$file = @fopen( $file_path, "rb" );
				
				
				// check file readable
				if( !$file ){
					header( "X-Handle-Reason: file not accessible" );
					header( $_SERVER[ "SERVER_PROTOCOL" ]." 500 Internal Server Error", true, 500 );
					exit;
				}
				
				// set the headers, prevent caching
				header( "Pragma: public" );
				header( "Expires: -1" );
				header( "Cache-Control: public, must-revalidate, post-check=0, pre-check=0" );
				header( "Content-Disposition: attachment; filename=\"{$file_name}\"" );
				// header( "Content-Type: ". $attach[ 'post_mime_type' ] );
				header( "Content-Type: ". mime_content_type( $file_path ) );
				
				
				// check if http_range is sent by browser (or download manager)
				$range = '';
				if( isset( $_SERVER[ 'HTTP_RANGE' ] ) ){
					
					list( $size_unit, $range_orig ) = explode( '=', $_SERVER[ 'HTTP_RANGE' ], 2 );
					
					if( $size_unit != 'bytes' ){
						//multiple ranges could be specified at the same time, but for simplicity only serve the first range
						//http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
						header( $_SERVER[ "SERVER_PROTOCOL" ] .' 416 Requested Range Not Satisfiable' );
						exit;
					}
					
					list( $range, $extra_ranges ) = explode( ',', $range_orig, 2 );

				}
				
				
				//figure out download piece from range (if set)
				list( $seek_start, $seek_end ) = explode( '-', $range, 2 );
		 
				
				//set start and end based on range (if set), else set defaults
				//also check for invalid ranges.
				$seek_end   = ( empty( $seek_end ) )
							? ( $file_size - 1 )
							: min( abs( intval( $seek_end ) ), ( $file_size - 1 ) )
							;
				$seek_start = ( empty( $seek_start ) || $seek_end < abs( intval( $seek_start ) ) )
							? 0
							: max( abs( intval( $seek_start ) ), 0 )
							;
		 
				
				//Only send partial content header if downloading a piece of the file (IE workaround)
				if( $seek_start > 0 || $seek_end < ( $file_size - 1 ) ){
					header( 'HTTP/1.1 206 Partial Content' );
					header( 'Content-Range: bytes '. $seek_start .'-'. $seek_end .'/'. $file_size );
					header( 'Content-Length: '. ( $seek_end - $seek_start + 1 ) );
				}
				else {
					header( "Content-Length: {$file_size}" );
				}
				
				header( 'Accept-Ranges: bytes' );
 
				set_time_limit(0);
				fseek( $file, $seek_start );

				while( !feof( $file ) ){
					print( @fread( $file, 1024 * 8 ) );
					ob_flush();
					flush();
					if( connection_status() != 0 ){
						@fclose( $file );
						exit;
					}
				}

				
				// file save was a success
				@fclose( $file );
				exit;

			}
			
			
			function output_json( $response ){
				header( 'content-type: application/json; charset=utf-8' );
				echo json_encode( $response );
				exit;
			}

		}
	}
