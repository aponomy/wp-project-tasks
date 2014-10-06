

/*///////////////////////////////////////////////////////

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

////////////////////////////////////////////////////////*/


project_tasks = {

	mode : 'admin_view',
	
	post_id : 0,
	
	list_page : 1,
	
	open_task_id : 0,
	
	previous_task_id : 0,
	
	next_task_id : 0,
	
	task_list_has_loaded : false,
	
	
	init: function () {
	
		// init list if it appears on admin bar
		
		jQuery("#wp-admin-bar-project_tasks").bind("mouseenter", function() {
		
			project_tasks.get_post_id();
			
			project_tasks.mode = 'admin_bar';
			
			jQuery("#wp-admin-bar-project_tasks_content").removeClass('spt_hidden');
			
			if ( project_tasks.task_list_has_loaded == false ) {
			
				project_tasks.task_list_has_loaded = true;
				
				project_tasks.update_task_list();
				
			}
		});
		
		
		// init list if it appears on admin page
		
		project_tasks.init_task_list_actions();
		
		
		// if it is in adminbar, update number of tasks on this page 
		
		if ( jQuery("#wp-admin-bar-project_tasks").length ) {
		
			project_tasks.get_post_id();
			
			if ( project_tasks.post_id != '' ) {
			
				project_tasks.mode = 'admin_bar';
				
				project_tasks.ajax (
				
					{ action : 'get_number_page_tasks_ajax', post_id : project_tasks.post_id }, function ( data ) { 
					
						jQuery("#spt_number_of_list_items_span").html(data);
						
						jQuery("#spt_number_of_list_items_span").removeClass('spt_hidden');
						
					}
				);
			} else {
			
				jQuery("#spt_number_of_list_items_span").remove();
				
			}
		}

	},
	
	
	// get current page id if there is one
	
	get_post_id : function () {
	
		if ( jQuery("#spt_post_id").length ) {
		
			project_tasks.post_id = jQuery("#spt_post_id").html();
			
			//alert(project_tasks.post_id);
			
			jQuery("#spt_post_id").remove();
			
		}
	},


	update_task_list : function () {
	
		var load_list = true;
		
		if ( project_tasks.mode == 'admin_bar' ) {
		
			if ( project_tasks.post_id != '' ) {
			
				post_id = project_tasks.post_id;
				
				jQuery("#wp-admin-bar-project_tasks_content").html('<a href="javascript:void(0);">Loading...</a>');
				
			} else {
			
				load_list = false;
				
				jQuery("#wp-admin-bar-project_tasks_content").html('<a href="/wp-admin/admin.php?page=project_tasks">Goto Project Tasks to view all Project Tasks</a>');
			
			}
		} else {
		
			jQuery("#project_tasks_content").html('Loading...');
			
			post_id = 0;
		}
	
		if ( load_list == true ) {
		
			project_tasks.ajax (
			
				{ action : 'render_task_list_ajax', post_id: post_id, page_num: project_tasks.list_page }, function ( data ) { 
				
					if ( project_tasks.mode == 'admin_view' ) {
					
						jQuery("#project_tasks_content").html(data);
						
					} else if ( project_tasks.mode == 'admin_bar' ) {
					
						jQuery("#wp-admin-bar-project_tasks_content").html(data);
						
						project_tasks.task_list_has_loaded = true;
						
						if ( spt_number_of_items_in_list ) jQuery("#spt_number_of_list_items_span").text(spt_number_of_items_in_list);
						
					}
					
					project_tasks.init_task_list_actions ();
				}
			);
		}
	},
	
	
	init_task_list_actions : function () {
	
		jQuery("#project_tasks_list").find("tbody").find(".spt_task_row").find("td").bind("click", function() {
		
			jQuery("#wp-admin-bar-project_tasks").removeClass( 'hover' );
			
			jQuery("#wp-admin-bar-project_tasks_content").removeClass( 'hover' );
			
			project_tasks.display_task_window ( jQuery(this).parent().attr('taskid'), 0 );
			
		});
		
		jQuery("#spt_add_new_task").bind("click", function (e) { project_tasks.display_task_window (0, 0); });
		
		jQuery("#spt_refresh_list").bind("click", function (e) { project_tasks.update_task_list(); });
		
		jQuery("#show_onlye_type").change( function() { project_tasks.change_show_only('type', jQuery(this).val());});
		
		jQuery("#show_only_mine").bind("click", function (e) { project_tasks.change_show_only ('mine', jQuery('#show_only_mine:checked').val() == '1' ? '1' : '0' );});
		
		jQuery("#show_only_open").bind("click", function (e) { project_tasks.change_show_only ('open', jQuery('#show_only_open:checked').val() == '1' ? '1' : '0' );});
		
		jQuery("#spt_list_pagination span").bind("click", function(e) { 
		
			if ( !jQuery(this).hasClass('active')) { 
			
				project_tasks.goto_list_page( jQuery(this).attr('id').replace('spt_list_page_', '') );
				
			} 
		});
	},
	
	
	goto_list_page : function ( page_num_el ) {
	
		project_tasks.list_page = page_num_el;
		
		project_tasks.update_task_list();
	},
	
	
	change_show_only :  function ( show_only_type, show_only_value ) {
	
		// when user clicks on task list settings, change settings and reload list
		
		project_tasks.set_cookie ( 'show_only_' + show_only_type, show_only_value );
		
		project_tasks.update_task_list();
	},
	
	
	set_cookie : function ( name, value ) {
	
		// set cookie, expires in one year
		
		var date = new Date();
		
		date.setTime(date.getTime()+(365*24*60*60*1000));
		
		var expires = "; expires="+date.toGMTString();
		
		document.cookie = name+"="+value+expires+"; path=/";	
	},
	
	
	display_task_window : function ( taskid, parent_task ) {
	
		// hide the task list and open the task form in a thickbox window
		
		project_tasks.open_task_id = taskid;
		
		jQuery("#wp-admin-bar-project_tasks_content").addClass('spt_hidden');
		
		var newURL = ajaxurl + '?action=render_task_form_ajax&height=456&width=560&post_id=' + project_tasks.post_id + '&task_id=' + taskid + '&parent_task=' + parent_task;

		if ( taskid != 0 ) tb_show("Edit Task #" + taskid, newURL);
		
		else tb_show("New Task", newURL); 
	},
	
	
	task_window_is_loaded : function () { 
		
		project_tasks.open_task_id = jQuery("#sptask_id").val();
		
		
		// when task form is loaded, bind actions to events in the form
		
		jQuery("#spt_save_task").bind("click", function() { project_tasks.save_task_window(); });
		
		jQuery("#spt_cancel_task").bind("click", function() { project_tasks.close_task(); });
		
		jQuery('#TB_window').bind("unload", function() { project_tasks.reset_list(); });
		
		jQuery("#sptask_content_button_1").bind ("click", function() { project_tasks.switch_tab( 1 ); });
		
		jQuery("#sptask_content_button_2").bind( "click", function() { project_tasks.switch_tab( 2 ); });
		
		jQuery("#sptask_content_button_3").bind( "click", function() { project_tasks.switch_tab( 3 ); });
		
		jQuery("#sptask_content_button_4").bind( "click", function() { project_tasks.switch_tab( 4 ); });
		
		jQuery("#sptask_content_button_5").bind( "click", function() { project_tasks.switch_tab( 5 ); });
		
		jQuery("#spt_complete_button").bind( "click", function() { project_tasks.set_progress_to_complete(); });
		
		jQuery(".spt_datespanner").find('input').bind( "click", function() { project_tasks.focus_and_highlight_el ( this ); });
		
		jQuery(".spt_datespanner").find('input').keyup( function(event) { if ( event.keyCode != 9 ) project_tasks.validate_number_field ( event.target, false, true ); });
		
		jQuery(".spt_datespanner").find('input').bind('focusout', function(event) { project_tasks.validate_number_field ( this, true, false ); });
		
		jQuery("#sptask_progress").bind( "click", function() { project_tasks.focus_and_highlight_el ( this ); });
		
		jQuery("#sptask_progress").keyup( function(event) { if ( event.keyCode != 9 ) project_tasks.validate_number_field ( event.target, false, true ); });
		
		jQuery("#sptask_progress").bind('focusout', function(event) { project_tasks.validate_number_field ( this, true, false ); }); 
		
		jQuery("#spt_subtasks_info").bind("click", function() { project_tasks.switch_tab( 4 ) });

		// lock certain input fields
		
		jQuery("#sptask_tasktype.spt_hidden ~ .spt_read_only_field .spt_change_read_only").bind( "click", function() { project_tasks.view_read_only_select_field ( "#sptask_tasktype" ); });
		
		jQuery("#sptask_taskassignedto.spt_hidden ~ .spt_read_only_field .spt_change_read_only").bind( "click", function() { project_tasks.view_read_only_select_field ( "#sptask_taskassignedto" ); });
		
		jQuery("#sptask_status.spt_hidden ~ .spt_read_only_field .spt_change_read_only").bind( "click", function() { project_tasks.view_read_only_select_field ( "#sptask_status" ); });
		
		jQuery("#sptask_priority.spt_hidden ~ .spt_read_only_field .spt_change_read_only").bind( "click", function() { project_tasks.view_read_only_select_field ( "#sptask_priority" ); });
		
		
		// dispable sub tasks if this is a new task
		
		if ( project_tasks.open_task_id == '0' ) jQuery("#sptask_content_button_4").addClass('spt_hidden');
		
		
		// find next and previous task in list
		
		task_tr = jQuery('#project_tasks_list [taskid="' + project_tasks.open_task_id + '"]');
		
		jQuery(task_tr).parent().children().removeClass('spt_list_selected');
		
		if (task_tr) {
		
			jQuery(task_tr).addClass('spt_list_selected');
			
			project_tasks.previous_task_id = jQuery(task_tr).prev().attr('taskid');
			
			project_tasks.next_task_id = jQuery(task_tr).next().attr('taskid');
			
			if ( project_tasks.previous_task_id && project_tasks.previous_task_id != 0 && project_tasks.previous_task_id != 'undefined' ) {
			
				jQuery('#spt_previous_task').removeClass( 'spt_hidden' );
				
				jQuery("#spt_previous_task").bind ("click", function() { project_tasks.close_and_open_new_task ( project_tasks.previous_task_id, 0 ); });
				
			}
			
			if ( project_tasks.next_task_id && project_tasks.next_task_id != 0 && project_tasks.next_task_id != 'undefined' ) {
			
				jQuery('#spt_next_task').removeClass( 'spt_hidden' );
				
				jQuery("#spt_next_task").bind ("click", function() { project_tasks.close_and_open_new_task ( project_tasks.next_task_id, 0 ); });
				
			}
		} else {
		
			project_tasks.previous_task_id = 0;
			
			project_tasks.next_task_id = 0;
		}
		
		// SUB TASKS IS NOT IMPLEMENTED YET
		// project_tasks.switch_tab( 4 );
	},
	
	reset_task_window : function () {
	
		project_tasks.open_task_id = '0';
		
		project_tasks.action_panel_has_loaded = false;
		
		jQuery("#spt_save_task").unbind("click");
		
		jQuery("#spt_cancel_task").unbind("click");
		
		//jQuery('#TB_window').trigger("unload").unbind().remove();
		
		jQuery("#sptask_content_button_1").unbind("click");
		
		jQuery("#sptask_content_button_2").unbind("click");
		
		jQuery("#sptask_content_button_3").unbind("click");
		
		jQuery("#sptask_content_button_4").unbind("click");
		
		jQuery("#sptask_content_button_5").unbind("click");
		
		jQuery("#spt_complete_button").unbind("click");
		
		jQuery(".spt_datespanner").find('input').unbind("click");
		
		jQuery(".spt_datespanner").find('input').unbind("keyup");
		
		jQuery(".spt_datespanner").find('input').unbind("focusout");
		
		jQuery("#sptask_progress").unbind("click");
		
		jQuery("#sptask_progress").unbind("keyup");
		
		jQuery("#sptask_progress").unbind("focusout");
		
		jQuery("#spt_subtasks_info").unbind("click");
		
		jQuery("#sptask_tasktype.spt_hidden ~ .spt_read_only_field .spt_change_read_only").unbind("click");
		
		jQuery("#sptask_taskassignedto.spt_hidden ~ .spt_read_only_field .spt_change_read_only").unbind("click");
		
		jQuery("#sptask_status.spt_hidden ~ .spt_read_only_field .spt_change_read_only").unbind("click");
		
		jQuery("#sptask_priority.spt_hidden ~ .spt_read_only_field .spt_change_read_only").unbind("click");
		
	},


	close_task : function () {
	
		project_tasks.reset_task_window ();
		
		tb_remove();
	},
	
	
	reset_list : function () {
	
		jQuery('#project_tasks_list [taskid="' + project_tasks.open_task_id + '"]').parent().children().removeClass('spt_list_selected');
	},
	
	
	close_and_open_new_task : function ( task_id, parent_task ) {
	
		project_tasks.reset_task_window ();
		
		jQuery("#TB_imageOff").unbind("click");
		
		jQuery("#TB_closeWindowButton").unbind("click");
		
		jQuery('#TB_window,#TB_overlay,#TB_HideSelect').trigger("unload").unbind().remove();
		
		jQuery("#TB_load").remove();
		
		if (typeof document.body.style.maxHeight == "undefined") {//if IE 6
		
			jQuery("body","html").css({height: "auto", width: "auto"});
			
			jQuery("html").css("overflow","");
		}
		
		document.onkeydown = "";
		
		document.onkeyup = "";
		
		project_tasks.display_task_window ( task_id, parent_task );
	},

	 
	focus_and_highlight_el : function ( el ) {
	
		jQuery( el ).focus();
		
		jQuery( el ).select();
	},
	
	
	view_read_only_select_field : function ( select_el_str ) {
	
		jQuery( select_el_str + " ~ .spt_read_only_field" ).addClass ( 'spt_hidden' );
		
		jQuery( select_el_str ).removeClass ( 'spt_hidden' );
		
		jQuery( select_el_str ).focus();
		
	},
	 
	 
	validate_number_field : function ( number_field, force_validate_number_range, can_refocus ) {
	
	
		// only keep numbers in the input that have had a key up
		
		jQuery( number_field ).val( project_tasks.only_keep_numbers_in_string ( jQuery( number_field ).val() ));
		
		switch (jQuery( number_field ).attr('class')) {
		
			case 'spt_date_day':
			
				if ( jQuery( number_field ).val().length >= 2 || force_validate_number_range == true ) {
				
					jQuery( number_field ).val( project_tasks.only_keep_numbers_in_range ( jQuery( number_field ).val(), 0, 31, 'DD' ));
					
					if ( can_refocus == true ) project_tasks.focus_and_highlight_el (jQuery('.spt_date_month'));
					
				}; 
				
				break;
				
				
			case 'spt_date_month':
			
				if ( jQuery( number_field ).val().length >= 2 || force_validate_number_range == true ) {
				
					jQuery( number_field ).val( project_tasks.only_keep_numbers_in_range ( jQuery( number_field ).val(), 0, 12, 'MM' ));
					
					if ( can_refocus == true ) project_tasks.focus_and_highlight_el (jQuery('.spt_date_year'));
					
				}; 
				
				break;
				
				
			case 'spt_date_year':
			
				if ( jQuery( number_field ).val().length >= 4 || force_validate_number_range == true ) {
				
					jQuery( number_field ).val( project_tasks.only_keep_numbers_in_range ( jQuery( number_field ).val(), 2011, 2016, 'YYYY' ));
					
					if ( can_refocus == true ) project_tasks.focus_and_highlight_el (jQuery('#sptask_notes'));
					
				}; 
				
				break;
				
				
			case 'spt_progress':
			
				if ( jQuery( number_field ).val().length >= 3 || force_validate_number_range == true ) {
				
					jQuery( number_field ).val( project_tasks.only_keep_numbers_in_range ( jQuery( number_field ).val(), 0, 100, '0' ));
					
					if ( can_refocus == true ) project_tasks.focus_and_highlight_el (jQuery('.spt_date_day'));
					
				}; 
				
				break;	
		}
	},
      
      
	only_keep_numbers_in_string : function ( string_with_numbers ) {
	
		var new_string_with_numbers = '';
		
		var i = string_with_numbers.length;
		
		while ( i-- ) { if ( parseInt( string_with_numbers[i] ) == string_with_numbers[i] ) new_string_with_numbers = string_with_numbers[i] + new_string_with_numbers; }
		
		return new_string_with_numbers;
	},
	
	
	only_keep_numbers_in_range : function ( number_in_range, low_number, high_number, return_if_offside ) {
	
		if ( parseInt( number_in_range ) >= low_number && parseInt( number_in_range ) <= high_number ) return number_in_range;
		
		else return return_if_offside;
	},
      
      
     switch_tab : function ( to_tab ) {
     
     	// set new tab and tab window to active an old ones not active
     	jQuery('.sptask_content_button').removeClass('active');
     	
		jQuery('#sptask_content_button_' + to_tab ).addClass('active');
		
     	jQuery('.sptask_edit_page').removeClass('active');
     	
		jQuery('#sptask_edit_page_' + to_tab ).addClass('active');
		
		switch ( to_tab ) {
		
			case 2: project_tasks.load_targets (); break;
			
			case 4: project_tasks.load_subtasks (); break; 
		}
     },
     
     
     load_targets : function () {
     
     	targets_field = jQuery('#spt_relations_field');
     	
     	if ( jQuery(targets_field).html() == 'Loading' ) {
     	
     		jQuery(targets_field).html('Loading...');
     		
     		project_tasks.ajax (
     		
     			{ action: 'render_task_form_targets_ajax', task_id: project_tasks.open_task_id, post_id: project_tasks.post_id },
     			
     			function ( data ) { jQuery(targets_field).html(data); }
     		);
		}
     },
     
     
     load_subtasks : function () {
     
     	subtask_field = jQuery('#spt_relations_field');
     	
     	if ( jQuery(subtask_field).html() == 'Loading' ) {
     	
     		jQuery(subtask_field).html('Loading...');
     		
     		project_tasks.ajax (
     		
     			{ action: 'render_task_form_subtasks_ajax', task_id: project_tasks.open_task_id },
     			
     			function ( data ) { 
     			
     				jQuery(subtask_field).html(data); 
     				
     				jQuery("#spt_delete_subtask_button").addClass('disabled');
     				
     				jQuery("#spt_edit_subtask_button").addClass('disabled');
     				
     				jQuery("#spt_add_new_subtask_button").bind('click', function() { 
     				
     					project_tasks.close_and_open_new_task ( 0, project_tasks.open_task_id );
     				});
     			}
     		);
		}   
     },

     
     set_progress_to_complete : function () {
     
     	// set progress to 100% and change status to closed
     	
     	jQuery("#sptask_progress").val('100');
     	
     	project_tasks.view_read_only_select_field ( "#sptask_status" );
     	
     },
     
     ajax : function ( data, fn_success, fn_error ) {
     
		jQuery.ajax({
		
			type: "POST", url: ajaxurl, dataType: 'html', data: data, context: document.body,
			
			success: function( data) { fn_success ( data ); },
			
			error: function ( jqXHR, textStatus, errorThrown )  { if ( fn_error ) { fn_error(); } else { alert(textStatus + ' ' + errorThrown ); }}
		});      
     },
     
     toggle_buttons_active : function ( doActive ) {
     
     	if ( doActive == true ) {
     	
			jQuery("#spt_save_task").attr("disabled", "");
			
			jQuery("#spt_save_task").removeClass("disabled");
			
			jQuery("#spt_cancel_task").attr("disabled", ""); 
			
			jQuery("#spt_save_task").removeClass("disabled"); 
			
     	} else {
     	
			jQuery("#spt_save_task").attr("disabled", "disabled");
			
			jQuery("#spt_save_task").addClass("disabled");
			
			jQuery("#spt_cancel_task").attr("disabled", "disabled"); 
			
			jQuery("#spt_save_task").addClass("disabled");
			
     	}
     },
	
	save_task_window : function () {
	
		data_ok = false;
	
	
		// disable save and cancel buttons
		project_tasks.toggle_buttons_active( false );


		// store data in a object
		savedata = { 
			action 				: 'save_task_form_ajax', 
			
			task_id 				: jQuery("input[name=sptask_id]").val(), 
			
			parent_task			: jQuery("input[name=sptparent_task_id]").val(),
			
			task_title 			: jQuery("input[name=sptask_title]").val(), 
			
			task_tasktype 			: jQuery("select[name=sptask_tasktype]").val(), 
			
			task_taskassignedto 	: jQuery("select[name=sptask_taskassignedto]").val(), 
			
			task_description 		: jQuery("textarea[name=sptask_description]").val(),
			
			task_relation 			: jQuery("select[name=sptask_relation]").val(), 
			
			task_progress 			: jQuery("input[name=sptask_progress]").val(), 
			
			task_duedate 			: jQuery("#sptask_duedate_day").val() + '-' + jQuery("#sptask_duedate_month").val() + '-' + jQuery("#sptask_duedate_year").val(), 
			
			task_notes 			: jQuery("textarea[name=sptask_notes]").val(),
			
			task_priority			: jQuery("select[name=sptask_priority]").val(),
			
			task_status			: jQuery("select[name=sptask_status]").val()
			
		};
		
		if ( savedata.task_title == '' ) {
		
			alert("Title-field cannot be emtpy.");
			
			project_tasks.toggle_buttons_active( true );
			
		} else {


			// if target window is not loaded, set ignore targets flag into savedata-object
			
			subtask_field = jQuery('#spt_relations_field');
			
     		if ( ( jQuery(subtask_field).html() == 'Loading' || jQuery(subtask_field).html() == 'Loading...' ) && savedata.task_id != '0' ) {
     		
     			savedata.ignore_targets = 1;
     			
     			data_ok = true;
     			
     			
     		} else {

				// get info from the targets list
				
				task_has_target = false;
				
				savedata.num_targets = jQuery("#spt_num_target").val();
				
				for (itarget = 1; itarget <= savedata.num_targets; itarget++) {
				
					target_type = jQuery("input[name=spt_target_" + itarget + "_type]").val();
					
					target_id = jQuery("input[name=spt_target_" + itarget + "_id]").val();
					
					target_name = jQuery("input[name=spt_target_" + itarget + "_name]").val();
					
					target_selected = jQuery("#spt_target_" + itarget + "_selected:checked").val();
					
					if ( !target_selected ) target_selected = 0;
					
					if ( target_selected != 0 ) task_has_target = true;
					
					eval('savedata.spt_target_' + itarget + '_type = target_type');
					
					eval('savedata.spt_target_' + itarget + '_id = target_id');
					
					eval('savedata.spt_target_' + itarget + '_name = target_name');
					
					eval('savedata.spt_target_' + itarget + '_selected = target_selected');
					
				}		
				
				if ( task_has_target == false && savedata.task_id == '0' ) {
				
					project_tasks.switch_tab( 2 );
					
					if (confirm("You have'nt connected this task to a target. Are you sure about this? \n\nWithout connecting the task to a target it might be hard to know what to do.")) 
					
						data_ok = true;
						
				} else { 
				
					data_ok = true; 
					
				}
			} 
			
			if ( data_ok == true ) {
			
				project_tasks.ajax (
				
					savedata,
					
					function ( data ) { 
					
						if ( data != 'OK' ) { 
						
							alert ( 'The task could not be saved!\n\n' + data);
							
							project_tasks.toggle_buttons_active( true );
							
						} else { 
						
							tb_remove(); 
							
							project_tasks.update_task_list(); 
							
						}
					}
				);
				
			} else {
			
				project_tasks.toggle_buttons_active( true );
			}
		}
	}

}

jQuery(document).ready(function() { project_tasks.init(); });