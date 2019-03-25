<?php

	/***
	 *	2019-03-06
	 *	https://rudrastyh.com/wordpress/tag-metabox-like-categories.html
	 */
	
	if( !class_exists( 'rForum_Tax' ) ){
		
		class rForum_Tax {
		
			protected static $slug = 'forum';
		
			
			public static function init(){
				
				// register tax
				add_action( 'init', 'rForum_Tax::register', 0 );
				
				add_action( 'admin_menu', 'rForum_Tax::remove_metabox' );
				
				add_action( 'admin_menu', 'rForum_Tax::add_new_metabox' );
				
				require_once( R_FORUM_DIR .'inc/tax-forum/sc.php' );
				rForum_Tax_SC::init();
				
				require_once( R_FORUM_DIR .'inc/tax-forum/fields.php' );
				rForum_Tax_Fields::init();
				
				require_once( R_FORUM_DIR .'inc/tax-forum/columns.php' );
				rForum_Tax_Columns::init();
				
				require_once( R_FORUM_DIR .'inc/tax-forum/helpers.php' );

			}


			public static function get_slug(){
				return self::$slug;
			}


			// Register Custom Post Type
			public static function register() {
			
				$labels = array(
					'name'                       => _x( 'Forums', 'Taxonomy General Name', 'r-forum' ),
					'singular_name'              => _x( 'Forum', 'Taxonomy Singular Name', 'r-forum' ),
					'menu_name'                  => __( 'Forums', 'r-forum' ),
					'all_items'                  => __( 'All Forums', 'r-forum' ),
					'parent_item'                => __( 'Parent Forum', 'r-forum' ),
					'parent_item_colon'          => __( 'Parent Forum:', 'r-forum' ),
					'new_item_name'              => __( 'New Forum Name', 'r-forum' ),
					'add_new_item'               => __( 'Add New Forum', 'r-forum' ),
					'edit_item'                  => __( 'Edit Forum', 'r-forum' ),
					'update_item'                => __( 'Update Forum', 'r-forum' ),
					'view_item'                  => __( 'View Forum', 'r-forum' ),
					'separate_items_with_commas' => __( 'Separate forums with commas', 'r-forum' ),
					'add_or_remove_items'        => __( 'Add or remove forums', 'r-forum' ),
					'choose_from_most_used'      => __( 'Choose from the most used', 'r-forum' ),
					'popular_items'              => __( 'Popular Forums', 'r-forum' ),
					'search_items'               => __( 'Search Forums', 'r-forum' ),
					'not_found'                  => __( 'Not Found', 'r-forum' ),
					'no_terms'                   => __( 'No forums', 'r-forum' ),
					'items_list'                 => __( 'Forums list', 'r-forum' ),
					'items_list_navigation'      => __( 'Forums list navigation', 'r-forum' ),
				);
				
				$args = [
					'labels'                     => $labels,
					'hierarchical'               => false,
					'public'                     => true,
					'show_ui'                    => true,
					'show_admin_column'          => true,
					'show_in_nav_menus'          => false,
					'show_tagcloud'              => false,
					'show_in_rest'               => true,
				
				];

				$args = apply_filters( 'r/forum/tax/args', $args );
				
				$cpts = apply_filters( 'r/forum/tax/associated_cpt', [] );
				
				register_taxonomy( self::get_slug(), $cpts, $args );

			}

			
			public static function remove_metabox(){
				$id = 'tagsdiv-forum'; // you can find it in a page source code (Ctrl+U)
				$post_type = rForum_CPT::get_slug(); // remove only from post edit screen
				$position = 'side';
				remove_meta_box( $id, $post_type, $position );
			}


			public static function add_new_metabox(){
				$id = 'rtagsdiv-forum'; // it should be unique
				$heading = __( 'Forums', 'r-forum' ); // meta box heading
				$callback = 'rForum_Tax::render_metabox'; // the name of the callback function
				$post_type = rForum_CPT::get_slug();
				$position = 'side';
				$pri = 'default'; // priority, 'default' is good for us
				add_meta_box( $id, $heading, $callback, $post_type, $position, $pri );
			}


			public static function render_metabox(){

				// get all blog post tags as an array of objects
				$all_tags = get_terms( [ 'taxonomy' => rForum_Tax::get_slug(), 'hide_empty' => 0 ] ); 

				// get all tags assigned to a post
				$all_tags_of_post = get_the_terms( $post->ID, rForum_Tax::get_slug() );  

				// create an array of post tags ids
				$ids = [];
				if( $all_tags_of_post ){
					foreach( $all_tags_of_post as $tag ){
						$ids[] = $tag->term_id;
					}
				}

				// HTML
				echo '<div id="'. rForum_Tax::get_slug() .'-all" class="categorydiv">';
				echo '<input type="hidden" name="tax_input['. rForum_Tax::get_slug() .'][]" value="0" />';
				echo '<ul>';
				foreach( $all_tags as $tag ){
					// unchecked by default
					$checked = "";
					// if an ID of a tag in the loop is in the array of assigned post tags - then check the checkbox
					if( in_array( $tag->term_id, $ids ) ){
						$checked = " checked='checked'";
					}
					$id = rForum_Tax::get_slug() .'-' . $tag->term_id;
					echo "<li id='{$id}'>";
					echo "<label><input type='checkbox' name='tax_input[". rForum_Tax::get_slug() ."][]' id='in-$id'". $checked ." value='$tag->slug' /> $tag->name</label><br />";
					echo "</li>";
				}
				echo '</ul></div>'; // end HTML
			}
		}
	}
