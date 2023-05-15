<?php
require_once( plugin_dir_path( __FILE__ ) . '../constants.php' );

function validate_location( $location ) {
	global $locations;

	foreach ( $locations as $_location ) {
		if ( $location === $_location['name'] ) {
			return true;
		}
	}

	return false;
}
?>