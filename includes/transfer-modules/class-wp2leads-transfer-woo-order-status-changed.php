<?php
/**
 * Modules for transfering data
 *
 * @package Wp2Leads
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wp2leads_Transfer_Woo_Order_Status_Changed {
    private static $key = 'woo_order_status_changed';

    private static $required_column = 'posts.ID';

    public static function transfer_init() {
        add_action( 'woocommerce_checkout_order_processed', 'Wp2leads_Transfer_Woo_Order_Status_Changed::checkout_order_processed', 10, 3 );
        add_action('woocommerce_order_status_changed', 'Wp2leads_Transfer_Woo_Order_Status_Changed::order_status_changed', 50, 4);
    }

    public static function get_label() {
        return __('Woocommerce: Order status changed', 'wp2leads');
    }

    public static function get_description() {
        return __('This module will transfer user data once order will be created or order\'s status will be changed');
    }

    public static function get_required_column() {
        return self::$required_column;
    }

    public static function get_instruction() {
        ob_start();
        ?>
        <p><?php _e('This module is created for Woocommerce orders maps.', 'wp2leads') ?></p>
        <p><?php _e('Once new order created or existed order\'s status changed user data will be transfered to KT account.', 'wp2leads') ?></p>
        <p><?php _e('Requirement: <strong>posts.ID</strong> column withing selected data.', 'wp2leads') ?></p>
        <?php

        return ob_get_clean();
    }

    public static function checkout_order_processed($order_id, $order_data, $order) {
        $id = $order_id;

        self::transfer($id);
    }

    public static function order_status_changed($order_id, $from, $to, $order_data) {
        $id = $order_id;

        self::transfer($id);
    }

    public static function transfer($id) {
        $existed_modules_map = Wp2leads_Transfer_Modules::get_modules_map();

        $condition = array(
            'tableColumn' => 'posts.ID',
            'conditions' => array(
                0 => array(
                    'operator' => 'like',
                    'string' => (string) $id
                )
            )
        );

        if (!empty($existed_modules_map[self::$key])) {
            foreach ($existed_modules_map[self::$key] as $map_id => $status) {
                $result = Wp2leads_Background_Module_Transfer::module_transfer_bg($map_id, $condition);
            }
        }
    }
}

function wp2leads_transfer_woo_order_status_changed_module($transfer_modules) {
    $transfer_modules['woo_order_status_changed'] = 'Wp2leads_Transfer_Woo_Order_Status_Changed';

    return $transfer_modules;
}

add_filter('wp2leads_transfer_modules', 'wp2leads_transfer_woo_order_status_changed_module');