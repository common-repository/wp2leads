<?php
/**
 * Modules for transfering data
 *
 * @package Wp2Leads
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wp2leads_Transfer_WP_User_Update {
    private static $key = 'wp_user_update';

    private static $required_column = 'users.ID';

    public static function transfer_init() {
        add_action( 'profile_update', 'Wp2leads_Transfer_WP_User_Update::profile_update', 10 );
        add_action( 'user_register', 'Wp2leads_Transfer_WP_User_Update::user_register', 10 );
    }

    public static function get_label() {
        return __('WP Core: User update', 'wp2leads');
    }

    public static function get_description() {
        return __('This module will transfer user data once new user will be created or existed user changed');
    }

    public static function get_required_column() {
        return self::$required_column;
    }

    public static function get_instruction() {
        ob_start();
        ?>
        <p><?php _e('This module is created for Wordpress user maps.', 'wp2leads') ?></p>
        <p><?php _e('Once new user created or existed user changed data will be transfered to KT account.', 'wp2leads') ?></p>
        <p><?php _e('Requirement: <strong>users.ID</strong> column withing selected data.', 'wp2leads') ?></p>
        <?php

        return ob_get_clean();
    }

    public static function profile_update($user_id) {
        $id = $user_id;

        self::transfer($id);
    }

    public static function user_register($user_id) {
        $id = $user_id;

        self::transfer($id);
    }

    public static function transfer($id) {
        $existed_modules_map = Wp2leads_Transfer_Modules::get_modules_map();

        $condition = array(
            'tableColumn' => 'users.ID',
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

function wp2leads_transfer_wp_user_update_module($transfer_modules) {
    $transfer_modules['wp_user_update'] = 'Wp2leads_Transfer_WP_User_Update';

    return $transfer_modules;
}

add_filter('wp2leads_transfer_modules', 'wp2leads_transfer_wp_user_update_module');