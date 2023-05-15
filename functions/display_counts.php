<?php
//Function to retrieve and display counts in a table format for each locaiton
//Return a html object to be rendered in admin page

require_once( plugin_dir_path( __FILE__ ) . '../constants.php');
require_once( plugin_dir_path( __FILE__ ) . '../covid-counter-plugin.php');


function retrieve_count($location) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'covid_counter_movements';

    $count = $wpdb->get_row("
        SELECT SUM(CASE WHEN type = 'entry' THEN 1 WHEN type = 'exit' THEN -1 END) AS count
        FROM $table_name
        WHERE location = '$location'
    ");

    $count->count = (int) $count->count;

    return $count->count;
}

function retrieve_table_data()
{
    global $locations;
    $counts = array();
    foreach ($locations as $location) {
        $counts[$location['display_name']] = retrieve_count($location['name']);
    }
    return $counts;
    
}

?>