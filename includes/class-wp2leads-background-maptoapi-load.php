<?php
/**
 * Auto Tags Section
 *
 * @package Wp2Leads
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wp2leads_Background_Maptoapi_Load {
    protected static $bg;

    public static function init() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-logger.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/abstract-class-wp2leads-background.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/class-wp2leads-background-maptoapi-load-request.php';

        self::$bg = new Wp2leads_Background_Maptoapi_Load_Request();
    }

    public static function run($map_id) {
        $map = MapsModel::get($map_id);

        if (empty($map)) {
            return 0;
        }

        self::$bg->set_map_id($map_id);

        $mapping = unserialize($map->mapping);
        $count = count(MapsModel::get_map_query_rows_count($mapping));

        $map_to_api_total = BackgroundProcessManager::get_transient('wp2lead_map_to_api_total');

        if (!$map_to_api_total) {
            $map_to_api_total = array();
        }

        $map_to_api_total[$map_id] = (int)$count;

        BackgroundProcessManager::set_transient('wp2lead_map_to_api_total', $map_to_api_total);

        $limit = BackgroundProcessManager::get_iteration_limit();
        $offset = 0;
        $iterations = ceil($count / $limit);

        for ($i = 0; $i < $iterations; $i++) {
            $offset = $limit * $i;

            $data = array (
                $map_id, $limit, $offset
            );

            self::$bg->push_to_queue( $data );
        }

        self::$bg->save()->dispatch();

        return $i;
    }
}

add_action( 'init', array( 'Wp2leads_Background_Maptoapi_Load', 'init' ) );