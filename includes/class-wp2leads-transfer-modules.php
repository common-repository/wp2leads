<?php
/**
 * Modules for transfering data
 *
 * @package Wp2Leads
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wp2leads_Transfer_Modules {
    private static $module_status_option_name = 'wp2leads_module_maps';

    /**
     * Stores modules for transfering data.
     *
     * @var array|null
     */
    public $transfer_modules = null;

    /**
     * Initialize shipping.
     */
    public function __construct() {
            $this->init();
    }

    /**
     * Initialize shipping.
     */
    public function init() {
        do_action( 'woocommerce_shipping_init' );
    }

    public static function get_transfer_modules_class_names() {
        $transfer_modules = array();

        return apply_filters( 'wp2leads_transfer_modules', $transfer_modules );
    }

    public static function get_modules_map() {
        return json_decode(get_option(self::$module_status_option_name), true);
    }

    public static function save_module_map($map_id, $module_key, $module_status) {
        $existed_modules_map = self::get_modules_map();

        if (empty($existed_modules_map)) {
            $existed_modules_map = array();
        }

        if (!isset($existed_modules_map[$module_key])) {
            $existed_modules_map[$module_key] = array();
        }

        if ($module_status) {
            if (!isset($existed_modules_map[$module_key][$map_id])) {
                $existed_modules_map[$module_key][$map_id] = true;
            }
        } else {
            if (isset($existed_modules_map[$module_key][$map_id])) {
                unset($existed_modules_map[$module_key][$map_id]);
            }
        }

        $modules_list = json_encode($existed_modules_map);
        $old_modules_list = get_option( self::$module_status_option_name );

        if ( $modules_list === $old_modules_list ) {
            $result = true;
        } else {
            $result = update_option(self::$module_status_option_name, $modules_list);
        }

        if ($result) {
            if ($module_status) {
                $message = __('Module enabled successfully.', 'wp2leads');
            } else {
                $message = __('Module disabled  successfully.', 'wp2leads');
            }

            $return = array(
                'success' => true,
                'message' => $message,
            );

            return $return;
        }

        $return = array(
            'error' => true,
            'message' => __('Something went wrong', 'wp2leads')
        );

        return $return;
    }
}