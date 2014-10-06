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

if (!class_exists("project_tasks_ajax")) { 

	class project_tasks_ajax {

		////////////////////////////////////////////////////////////////////////////////
		//
		// INITIALIZE OBJECT
		//
		////////////////////////////////////////////////////////////////////////////////
	
		public function __construct() {
			
			// add ajax functions
			
			add_action('wp_ajax_render_task_list_ajax', array ( $this, 'render_task_list_ajax' ));
			
			add_action('wp_ajax_render_task_form_ajax', array ( $this, 'render_task_form_ajax' ));
			
			add_action('wp_ajax_render_task_form_targets_ajax', array ( $this, 'render_task_form_targets_ajax' ));
			
			add_action('wp_ajax_render_task_form_subtasks_ajax', array ( $this, 'render_task_form_subtasks_ajax' ));
			
			add_action('wp_ajax_get_number_page_tasks_ajax', array ( $this, 'get_number_page_tasks_ajax' ));
			
			add_action('wp_ajax_save_task_form_ajax', array ( $this, 'save_task_form_ajax' ));

		}

		////////////////////////////////////////////////////////////////////////////////
		// GET NUMBER OF PAGE TASKS
		////////////////////////////////////////////////////////////////////////////////
		
		function get_number_page_tasks_ajax () {
		
			global $project_tasks;
			
			global $wpdb;
			
			$post_id = isset( $_POST[ 'post_id' ] )? $_POST[ 'post_id' ] : 0;
			
			$post_where_sql = $project_tasks->data->get_sql_where_for_post_tasks ( $post_id );
			
			if ( $post_where_sql == '' ) echo '0';
			
			else {
				
				$sql = 'SELECT COUNT( DISTINCT ' . $project_tasks->data->tasks_table_name . '.ID) FROM ' . $project_tasks->data->tasks_table_name . ' WHERE ' . $project_tasks->data->tasks_table_name . '.ID IN ( SELECT task FROM ' .  $project_tasks->data->task_relation_table_name . ' WHERE ' . $post_where_sql . ');';
				
				$num_posts = $wpdb->get_var( $sql );
				
				echo $num_posts != '' ? $num_posts : '0';
			}

			die();
		}

		////////////////////////////////////////////////////////////////////////////////
		// RENDER TASK LIST
		////////////////////////////////////////////////////////////////////////////////
	
		function render_task_list_ajax () { 
		
			$task_list = new project_tasks_list();
			
			$task_list->render_list();
			
			die();
		}

		
		////////////////////////////////////////////////////////////////////////////////
		// RENDER TASK FORM
		////////////////////////////////////////////////////////////////////////////////
		
		function render_task_form_ajax () {
		
			$task_form = new project_tasks_form ();
			
			$task_form->render_form ();
			
			die(); 
		}
		
		
		////////////////////////////////////////////////////////////////////////////////
		// RENDER TASK FORM TARGET LIST
		////////////////////////////////////////////////////////////////////////////////
				
		function render_task_form_targets_ajax () {
		
			$task_form = new project_tasks_form ();
			
			$task_form->render_task_target_list (); 
			
			die(); 		
		}
		
		
		////////////////////////////////////////////////////////////////////////////////
		// RENDER TASK FORM SUB TASK LIST
		////////////////////////////////////////////////////////////////////////////////
				
		function render_task_form_subtasks_ajax () {
		
			$task_form = new project_tasks_form ();
			
			$task_form->render_subtasks_list (); 
			
			die(); 		
		}
		
		
		////////////////////////////////////////////////////////////////////////////////
		// SAVE TASK FORM
		////////////////////////////////////////////////////////////////////////////////
		
		function save_task_form_ajax () { 
		
			$task_form = new project_tasks_form ();
			
			$task_form->save_task_form(); 
			
			die(); 
		}
		
	} //End Class
}
?>