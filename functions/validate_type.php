<?php
require_once( plugin_dir_path( __FILE__ ) . '../constants.php' );

function validate_type( $type ) {
	return in_array( $type, $movement_types );
}
?>
