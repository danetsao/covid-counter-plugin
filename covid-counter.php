<?php
/**
 * Plugin Name: Covid Counter
 * Version:     1.0.0
 * Author:      The University of Alabama Libraries
 * Author URI:  https://lib.ua.edu/
 */

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

global $wpdb;

$table_name = $wpdb->prefix . 'covid_counter_movements';

register_activation_hook( __FILE__, function() use ( $wpdb, $table_name ) {
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

add_action( 'rest_api_init', function() use ( $wpdb, $table_name ) {
	register_rest_route( 'covid-counter', '/count', array(
		'methods'  => 'GET',
		'callback' => function() use ( $wpdb, $table_name ) {
			return array( 'count' => (int) $wpdb->get_var( "
				SELECT SUM(
					CASE
						WHEN type = 'entry' THEN 1
						WHEN type = 'exit' THEN -1
					END
				) FROM $table_name
			" ) ?? 0 );
		},
	) );
} );
?>
