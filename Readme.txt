=== Project Tasks ===
Contributors: stratoponjak
Donate link: http://klasehnemark.com/wordpress-plugins
Tags: project, project management, task, tasks, bugs, gtd
Requires at least: 3.0
Tested up to: 3.9
Stable tag: 0.9.3

This Wordpress plugin is a complete flexible Projekt Task Management System. It's a very easy to use, but yet a powerful plugin.

== Description ==

This Wordpress plugin is a complete flexible Projekt Task Management System. It helps you take control of all tasks when developing a Wordpress Theme in a team or with a client.

Apart from common task management system features, Project Tasks have unique functions to link each task to a specific object in Wordpress. And when you're editing a page, it knows what objects and elements that page is made of. 

Let's say you have a page that uses the a certain page template, contains three different shortcodes and two different post types. When you create a project task on that page, these templates, shortcodes and posttypes comes up as suggestions to link your task to.

Project Tasks are accessable from the Wordpress Adminbar when browsing your Wordpress site where you can see how many and which tasks are linked to that particular page. 

Project Tasks are also available from the the Wordpress Admin that gives you an overview of all tasks and where you can manage them properly.

== Installation ==

1. Upload the folder `project-tasks` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Once you’ve activated the plugin you access Project Tasks from the Wordpress Admin Menu and from the Wordpress Bar..

== Frequently Asked Questions ==

= Where can I read more about how to use this plugin? =

More information can be found at [klasehnemark.com/project-tasks](http://klasehnemark.com/project-tasks "Documentation of Project Tasks")

= Can I make custom task types? =

Yes, you can by using Wordpress Hooks. Read more at [klasehnemark.com/project-tasks](http://klasehnemark.com/project-tasks "Documentation of Project Tasks")

= Can I customize this plugin? Where are the settings? =

There are plenty of customizations available! Read instructions at how to use Wordpress Hooks at [klasehnemark.com/project-tasks](http://klasehnemark.com/project-tasks "Documentation of Project Tasks")

= Will deactivating the plugin remove all data? =

No, the database tables created by the plugin remains intact when deactivating the plugin. The tables are deleted when removing the plugin completely from Wordpress.

= Is this plugin working in a multisite environment? =

Yes, however, the Project Tasks Menu in Wordpress Admin is only shown in the main site.

== Screenshots ==

1. The Project Tasks Menu in Wordpress Admin
1. The Project Tasks overview in Wordpress Admin
1. Project Tasks in Wordpress Adminbar. The number next to the header is telling how many tasks currently are asigned to this page
1. Dropdown list with all project tasks on a page from Wordpress Adminbar
1. The Edit Project Task Dialog Window
1. Selecting a target for the project task. Here is where some of the magic is happening 
1. A Tasks third tab is for writing notes
1. A Tasks fourth tab is logging everything

== Changelog ==

= 0.9.3 =
* Bugfixes

= 0.9.2 =
* Bugfixes and some adjustments to current Wordpress version

== Changelog ==

= 0.9.1 =
* Changed how plugin-directory is detected, previous method caused the plugin not to work properly in some Wordpress installations
* Corrected a bug that made the project task form display incorrect under some circumstances. 

= 0.9 =
* Initial release
