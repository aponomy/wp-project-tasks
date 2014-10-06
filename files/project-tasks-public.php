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
define('PROJECT_TASKS_PUBLIC', true);
if (!class_exists("project_tasks_public")) { 

	class project_tasks_public {


		////////////////////////////////////////////////////////////////////////////////
		//
		// INITIALIZE OBJECT
		//
		////////////////////////////////////////////////////////////////////////////////
	
		public function __construct() {
		
			if ( defined ( 'PROJECT_TASKS_PUBLIC' ) && PROJECT_TASKS_PUBLIC === true ) {

				include_once ABSPATH . 'wp-admin/includes/theme.php';
		
				add_shortcode('project-tasks', array ( $this, 'shortcode_project_tasks' ));
				
				add_action( 'wp_enqueue_scripts', 'project_tasks_general::load_scripts_and_styles' );
				
				add_action( 'wp_head', array ( $this, 'wp_head' ));
				
				add_filter( 'show_admin_bar', '__return_false' );
			}
		}
	

		////////////////////////////////////////////////////////////////////////////////
		//
		// WP HEAD
		//
		////////////////////////////////////////////////////////////////////////////////
	
		public function wp_head() {
		
			echo '<script type="text/javascript"> ajaxurl = "' . admin_url ( 'admin-ajax.php' ) . '"; </script>';

		}
		

		////////////////////////////////////////////////////////////////////////////////
		//
		// SHORTCODE
		//
		////////////////////////////////////////////////////////////////////////////////
	
		public function shortcode_project_tasks( $attr, $content = null ) {
		
			if ( is_user_logged_in() ) {
		
				echo 	'<div id="project_tasks_content">';
						
				$task_list = new project_tasks_list();
				
				$task_list->render_list();
				
				echo '	</div>';
				
			} else {
			
				echo 'You need to be <a href="' . wp_login_url( $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ) . '">logged in</a> to see project tasks.';
			}
		}

		
	} //End Class
}
?>