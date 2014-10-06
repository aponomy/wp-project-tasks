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

if (!class_exists("project_tasks_form")) { 

	class project_tasks_form {

		////////////////////////////////////////////////////////////////////////////////
		//
		// INITIALIZE OBJECT
		//
		////////////////////////////////////////////////////////////////////////////////
	
		public function __construct() {
			
		}
		
		

		////////////////////////////////////////////////////////////////////////////////
		//
		// RENDER TASK FORM
		// Called from admin bar by javascript ajax and from admin overview page
		// Viewed inside a thickbox
		//
		////////////////////////////////////////////////////////////////////////////////
	
		function render_form () {
		
			global $project_tasks;
			
			global $wpdb;
			
			$task_id 		= project_tasks_general::get_var_from_request ( 'task_id', '0' );
			
			$post_id 		= project_tasks_general::get_var_from_request ( 'post_id', '0' );
			
			$parent_task 	= project_tasks_general::get_var_from_request ( 'parent_task', '0' );
			
			if ( !isset($post_id) || $post_id == '' ) { echo ('Error: No post ID in the reguest.'); die(); }
			
			
			// get arrays of content
			
			$post 					= get_post ( $post_id );
			
			$users 					= $project_tasks->data->get_users ();
			
			$all_status 				= $project_tasks->data->get_status ();	
			
			$task_types 				= $project_tasks->data->get_task_types();
			

			// default values
			
			$task_notes = $task_description = $task_status = $task_deadline = $parent_task_title = $task_type_name = '';
			
			$task_action_type = $task_action_step = $task_action_responder = $task_action_created_by = $action_content = $parent_task_type = '';
			
			$task_creator = get_current_user_id();
			
			$task_time = time();
			
			$task_closed = $task_global  = false;
			
			$num_subtasks = 0;
			
			$task_assigned_to = $task_progress = '0';
			
			$assigned_user_name = 'NOT ASSIGNED';
			
			$status_name = 'CREATING';
			
			$task_type  = 'TASK';
			
			$task_title = 'New Task';
			
			$select_class = $type_select_class = '';
			
			$readonly_class = $type_readonly_class = 'spt_hidden';
			
			$task_log = 'Nothing yet';
			
			$task_priority = 5;
			
			$task_due_date_day = 'DD';
			
			$task_due_date_month = 'MM';
			
			$task_due_date_year = 'YYYY';
			
			
			// get values from database if this is a saved task
			
			if ( $task_id != '0' ) {
			
				global $wpdb;
				
				$task = $wpdb->get_results('SELECT *, (select count(*) from ' . $project_tasks->data->tasks_table_name . ' where parent_task = ' . $task_id  . ') as num_subtasks FROM ' . $project_tasks->data->tasks_table_name . ' WHERE ID = ' . $task_id );
				
				if ( empty( $task ) || empty ($task[0]) ) wp_die ('Error: Cannot find task #' . $task_id .' in the database');
								
				$parent_task				= $task[0]->parent_task;
				
				$num_subtasks				= $task[0]->num_subtasks;
				
				$task_time 				= $task[0]->created_date;
				
				$task_creator 				= $task[0]->creator;
				
				$task_assigned_to 			= $task[0]->assigned_to;
				
				$task_type 				= $task[0]->type;
				
				$task_title 				= $task[0]->title;
				
				$task_description 			= $task[0]->description;
				
				$task_notes 				= $task[0]->notes;
				
				$task_priority				= $task[0]->priority;
				
				$task_status 				= $task[0]->status;
				
				$task_due_date 			= $task[0]->due_date;
				
				$task_progress 			= $task[0]->progress;
				
				$task_log		 			= $task[0]->log;
				
				
				$select_class = $type_select_class = 'spt_hidden';
				
				$readonly_class = $type_readonly_class = '';
				
				if ( $task_due_date != 0 ) {
				
					$task_due_date_day = date('d', $task_due_date);
					
					$task_due_date_month = date('m', $task_due_date);
					
					$task_due_date_year = date('Y',  $task_due_date);
				}
				
				//$task_targets = $wpdb->get_results('SELECT * FROM ' . $project_tasks->data->task_relation_table_name . ' WHERE task = ' . $task_id );
			} 
			
			$task_action_type_selected	= 'spt_task_action_type_confirmtype';
			
			$user_creator 				= get_userdata ($task_creator);
			
			$task_creator_name 			= $user_creator->display_name;
			
			$task_time_clear 			= date ( 'd/m/Y', $task_time );
			
			$task_deadline_clear 		= '';
			
			$task_priority				= ( $task_priority > 0 && $task_priority < 11 ) ? $task_priority : 5; ?>
		
			<html><head><?php
			if ( project_tasks_general::get_var_from_request ( 'css', '0' ) != '0' ) {?>
				<link rel="stylesheet" type="text/css" href="<?php echo WP_PLUGIN_URL . '/' . dirname( plugin_basename(__FILE__) ) . '/project-tasks.css' ?>" />
				<script type='text/javascript' src='/wp-admin/load-scripts.php?c=1&load%5B%5D=jquery-core,jquery-migrate,utils&ver=3.8.1'></script>
				<link media="all" type="text/css" href="/wp-admin/load-styles.php?c=1&dir=ltr&load=dashicons,admin-bar,wp-admin,buttons,wp-auth-check&ver=3.8.1" rel="stylesheet">
				<script type='text/javascript' src='<?php echo WP_PLUGIN_URL . '/' . dirname( plugin_basename(__FILE__) ) . '/project-tasks.js' ?>'></script><?php
			}?>
			</head>
			<body id="theme_project_tasks_edit">
				<div class="sptask_edit_page_tabs">
					<a class="sptask_content_button active" id="sptask_content_button_1">Task</a>
					<a class="sptask_content_button" id="sptask_content_button_2">Targets</a>
					<a class="sptask_content_button" id="sptask_content_button_3">Notes</a>
					<!--<a class="sptask_content_button" id="sptask_content_button_4">Sub Tasks</a>-->
					<a class="sptask_content_button" id="sptask_content_button_5">Log</a>
					<div style="clear: both;"></div>
				</div>
			
				<div class="sptask_edit_page_content">
					<div id="sptask_edit_page_1" class="sptask_edit_page active">
						<div class="spt_fieldrow first_row bottom_border">
							<input type="hidden" name="sptask_id" id="sptask_id" value="<?php echo $task_id;?>" >
							<input type="hidden" name="sptparent_task_id" id="sptparent_task_id" value="<?php echo $parent_task;?>" >
							<input type="hidden" name="sptask_creator_id" id="sptask_creator_id" value="<?php echo $task_creator;?>" >
							<label for="sptask_title">Title:</label><input type="text" name="sptask_title" id="sptask_title" value="<?php echo $task_title; ?>" />
							<div class="spt_secondfield small_font"><label for="created_by" class="small_label" >Created by:</label>
								<div class="created_by"><?php echo $task_creator_name; ?></div>
								<div class="created_date"><?php echo $task_time_clear; ?></div>
							</div>
						</div>
						<div class="spt_fieldrow">
							<label for="sptask_tasktype">Type:</label>
							<select name="sptask_tasktype" id="sptask_tasktype" class="<?php echo $type_select_class;?>"><?php
							
							
								// display dropdown of task types
								
								foreach ( $task_types as $the_task_type ) {
								
									if ( $parent_task_type == '' && $task_type ==  $the_task_type['id'] ) $task_type_name = $the_task_type['name'];
									
									else if ( $parent_task_type != '' && $parent_task_type ==  $the_task_type['id'] ) $task_type_name = $the_task_type['name'];
									
									echo '<option value="' . $the_task_type['id'] . '"' . ( $task_type_name ==  $the_task_type['name'] ? ' selected="selected"' : '' ) . '>' .  $the_task_type['name']  . '</option>';
									
								}?>
							</select>
							<div id="sptask_tasktype_readonly" class="spt_read_only_field <?php echo $type_readonly_class;?>"><?php 
							
								echo $task_type_name;
								
								if ( $parent_task_type == '' ) echo '<span class="spt_change_read_only">Change</span>';
								
								?>
							</div>
							<div class="spt_secondfield"><label for="sptask_duedate">Due Date:</label>
								<div class="spt_datespanner">
									<input type="text" class="spt_date_day" name="sptask_duedate_day" id="sptask_duedate_day" value="<?php echo $task_due_date_day; ?>" /><span>/</span><input type="text" class="spt_date_month" name="sptask_duedate_month" id="sptask_duedate_month" value="<?php echo $task_due_date_month; ?>" /><span>/</span><input type="text" name="sptask_duedate_year" class="spt_date_year" id="sptask_duedate_year" value="<?php echo $task_due_date_year; ?>" />
								</div>
							</div>
						</div>
						<div class="spt_fieldrow bottom_border" style="height: 120px;"><label for="sptask_description">Description:</label><textarea name="sptask_description" id="sptask_description"><?php echo $task_description; ?></textarea></div>
						<div class="spt_fieldrow">
							<label for="sptask_resource">Assigned to:</label>
							<select name="sptask_taskassignedto" id="sptask_taskassignedto" class="<?php echo $select_class;?>">
								<option value="0">[NOT ASSIGNED]</option>'<?php
								
								
								// display a dropdown of users
								
								foreach ( $users as $user ) {
								
									if ( $user['id'] == $task_assigned_to ) $assigned_user_name = $user['name'] ;
									
									if ( $user['id'] == $task_action_responder ) $task_action_responder_name = $user['name'];
									
									if ( $user['id'] == $task_action_created_by ) $task_action_created_by_name = $user['name'];
									
									echo '<option value="' . $user['id'] . '"' . ( $user['id'] == $task_assigned_to ? ' selected="selected"' : '' ) . '>' . $user['name'] . '</option>';
									
								}?>
							</select>
							<div id="sptask_taskassignedto_readonly" class="spt_read_only_field <?php echo $readonly_class;?>"><?php echo $assigned_user_name; ?><span class="spt_change_read_only">Re-assign</span></div>
							<div class="spt_secondfield">
								<label for="sptask_progress">Progress:</label><input type="text" name="sptask_progress" id="sptask_progress" class="spt_progress" value="<?php echo $task_progress; ?>" /><div id="sptask_progress_percent">% <span id="spt_complete_button" class="spt_change_read_only">Complete</span></div>
									
							</div>
						</div>

						<div class="spt_fieldrow">
							<label for="sptask_status">Status:</label>
							<select name="sptask_status" id="sptask_status" class="<?php echo $select_class;?>"><?php
							
							
								// display a drop down with all status
								
								foreach ( $all_status as $status ) {
								
									if ( $task_status ==  $status['id'] ) $status_name = $status['name'];
									
									echo '<option value="' . $status['id'] . '"' . ( $task_status ==  $status['id'] ? ' selected="selected"' : '' ) . '>' .  $status['name']  . '</option>';
									
								}?>
							</select>
							<div id="sptask_progress_readonly" class="spt_read_only_field <?php echo $readonly_class;?>"><?php echo $status_name; ?><span class="spt_change_read_only">Change</span></div>
							<div class="spt_secondfield"><label for="sptask_duedate">Priority:</label>
								<select name="sptask_priority" id="sptask_priority" class="<?php echo $select_class;?>">
									<option value="9" <?php if ( $task_priority == 9 ) echo ' selected="selected" '; ?>>Urgent</option>
									<option value="7" <?php if ( $task_priority == 7 ) echo ' selected="selected" '; ?>>High</option>
									<option value="5" <?php if ( $task_priority == 5 ) echo ' selected="selected" '; ?>>Normal</option>
									<option value="3" <?php if ( $task_priority == 3 ) echo ' selected="selected" '; ?>>Low</option>
								</select>
								<div id="sptask_priority_readonly" class="spt_read_only_field <?php echo $readonly_class;?>"><?php 
									$priorities = array ('Low', 'Low', 'Low', 'Low', 'Normal', 'Normal', 'Normal', 'High', 'High', 'Urgent', 'Urgent' );
									echo $priorities[$task_priority]; 
								?><span class="spt_change_read_only">Change</span></div>
							</div>
						</div>
					</div>
					<div id="sptask_edit_page_2" class="sptask_edit_page">
						<div class="how_to">Select which targets this task aims for. What objects shall be worked on? <?php
						if ( $post_id != '0' ) echo '<strong>Note: Only actual targets that appear on this page/post is present in this list.</strong>'?>
						 </div>
						<div id="spt_relations_field" class="spt_relations_field">Loading</div>
					</div>
					<div id="sptask_edit_page_3" class="sptask_edit_page">
						<div class="spt_fieldrow" xstyle="height: 120px;"><textarea name="sptask_notes" id="sptask_notes"><?php echo $task_notes; ?></textarea></div>
					</div>
					<!--<div id="sptask_edit_page_4" class="sptask_edit_page">
						<div class="how_to">This task can hold one or more sub tasks.</div>
						<div id="spt_sub_target_field" class="spt_relations_field spt_sub_target_field">Loading</div>
						<div class="spt_sub_tasks_buttons">
							<span id="spt_add_new_subtask_button" class="button align_left">Add New Sub Task...</span>
							<span id="spt_edit_subtask_button" class="button">Edit...</span>
							<span id="spt_delete_subtask_button" class="button">Delete</span>
						</div>
						<div class="spt_sub_tasks_buttons">
							<span id="spt_make_to_subtask" class="button align_left">Make this a Sub Task</span>
						</div>
					</div>-->
					<div id="sptask_edit_page_5" class="sptask_edit_page">
						<div id="sptask_log_field"><?php echo str_replace( '\n', '<br>', $task_log ); ?></div>
					</div>
				</div>
				<div id="spt_edit_buttons">
					<span id="spt_previous_task" class="spt_action_button align_left spt_hidden">Previous</span><span id="spt_next_task" class="spt_action_button align_left spt_hidden">Next</span>
					<span id="spt_save_task" class="spt_action_button spt_button_primary">Save Task</span><span id="spt_cancel_task" class="spt_action_button">Cancel</span>
					<!--<span id="spt_show_sub_tasks" class="spt_action_button align_left">More...</span>-->
				</div>
				<script language="javascript">
					project_tasks.task_window_is_loaded();
					//try { project_tasks.task_window_is_loaded(); } catch(x) { alert("This form must be loaded within Theme Task Admin Menu") }
				</script>
				<!--<div class="sptask_form_message">This Task is overdue!</div>-->
			</body></html><?php
		}
		



		////////////////////////////////////////////////////////////////////////////////
		//
		// RENDER TASK TARGET LIST
		// Called from task form by javascript ajax
		//
		////////////////////////////////////////////////////////////////////////////////
	
		public function render_task_target_list () {
		
			global $project_tasks;
			
			global $wpdb;
			
			// get variables
			
			$task_id = project_tasks_general::get_var_from_request ( 'task_id', '0' );
			
			$post_id = project_tasks_general::get_var_from_request ( 'post_id', '0' );
			
			if ( !isset($post_id) || $post_id == '' ) { echo ('Error: No post ID in the reguest.'); die(); }
			
			
			// get arrays of content
			
			$post 					= get_post ( $post_id );
			
			$all_targets 				= $project_tasks->data->get_targets ();
			
			$task_targets				= false;
			
			$active_targets 			= !empty($post) ? $project_tasks->data->get_active_targets_on_post ( $post ) : null;
			
			$target_categories 			= $project_tasks->data->get_target_categories ();
			
			
			// list all targets
			$this->list_previous_category		= '';
			
			$this->list_category_class		= ' spt_r_firstrow';
			
			$this->num_targets 				= 0;
			
			
			if ( $task_id != '0' ) {
			
				$task_targets = $wpdb->get_results('SELECT * FROM ' . $project_tasks->data->task_relation_table_name . ' WHERE task = ' . $task_id );
				
			} else {
			
			
				// if this is a new task and created from a post, add the post as active
				
				$post_selected = new stdClass();
				
				$post_selected->id = '';
				
				$post_selected->task = 0;
				
				$post_selected->target_type = 'spt_target_content';
				
				$post_selected->target_id = $post_id;
				
				$task_targets = array ($post_selected);
			}
			
			echo '<table cellpadding="0" cellspacing="0"><thead><tr><td class="spt_rtd_1">Select</td><td class="spt_rtd_2">Type</td><td class="spt_rtd_3">Item</td></tr></thead><tbody>';
			
			
			// first render all selected targets
			
			$this->render_task_targets ( $all_targets, $task_targets, $active_targets, $post, $target_categories, true );
			
			
			// then render all non selected targets
			
			$this->render_task_targets ( $all_targets, $task_targets, $active_targets, $post, $target_categories, false, true );
			
			
			echo '<input type="hidden" name="spt_num_target" id="spt_num_target" value="' . $this->num_targets . '" />';
			
			echo '</tbody></table>';
			
		}
		


		
		////////////////////////////////////////////////////////////////////////////////
		// RENDER TASK TARGETS
		// Internal function 
		////////////////////////////////////////////////////////////////////////////////
			
		private function render_task_targets ( $all_targets, $task_targets, $active_targets, $post, $target_categories, $only_selected = false, $only_nonselected = false  ) { 
		
			foreach ( $all_targets as $target ) {
			
				$this->num_targets++;
										
				if ( $post )  $active_target = project_tasks_general::find_item_in_array ( $active_targets, 'target_type', $target['target_type'], 'target_id', $target['target_id'] );
				
				else $active_target = array();
				
				$active_category = project_tasks_general::find_item_in_array ( $target_categories, 'name', $target['target_category'] );
				
				$this->num_targets++;
				
				if ( !empty( $active_category ) && ( empty( $post ) || !empty( $active_target ))) {
				
					$target_tr = $this->render_task_target ( $target, $active_target, $task_targets, $only_selected, $only_nonselected );
					
					if ( $target_tr != '' ) {
					
						if ( $this->list_previous_category != $target['target_category'] ) {
						
							echo '<tr class="spt_task_type_row' . $this->list_category_class . '"><td colspan="4">' . $target['target_category'] . '</td></tr>';
							
							$this->list_previous_category = $target['target_category'];
							
							$this->list_category_class = '';
							
						}			
						
						echo $target_tr;
					}
				}
			}
		}
		
		
		
		////////////////////////////////////////////////////////////////////////////////
		// RENDER TASK TARGET
		// Internal function 
		////////////////////////////////////////////////////////////////////////////////
		
		private function render_task_target ( $target, $active_target, $db_task_targets = false, $only_selected = false, $only_nonselected = false ) {
		
			$target_type_name 		= isset( $active_target['name'] )? 		$active_target['name'] 		: ( isset ( $target['name'] ) ? 		$target['name']		: ' - missing name -' );
			
			$target_item_name 		= isset( $active_target['item_name'] )? 	$active_target['item_name'] 	: ( isset ( $target['item_name'] ) ? 	$target['item_name'] 	: ' - missing item_name -' );
			
			$target_item_description = isset( $active_target['description'] )? 	$active_target['description'] : ( isset ( $target['description'] ) ? 	$target['description'] 	: ' - missing description -' );
			
			$target_id 			= isset( $active_target['target_id'] )? 	$active_target['target_id'] 	: ( isset ( $target['target_id'] ) ? 	$target['target_id'] 	: ' - missing target_id -' );
			
			$target_selected 		= false;
			
			if ( $db_task_targets !== false ) {
			
				foreach ( $db_task_targets as $db_task_target ) {
				
					//echo '<br>.....$db_task_target->target_type: ' . $db_task_target->target_type . ', $target[target_type]: ' . $target['target_type'];
					
					if ( $db_task_target->target_type ==  $target['target_type'] && $db_task_target->target_id == $target_id ) {
					
						$target_selected = true;
						
						continue;
					}
				}
			}
			
			
			if ( ( $target_selected === true && $only_selected === true ) ||
			
				( $target_selected === false && $only_nonselected === true ) ||
				
				( $only_selected === false &&  $only_nonselected === false )) {
				
			
				$start_link = $end_link = '';
				
				if ( $target_selected == true && $target['target_type'] == 'spt_target_content' ) {
				
					$start_link = '<a href="' . get_permalink ( $target_id ) . '">';
					
					$end_link = '</a>';
				}

				return '
				<tr class="spt_row_' . $target['shortening'] . '">
					<td class="spt_rtd_1"><input type="checkbox" name="spt_target_' . $this->num_targets . '_selected" id="spt_target_' . $this->num_targets . '_selected" value="1" ' . ( $target_selected == true ? ' checked="checked"' : '' ) . ' />
					<input type="hidden" name="spt_target_' . $this->num_targets . '_type" id="spt_target_' . $this->num_targets . '_type" value="' . $target['target_type'] . '">
					<input type="hidden" name="spt_target_' . $this->num_targets . '_id"   id="spt_target_' . $this->num_targets . '_id"   value="' . $target_id . '">
					<input type="hidden" name="spt_target_' . $this->num_targets . '_name" id="spt_target_' . $this->num_targets . '_name" value="' . $target_item_name . '"></td>
					<td class="spt_rtd_2">' . $target_type_name . '</td><td class="spt_rtd_3">' . $start_link . $target_item_name . $end_link . '</td>
				</tr>
				<tr class="spt_row_description spt_row_' . $target['shortening'] . '"><td colspan="3">' . $target_item_description. '</td></tr>';
				
			} else return '';
			
		}
		
		
		
		////////////////////////////////////////////////////////////////////////////////
		//
		// RENDER SUB TASKS LIST
		// Called from task form by javascript ajax
		//
		////////////////////////////////////////////////////////////////////////////////
		
		public function render_subtasks_list () {
		
			$task_id = project_tasks_general::get_var_from_request ( 'task_id', '0' );
			
			if ( $task_id == '0' ) die('You must save this task before adding a sub task');
			
			$task_list = new project_tasks_list();
			
			$task_list->render_list( array (
			
				'parent_task'		=> project_tasks_general::get_var_from_request ( 'task_id', $task_id ),
				
				'columns' 		=> array ( 'title', 'assigned_to', 'status' ),
				
				'show_controls'	=> '0',
				
				'embed_selectors'	=> '1'
			));
		}
	
	
	
	
		////////////////////////////////////////////////////////////////////////////////
		//
		// SAVE TASK FORM
		// Called from admin bar by javascript ajax and from admin overview page
		// Saving info from task form
		//
		////////////////////////////////////////////////////////////////////////////////

		function save_task_form () {
		
			global $project_tasks;
			
			global $wpdb;
			
			$current_user = wp_get_current_user();
						
						
			$task_id = isset( $_POST[ 'task_id' ] )? $_POST[ 'task_id' ] : 0;
			
			$num_targets = isset( $_POST[ 'num_targets' ] )? intval($_POST[ 'num_targets' ]) : 0;
			
			$num_subtasks = isset( $_POST[ 'num_subtasks' ] )? intval($_POST[ 'num_subtasks' ]) : 0;
			
			$ignore_targets = isset( $_POST[ 'ignore_targets' ] )? true : false;
			
			$do_update = false;
			
			$task_log = '';
			
			$new_log_entry_intro = date('d-m-Y') . ': ' . $current_user->display_name;
			
			
			$db_task_item_columns = array (
			
				'created_date'		=> time(),
				
				'last_action_date'	=> time(),
				
				'creator'			=> get_current_user_id(),
				
				'parent_task'		=> isset( $_POST[ 'parent_task' ] )? $_POST[ 'parent_task' ] : '0', 
				
				'assigned_to'		=> isset( $_POST[ 'task_taskassignedto' ] )? $_POST[ 'task_taskassignedto' ] : '', 
				
				'priority'		=> isset( $_POST[ 'task_priority' ] )? $_POST[ 'task_priority' ] : '5',
				
				'due_date'		=> isset( $_POST[ 'task_duedate' ] )? trim ( $_POST[ 'task_duedate' ] ) : '',
				
				'progress'		=> isset( $_POST[ 'task_progress' ] )? trim( $_POST[ 'task_progress' ] ) : '',	
				
				'type'			=> isset( $_POST[ 'task_tasktype' ] )? $_POST[ 'task_tasktype' ] : '',
				
				'title'			=> isset( $_POST[ 'task_title' ] )? $_POST[ 'task_title' ] : '',
				
				'description'		=> isset( $_POST[ 'task_description' ] )? $_POST[ 'task_description' ] : '',
				
				'notes'			=> isset( $_POST[ 'task_notes' ] )? $_POST[ 'task_notes' ] : '',
				
				'status'			=> isset( $_POST[ 'task_status' ] )? $_POST[ 'task_status' ] : '',
				
				'log'			=> ''		
			);
			
			//$db_task_item_columns_format = array ( '%d', '%d', '%d', '%d', '%d','%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s' );

			
			// Some validation and checks
			
			project_tasks_general::validate_field ( $db_task_item_columns['title'], 		'mandatory', 	'- You must write something in the title field.' );
			
			project_tasks_general::validate_field ( $db_task_item_columns['progress'], 	'number', 	'- Progress must be a number.' );
			
			if ( $db_task_item_columns['due_date'] == 'DD-MM-YYYY' ) $db_task_item_columns['due_date'] = '0';
			
			else if ( $db_task_item_columns['due_date'] != '' ) {
			
				project_tasks_general::validate_field ( $db_task_item_columns['due_date'], 'date', '- Due date must be a valid date (DD-MM-YYYY).' );
				
				$db_task_item_columns['due_date']  = strtotime( $db_task_item_columns['due_date'] ); // Translate due date into a unix time stamp
				
			}

			
			// insert the task item into database (%s as string; %d as decimal number and %f as float)
			
			if ( $task_id == 0 ) {
			
				$db_task_item_columns['log'] = $new_log_entry_intro . ' created this task';
				
				$wpdb->insert( $project_tasks->data->tasks_table_name, $db_task_item_columns, array ( '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s' ) );
				
				$task_id = $wpdb->insert_id;
			
			
			// or get the previous saved task and compare what has been changed to write to the log
			
			} else {
			
				$do_update = true;
				
				$task = $wpdb->get_results('SELECT * FROM ' . $project_tasks->data->tasks_table_name . ' WHERE ID = ' . $task_id );
				
				if ( isset( $task[0] )) {
				
					if ( $task[0]->assigned_to != $db_task_item_columns['assigned_to'] ) 	$task_log .= '\n' . $new_log_entry_intro . ' changed "assigned to" to ' . $db_task_item_columns['assigned_to'] == '0'? 'NOT ASSIGNED' : project_tasks_general::find_name_in_array ( $project_tasks->data->get_users (),  $db_task_item_columns['assigned_to'] );
					
					if ( $task[0]->type != $db_task_item_columns['type'] ) 			$task_log .= '\n' . $new_log_entry_intro . ' changed "type" to ' . $db_task_item_columns['type'];
					
					if ( $task[0]->title != $db_task_item_columns['title'] ) 			$task_log .= '\n' . $new_log_entry_intro . ' changed "title" to "' . $db_task_item_columns['title'] . '"';
					
					if ( $task[0]->description != $db_task_item_columns['description'] ) 	$task_log .= '\n' . $new_log_entry_intro . ' changed "description"';
					
					if ( $task[0]->notes != $db_task_item_columns['notes'] ) 			$task_log .= '\n' . $new_log_entry_intro . ' changed "notes"';
					
					if ( $task[0]->due_date != $db_task_item_columns['due_date'] ) 		$task_log .= '\n' . $new_log_entry_intro . ' changed "due date" to ' .  date ( 'd/m/Y', $db_task_item_columns['due_date'] );
					
					if ( $task[0]->progress != $db_task_item_columns['progress'] ) 		$task_log .= '\n' . $new_log_entry_intro . ' changed "progress" to ' . $db_task_item_columns['progress'];
					
					if ( $task[0]->status != $db_task_item_columns['status'] ) 			$task_log .= '\n' . $new_log_entry_intro . ' changed "status" to ' . $db_task_item_columns['status'];
					
					if ( $task[0]->priority != $db_task_item_columns['priority'] ) 		$task_log .= '\n' . $new_log_entry_intro . ' changed "priority" to ' . $db_task_item_columns['priority'];
					
					if ( $task[0]->parent_task != $db_task_item_columns['parent_task'] ) {
					
						if ( $db_task_item_columns['parent_task'] == '0' ) 			$task_log .= '\n' . $new_log_entry_intro . ' removed connection to parent task';
						
						else 												$task_log .= '\n' . $new_log_entry_intro . ' set this task as a child task to task no ' . $db_task_item_columns['parent_task'] ;
						
					}
				}
			}
			
			
			// loop thru every target and save it to the database
			
			if ( $ignore_targets == false ) {
			
				for ( $itarget = 1; $itarget <= $num_targets; $itarget++ ) {
				
					$targets_type = 		isset( $_POST[ 'spt_target_' . $itarget . '_type' ] )? 	$_POST[ 'spt_target_' . $itarget . '_type' ] 	: '';
					
					$targets_id = 			isset( $_POST[ 'spt_target_' . $itarget . '_id' ] )? 		$_POST[ 'spt_target_' . $itarget . '_id' ] 		: '';
					
					$targets_checked = 		isset( $_POST[ 'spt_target_' . $itarget . '_selected' ] )? 	$_POST[ 'spt_target_' . $itarget . '_selected' ] 	: '0';
					
					$targets_item_name = 	isset( $_POST[ 'spt_target_' . $itarget . '_name' ] )? $_POST[ 'spt_target_' . $itarget . '_name' ] : '';
					
					
					//echo '<br>$targets_type: ' . $targets_type . ', $targets_id: ' . $targets_id . ', $targets_checked: ' . $targets_checked . ', $targets_item_name: ' . $targets_item_name;
					 
					// check wheather the connection between task and target allready exists in the database
					
					$target_exists = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $project_tasks->data->task_relation_table_name . ' WHERE task=' . $task_id . ' AND target_type=\'' . $targets_type . '\' AND target_id=\'' . $targets_id . '\';' );
					
					// insert or update database
					
					if ( $targets_checked == '1' ) {
					
						// prepare data for target
						
						$db_task_target_columns = array ( 'task' => $task_id, 'target_type' => $targets_type, 'target_id'	=> $targets_id );
						
						$db_task_target_columns_format = array ( '%d', '%s', '%s' );
	
						if ( $target_exists ) {
							// Update database, NOT IMPLEMENTED NOW BECAUSE THERE ARE NO FIELDS TO UPDATE
						
						} else {
						
							$wpdb->insert( $project_tasks->data->task_relation_table_name, $db_task_target_columns, $db_task_target_columns_format );
							
							$task_log .= '\n' . $new_log_entry_intro . ' added target ' . project_tasks_general::find_name_in_array ( $project_tasks->data->get_targets (), $targets_type, 'target_type' ) . ' ' . $targets_item_name ;
						}
					
					// delete target from database
					
					} else {
						
						if ( $target_exists ) {
							
							$rows_affected = $wpdb->query('DELETE FROM ' . $project_tasks->data->task_relation_table_name . ' WHERE task=' . $task_id . ' AND target_type=\'' . $targets_type . '\' AND target_id=\'' . $targets_id . '\'');
							
							if ( $rows_affected ) $task_log .= '\n' . $new_log_entry_intro . ' removed target ' . project_tasks_general::find_name_in_array ( $project_tasks->data->get_targets (), $targets_type, 'target_type' ) . ' ' . $targets_item_name ;
						}
					}
				}
			}
			
			
			// or, update the task item in the database ( and don't update when the task was created and who was the creator )
			
			if ( $do_update == true ) {
			
				unset( $db_task_item_columns['created_date'] ); 
				
				unset( $db_task_item_columns['creator'] );
				
				$db_task_item_columns['log'] = $wpdb->get_var( 'SELECT log FROM ' . $project_tasks->data->tasks_table_name . ' WHERE id=' . $task_id . ';' ) . $task_log;
				
				$wpdb->update( $project_tasks->data->tasks_table_name, $db_task_item_columns, array( 'id' => $task_id ),  array ( '%d', '%d', '%d','%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s' ), array( '%d' ) );	
				
			}
			echo 'OK';
		}
		
	} //End Class
}

?>