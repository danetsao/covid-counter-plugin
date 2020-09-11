<?php
/**
 * Plugin Name: Covid Counter
 * Version:     1.0.0
 * Author:      The University of Alabama Libraries
 * Author URI:  https://lib.ua.edu/
 */

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
require_once( plugin_dir_path( __FILE__ ) . 'constants.php' );
require_once( plugin_dir_path( __FILE__ ) . 'functions/create_enum_string.php' );
require_once( plugin_dir_path( __FILE__ ) . 'functions/validate_location.php' );
require_once( plugin_dir_path( __FILE__ ) . 'functions/validate_type.php' );

global $wpdb;

$table_name = $wpdb->prefix . 'covid_counter_movements';

register_activation_hook( __FILE__, function() use ( $wpdb, $table_name, $locations, $types ) {
	$locations_string = create_enum_string( array_map( function( $location ) {
		return $location['name'];
	}, $locations ) );
	$types_string = create_enum_string( $types );
	$charset_collate = $wpdb->get_charset_collate();

	dbDelta( "
		CREATE TABLE $table_name (
			id INT AUTO_INCREMENT,
			location ENUM($locations_string),
			type ENUM($types_string),
			occurred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;
	" );

	get_role( 'administrator' )->add_cap( 'create_covid_counter_movements', true );

	add_role( 'covid_counter', 'Covid Counter', array(
		'read' => true,
		'create_covid_counter_movements' => true,
	) );
} );

$namespace = 'covid-counter';

add_action( 'rest_api_init', function() use ( $namespace, $locations, $wpdb, $table_name ) {
	register_rest_route( $namespace, '/counts', array(
		'methods'  => 'GET',
		'callback' => function( WP_REST_Request $request ) use ( $wpdb, $table_name ) {
			$counts = $wpdb->get_results( "
				SELECT location, SUM(CASE WHEN type = 'entry' THEN 1 WHEN type = 'exit' THEN -1 END) AS count
				FROM $table_name
				GROUP BY location
			" );

			foreach ( $counts as $count ) {
				$count->count = (int) $count->count;
			}

			return $counts;
		},
	) );
	register_rest_route( $namespace, '/counts/(?P<location>[a-z]+)', array(
		'methods' => 'GET',
		'callback' => function( WP_REST_Request $request ) use ( $wpdb, $table_name ) {
			$location = $request['location'];
			$count = $wpdb->get_row( "
				SELECT SUM(CASE WHEN type = 'entry' THEN 1 WHEN type = 'exit' THEN -1 END) AS count
				FROM $table_name
				WHERE location = '$location'
			" );

			$count->count = (int) $count->count;

			return $count;
		},
		'args' => array(
			'location' => array(
				'validate_callback' => 'validate_location',
			),
		),
	) );
	register_rest_route( $namespace, '/locations', array(
		'methods'  => 'GET',
		'callback' => function() use ( $locations ) {
			return $locations;
		},
	) );
	register_rest_route( $namespace, '/movements', array(
		'methods'  => 'POST',
		'callback' => function( WP_REST_Request $request ) use ( $wpdb, $table_name ) {
			$wpdb->insert( $table_name, array(
				'location' => $request['location'],
				'type'     => $request['type'],
			) );

			return new WP_REST_Response( null, 201 );
		},
		'args' => array(
			'location' => array(
				'required'          => true,
				'validate_callback' => 'validate_location',
			),
			'type' => array(
				'required'          => true,
				'validate_callback' => 'validate_type',
			),
		),
		'permission_callback' => function() {
			return current_user_can( 'create_covid_counter_movements' );
		},
	) );
	register_rest_route( $namespace, '/analytics', array(
		'methods'  => 'GET',
		'callback' => function() use ( $wpdb, $table_name ) {
			return $wpdb->get_results("
				SELECT MONTH(occurred_at) AS month, DAY(occurred_at) AS day, HOUR(occurred_at) AS hour, location, COUNT(id) AS number_of_entries
				FROM wp_covid_counter_movements
				GROUP BY month, day, hour, location
				ORDER BY month, day, hour, location
			");
		},
	) );
} );

$menu_slug = 'covid-counter-analytics';

add_action( 'admin_menu', function() use ( $menu_slug ) {
	add_menu_page( 'COVID Counter Analytics', 'COVID Counter Analytics', 'read', $menu_slug, function() {
		?>
		<button id="covid-counter-analytics-download-csv-button">Download CSV</button>
		<?php
	} );
} );

add_action( 'admin_enqueue_scripts', function( $hook_suffix ) use ( $menu_slug, $namespace ) {
	if ( $hook_suffix === 'toplevel_page_' . $menu_slug ) {
		wp_enqueue_style( 'covid_counter_analytics_style', plugins_url( 'analytics.css', __FILE__ ) );

		$script_handle = 'covid_counter_analytics_script';

		wp_register_script( $script_handle, plugins_url( 'analytics.js', __FILE__ ), array(), null, true );
		wp_localize_script( $script_handle, 'covidCounterAnalytics', array( 'covidCounterApiBaseUrl' => rest_url() . $namespace ) );
		wp_enqueue_script( $script_handle );
	}
} );
?>
