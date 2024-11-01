<?php
/**
 * Auto Tags Section
 *
 * @package Wp2Leads
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wp2leads_Background_Cron_Load {
    protected static $cron_load_bg;

    public static function init() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-logger.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/abstract-class-wp2leads-background.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/class-wp2leads-background-cron-load-request.php';

        self::$cron_load_bg = new Wp2leads_Background_Cron_Load_Request();
    }

    public static function cron_load_bg($map_id, $from_time, $till_time) {
        $time_difference = $till_time - $from_time;

        self::$cron_load_bg->set_map_id($map_id);
        self::$cron_load_bg->set_from_time($from_time);
        self::$cron_load_bg->set_till_time($till_time);

        $map = MapsModel::get($map_id);
        $mapping = unserialize($map->mapping);
        $count = count(MapsModel::get_map_query_rows_count($mapping));
        $limit = BackgroundProcessManager::get_iteration_limit();
        $offset = 0;
        $iterations = ceil($count / $limit);

        for ($i = 0; $i < $iterations; $i++) {
            $offset = $limit * $i;

            $data = array (
                $map_id, $limit, $offset, $from_time, $till_time
            );

            self::$cron_load_bg->push_to_queue( $data );
        }

        self::$cron_load_bg->save()->dispatch();

        return true;
    }
}

add_action( 'init', array( 'Wp2leads_Background_Cron_Load', 'init' ) );