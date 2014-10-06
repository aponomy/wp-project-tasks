<?php
/*
Plugin Name: Project Tasks
Plugin URI: http://klasehnemark.com
Description: A complete project task management system, perfect for when developing a Wordpress theme as a team. Tasks can be connected to pages, posts, post_types, plugins, templates and custom tasks, and accessed within the Wordpress Bar and Wordpress Admin. It also has a simple process-based information collecting system.
Author: Klas Ehnemark
Version: 0.9.2
Author URI: http://klasehnemark.com

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


require_once("files/project-tasks-admin-bar.php");

require_once("files/project-tasks-admin.php");

require_once("files/project-tasks-ajax.php");

require_once("files/project-tasks-data.php");

require_once("files/project-tasks-form.php");

require_once("files/project-tasks-general.php");

require_once("files/project-tasks-list.php");

require_once("files/project-tasks-public.php");

// require_once("files/project-tasks-process.php");


add_action('activated_plugin','save_project_task_error');

function save_project_task_error(){

    update_option('project_tasks_plugin_error',  ob_get_contents());
}
update_option('project_tasks_plugin_error',  '');


if (!class_exists("project_tasks")) { 

	class project_tasks {
	
		public $admin_bar;
		
		public $admin;
		
		public $ajax;
		
		public $data;
		

		////////////////////////////////////////////////////////////////////////////////
		//
		// INITIALIZE OBJECT
		//
		////////////////////////////////////////////////////////////////////////////////
	
		public function __construct() {
		
		
			///////////////////////////////////////////////////////
			// start initializing
			///////////////////////////////////////////////////////
			
			$this->admin_bar 	= new project_tasks_admin_bar ();
			
			$this->admin 		= new project_tasks_admin ();
			
			$this->ajax 		= new project_tasks_ajax ();
			
			$this->data 		= new project_tasks_data ();
			
			$this->public 		= new project_tasks_public ();


			///////////////////////////////////////////////////////
			// register hooks for activate and deactivate plugin
			///////////////////////////////////////////////////////
			
			register_activation_hook 	( __FILE__, array(&$this, 'on_activate_plugin'));
			
			register_deactivation_hook 	( __FILE__, array(&$this, 'on_deactivate_plugin'));
			
			register_uninstall_hook 		( __FILE__, 'project_tasks::on_uninstall_plugin');

		}
		
		
		////////////////////////////////////////////////////////////////////////////////
		//
		// ACTIVATING AND DEACTIVATING PLUGIN FUNCTION
		//
		////////////////////////////////////////////////////////////////////////////////
		
		public function on_activate_plugin() {
		
			$this->data->update_database ();
		}
		
		public function on_deactivate_plugin() {
		
			// leave data if user deactivates plugin
		}
		
		public static function on_uninstall_plugin() {
		
			project_tasks_data::delete_database ();
		}	
		
		
		//////////////////////////////////////////////////////////////////////////////
		//
		// DEBUG
		//
		//////////////////////////////////////////////////////////////////////////////		
	
		public static function debug ( $what, $die = false ) {
		
			if ( $what == 'mem' ) $what = memory_get_peak_usage()/1000000;
		
			$output = '<pre>' . print_r ( $what, true ) . '</pre>';
			
			if ( $die === true ) wp_die ( $output );
			else echo $output;
		
		}

		
	} //End Class
}

global $project_tasks;

if (class_exists("project_tasks")) { $project_tasks = new project_tasks(); }

echo get_option('project_tasks_plugin_error');

?>