<?php

	/***
	 *	2019-02-19
	 *
	 *	REFS:
	 *		- https://allisontarr.com/2017/11/17/custom-meta-boxes-media/
	 *		- https://gist.github.com/cferdinandi/86f6e326b30b8b5416c0a7e43271efa6
	 *		- https://code.tutsplus.com/articles/attaching-files-to-your-posts-using-wordpress-custom-meta-boxes-part-1--wp-22291
	 *
	 */

	if( !class_exists( 'Extranet_SC_Files_Metabox' ) ){

		class Extranet_SC_Files_Metabox {


			public static function init(){

				add_action( 'admin_enqueue_scripts', 'Extranet_SC_Files_Metabox::enqueues', 10, 1 );
				
				// add metabox
				add_action( 'add_meta_boxes', 'Extranet_SC_Files_Metabox::add' );
				
				add_action( 'save_post', 'Extranet_SC_Files_Metabox::save' );
				

			}

			
			public static function enqueues(){
				global $typenow;
				if( $typenow == Extranet_SC_Files_CPT::get_slug() ){
					
					wp_enqueue_media();
					
					// Registers and enqueues the required javascript.
					wp_register_script( 'esc-files-admin-metabox', EXTRANET_SC_URL .'assets/js/files/admin/metabox.js', [ 'jquery' ] );
					
					wp_localize_script( 'esc-files-admin-metabox', 'files_image',
						array(
							'title' => __( 'Choose or Upload File', 'extranet-seven-capital' ),
							'button' => __( 'Use this file', 'extranet-seven-capital' ),
						)
					);
					wp_enqueue_script( 'esc-files-admin-metabox' );
				}
			}

			
			public static function add() {
				add_meta_box( 
					'esc_files_file',
					__( 'File', 'extranet-seven-capital' ),
					'Extranet_SC_Files_Metabox::render',
					'files',
					'normal'
				) ;
			}


			// Register Custom Post Type
			public static function render(){
		
				// Variables
				global $post;
				
				$id = '';
				$url = '';
				$view = __( 'No file selected', 'extranet-seven-capital' );
				
				if( $field = get_post_meta( $post->ID, '_file', true ) ){
					$field = json_decode( $field, true );
					$id = $field[ 'id' ];
					$url = $field[ 'url' ];
					$view = "<a href=\"{$url}\">{$url}</a> (ID {$id})";
				}
				
				

?>
			<fieldset>
				<div>
					<p><strong><?php _e( 'Current File', 'extranet-seven-capital' ) ?>:</strong> <span id="files_file_view"><?php echo $view; ?></span></p>
					<button type="button" class="button" id="files_file_btn" data-media-uploader-target="files_file"><?php _e( 'Upload File', 'extranet-seven-capital' )?></button>
					<input type="hidden" name="files_file[id]" id="files_file_id" value="<?php echo esc_attr( $id ); ?>">
					<input type="hidden" name="files_file[url]" id="files_file_url" value="<?php echo esc_attr( $url ); ?>">
				</div>
			</fieldset>

<?php
				// Security field
				// wp_nonce_field( 'myplugin_form_metabox_nonce', 'myplugin_form_metabox_process' );
				wp_nonce_field( 'esc_files_file_add', 'esc_files_file_nonce' );
			}
			
			
			public static function save( $id ) {
				if( !empty( $_POST[ 'files_file' ] ) ){
					update_post_meta( $id, '_file', json_encode( $_POST[ 'files_file' ] ) );
				}
			}


		}
	}
