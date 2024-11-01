<?php
/**
 * Modules for transfering data
 *
 * @package Wp2Leads
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wp2leads_Transfer_EA_New_Edit {
    private static $key = 'ea_new_edit';

    private static $required_column = 'ea_appointments.id';

    public static function transfer_init() {
        add_action( 'ea_edit_app', 'Wp2leads_Transfer_EA_New_Edit::edit_app', 10 );
        add_action( 'ea_new_app', 'Wp2leads_Transfer_EA_New_Edit::new_app', 10, 2 );
    }

    public static function get_label() {
        return __('Easy Appointments: New or edit appointment', 'wp2leads');
    }

    public static function get_description() {
        return __('This module will transfer user data once new appointment will be created or existed appointment changed');
    }

    public static function get_required_column() {
        return self::$required_column;
    }

    public static function get_instruction() {
        ob_start();
        ?>
        <p><?php _e('This module is created for Easy Appointments maps.', 'wp2leads') ?></p>
        <p><?php _e('Once new appointment created or existed appointment changed data will be transfered to KT account.', 'wp2leads') ?></p>
        <p><?php _e('Requirement: <strong>ea_appointments.id</strong> column withing selected data.', 'wp2leads') ?></p>
        <?php

        return ob_get_clean();
    }

    public static function edit_app($app_id) {
        $id = $app_id;

        self::transfer($id);
    }

    public static function new_app($app_id, $app_data) {
        $id = $app_id;

        self::transfer($id);
    }

    public static function transfer($id) {
        $existed_modules_map = Wp2leads_Transfer_Modules::get_modules_map();

        $condition = array(
            'tableColumn' => 'ea_appointments.id',
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

function wp2leads_transfer_ea_new_edit_module($transfer_modules) {
    $transfer_modules['ea_new_edit'] = 'Wp2leads_Transfer_EA_New_Edit';

    return $transfer_modules;
}

add_filter('wp2leads_transfer_modules', 'wp2leads_transfer_ea_new_edit_module');