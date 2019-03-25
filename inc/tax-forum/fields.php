<?php

	/**
	 * 2019-02-15
	 *
	 *	Refs:
	 *		https://catapultthemes.com/adding-an-image-upload-field-to-categories/
	 *		https://wordpress.stackexchange.com/questions/29322/add-custom-taxonomy-fields-when-creating-a-new-taxonomy
	 *		https://pippinsplugins.com/adding-custom-meta-fields-to-taxonomies/
	 *		https://rudrastyh.com/wordpress/select2-for-metaboxes-with-ajax.html
	 **/
	
	if( ! class_exists( 'rForum_Tax_Fields' ) ) {

		class rForum_Tax_Fields {
 
			/***
			 *	Initialize the class and start calling our hooks and filters
			 */
			public static function init(){

				// global $wpdb, $user, $current_user, $pagenow, $wp_version;
				global $pagenow;

				// $targets = [ 'edit-tags.php', 'categories.php', 'media.php', 'term.php', 'profile.php', 'user-edit.php' ];
				$targets = [ 'edit-tags.php', 'term.php' ];

				if( in_array( $pagenow, $targets ) ){

					$taxonomy = rForum_Tax::get_slug();

					add_action( "{$taxonomy}_add_form_fields", 'rForum_Tax_Fields::hide_fields', 10 );
					
					add_action( "{$taxonomy}_edit_form_fields", 'rForum_Tax_Fields::add_fields', 10 );
					
					#add_action( "create_{$taxonomy}", 'rForum_Tax_Fields::save_field', 10 );
					add_action( "edited_{$taxonomy}", 'rForum_Tax_Fields::save_fields', 10 );

					add_action( 'init', 'rForum_Tax_Fields::allow_media', 9999 );
					
					if( empty( $_REQUEST[ 'action' ] ) ){
						add_filter( 'get_terms', 'rForum_Tax_Fields::description_on_list' );
					}
					
					add_action( 'admin_enqueue_scripts', 'rForum_Tax_Fields::enqueue' );

				}
				
				add_action( 'wp_ajax_rforum_list_users', 'rForum_Tax_Fields::ajax_list_users' );
			}


			public static function enqueue(){
				wp_enqueue_style(	'select2',	'//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
				wp_enqueue_script(	'select2',	'//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', [ 'jquery' ] );
				
				wp_enqueue_script(
					rForum_Core::get_name() .'-tax-fields',
					R_FORUM_URL .'assets/js/admin/tax/fields.js',
					[ 'jquery' ],
					rForum_Core::get_version(),
					true
				);
			}
			
			
			
			public static function hide_fields( $term ){
 ?>
<script>
jQuery( window ).ready(function( $ ){
	$( 'label[for=tag-description], label[for=tag-slug]').parent().remove();
});
</script>
<?php
			}

			
			/***
			 *	Add field
			 */
			public static function add_fields( $term ){
 ?>
    <tr class="form-field" valign="top">
        <th scope="row"><?php _e( 'Description', 'r-forum' ) ?></th>
        <td>
            <?php wp_editor( html_entity_decode( $term->description ), 'description', [ 'media_buttons' => true ] ); ?>
            <script>
                jQuery( window ).ready(function(){
                    jQuery( 'label[for=description]').parent().parent().remove();
                    // jQuery( 'label[for=tag-description]').parent().remove();
                });
            </script>
        </td>
    </tr>
	<tr class="form-field" valign="top">
        <th scope="row"><?php _e( 'Moderators', 'r-forum' ) ?></th>
        <td>
			<select id="forum-moderator" name="moderator[]" multiple>
<?php

	$moderator = (array) get_term_meta( $term->term_id, 'moderator', 1 );

	if( $users = get_users( [
		// 'role__in' => [ 'moderator_forum', 'moderator_topic', 'author', 'contributor', 'subscriber' ],
		'orderby' => 'display_name',
		'order' => 'ASC',
	] ) ){
		foreach( $users as $user ){
?>
				<option value="<?php echo $user->ID ?>"<?php if( in_array( $user->id, $moderator ) ) echo ' selected' ?>><?php echo $user->display_name ?> ( <?php echo count_user_comments( $user->ID ) .' '. __( 'comments', 'r-forum' ) ?> )</option>
<?php
		}
	}
?>
			</select>
    </tr>
    <?php
			}
			
			
			/***
			 *	 save field
			 */
			public static function save_fields( $term_id ){
				if( isset( $_POST[ 'moderator' ] ) ){
					update_term_meta( $term_id, 'moderator', $_POST[ 'moderator' ] );
				}  
			}


			/***
			 *	using wysiwyg required allow media to content
			 */
			public static function allow_media(){
				$filters = [ 'pre_term_description', 'pre_link_description', 'pre_link_notes', 'pre_user_description' ];
				foreach( $filters as $filter ){
					remove_filter( $filter, 'wp_filter_kses' );
				}
			}
			
			/***
			 *	fix description returned on get_terms
			 */
			public static function description_on_list( $terms = [], $taxonomies = null, $args = [] ){
				if( is_array( $terms ) ){
					foreach( $terms as $key => $term ){
						if( is_object( $term ) && isset( $term->description ) ){
							$term->description = rForum_Tax_Fields::trim_excerpt( $term->description );
						}
					}
				}
				return $terms;
			}
			
			/***
			 *	fix description trim
			 */
			public static function trim_excerpt($text) {
				$raw_excerpt = $text;
				$text = str_replace(']]>', ']]&gt;', $text);
				$excerpt_length = apply_filters( 'term_excerpt_length', 40 );
				$excerpt_more = ' ' . '[...]';
				$words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
				if ( count($words) > $excerpt_length ) {
					array_pop($words);
					$text = implode(' ', $words);
					$text = $text . $excerpt_more;
				} else {
					$text = implode(' ', $words);
				}
				return apply_filters(' wp_trim_term_excerpt', force_balance_tags( $text ), $raw_excerpt );
			}
			
			
			/***
			 *	ajax function to populate select2 on forum's moderator field
			 */
			public static function ajax_list_users(){
 
				$return = [];
 
				$search = new WP_User_Query( [
					'search' => '*'. $_GET[ 'q' ] .'*', // the search query
					// 'role__in' => [ 'moderator_forum', 'moderator_topic', 'author', 'contributor', 'subscriber' ],
					'orderby' => 'display_name',
					'order' => 'ASC',
					'number' => -1
				] );
				
				if( $users = $search->get_results() ){
					foreach( $users as $user ){
						$return[] = [ $user->ID, $user->display_name ];
					}
				}

				echo json_encode( $return );
				die;
			}
		
		
		}

	}
