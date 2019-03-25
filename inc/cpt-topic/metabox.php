<?php

	/***
	 *	2019-03-17
	 *
	 *	REFS:
	 *		- https://allisontarr.com/2017/11/17/custom-meta-boxes-media/
	 *		- https://gist.github.com/cferdinandi/86f6e326b30b8b5416c0a7e43271efa6
	 *		- https://code.tutsplus.com/articles/attaching-files-to-your-posts-using-wordpress-custom-meta-boxes-part-1--wp-22291
	 *		- https://developer.wordpress.org/plugins/metadata/custom-meta-boxes/
	 */

	if( !class_exists( 'rForum_CPT_Metabox' ) ){

		class rForum_CPT_Metabox {


			public static function init(){

				add_action( 'admin_enqueue_scripts', 'rForum_CPT_Metabox::enqueues', 10, 1 );
				
				// add metabox
				add_action( 'add_meta_boxes', 'rForum_CPT_Metabox::add' );
				
				add_action( 'save_post', 'rForum_CPT_Metabox::save' );
				

			}

			
			public static function enqueues(){
				
				#$screen = get_current_screen();
				
				#if( is_object( $screen ) && in_array( $screen->post_type, [ rForum_CPT::get_slug() ] ) ){

					wp_enqueue_style(	'select2',	'//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
					wp_enqueue_script(	'select2',	'//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', [ 'jquery' ] );
					
					wp_enqueue_script(
						rForum_Core::get_name() .'-cpt-metabox',
						R_FORUM_URL .'assets/js/admin/cpt/metabox.js',
						[ 'jquery' ],
						rForum_Core::get_version(),
						true
					);
				#}
			}

			
			public static function add() {
				add_meta_box( 
					'esc_files_file',				// id
					__( 'Moderators', 'r-forum' ),	// title
					'rForum_CPT_Metabox::render',	// cb
					rForum_CPT::get_slug(),			// screen
					'normal'						// context
					// 'default',					// priority
					// null,						// cb_args
				);
			}

			public static function render( $post ){
		
				$moderator = (array) get_post_meta( $post->ID, '_moderator', 1 );

?>
				<select name="moderator[]" id="topic_moderator" multiple>
<?php
				if( $users = get_users( [
					// 'role__in' => [ 'moderator_forum', 'moderator_topic', 'author', 'contributor', 'subscriber' ],
					'orderby' => 'display_name',
					'order' => 'ASC',
				] ) ){
					foreach( $users as $user ){
?>
				<option value="<?php echo $user->ID ?>"<?php if( in_array( $user->ID, $moderator ) ) echo ' selected' ?>><?php echo $user->display_name ?> ( <?php echo count_user_comments( $user->ID ) .' '. __( 'comments', 'r-forum' ) ?> )</option>
<?php
					}
				}
?>
				</select>

<?php
			}
			
			
			public static function save( $post_id ) {
				if( isset( $_POST[ 'moderator' ] ) && !empty( $_POST[ 'moderator' ] ) ){
					update_post_meta( $post_id, '_moderator', $_POST[ 'moderator' ] );
				}
			}


		}
	}
