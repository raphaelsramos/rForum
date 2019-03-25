<?php

	/***
	 *	2019-02-19
	 */
	
	if( !class_exists( 'rForum_Tax_SC' ) ){
		
		class rForum_Tax_SC {
		
			public static function init(){
				
				#var_dump( 'Extranet_SC_Files_SC::init' );
				
				// add
				add_shortcode( 'forum_list', 'rForum_Tax_SC::render' );
				
				// enqueue files
				#add_action( 'wp_enqueue_scripts', 'rForum_Tax_SC::enqueues' );

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


			public static function render( $atts ){
				
				if( !is_user_logged_in() ){
					return '';
				}
				
				// user data
				$user = wp_get_current_user();
				
				// if user can moderate forum, show some extra options
				$moderators = get_forum_moderators();
				
				#echo "<pre>";
				#var_dump( [ 'moderators' => $moderators ] );
				#echo "</pre>";
				
				$is_forum_mod = in_array( $user->ID, $moderators ) || current_user_can( 'edit_post' );
				

				// get list of foruns
				$args = [
					'taxonomy' => rForum_Tax::get_slug(),
					'count' => true,
				];
				
				if( !!$is_forum_mod ){
					$args[ 'hide_empty' ] = false;
				}
				
				$args = apply_filters( 'r/forum/tax/sc/args', $args );
				
				$foruns = get_terms( $args );
				
				// deal with error
				if( empty( $foruns ) || is_wp_error( $foruns ) ){
					return '';
				}

				$html = [
					'<table class="foruns">',
						'<thead>',
							'<tr>',
								'<th scope="col">'. __( 'Forum', 'r-forum' ) .'</th>',
								'<th scope="col">'. __( 'Topics', 'r-forum' ) .'</th>',
							'</tr>',
						'</thead>',
						'<tfoot>',
							'<tr>',
								'<th scope="col">'. __( 'Forum', 'r-forum' ) .'</th>',
								'<th scope="col">'. __( 'Topics', 'r-forum' ) .'</th>',
							'</tr>',
						'</tfoot>',
						'<tbody>',
				];
				
				foreach( $foruns as $forum ){
					$item = implode( "\n", [
						'<tr>',
							'<td scope="col" class="forum-name">',
								'<h2 class="forum-name">',
									'<a href="'. get_term_link( $forum->term_id ) .'">'. $forum->name .'</a>',
								'</h2>',
								( !!$forum->description
									? apply_filters( 'the_content', $forum->description )
									: ''
								),
								'<a href="'. get_term_link( $forum->term_id ) .'" class="button outline">',
									$forum->count ? __( 'See topics in this forum', 'r-forum' ) : __( 'Add the first topic to this forum', 'r-forum' ),
								'</a>',
							'</td>',
							'<td scope="col" class="forum-comments">'. $forum->count .'</td>',
						'</tr>'
					] );
					
					$item = apply_filters( 'r/forum/tax/sc/item', $item, $forum );
					
					$html[] = $item;
				}
				$html[] = '</tbody>';
				$html[] = '</table>';
				
				#if( !!$is_forum_mod ){
				#	$html[] = '<hr />';
				#	$html[] = '<p>formulário para adicionar novo Fórum</p>';
				#}
				
				$output = implode( "\n", $html );
				
				$output = apply_filters( 'r/forum/tax/sc/output', $output, $foruns );
				
				return $output;
			}


		}
	}