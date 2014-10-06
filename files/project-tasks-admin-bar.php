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

if (!class_exists("project_tasks_admin_bar")) { 

	class project_tasks_admin_bar {

		////////////////////////////////////////////////////////////////////////////////
		//
		// INITIALIZE OBJECT
		//
		////////////////////////////////////////////////////////////////////////////////
	
		public function __construct() {

			// Initialization stuff
			
			add_action('wp_enqueue_scripts', array(&$this, 'wordpress_init'));
			
			add_action( 'wp_before_admin_bar_render', array(&$this, 'render_admin_bar' ));
			
		}
			
		
		////////////////////////////////////////////////////////////////////////////////
		//
		// MAIN INIT FUNCTION
		// Runs upon WordPress initialization
		//
		////////////////////////////////////////////////////////////////////////////////
		
		function wordpress_init() {

			global $wp_admin_bar;
			
			if ( !empty( $wp_admin_bar )) {
			
				// enqueue scripts used then adminbar is on
				
				wp_enqueue_script ( 'jquery' );
				
				project_tasks_general::load_scripts_and_styles ();
				
				add_action('wp_head', array( $this, 'set_params_for_public_javascript' ));
			}
		}
		
		function set_params_for_public_javascript() { 
		
			global $post; ?>
			
			<script type="text/javascript"> 
			
				var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
				
				var wp_post_id = "<?php if ( !empty($post)) echo $post->ID; else echo '0' ?>";
				
			</script><?php
		}
		
		
		
		////////////////////////////////////////////////////////////////////////////////
		//
		// RENDER ADMIN MENU AND ADMIN OPTIONS PAGE
		//
		////////////////////////////////////////////////////////////////////////////////		
		
		/*function wordpress_admin_menu () {
		
			add_menu_page('Project Tasks', 'Project Tasks', 'administrator', 'project_tasks', array ( &$this, 'project_task_admin_page') ,plugins_url('/theme-project-task-icon-small.png', __FILE__));
			
			//add_submenu_page ( 'project_tasks', 'Add New', 'Add New', 'administrator', 'project_tasks_add_new', array ( &$this, 'project_task_add_new_admin_page'));
		}
		
		function project_task_admin_page () {
		
		
			//require_once( ABSPATH . 'wp-admin/includes/class-wp-' . $core_classes[ $class ] . '-list-table.php' );
			//return new $class;
		
		
			echo '
				<div class="wrap">
					<div id="icon-themes" class="icon32" style="background: url(' . plugins_url('/project-tasks/project-task-icon-large.png') . ') no-repeat;"><br/></div>
					<h2>Project Tasks</h2>
					<div id="theme_project_tasks_content">
					<!--<ul class="subsubsub">
						<li class="all">
						<li class="publish">
					</ul>-->';
					
					
			$this->render_task_list ();
			
			echo '	</div></div>
			';
		}
		
		function project_task_add_new_admin_page () {
		
		}*/
		
		
		////////////////////////////////////////////////////////////////////////////////
		//
		// RENDER ADMIN BAR
		//
		////////////////////////////////////////////////////////////////////////////////
				
		function render_admin_bar () {
			
			if ( !is_admin() || ( is_admin() && strpos( $_SERVER["REQUEST_URI"] , '/wp-admin/post.php?') !== false ) ) {
			
				global $wp_admin_bar;
				
				global $post;
				
				if ( !empty( $post ) ) { $post_id = $post->ID; }
				
				else { $post_id = ''; }
				
				$wp_admin_bar->add_menu( array (
				
					//'parent' => 'new-content',
					
					'id' => 'project_tasks',
					
					'title' => __('Project Tasks') . ' <span id="spt_number_of_list_items_span" class="pending-count spt_hidden">&nbsp;</span><span id="spt_post_id" class="spt_hidden">' . $post_id . '</span>',
					
					'href' => admin_url( 'admin.php?page=project_tasks')
				));
				
				$wp_admin_bar->add_menu( array (
				
					'parent' => 'project_tasks',
					
					'id' => 'project_tasks_content',
					
					'title' => __('Loading'),
					
					'href' => 'javascript:void();'
					
				));
			}; 
		}

	} //End Class
} 

?>