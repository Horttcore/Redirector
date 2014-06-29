<?php
if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ) exit();

global $wpdb;
$sql = "DELETE FROM $wpdb->postmeta WHERE meta_key = '_redirector'";
$wpdb->query($sql);

do_action( 'redirector_uninstall' );
