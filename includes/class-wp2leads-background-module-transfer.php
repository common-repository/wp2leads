<?php
/**
 * Auto Tags Section
 *
 * @package Wp2Leads
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wp2leads_Background_Module_Transfer {
    protected static $module_transfer_bg;

    public static function init() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-logger.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/abstract-class-wp2leads-background.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/class-wp2leads-background-module-transfer-request.php';

        if (class_exists('Wp2leads_Background_Module_Transfer_Request')) {
            self::$module_transfer_bg = new Wp2leads_Background_Module_Transfer_Request();
        }
    }

    public static function module_transfer_bg($map_id, $condition) {
        if (!empty(self::$module_transfer_bg)) {
            self::$module_transfer_bg->push_to_queue( array(
                $map_id,
                $condition,
            ) );

            self::$module_transfer_bg->save()->dispatch();
        }

        return true;
    }
}

add_action( 'init', array( 'Wp2leads_Background_Module_Transfer', 'init' ) );