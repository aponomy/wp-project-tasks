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


if (!class_exists("project_tasks_list")) { 

	class project_tasks_list {

		private $list_previous_category = '';
		
		private $list_category_class = '';
		
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
		// RENDER TASK LIST
		// Called from admin bar by javascript ajax and from admin overview page
		//
		////////////////////////////////////////////////////////////////////////////////
				
		function render_list ( $args = null ) {

			$defaults = array (
			
				'post_id'				=> isset( $_POST[ 'post_id' ] )? $_POST[ 'post_id' ] : '0',
				
				'parent_task'			=> '0',
				
				'show_subtasks'		=> '1',
				
				'show_controls'		=> '1',
				
				'list_id'				=> 'project_tasks_list',
				
				'num_tasks_per_page'	=> isset( $_POST[ 'num_tasks_per_page' ] )? $_POST[ 'num_tasks_per_page' ] : '20',
				
				'page_num'			=> isset( $_POST[ 'page_num' ] )? $_POST[ 'page_num' ] : '1',
				
				'show_only_type'		=> isset($_COOKIE['show_only_type']) && $_COOKIE['show_only_type'] != '' ? $_COOKIE['show_only_type'] : 'Everything',
				
				'show_only_mine' 		=> isset($_COOKIE['show_only_mine']) && $_COOKIE['show_only_mine'] != '' ? $_COOKIE['show_only_mine'] : '0',
				
				'show_only_open'		=> isset($_COOKIE['show_only_open']) && $_COOKIE['show_only_open'] != '' ? $_COOKIE['show_only_open'] : '0',
				
				'columns'				=> array ( 'title', 'targets', 'priority', 'creator', 'assigned_to', 'progress', 'status', 'due_date' )
			);
					
			$r = wp_parse_args( $args, $defaults );
			
			global $project_tasks;
			
			global $wpdb;
			
			$num_columns				= count($r['columns']);
			
			$offset 					= ( $r['page_num']-1 ) * $r['num_tasks_per_page'];
			
			$users 					= $project_tasks->data->get_users ();
			
			$all_targets 				= $project_tasks->data->get_targets ();
			
			$all_status 				= $project_tasks->data->get_status ();
			
			$task_types 				= $project_tasks->data->get_task_types ();
			
			$the_list					= array();
			
			$this_list_ids				= '';
			?>
			
			<div id="<?php echo $r['list_id']; ?>" class="project_tasks_list">
				<table cellpadding="0" cellspacing="0">
					<thead>
						<tr><?php
						
						foreach ( $r['columns'] as $column ) {
						
							switch ( $column ) {
							
								case 'title':
								
									echo '<td class="spt_title">';
									
									if ( $r['show_only_type'] == 'Everything' ) echo 'Title';
									
									else { foreach ( $task_types as $the_task_type ) { if ( $r['show_only_type'] ==  $the_task_type['id'] ) echo $the_task_type['name']; }}
									
									echo '</td>';
									
									break;
									
								case 'targets': 	echo '<td class="spt_targets" id="spt_targets_hd">Targets</td>'; break;
								
								case 'assigned_to': echo '<td class="spt_assigned_to" id="spt_assigned_to_hd">Assigned to</td>'; break;
								
								case 'progress': 	echo '<td class="spt_progress" id="spt_progress_hd">Progress</td>'; break;
								
								case 'status': 	echo '<td class="spt_status" id="spt_status_hd">Status</td>'; break;
								
								case 'due_date':	echo '<td class="spt_due_date" id="spt_due_date_hd">Due Date</td>'; break;
								
								case 'creator':	echo '<td class="spt_creator" id="spt_creator_hd">Created by</td>'; break;
								
								case 'priority':	echo '<td class="spt_priority" id="spt_priority_hd">Priority</td>'; break;
								
							}
						}?>
					</thead>
					<tbody><?php
					
						$previous_target_type = '';
						
						$sql_where = '';
						
						$target_count = 0;
						
						$last_type = '';
						
						$first_row = true;
						
						$task_count = 0;
						
						
						// Build ths SQL String
						
						$sql = 'SELECT * ';
						
						foreach ( $all_targets as $target ) {
						
							if ( $previous_target_type != $target['target_type'] ) {
							
								$target_count++;
								
								$sql .= ', (select count(*) from ' . $project_tasks->data->task_relation_table_name . ' where task = ' . $project_tasks->data->tasks_table_name . '.id AND target_type = "' . $target['target_type'] . '") as target' . $target_count . ' ';
								
								$previous_target_type  = $target['target_type'];
							}
						}
						
						$sql .= ' FROM ' . $project_tasks->data->tasks_table_name;
						
						if ( $r['show_only_mine'] != '0' ) 		$sql_where .= ' assigned_to = ' . get_current_user_id();
						
						if ( $r['show_only_open'] != '0' ) 		$sql_where .= ( $sql_where != '' ? ' AND ' : '' ) . ' status <> \'spt_status_closed\' AND status <> \'spt_status_removed\'';
						
						if ( $r['show_only_type'] != 'Everything' ) 	$sql_where .= ( $sql_where != '' ? ' AND ' : '' ) . ' type = \'' . $r['show_only_type'] . '\'';
						
						if ( $r['post_id'] != '0' ) 				$sql_where .= ( $sql_where != '' ? ' AND ' : '' ) . $project_tasks->data->tasks_table_name . '.ID IN ( SELECT task FROM ' .  $project_tasks->data->task_relation_table_name . ' WHERE ' . $project_tasks->data->get_sql_where_for_post_tasks ( $r['post_id'] ) . ')';
						
						
						$sql_where .= ( $sql_where != '' ? ' AND ' : '' ) . ' parent_task = ' . $r['parent_task'];
			
						if ( $sql_where != '' ) $sql .= ' WHERE ' . $sql_where;
						
						$sql .= ' ORDER BY type, id DESC';
						
						$all_tasks = $wpdb->get_results($sql);
						
						$num_all_tasks = count ( $all_tasks );


						// Get data from database, get all tasks
						
						$tasks = $wpdb->get_results( $sql . ' LIMIT ' . $offset . ', ' . $r['num_tasks_per_page'] . '' );
						
						
						// loop thru all task in the list and store them in an array and the id's in a comma separated string
						
						foreach ( $tasks as $task ) {
						
							$task_count ++;
							
							$the_list_html = '';
							
							if ( $task->type != $last_type && $r['show_only_type'] == 'Everything' ) {
							
								$the_list_html .= '<tr class="spt_task_type_row' . ( $first_row === true ? ' spt_r_firstrow' : '' ) . '"><td colspan="' . $num_columns . '">';
								
								$the_list_html .= project_tasks_general::find_name_in_array ( $task_types, $task->type, 'id', 'plural', '[Unknown Type]');
								
								$the_list_html .= '</td></tr>';
								
								$last_type = $task->type;
								
								$first_row = false;
							}
							
							$taskstatus = project_tasks_general::find_name_in_array ( $all_status, $task->status );
							
							$the_list_html .= '<tr class="spt_task_row spt_task_status_' . $taskstatus . ( $first_row === true ? ' spt_r_firstrow' : '' ) . '" taskid="' . $task->id . '">';
							
							$first_row = false;
							
							foreach ( $r['columns'] as $column ) {
							
								switch ( $column ) {
								
									case 'title': $the_list_html .= '<td class="spt_title">' . $task->id . '. ' . $task->title . '</td>'; break;
									
									case 'targets': 	
									
										$targets_html = '';
										
										$previous_target_type = '';
										
										$target_count = 0;
										
										foreach ( $all_targets as $target ) {
										
											if ( $previous_target_type != $target['target_type'] ) {
											
												$target_count++;
												
												if ( $task->{'target' . $target_count} ) $targets_html .= '<span title="' . $target['name'] . '">' . $target['shortening'] . '</span>';
												
												$previous_target_type  = $target['target_type'];
											
											}
										
										}
										$the_list_html .= '<td class="spt_targets">' . ( $targets_html == '' ? '&nbsp;' : $targets_html ) . '</td>';
										
										break;
										
									case 'assigned_to': $the_list_html .= '<td>' . project_tasks_general::nbsp ( project_tasks_general::find_name_in_array ( $users, $task->assigned_to ), '- none -' ) . '</td>'; break;
									
									case 'progress': 	$the_list_html .= '<td>' . project_tasks_general::nbsp ( $task->progress ) . '%</td>'; break;
									
									case 'status': 	$the_list_html .= '<td>' . project_tasks_general::nbsp ( $taskstatus ) . '</td>'; break;
									
									case 'due_date':	$the_list_html .= '<td>' . project_tasks_general::nbsp (( $task->due_date != '0' ? date( 'd', $task->due_date ) . '/' .  date( 'm', $task->due_date ) . '/' . date( 'Y',  $task->due_date ) : '' ) ) . '</td>'; break; 
									
									case 'creator':	$the_list_html .= '<td>' . project_tasks_general::nbsp ( project_tasks_general::find_name_in_array ( $users, $task->creator ) ) . '</td>'; break;
									
									case 'priority':	$the_list_html .= '<td>' . project_tasks_general::nbsp ( $task->priority ) . '</td>'; break;
								}
							}
							
							$the_list_html .= '</tr>';
							
							$the_list[ $task->id ] = array ( 'task' => $the_list_html, 'subtasks' => '' );
							
							$this_list_ids .= $task->id . ',';
						}
						
						// SUB TASKS IS NOT IMPLEMENTED YET
						// Make another request to the database, get all sub tasks for the current list
						/*if ( $r['show_subtasks'] != '0' ) {
							if ( count($this_list_ids) > 0 ) $this_list_ids = substr( $this_list_ids, 0, -1 );
							$sub_tasks = $wpdb->get_results( 'SELECT title, id, parent_task, assigned_to from ' . $project_tasks->data->tasks_table_name . ' where parent_task in (' . $this_list_ids . ') ORDER BY parent_task ' );
							echo $this_list_ids;
							var_dump($sub_tasks);
							foreach ( $sub_tasks as $sub_task ) {
								if ( isset( $the_list[ $sub_task->parent_task ] ) ) {
									$the_list[ $sub_task->parent_task ]['subtasks'] .= '<span class="" taskid="' . $sub_task->id . '">' . $sub_task->title . ' (' . project_tasks_general::find_name_in_array ( $project_tasks->data->get_users (),  $sub_task->assigned_to ) . ')</span>';
								}
							}
						}*/
						
						
						// loop thru the list and output it
						
						foreach ( $the_list as $list_item ) {
						
							echo $list_item['task'];
							
							// SUB TASKS IS NOT IMPLEMENTED YET
							/*if ( $list_item['subtasks'] != '' ) {
								echo '<tr class="spt_subtaskslist"><td colspan="' . $num_columns . '">' . $list_item['subtasks'] . '</td></tr>';
							}*/
						}
						
						?>
					</tbody>
				</table><?php
				
				if ( $r['show_controls'] == '1' ) {?>
				
					<div class="spt_action_buttons"><?php
					
						$num_pages = ceil( $num_all_tasks / $r['num_tasks_per_page'] );
						
						if ( $num_pages > 1 ) {
						
							echo '<div id="spt_list_pagination">';
							
							for ( $ipage = 1; $ipage <= $num_pages; $ipage++ ) {
							
								echo '<span' . ($r['page_num'] == $ipage ? ' class="active"' : '' ) .' id="spt_list_page_' . $ipage . '">' . $ipage . '</span>';
								
							}
							
							echo '</div>';
						} ?>
						<span id="spt_add_new_task" class="spt_action_button spt_button_primary">Add New Task</span>
						<span id="spt_refresh_list" class="spt_action_button">Refresh</span><!--spt_action_button -->
						<div id="spt_action_alts">
							<div id="spt_action_altchecks">
								<label for="show_only_mine"><input type="checkbox" name="show_only_mine" id="show_only_mine" value="1" <?php if ($r['show_only_mine'] == '1') echo ' checked="checked"'; ?>>that are assigned to me</label>
								<label for="show_only_open"><input type="checkbox" name="show_only_open" id="show_only_open" value="1" <?php if ($r['show_only_open'] == '1') echo ' checked="checked"'; ?>>and Open.</label>
							</div>
							<div id="spt_action_altdrop">
								<label for="show_onlye_type">Show: 
									<select name="show_onlye_type" id="show_onlye_type">
										<option value="Everything" <?php if ( $r['show_only_type'] == 'Everything' ) echo ' selected'; ?>>Everything</option><?php
									
										// display dropdown of task types
										
										foreach ( $task_types as $the_task_type ) {
										
											echo '<option value="' . $the_task_type['id'] . '"' . ( $r['show_only_type'] ==  $the_task_type['id'] ? ' selected="selected"' : '' ) . '>' .  $the_task_type['plural']  . '</option>';
										}?>
									</select>
								</label>
							</div>
						</div>
					</div><?php
				}?>

			</div>
			<script language="javascript">spt_number_of_items_in_list = <?php echo count($tasks)?>;</script><?php
		}
		
	} //End Class
} 

?>