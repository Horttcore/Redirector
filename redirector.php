<?php
/*
Plugin Name: Redirector
Plugin URL: http://horttcore.de/plugin/redirector
Description: Redirect any page to an internal or external URL
Version: 3.0.1
Author: Ralf Hortt
Author URL: http://horttcore.de/
*/



/**
 * Security, checks if WordPress is running
 **/
if ( !function_exists( 'add_action' ) ) :
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
endif;



/**
 * Kickstart
 */
if ( is_admin() )
	require_once( 'classes/class.redirector.admin.php' );
else
	require_once( 'classes/class.redirector.php' );
