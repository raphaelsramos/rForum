<?php

	/***
	 *	2019-02-15
	 */
	
	if( !class_exists( 'Extranet_SC_Users_Columns' ) ){
		
		class Extranet_SC_Users_Columns {
		
			public static function init(){
					
				// column
				// https://www.smashingmagazine.com/2017/12/customizing-admin-columns-wordpress/
				// https://wordpress.stackexchange.com/questions/27518/sortable-custom-columns-in-user-panel-users-php
				add_filter( 'manage_users_columns', 'Extranet_SC_Users_Columns::order' );
				add_filter( 'manage_users_custom_column', 'Extranet_SC_Users_Columns::content', 10, 3 );

			}


			public static function order( $cols ){
				$cols = [
					"cb"		=> "",
					"username"	=> __( "Username" ),
					"name"		=> __( "Name" ),
					"email"		=> __( "E-mail" ),
					"company"	=> __( "Company", 'extranet-seven-capital' ),
					"level"		=> __( "Level", 'extranet-seven-capital' ),
					"role"		=> __( "Role" )
				];
				return $cols;
			}

			
			public function content( $val, $column_name, $user_id ){
				switch( $column_name ){
					
					case 'company':
					case 'level':
						if( $data_id = get_user_meta( $user_id, '_'. $column_name, true ) ){
							if( $tax = get_term( $data_id, $column_name ) ){
								return $tax->name;
							}
						}
						return '-';
						break;

					default:
				}
				
				return $val;
			}


		}
	}