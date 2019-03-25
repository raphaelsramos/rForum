<?php

	/**
	 * 2019-03-17
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
			public function init(){

				// global $wpdb, $user, $current_user, $pagenow, $wp_version;
				global $pagenow;

				// $targets = [ 'edit-tags.php', 'categories.php', 'media.php', 'term.php', 'profile.php', 'user-edit.php' ];
				$targets = [ 'edit-tags.php', 'term.php' ];

				if( in_array( $pagenow, $targets ) ){

					$taxonomy = rForum_Tax::get_slug();

					add_action( "{$taxonomy}_add_form_fields", 'rForum_Tax_Fields::hide_fields', 10 );
					
					add_action( "{$taxonomy}_edit_form_fields", 'rForum_Tax_Fields::add_fields', 10 );

					add_action( 'init', 'rForum_Tax_Fields::allow_media', 9999 );
					
					if( empty( $_REQUEST[ 'action' ] ) ){
						add_filter( 'get_terms', 'rForum_Tax_Fields::description_on_list' );
					}

				}

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
    <?php
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
					$words = preg_split( "/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY );
					if( count($words) > $excerpt_length ) {
						array_pop($words);
						$text = implode( ' ', $words );
						$text = $text . $excerpt_more;
					}
					else {
						$text = implode( ' ', $words );
					}
					return apply_filters( 'wp_trim_term_excerpt', force_balance_tags( $text ), $raw_excerpt );
				}
		
		
		}

	}
