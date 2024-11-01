<?php
/**
 * Modules for transfering data
 *
 * @package Wp2Leads
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wp2leads_Transfer_Business_Directory_Listing {
    private static $key = 'wpbdp_listing';

    private static $required_column = 'wpbdp_listings.listing_id';

    public static function transfer_init() {
        add_action( 'wpbdp_save_listing', 'Wp2leads_Transfer_Business_Directory_Listing::save_listing', 10 );
    }

    public static function get_label() {
        return __('Business Directory Plugin: Listings list changed', 'wp2leads');
    }

    public static function get_description() {
        return __('This module will transfer user data once new listing will be created or existed listing changed');
    }

    public static function get_required_column() {
        return self::$required_column;
    }

    public static function get_instruction() {
        ob_start();
        ?>
        <p><?php _e('This module is created for Business Directory Plugin maps.', 'wp2leads') ?></p>
        <p><?php _e('Once new listing created or existed changed user data will be transfered to KT account.', 'wp2leads') ?></p>
        <p><?php _e('Requirement: <strong>wpbdp_listings.listing_id</strong> column withing selected data.', 'wp2leads') ?></p>
        <?php

        return ob_get_clean();
    }

    public static function save_listing($id) {
        self::transfer($id);
    }

    public static function transfer($id) {
        $existed_modules_map = Wp2leads_Transfer_Modules::get_modules_map();

        $condition = array(
            'tableColumn' => 'wpbdp_listings.listing_id',
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

function wp2leads_transfer_wpbdp_listing_module($transfer_modules) {
    $transfer_modules['wpbdp_listing'] = 'Wp2leads_Transfer_Business_Directory_Listing';

    return $transfer_modules;
}

add_filter('wp2leads_transfer_modules', 'wp2leads_transfer_wpbdp_listing_module');