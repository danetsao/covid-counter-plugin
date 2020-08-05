<?php
require_once( plugin_dir_path( __FILE__ ) . '../constants.php' );

function validate_location( $location ) {
	global $locations;
	return in_array( $location, $locations );
}
?>
