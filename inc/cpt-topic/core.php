<?php

	/***
	 *	2019-03-06
	 *	https://wordpress.stackexchange.com/questions/94817/add-category-base-to-url-in-custom-post-type-taxonomy/188834
	 *	https://wordpress.stackexchange.com/questions/108642/permalinks-custom-post-type-custom-taxonomy-post
	 */

	if( !class_exists( 'rForum_CPT' ) ){

		class rForum_CPT {

			
			protected static $slug = 'topicos';


			public static function init(){

				// register cpt
				add_action( 'init', 'rForum_CPT::register', 0 );

				// associate with forum tax
				add_filter( 'r/forum/tax/associated_cpt', 'rForum_CPT::assoc_forum_tax' );
				add_action( 'admin_footer', 'rForum_CPT::make_forum_tax_required' );
				
				// add url rule for cpt
				add_action( 'init', 'rForum_CPT::add_url_rule' );
				
				// adjust cpt link
				add_filter( 'post_type_link', 'rForum_CPT::adjust_link', 1, 3 );
				
				
				add_action( 'pre_get_posts', 'rForum_CPT::pre_get_posts' );
				
				// flush rules on activation
				add_action( 'r/forum/activate', 'flush_rewrite_rules' );
				
				
				// add metabox for choose moderator
				// select2 for moderator
				require_once( R_FORUM_DIR .'inc/cpt-topic/metabox.php' );
				rForum_CPT_Metabox::init();
				
				// column to list moderator
				
				// proccess form to add topic
				require_once( R_FORUM_DIR .'inc/cpt-topic/admin-post.php' );
				rForum_CPT_AdminPost::init();

			}
			
			
			public static function get_slug(){
				return self::$slug;
			}


			// Register Custom Post Type
			public static function register() {
			
				$labels = [
					'name'                  => _x( 'Topics', 'Post Type General Name', 'r-forum' ),
					'singular_name'         => _x( 'Topic', 'Post Type Singular Name', 'r-forum' ),
					'menu_name'             => __( 'Topics', 'r-forum' ),
					'name_admin_bar'        => __( 'Topic', 'r-forum' ),
					'archives'              => __( 'Topic Archives', 'r-forum' ),
					'attributes'            => __( 'Topic Attributes', 'r-forum' ),
					'parent_item_colon'     => __( 'Parent Topic:', 'r-forum' ),
					'all_items'             => __( 'All Topics', 'r-forum' ),
					'add_new_item'          => __( 'Add New Topic', 'r-forum' ),
					'add_new'               => __( 'Add New Topic', 'r-forum' ),
					'new_item'              => __( 'New Topic', 'r-forum' ),
					'edit_item'             => __( 'Edit Topic', 'r-forum' ),
					'update_item'           => __( 'Update Topic', 'r-forum' ),
					'view_item'             => __( 'View Topic', 'r-forum' ),
					'view_items'            => __( 'View Topics', 'r-forum' ),
					'search_items'          => __( 'Search Topic', 'r-forum' ),
					'not_found'             => __( 'Not found', 'r-forum' ),
					'not_found_in_trash'    => __( 'Not found in Trash', 'r-forum' ),
					'featured_image'        => __( 'Featured Image', 'r-forum' ),
					'set_featured_image'    => __( 'Set featured image', 'r-forum' ),
					'remove_featured_image' => __( 'Remove featured image', 'r-forum' ),
					'use_featured_image'    => __( 'Use as featured image', 'r-forum' ),
					'insert_into_item'      => __( 'Insert into topic', 'r-forum' ),
					'uploaded_to_this_item' => __( 'Uploaded to this topic', 'r-forum' ),
					'items_list'            => __( 'Topics list', 'r-forum' ),
					'items_list_navigation' => __( 'Topics list navigation', 'r-forum' ),
					'filter_items_list'     => __( 'Filter topics list', 'r-forum' ),
				];
				
				$rewrite = [
					// 'slug'			=> rForum_Tax::get_slug() .'//'. self::get_slug(),
					'slug'			=> rForum_Tax::get_slug() .'/%forum%',
					'with_front'	=> false,
					'pages'			=> true,
					'feeds'			=> true,
				];
				
				$args = [
					'label'                 => $labels,
					'description'           => __( 'List of topics of forums', 'r-forum' ),
					'labels'                => $labels,
					'supports'              => [ 'title', 'editor', 'author', 'comments', 'revisions' ],
					'taxonomies'            => [ 'forum' ],
					'hierarchical'          => false,
					'public'                => true,
					'show_ui'               => true,
					'show_in_menu'          => true,
					'menu_position'         => 6,
					'menu_icon'             => 'dashicons-format-chat',
					'show_in_admin_bar'     => true,
					'show_in_nav_menus'     => true,
					'can_export'            => true,
					'has_archive'           => true,
					'exclude_from_search'   => false,
					'publicly_queryable'    => true,
					'rewrite'               => $rewrite,
					'capability_type'       => 'page',
					'show_in_rest'          => true,
				];
				
				$args = apply_filters( 'r/forum/cpt/args', $args );
				
				register_post_type( self::get_slug(), $args );

			}


			public static function assoc_forum_tax( $cpts ){
				$cpts[] = self::get_slug();
				return $cpts;
			}
			
			
			public static function make_forum_tax_required(){
				global $typenow;
				if( $typenow == rForum_CPT::get_slug() ){
?>
<script type="text/javascript">
    ( function( $ ){
		$( function(){

			var $tx = '<?php echo rForum_Tax::get_slug() ?>',
				$scope = $( '#'+ $tx +'-all > ul' );
			
			if( $scope.length ){				
				$( '#publish' ).on( 'click', function(){
					if( !$scope.find( 'input:checked' ).length ){
						alert( '<?php _e( 'You need to select a Forum.', 'r-forum' ) ?>' );
						return false;
					}
				} );
			}

		});
	})( jQuery );
</script>
<?php
				}
			}


			public static function add_url_rule(){
				add_rewrite_rule(
					'^'. rForum_Tax::get_slug() .'/([^/]*)/([^/]*)/?',
					'index.php?post_type='. rForum_CPT::get_slug() .'&name=$matches[2]', 'top' );
			}


			public static function adjust_link( $post_link, $id = 0 ){
				$post = get_post( $id );
				if( is_object( $post ) ){
					$terms = wp_get_object_terms( $post->ID, rForum_Tax::get_slug() );
					if( $terms ){
						return str_replace( '%forum%', $terms[0]->slug, $post_link );
					}
				}
				return $post_link;
			}

			
			public static function pre_get_posts( $query ){
				if( $query->is_main_query() && !is_admin() && is_tax( 'forum' ) ){
					$query->set( 'post_status', [ 'publish', 'pending' ] );
				}
			}

		}
	}
