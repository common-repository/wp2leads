<?php
/**
 * Auto Tags Section
 *
 * @package Wp2Leads
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wp2leads_Background_Cron_Transfer {
    protected static $cron_transfer_bg;

    public static function init() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-logger.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/abstract-class-wp2leads-background.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/class-wp2leads-background-cron-transfer-request.php';

        self::$cron_transfer_bg = new Wp2leads_Background_Cron_Transfer_Request();
    }

    public static function cron_transfer_bg($map_id) {
        global $wpdb;
        $wp2leads_cron_prepared_key = 'wp2lead_cron_map_to_api_prepared__' . $map_id;

        $saved_results_transient = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%".$wp2leads_cron_prepared_key."%'", ARRAY_A );
        $count = count($saved_results_transient);

        foreach ($saved_results_transient as $loaded_data) {
            $data_array = explode('__', $loaded_data['option_name']);

            $from = $data_array[4];
            $till = $data_array[5];
            $from_time = $data_array[2];
            $till_time = $data_array[3];
            $loaded_data_name = $loaded_data['option_name'];

            $data = array (
                $map_id,
                $from,
                $till,
                $from_time,
                $till_time,
                $loaded_data_name
            );

            self::$cron_transfer_bg->push_to_queue( $data );
        }

        self::$cron_transfer_bg->save()->dispatch();

        return $count;
    }
}

add_action( 'init', array( 'Wp2leads_Background_Cron_Transfer', 'init' ) );