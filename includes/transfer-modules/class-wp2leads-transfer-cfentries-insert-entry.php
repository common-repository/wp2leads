<?php
/**
 * Modules for transfering data
 *
 * @package Wp2Leads
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wp2leads_Transfer_Cfentries_Inseart_Entry {
    private static $key = 'cfentries_insert_entry';

    private static $required_column = 'vxcf_leads.id';

    public static function transfer_init() {
        add_action( 'vxcf_entry_created', 'Wp2leads_Transfer_Cfentries_Inseart_Entry::entry_created', 10, 3 );
    }

    public static function get_label() {
        return __('Contact Form Entries: Lead update', 'wp2leads');
    }

    public static function get_description() {
        return __('This module will transfer user data once new lead will be created or existed lead changed');
    }

    public static function get_required_column() {
        return self::$required_column;
    }

    public static function get_instruction() {
        ob_start();
        ?>
        <p><?php _e('This module is created for Contact Form Entries maps.', 'wp2leads') ?></p>
        <p><?php _e('Once new lead created or existed changed user data will be transfered to KT account.', 'wp2leads') ?></p>
        <p><?php _e('Requirement: <strong>vxcf_leads.id</strong> column withing selected data.', 'wp2leads') ?></p>
        <?php

        return ob_get_clean();
    }

    public static function entry_created($lead, $entry_id, $form) {
        $id = $entry_id;

        self::transfer($id);
    }

    public static function transfer($id) {
        $existed_modules_map = Wp2leads_Transfer_Modules::get_modules_map();

        $condition = array(
            'tableColumn' => 'vxcf_leads.id',
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

function wp2leads_transfer_cfentries_insert_entry_module($transfer_modules) {
    $transfer_modules['cfentries_insert_entry'] = 'Wp2leads_Transfer_Cfentries_Inseart_Entry';

    return $transfer_modules;
}

add_filter('wp2leads_transfer_modules', 'wp2leads_transfer_cfentries_insert_entry_module');