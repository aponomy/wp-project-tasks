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


if (!class_exists("project_tasks_data")) { 

	class project_tasks_data {


		public $tasks_table_name;
		
		public $task_relation_table_name;
		
		public $process_table_name;
		
		public $process_objects_table_name;
		
		public $use_all_post_types = false;
		
		
		private $project_tasks_db_version = '0.1714';
		
		private $found_shortcodes_in_post;
		
		private $manual_page_targets = array();
		


		////////////////////////////////////////////////////////////////////////////////
		//
		// INITIALIZE OBJECT
		//
		////////////////////////////////////////////////////////////////////////////////
	
		public function __construct() {

			// set variables
			
			global $wpdb;
			
			$this->tasks_table_name 			= $wpdb->prefix . "project_tasks";
			
			$this->task_relation_table_name 	= $wpdb->prefix . "project_task_targets";
			
			$this->process_table_name 		= $wpdb->prefix . "project_tasks_process";
			
			$this->process_objects_table_name 	= $wpdb->prefix . "project_task_process_objects";
			
			
			// add filter so we can use "apply_filters( 'spt_page_target', spt_TARGET_TYPE )" on a 
			// page to make that target selectable on that page.
			// NOT ACTIVE
			// add_filter('project_tasks_page_target', array ( $this, 'add_page_target'), 10, 1);
	
		}
		
		
		
		////////////////////////////////////////////////////////////////////////////////
		//
		// UPDATE DATABASE
		//
		////////////////////////////////////////////////////////////////////////////////
		
		public function update_database () {

			// creating a database table
			
		   	global $wpdb;

			$sql = "CREATE TABLE " . $this->tasks_table_name . " (
			
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			
			process mediumint(9) DEFAULT '0',
			
			process_object mediumint(9) DEFAULT '0',
			
			parent_task mediumint(9) DEFAULT '0',
			
			created_date bigint(11) DEFAULT '0' NOT NULL,
			
			last_action_date bigint(11) DEFAULT '0' NOT NULL,
			
			creator mediumint(9) DEFAULT '0',
			
			assigned_to mediumint(9) DEFAULT '0', 
			
			type varchar(80) DEFAULT 'TASK',
			
			title tinytext DEFAULT '' NOT NULL,
			
			description text DEFAULT '' NOT NULL,
			
			notes text DEFAULT '' NOT NULL,
			
			status varchar(255) DEFAULT 'CREATED',
			
			priority mediumint(9) DEFAULT 5, 
			
			due_date bigint(11) DEFAULT '0' NOT NULL,
			
			progress mediumint(9) DEFAULT '0',
			
			log text DEFAULT '',
			
			UNIQUE KEY id (id)
			);
			
			
			CREATE TABLE " . $this->task_relation_table_name . " (
			
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			
			task mediumint(9) NOT NULL DEFAULT '0',
			
			target_type varchar(30) NOT NULL DEFAULT '',
			
			target_id varchar(30) DEFAULT '',
			
			UNIQUE KEY id (id)
			
			);
			
			
			CREATE TABLE " . $this->process_table_name . " (
			
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			
			builtin_name varchar(30) DEFAULT '',
			
			parent_process_object mediumint(9) DEFAULT '0',
			
			name varchar(30) NOT NULL DEFAULT '',
			
			description text DEFAULT '',
			
			created_date bigint(11) DEFAULT '0' NOT NULL,
			
			last_action_date bigint(11) DEFAULT '0' NOT NULL,
			
			creator mediumint(9) DEFAULT '0',
			
			log text DEFAULT '',
			
			UNIQUE KEY id (id)
			
			);			


			CREATE TABLE " . $this->process_objects_table_name . " (
			
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			
			builtin_name varchar(30) DEFAULT '',
			
			process mediumint(9) DEFAULT '0',
			
			type varchar(15) NOT NULL DEFAULT 'ACTIVITY',
			
			instance_of mediumint(9) DEFAULT '0',
			
			connected_to mediumint(9) DEFAULT '0',
			
			connected_type varchar(15) NOT NULL DEFAULT '',
			
			position varchar(255) DEFAULT '',
			
			name varchar(30) NOT NULL DEFAULT '',
			
			description text DEFAULT '',
			
			created_date bigint(11) DEFAULT '0' NOT NULL,
			
			last_action_date bigint(11) DEFAULT '0' NOT NULL,
			
			creator mediumint(9) DEFAULT '0',
			
			log text DEFAULT '',
			
			UNIQUE KEY id (id)
			
			);";	
			

		   	// create table if no one exist
		   	
			if($wpdb->get_var("show tables like '$this->tasks_table_name'") != $this->tasks_table_name) {
			  
			  	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			  	
			  	dbDelta($sql);
			  	
			  	//$rows_affected = $wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'name' => 'first ticket', 'ticket' => '1234-5678-9012-3456-5357' ) );
			  	
			  	add_option("project_tasks_db_version", $this->project_tasks_db_version );
		
			}
		   
		   
		    // updates table of there is an old version
		    
			$installed_ver = get_option( "project_tasks_db_version" );

			if( $installed_ver != $this->project_tasks_db_version ) {
		
			  	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			  	
			  	dbDelta($sql);
			  	
			  	//$rows_affected = $wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'name' => 'third ticket', 'ticket' => 'update' ) );
			  	
			  	update_option( "project_tasks_db_version", $this->project_tasks_db_version );
			  	
			}
			
			
			// create default processes in the database
			
			// project_tasks_process::create_default_processes();
			
		}


		////////////////////////////////////////////////////////////////////////////////
		//
		// DELETE DATABASE
		//
		////////////////////////////////////////////////////////////////////////////////
				
		public static function delete_database () {
					
			// deleting the database table and options
			
			global $wpdb;
			
			$tasks_table_name = $wpdb->prefix . "theme_project_tasks";
			
			$task_relation_table_name = $wpdb->prefix . "theme_project_task_targets";
			
		   	$wpdb->query("DROP TABLE IF EXISTS $tasks_table_name");
		   	
		   	$wpdb->query("DROP TABLE IF EXISTS $task_relation_table_name");
		   	
			delete_option( "project_tasks_db_version" );
		}	
		
		
		
		////////////////////////////////////////////////////////////////////////////////
		//
		// 
		//
		////////////////////////////////////////////////////////////////////////////////
				
		public function get_sql_where_for_post_tasks ( $post_id ) {

			$sql = '';
			
			$post = get_post ( $post_id );
			
			$active_targets = $this->get_active_targets_on_post ( $post );
			
			foreach ( $active_targets as $active_target ) {
			
				if ( $sql != '' ) $sql .= ' OR ';
				
				$sql .= '(' . $this->task_relation_table_name . '.target_type = \'' . $active_target['target_type'] . '\' AND ' . $this->task_relation_table_name . '.target_id = \'' . $active_target['target_id'] . '\')';
			}
			
			return $sql;
		}



		////////////////////////////////////////////////////////////////////////////////
		//
		// GET CONTENT AND APPLY FILTER FUNCTIONS
		//
		////////////////////////////////////////////////////////////////////////////////
		
		// Get task action types
		/*function get_task_action_types () {
			return apply_filters ('projekt_tasks_task_action_types', array( 
				array ( 'id' => 'spt_task_action_type_getinput', 'name' => 'Get Input', 'description' => 'A question is sent to the responder who answers the question to finish the task action.', 'question' => 'Please answer this question:',
					'response' => array ( 'See my answer below' ) ), 
				array ( 'id' => 'spt_task_action_type_getypeconfirmed', 'name' => 'Get Task Type Confirmed', 'description' => 'A question about the task type is sent to the responder who either confirms the current task type or changes it.', 'question' => 'Is the task type correct?',
					'response' => array ( 'Yes, the task type is correct', 'No, the task type is NOT correct', 'Task type was not correct, I\'ve now changed it' ) ), 
				) );
		}*/
		

		// Get task types
		
		public function get_task_types () {
		
			return apply_filters ('projekt_tasks_task_types', array( array ( 'id' => 'spt_task_type_project_task', 'name' => 'Project Task', 'plural' => 'Project Tasks' ), array ( 'id' => 'spt_task_type_bug', 'name' => 'Bug', 'plural' => 'Bugs' ), array ( 'id' => 'spt_task_type_wish', 'name' => 'Wish', 'plural' => 'Wishes' ) ) );
		
		}
		
		
		// Get target categories
		
		public function get_target_categories () {
			
			return apply_filters ('projekt_tasks_task_types', array( array ( 'id' => 'spt_target_category_content', 'name' => 'Content' ), array ( 'id' => 'spt_target_theme_development', 'name' => 'Theme Development' ) ) );		
		
		}
		
		
		// Get task status
		
		public function get_status () {
		
			return apply_filters ('projekt_tasks_task_status', array( array ( 'id' => 'spt_status_created', 'name' => 'CREATED' ), array ( 'id' => 'spt_status_open', 'name' => 'OPEN' ), array ( 'id' => 'spt_status_idle', 'name' => 'IDLE' ), array ( 'id' => 'spt_status_closed', 'name' => 'CLOSED' ), array ( 'id' => 'spt_status_removed', 'name' => 'REMOVED' ) ) );				
		
		}
		
		
		// Get users
		
		public function get_users () {
		
			global $wpdb;
			
			$user_array = array();
			
			$user_ids = $wpdb->get_col( "SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY $wpdb->users.ID ASC" );
			
			foreach ( $user_ids as $user_id ) {
				
				$user = get_userdata( $user_id );
				
				if ( $user->first_name ) array_push ( $user_array, array ('id' => $user_id, 'name' => ucwords( strtolower( $user->first_name . ' ' . $user->last_name ) ), 'email' => $user->user_email ) );
			}
			
			return $user_array;
		}					
		
		
		// Get targets
		
		public function get_targets () {


			// add standard targets. target_id = * means target can have any target id set in active targets on post
			$num_targets = 0;
			
			$all_targets = array ();
			
			$post_types = get_post_types('','objects');
			
			// add all pages and posts
			
			$all_posts = new WP_Query( array( 'post_type' => 'page', 'post_status' => 'any', 'showposts' => '-1' ) );
			
			foreach ($all_posts->posts as $single_post ) {
			
				array_push ( $all_targets, array ( 'target_type' => 'spt_target_content', 'target_id' => $single_post->ID, 'target_category' => 'Content', 'name' => ucfirst( $single_post->post_type ), 'shortening' => 'C', 'item_name' 	=> ucfirst( $single_post->post_title ), 'order' => ( $num_targets++ ), 'description' => 'Connect task to the admin generated content of ' . $single_post->post_title . '.' ));
			
			}
			
			$all_posts = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'any', 'showposts' => '-1' ) );
			
			foreach ($all_posts->posts as $single_post ) {
				
				array_push ( $all_targets, array ( 'target_type' => 'spt_target_content', 'target_id' => $single_post->ID, 'target_category' => 'Content', 'name' => ucfirst( $single_post->post_type ), 'shortening' => 'C', 'item_name' 	=> ucfirst( $single_post->post_title ), 'order' => ( $num_targets++ ), 'description' => 'Connect task to the admin generated content of ' . $single_post->post_title . '.' ));
			
			}
			
			
			// add all other post types instances
			
			if ( $this->use_all_post_types === true ) {
			
				foreach ($post_types as $post_type ) {
				
					if ( $post_type->_builtin != true ) {
					
						$all_posts = new WP_Query( array( 'post_type' => $post_type->name, 'post_status' => 'any', 'showposts' => '-1' ) );
						
						foreach ($all_posts->posts as $single_post ) {
							
							array_push ( $all_targets, array ( 'target_type' => 'spt_target_content', 'target_id' => $single_post->ID, 'target_category' => 'Content', 'name' => ucfirst( $single_post->post_type ), 'shortening' => 'C', 'item_name' 	=> ucfirst( $single_post->post_title ), 'order' => ( $num_targets++ ), 'description' => 'Connect task to the admin generated content of ' . $single_post->post_title . '.' ));
						
						}	
					} 
				}
			}


			// add all available post types
			
			//$post_types = get_post_types('','objects');
			
			foreach ($post_types as $post_type ) {
				
				if ( $post_type->_builtin != true ) {
					
					array_push ( $all_targets, array ( 'target_type' => 'spt_target_post_type', 'target_id' => $post_type->name, 'target_category' => 'Theme Development', 'name' => 'Post type', 'shortening' => 'PT', 'item_name' 	=> ucfirst( $post_type->label ), 'order' => ( $num_targets++ ), 'description' => 'Connect this task to the post type  "' . $post_type->label  . '". This task will appear on every page/post of this post type.' ));
				
				}
			}
			
			
			// add all available page templates
			
			$page_templates = get_page_templates();
			
			foreach ( $page_templates as $page_template_key => $page_template_value ) {
				
				array_push ( $all_targets, array ( 'target_type' => 'spt_target_page_template', 'target_id' => $page_template_key, 'target_category' => 'Theme Development', 'name' => 'Page Template', 'shortening' => 'T', 'item_name' => $page_template_key, 'order' => ( $num_targets++ ), 'description' => 'Connect this task to the page template  "' . $page_template_key . '". This task will appear on every page using this page template.' ));
			
			}
					
					
			// add all available shortcodes
			
			global $shortcode_tags;
			
			foreach ( $shortcode_tags as $shortcode_tag_key => $shortcode_tag_value ) {
				
				array_push ( $all_targets, array ( 'target_type' => 'spt_target_shortcode', 'target_id' => $shortcode_tag_key, 'target_category' => 'Theme Development', 'name' => 'Shortcode', 'shortening' => 'S', 'item_name' 	=> ucfirst( $shortcode_tag_key ), 'order' => ( $num_targets++ ), 'description' => 'Connect this task to the shortcode  "' . $shortcode_tag_key . '". This task will appear on every page/post using this shortcode.' ));
			
			}

			return apply_filters ('projekt_tasks_targets', $all_targets );

		}
		
		
		// Get active targets on post
		
		public function get_active_targets_on_post ( $post ) {
		
			// add standard targets
			
			$active_targets = array(
			
				array ( 'target_type' => 'spt_target_content',   'target_id' => $post->ID, 	  								 'item_name' => ucfirst( $post->post_title ), 'description' => 'Connect this task to the <i>content</i> on this ' . $post->post_type . '. The content is changed in Wordpress admin and does not affect the Theme development.' ) , 
				
				array ( 'target_type' => 'spt_target_post', 	    'target_id' => $post->ID, 	  'name' => ucfirst( $post->post_type ), 'item_name' => ucfirst( $post->post_title ), 'description' => 'Connect this task to the ' . $post->post_type . ' "' . $post->post_title . '" and no particular other function on this ' . $post->post_type . '. Try to be as specific as possible, only choose this if nothing else below will do.' ), 
				
				array ( 'target_type' => 'spt_target_post_type', 'target_id' => $post->post_type, 								 'item_name' => ucfirst( $post->post_type ),  'description' => 'Connect this task to the post type "' . $post->post_type . '". This task generally targets this ' . $post->post_type . ' and all other with the same post type.' )
			);
			
			
			// add page template if this post is a page
			
			if ( $post->post_type == 'page' ) {
			
				$page_template_name = get_post_meta( $post->ID, '_wp_page_template' ,true );
				
				array_push ( $active_targets, array ( 'target_type' => 'spt_target_page_template', 'target_id' => $page_template_name, 'item_name' => ucfirst( $page_template_name ), 'description' => 'Connect this task to the page template "' . $page_template_name . '". This task will appear on every page/post that uses this page template.' ));
			
			}
			
			
			// add shortcodes used in this post
			
			global $shortcode_tags;
			
			if (!empty($shortcode_tags) || is_array($shortcode_tags)) {
			
				$pattern = get_shortcode_regex();
				
				$this->found_shortcodes_in_post = array();
				
				preg_replace_callback('/'.$pattern.'/s', array($this, 'do_shortcode_tag'), $post->post_content);
				
				foreach ( $this->found_shortcodes_in_post as $found_shortcode ) {
				
					array_push ( $active_targets, array ( 
					
						'target_type'	=> 'spt_target_shortcode',
						
						'target_id'	=> $found_shortcode['name'],
						
						'item_name' 	=> ucfirst( $found_shortcode['name'] ), 
						
						'description' 	=> 'Connect this task to the shortcode  "' . $found_shortcode['name'] . '". This task will appear on every page/post that uses this shortcode.' . ( $found_shortcode['params'] != '' ? ' On this ' . $post->post_type . ' ' . $found_shortcode['name']  . ' had parameters <b>' . $found_shortcode['params'] . '</b>' : '' ) ));
				}
			}
						
			// make the array adjustable for others
			
			return apply_filters ('projekt_tasks_active_targets_on_post', $active_targets, $post );
		}
		
		
		
		// Internal: detect shortcodes in regular expression
		
		public function do_shortcode_tag ( $m ) { 
		
			global $shortcode_tags; 
			
			array_push( $this->found_shortcodes_in_post, array( 'name' => $m[2], 'params' => $m[3] )); 
			
		}
		
	} //End Class
} 

?>