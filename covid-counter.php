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
$movement_types = array( 'entry', 'exit' );

register_activation_hook( __FILE__, function() use ( $wpdb, $table_name, $movement_types ) {
	$movement_types_string = "'" . implode( "', '", $movement_types ) . "'";
	$charset_collate = $wpdb->get_charset_collate();

	dbDelta( "
		CREATE TABLE $table_name (
			id INT AUTO_INCREMENT,
			type ENUM($movement_types_string),
			occurred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;
	" );
} );

add_action( 'rest_api_init', function() use ( $wpdb, $table_name, $movement_types ) {
	$namespace = 'covid-counter';

	register_rest_route( $namespace, '/count', array(
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
	register_rest_route( $namespace, '/movement', array(
		'methods'  => 'POST',
		'callback' => function( WP_REST_Request $request ) use ( $wpdb, $table_name, $movement_types ) {
			$type = $request->get_json_params()['type'];

			if ( in_array( $type, $movement_types ) ) {
				$wpdb->insert( $table_name, array( 'type' => $type ) );
				return new WP_REST_Response( null, 201 );
			} else {
				return new WP_Error( 'invalid_type', 'Invalid type', array( 'status' => 400 ) );
			}
		}
	) );
} );
?>
