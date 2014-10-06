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

if (!class_exists("project_tasks_admin")) { 

	class project_tasks_admin {
	

		////////////////////////////////////////////////////////////////////////////////
		//
		// INITIALIZE OBJECT
		//
		////////////////////////////////////////////////////////////////////////////////
	
		public function __construct() {

			// Initialization stuff
			
			add_action('admin_init', array(&$this, 'wordpress_admin_init'));
			
			add_action('admin_menu', array (&$this, 'wordpress_admin_menu' ) );
						
		}
		
		
		////////////////////////////////////////////////////////////////////////////////
		//
		// INIT ADMIN
		//
		////////////////////////////////////////////////////////////////////////////////
	
		function wordpress_admin_init() {
		
			$page = isset($_GET['page'])? $_GET['page'] : '';
		
			if( $page == 'project_tasks' || $page == 'project_tasks_processes' ) project_tasks_general::load_scripts_and_styles ();
			
		}

		
		////////////////////////////////////////////////////////////////////////////////
		//
		// RENDER ADMIN MENUS
		//
		////////////////////////////////////////////////////////////////////////////////		
		
		function wordpress_admin_menu () {
		
			add_menu_page( 'Project Tasks', 'Project Tasks', 'administrator', 'project_tasks', array ( &$this, 'project_task_admin_page') ,	plugins_url('project-tasks' ) . '/files/project-task-icon-small.png' );
			
			//add_submenu_page( 'project_tasks', 'Processes', 'Processes', 'administrator', 'project_tasks_processes', array ( &$this, 'project_tasks_processes_page') ); 
			
			//add_submenu_page( 'project_tasks', 'Agile Development', 'Agile Development', 'administrator', 'project_tasks_agile', array ( &$this, 'project_tasks_agile_page') ); 
			
			//add_submenu_page( 'project_tasks', 'Settings', 'Settings', 'administrator', 'project_tasks_settings', array ( &$this, 'project_tasks_settings_page') );
			
		}


		////////////////////////////////////////////////////////////////////////////////
		//
		// RENDER ADMIN PROJECT TASKS PAGE
		//
		////////////////////////////////////////////////////////////////////////////////
		
		function project_task_admin_page () {
		
			echo '
				<div class="wrap">
					<div id="icon-themes" class="icon32" style="background: url(' . plugins_url('project-tasks' ) . '/files/project-task-icon-large.png' . ') no-repeat;"><br/></div>
					<h2>Project Tasks</h2>
					<div id="project_tasks_content">';
					
			$task_list = new project_tasks_list();
			
			$task_list->render_list();
			
			echo '	</div></div>
			';
		}	
		
		
		////////////////////////////////////////////////////////////////////////////////
		//
		// RENDER ADMIN PROCESSES PAGE
		//
		////////////////////////////////////////////////////////////////////////////////
		
		function project_tasks_processes_page () {
		
			global $project_tasks;
		
			$project_tasks->data->update_database ();
		
			echo '
				<div class="wrap">
					<div id="icon-themes" class="icon32" style="background: url(' . plugins_url('project-tasks' ) . '/files/project-task-icon-large.png' . ') no-repeat;"><br/></div>
					<h2>Processes</h2>
					<div id="project_tasks_content">';
					
			$process = new project_tasks_process();
			
			$process->render_process_map( 3 );
			
			echo '	</div></div>
			';
		}	
		
		
		
		
		////////////////////////////////////////////////////////////////////////////////
		//
		// RENDER ADMIN AGILE PAGE
		//
		////////////////////////////////////////////////////////////////////////////////
		
		function project_tasks_agile_page () {
		
			echo '
				<div class="wrap">
					<div id="icon-themes" class="icon32" style="background: url(' . plugins_url('project-tasks' ) . '/files/project-task-icon-large.png' . ') no-repeat;"><br/></div>
					<h2>Agile Development</h2>
					<div id="project_tasks_content">';
			
			echo '	</div></div>
			';
		}	
		
		
		
		
		////////////////////////////////////////////////////////////////////////////////
		//
		// RENDER ADMIN SETTINGS PAGE
		//
		////////////////////////////////////////////////////////////////////////////////
		
		function project_tasks_settings_page () {
		
			echo '
				<div class="wrap">
					<div id="icon-themes" class="icon32" style="background: url(' . plugins_url('project-tasks' ) . '/files/project-task-icon-large.png' . ') no-repeat;"><br/></div>
					<h2>Project Tasks Settings</h2>
					<div id="project_tasks_content">';
			
			echo '	</div></div>
			';
		}	
		
	} //End Class
} 
?>