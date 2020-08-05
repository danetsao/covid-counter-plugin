<?php
function create_enum_string( $enum_array ) {
	return "'" . implode( "', '", $enum_array ) . "'";
}
?>
