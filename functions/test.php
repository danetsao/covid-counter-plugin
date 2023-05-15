<?php

echo ( __DIR__  . "\n");


require_once( __DIR__ . '/create_enum_string.php' );
require_once( __DIR__ . '/validate_location.php' );
require_once( __DIR__ . '/validate_type.php' );
require_once( __DIR__ . '/constants.php' );


echo "Testing of create_enum_string function: \n";

$example_array = array( 'value1', 'value2', 'value3' );

foreach( $example_array as $example_string ) {
    echo ($example_string . "\n");
}

$example_enum_string = create_enum_string( $example_array );

echo $example_enum_string . "\n";

echo "Testing of validate_location function: \n";

$example_location = 'gorgas';

echo 'Location: ' . $example_location . ' is ' . validate_location($example_location) . "\n";

$bad_example_location = 'gorgas2';

echo 'Location: ' . $bad_example_location . ' is ' . validate_location($bad_example_location) . "\n";

$example_location = 'bruno';

echo 'Location: ' . $example_location . ' is ' . validate_location($example_location) . "\n";

$bad_example_location = 'not_bruno';

echo 'Location: ' . $bad_example_location . ' is ' . validate_location($bad_example_location) . "\n";

echo "*Note, php bool prints 1 if true and nothing if false\n";

echo "Testing of validate_type function: \n";

$example_type = 'entry';

echo 'Type: ' . $example_type . ' is ' . validate_type($example_type) . "\n";

$bad_example_type = 'entry2';

echo 'Type: ' . $bad_example_type . ' is ' . validate_type($bad_example_type) . "\n";

$example_type = 'exit';

echo 'Type: ' . $example_type . ' is ' . validate_type($example_type) . "\n";

$bad_example_type = 'not_exit';

echo 'Type: ' . $bad_example_type . ' is ' . validate_type($bad_example_type) . "\n";



?>