<?php
/*

Project Tasks

Copyright (C) 2011-2014 Klas Ehnemark (http://klasehnemark.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

More information can be found at http://klasehnemark.com/wordpress-plugins

*/

if (!class_exists("project_tasks_general")) { 

	class project_tasks_general {


		////////////////////////////////////////////////////////////////////////////////
		//
		// INITIALIZE OBJECT
		//
		////////////////////////////////////////////////////////////////////////////////
	
		public function __construct() {
		
		}


		////////////////////////////////////////////////////////////////////////////////
		//
		// INTERNAL FUNCTIONS
		// And small intermediate ones
		//
		////////////////////////////////////////////////////////////////////////////////
		
		
		public static function load_scripts_and_styles () {
	
			wp_enqueue_script ( 'thickbox' );
			
			wp_register_script	( 'project_tasks', 	plugins_url('project-tasks') . '/files/project-tasks.js' );
			
			wp_enqueue_script ( 'project_tasks' );
			
			wp_enqueue_style ( 'thickbox' );
			
			wp_register_style	( 'project_tasks', 	plugins_url('project-tasks') . '/files/project-tasks.css' );
			
			wp_enqueue_style ( 'project_tasks' );		
		}
		

		// find item in array based on item key value ( and optional another item key value )
		
		public static function find_item_in_array ( $array, $item_key, $item_key_value, $item_key2 = null, $item_key2_value  = null) {
		
			foreach ( $array as $item ) { 
			
				if ( isset($item[$item_key]) && $item[$item_key] == $item_key_value ) { 
				
					if ( ( $item_key2 == null && $item_key2_value == null ) || $item_key2_value== '*' ) { return $item; break; }
					
					else {
					
						if ( isset($item[$item_key2]) && $item[$item_key2] == $item_key2_value ) { return $item; break; }
						
					}
				}
			} 
		}
		
		
		// find name value in a array based on id value
		
		public static function find_name_in_array ( $array, $id, $id_key = 'id', $name_key = 'name', $return_if_empty = '' ) {
		
			foreach ( $array as $item ) { if ( $item[ $id_key ] == $id ) {
			
				return $item[ $name_key ]; break; } 
			
			}
			
			return $return_if_empty;
		}
		
		
		// validate field and if not validated, echo error message and die (oooch!)
		
		public static function validate_field ( $field_value, $validate, $error_message ) {
		
			switch ($validate) {
			
				case 'mandatory'	: if ( $field_value == '' ) { echo $error_message; die(); } break;
				
				case 'number'		: if ( !is_numeric ( $field_value ) ) { echo $error_message; die(); } break; 
				
				case 'date' 		: if ( !( date( 'd-m-Y', strtotime( $field_value ) ) == $field_value ) ) { echo $error_message; die(); } break; 
			}
		}
		
		
		// get value from post or get variable
		
		public static function get_var_from_request ( $var_name, $default_value ) {
		
			$var_value;
			
			if ( isset( $_GET[ $var_name ] ) ) $var_value = $_GET[ $var_name ];
			
			if ( empty ( $var_value ) && isset( $_POST[ $var_name ] )) $var_value = $_POST[ $var_name ];
			
			if ( empty ( $var_value) || $var_value == '' ) $var_value = $default_value;
			
			return $var_value;
		}
		
		
		// send mail
		
		public static function send_mail ( $to, $subject, $message ) {
		
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			
			$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			
			$headers .= 'From: ' . get_option('blogname') . ' <noreply@' . str_replace( 'https://' , '', str_replace( 'http://' , '', get_option('siteurl') )) . '>' . "\r\n";
			
			$headers .= 'Content-Transfer-Encoding: 8bit' . '\r\n\r\n';
			
			mail( $to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers); 
			
		}
		
		// set nbsp if no content
		
		public static function nbsp ( $content, $if_empty = '&nbsp;' ) {
		
			return $content == '' ? $if_empty : $content;
		}

		
	} //End Class
}
?>