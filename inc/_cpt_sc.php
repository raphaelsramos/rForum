<?php

	/***
	 *	2019-02-19
	 */
	
	if( !class_exists( 'rForum_CPT_SC' ) ){
		
		class rForum_CPT_SC {
		
			public function init(){
				
				#var_dump( 'Extranet_SC_Files_SC::init' );
				
				// add
				add_shortcode( 'topics',	'rForum_CPT_SC::render' );
				add_shortcode( 'topicos',	'rForum_CPT_SC::render' );
				
				// enqueue files
				add_action( 'wp_enqueue_scripts', 'rForum_CPT_SC::enqueues' );

			}


			public static function enqueues() {
				wp_enqueue_style(
					rForum_Core::get_plugin_name() .'-cpt-sc',
					EXTRANET_SC_URL .'assets/css/cpt/sc.css',
					[],
					rForum_Core::get_version(),
					'all'
				);

				wp_enqueue_script(
					rForum_Core::get_plugin_name() .'-cpt-sc',
					EXTRANET_SC_URL .'assets/js/cpt/sc.js',
					[ 'jquery' ],
					rForum_Core::get_version(),
					true
				);
			}


			function render( $atts ){
				
				$user = wp_get_current_user();

				$files = [];

				$html = [];
				
				// set file tax query
				$company = get_user_meta( $user->ID, '_company', true );
				
				$tax_query = [
					[
						'taxonomy' => 'company',
						'terms' => $company
					]
				];
				
				
				// deal with level tax
				if( $level = get_user_meta( $user->ID, '_level', true ) ){
					$tax_query[] = [
						'taxonomy' => 'level',
						'terms' => $level
					];
				}
				
				
				// get files
				$q = new WP_Query( [
					'post_type' => Extranet_SC_Files_CPT::get_slug(),
					'posts_per_page' => -1,
					'tax_query' => $tax_query,
					'no_found_rows' => true, // counts posts, remove if pagination required
					'update_post_term_cache' => false, // grabs terms, remove if terms required (category, tag...)
					'update_post_meta_cache' => false, // grabs post meta, remove if post meta required
				] );

				if( $q->have_posts() ){
					$html[] = '<div class="arquivos-list">';
					while( $q->have_posts() ){
						$q->the_post();
						
						$company_id = '';
						$company_name = '';
						$company_slug = '';
						// $companies = get_the_category();
						$companies = get_the_terms( get_the_ID(), 'company' );
						
						if( count( $companies ) ){
							$company_id = $companies[ 0 ]->term_id;
							$company_name = $companies[ 0 ]->name;
							$company_slug = $companies[ 0 ]->slug;
						}
						
						// $file = get_field( 'arquivo' );
						$file = get_post_meta( get_the_ID(), '_file', true );
						$file = json_decode( $file, true );
						$file_path = get_attached_file( $file[ 'id' ] );
						$get_post = get_post( $file[ 'id' ] );
						
						$file_meta = wp_get_attachment_metadata( $file[ 'id' ] );
						
						// $file_data = get_file_data( $file[ 'id' ] );
						
						#$file_name_arr = explode( $file[ 'url' ], '/' );
						#$file_name = end( $file_name_arr );
						$file_name = end( explode( '/', $file[ 'url' ] ) );
						
						$file_size = size_format( filesize( $file_path ), 2 );
						
						#echo "<pre>";
						#var_dump( [
						#	'file' => $file,
						#	'path' => $file_path,
						#	'post' => $get_post,
							#'meta' => $meta_data,
							// 'data' => $file_data,
						#	'data' => [
						#		'name' => $file_name,
						#		'size' => $file_size,
						#	],
						#] );
						#echo "</pre>";
						
						$post_id = apply_filters( 'Extranet_SC/download/post_id_enc', get_the_id() );

						$html[] = apply_filters( 'Extranet_SC/files/sc/item_html', implode( "\n", [
							'<details class="file-item" data-company="'. $company_slug .'" data-company-id="'. $company_id .'">',
								'<summary>',
									'<span class="file-company_name">'. $company_name .'</span>',
									'<span class="file-date">'. get_the_date() .'</span>',
									'<span class="file-name">'. get_the_title() .'</span>',
								'</summary>',
								'<div class="file-content">',
									'<div class="file-descr">',
										apply_filters( 'the_content', get_the_content() ),
									'</div>',
									'<p class="file-download">',
										'<a href="'. home_url( 'download/' ) . $post_id .'/" download target="_blank">',
											#'<img src="'. $file[ 'icon' ] .'" alt="'. $file[ 'mime_type' ] .'" />',
											// '<span>'. $file[ 'filename' ] .'</span>',
											// '<span>'. $get_post->post_title .'</span>',
											'<span>'. $file_name .'</span>',
											// ' ('. Extranet_SC_Files_Helpers::formatBytes( $file[ 'filesize' ] ) .')',
											' ('. $file_size .')',
										'</a>',
									'</p>',
								'</div>',
							'</details>'
						] ) );
					}
					wp_reset_postdata();
					$html[] = '</div>';
				}
				else {
					$html[] = '<p class="warning">'. __( 'No files found.', 'extranet-seven-capital' ) .'</p>';
				}
				
				
				return implode( "\n", $html );
			}


		}
	}