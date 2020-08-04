<?php
/**
 * Plugin Name: Covid Counter
 * Version:     1.0.0
 * Author:      The University of Alabama Libraries
 * Author URI:  https://lib.ua.edu/
 */

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

register_activation_hook( __FILE__, function() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'covid_counter_movements';
	$charset_collate = $wpdb->get_charset_collate();

	dbDelta( "
		CREATE TABLE $table_name (
			id INT AUTO_INCREMENT,
			type ENUM('entry', 'exit'),
			occurred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;
	" );
} );
?>
