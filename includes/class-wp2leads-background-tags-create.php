<?php
/**
 * Auto Tags Section
 *
 * @package Wp2Leads
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wp2leads_Background_Tags_Create {
    protected static $run_bg;

    public static function init() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-logger.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/abstract-class-wp2leads-background.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/class-wp2leads-background-tags-create-request.php';

        self::$run_bg = new Wp2leads_Background_Tags_Create_Request();
    }

    public static function run_bg($tags, $map_id, $tags_set_id) {
        $count = 0;

        self::$run_bg->set_map_id($map_id);
        self::$run_bg->set_tags_set_id($tags_set_id);

        foreach ($tags as $tag) {
            self::$run_bg->push_to_queue( $tag );
            $count++;
        }

        self::$run_bg->save()->dispatch();

        return $count;
    }
}

add_action( 'init', array( 'Wp2leads_Background_Tags_Create', 'init' ) );