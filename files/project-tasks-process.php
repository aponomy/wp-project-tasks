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


if (!class_exists("project_tasks_process")) { 

	class project_tasks_process {

		//private $num_targets = 0;


		////////////////////////////////////////////////////////////////////////////////
		//
		// INITIALIZE OBJECT
		//
		////////////////////////////////////////////////////////////////////////////////
	
		public function __construct() {
		
		}
		
		
		
		////////////////////////////////////////////////////////////////////////////////
		//
		// RENDER PROCESS MAP
		//
		////////////////////////////////////////////////////////////////////////////////
				
		public function render_process_map( $process_id ) {
		
			global $project_tasks;
			
			global $wpdb;


			// Get the process
			
			$process = $wpdb->get_results( $wpdb->prepare ( 'SELECT * FROM ' . $project_tasks->data->process_table_name . ' WHERE id = %d', $process_id ));
			
		
			// Get all process objects
			
			if ( $wpdb->num_rows > 0 ) {
			
				echo '<h2>' . $process[0]->name . ( $process[0]->builtin_name != '' ? ' (built in)' : '' ) . '</h2>';
				
				echo '<p>' . $process[0]->description . '</p>';
				
				echo '<p>Created: ' . Date('Y-m-d', $process[0]->created_date) . ', last action ' . Date('Y-m-d', $process[0]->last_action_date)  . '</p>';
			
				$probjects = $wpdb->get_results( $wpdb->prepare ( 'SELECT id, type, instance_of, connected_to, connected_type, position, name, description FROM ' . $project_tasks->data->process_objects_table_name . ' WHERE process = %d', $process_id ), OBJECT_K);
				
				echo '<ul class="spt_process_objects">' ;
				
				
				
				// loop thru every object
				
				foreach ( $probjects as $probject ) {
				
					if ( $probject->position != '' ) {
					
						$pos 	= unserialize ( $probject->position );
						
						$left	= isset ( $pos['left'] ) ? $pos['left'] : 0;
						
						$top		= isset ( $pos['top'] ) ? $pos['top'] : 0;
				
						echo '<li id="poid_' . $probject->id . '" class="spt_' . strtolower( $probject->type ) . '" title="' . $probject->description . '" data-type="' . $probject->type . '" '; 
						
						echo 'data-instance-of="' . $probject->instance_of . '" data-connected-to="' . $probject->connected_to . '" data-connected-type="' . $probject->connected_type . '" ';
						
						echo 'style="left: ' . $left . 'px; top: ' . $top . 'px;"><a href="">' . $probject->name . '</a></li>';
					}
					
				
				
				
				}
				
				echo '</ul>';
				
				//project_tasks::debug($probjects, true);
				//echo '<p>' . str_replace( '\n', '<br>', $process[0]->log  ) . '</p>';
			
			
				//project_tasks::debug($process, true);
				
				
			} else {
			
				wp_die( 'Cannot find a process with id ' . $process_id );
			
			}
		}
		
		
		////////////////////////////////////////////////////////////////////////////////
		//
		// CREATE DEFAULT PROCESSES IN DATABASE
		//
		////////////////////////////////////////////////////////////////////////////////
			
		public static function create_default_processes() {

			global $project_tasks;
			
			global $wpdb;
			
			
			$created_info = array (
				'created_date'				=> time(),
				'last_action_date'			=> time(),
				'creator'					=> get_current_user_id(),
				'log'					=> date('d-m-Y') . ': An administrator created this by installing the project tasks-plugin.'
			);


			// creates a simple task process

			$process_id 					= project_tasks_process::create_default_process_object ( array_merge ( $created_info, array (
				'builtin_name'				=> 'simple_task',
				'name' 					=> 'Simple Task',
				'description'				=> 'This processes contains a simple task that is created, attached to someone, worked upon and completed.',
				'parent_process_object'		=> '0'
			)), 'PROCESS');

			
			// create the roles in the process
			$role_creator_id 				= project_tasks_process::create_default_process_object ( array_merge ( $created_info, array (
				'builtin_name'				=> 'st_role_creator',
				'name'					=> 'Creator',
				'description'				=> 'This role can create a new task',
				'process'					=> $process_id, 
				'type'					=> 'ROLE',
				'instance_of'				=> '0',
				'connected_to'				=> '0',
				'connected_type'			=> '',
				'position'				=> ''
			)));	
			
			
			// create the roles in the process
			$role_assigner_id 				= project_tasks_process::create_default_process_object ( array_merge ( $created_info, array (
				'builtin_name'				=> 'st_role_assigner',
				'name'					=> 'Assigner',
				'description'				=> 'This role can assign a newly created task',
				'process'					=> $process_id, 
				'type'					=> 'ROLE',
				'instance_of'				=> '0',
				'connected_to'				=> '0',
				'connected_type'			=> '',
				'position'				=> ''
			)));

			// create the activities and deliverables
			
			// Create
			$create_task_id 				= project_tasks_process::create_default_process_object ( array_merge ( $created_info, array (
				'builtin_name'				=> 'st_create_task',
				'name'					=> 'Create Task',
				'description'				=> 'User creates a new task',
				'process'					=> $process_id, 
				'type'					=> 'ACTIVITY',
				'instance_of'				=> '0',
				'connected_to'				=> '0',
				'connected_type'			=> '',
				'position'				=> serialize( array( 'left' => 0, 'top' => 50 ))
			)));
			$new_task_id 					= project_tasks_process::create_default_process_object ( array_merge ( $created_info, array (
				'builtin_name'				=> 'st_new_task',
				'name'					=> 'New Task',
				'description'				=> '',
				'process'					=> $process_id, 
				'type'					=> 'DELIVERABLE',
				'instance_of'				=> '0',
				'connected_to'				=> $create_task_id,
				'connected_type'			=> 'CHILD_OF',
				'position'				=> serialize( array( 'left' => 175, 'top' => 50 ))
			)));	
			
			// Assign
			$assign_task_id 				= project_tasks_process::create_default_process_object ( array_merge ( $created_info, array (
				'builtin_name'				=> 'st_assign',
				'name'					=> 'Assign Task',
				'description'				=> 'User assigns this task to a person',
				'process'					=> $process_id, 
				'type'					=> 'ACTIVITY',
				'instance_of'				=> '0',
				'connected_to'				=> $new_task_id,
				'connected_type'			=> 'CHILD_OF',
				'position'				=> serialize( array( 'left' => 275, 'top' => 50 ))
			)));
			$assigned_task_id 				= project_tasks_process::create_default_process_object ( array_merge ( $created_info, array (
				'builtin_name'				=> 'st_assigned_task',
				'name'					=> 'Assigned Task',
				'description'				=> '',
				'process'					=> $process_id, 
				'type'					=> 'DELIVERABLE',
				'instance_of'				=> '0',
				'connected_to'				=> $assign_task_id,
				'connected_type'			=> 'CHILD_OF',
				'position'				=> serialize( array( 'left' => 450, 'top' => 50 ))
			)));
			
			// Working
			$working_task_id 				= project_tasks_process::create_default_process_object ( array_merge ( $created_info, array (
				'builtin_name'				=> 'st_working',
				'name'					=> 'Working',
				'description'				=> 'User works on the task and closes it when it\'s finished',
				'process'					=> $process_id, 
				'type'					=> 'ACTIVITY',
				'instance_of'				=> '0',
				'connected_to'				=> $assigned_task_id,
				'connected_type'			=> 'CHILD_OF',
				'position'				=> serialize( array( 'left' => 550, 'top' => 50 ))
			)));
			$closed_task_id 				= project_tasks_process::create_default_process_object ( array_merge ( $created_info, array (
				'builtin_name'				=> 'st_closed_task',
				'name'					=> 'Closed Task',
				'description'				=> '',
				'process'					=> $process_id, 
				'type'					=> 'DELIVERABLE',
				'instance_of'				=> '0',
				'connected_to'				=> $working_task_id,
				'connected_type'			=> 'CHILD_OF',
				'position'				=> serialize( array( 'left' => 725, 'top' => 50 ))
			)));		
		
			// create instances of roles connected to activities
			$create_task_role_id 			= project_tasks_process::create_default_process_object ( array_merge ( $created_info, array (
				'builtin_name'				=> 'st_create_task_role',
				'name'					=> '',
				'description'				=> '',
				'process'					=> $process_id, 
				'type'					=> 'ROLE',
				'instance_of'				=> $role_creator_id,
				'connected_to'				=> $create_task_id,
				'connected_type'			=> 'PERFORMS',
				'position'				=> serialize( array( 'left' => 280, 'top' => 50 ))
			)));

		}
		
		
		
		////////////////////////////////////////////////////////////////////////////////
		//
		// INTERNAL FUNCTION, CREATE DEFAULT PROCESS OBJECT
		//
		////////////////////////////////////////////////////////////////////////////////
				
		private function create_default_process_object ( $db_process_object_columns, $type = 'OBJECT' ) {
		
			global $project_tasks;
			
			global $wpdb;
			
			$tablename = $type == 'PROCESS' ? $project_tasks->data->process_table_name : $project_tasks->data->process_objects_table_name;
			
			$existing_object = $wpdb->get_results( 'SELECT id, log FROM ' . $tablename . ' WHERE builtin_name = \'' . $db_process_object_columns['builtin_name'] . '\'');

			if ( $wpdb->num_rows > 0 ) {
			
				$builtin_name = $db_process_object_columns['builtin_name'];
				
				unset ( $db_process_object_columns['builtin_name']);
				
				unset ( $db_process_object_columns['id']);
				
				//$db_process_object_columns['log'] =  date('d-m-Y') . ': An administrator updated this by updating the project tasks-plugin.' . '\n' . $existing_object[0]->log;
				
				$format = $type == 'PROCESS' ? array ( '%d', '%d', '%d', '%s',    '%s', '%s', '%d' ) : array ( '%d', '%d', '%d', '%s',    '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s' );
			
				$wpdb->update( $tablename, $db_process_object_columns, array( 'builtin_name' => $builtin_name ), $format , array( '%s' ) );	
			
				return  $existing_object[0]->id;
			
			} else {
			
				$format = $type == 'PROCESS' ? array ( '%d', '%d', '%d', '%s',    '%s', '%s', '%s', '%d' ) : array ( '%d', '%d', '%d', '%s',    '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s' );
		
				$wpdb->insert( $tablename, $db_process_object_columns, $format );
				
				return $wpdb->insert_id;
			
			}
		}
		
		
	} //End Class
} 

?>