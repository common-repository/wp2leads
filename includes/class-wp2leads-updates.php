<?php

/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp2leads
 * @subpackage Wp2leads/includes
 */
class Wp2leads_Updates
{
    public static function wp2leads_3_0_4_update() {
        // return;
        global $wpdb;
        $table = MapsModel::get_table();
        $maps = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);

        if (!empty($maps)) {
            foreach ($maps as $map) {
                $id = $map['id'];
                $api = unserialize($map['api']);
                $need_update = false;

                foreach ($api['fields'] as $key => $field) {
                    if (!empty($field['type']) && in_array($field['type'], array('time', 'datetime', 'date')) && !empty($field['table_columns']) && !empty($field['gmt'])) {
                        $table_column = $field['table_columns'][0];
                        $gmt = $field['gmt'];

                        if (
                            'posts.post_date_gmt' === $table_column && !empty($gmt) ||
                            'amelia_appointments.bookingStart' === $table_column && !empty($gmt) ||
                            false !== strpos($table_column, '_gmt')
                        ) {
                            $api['fields'][$key]['gmt'] = false;
                            $need_update = true;
                        }
                    }
                }

                if ($need_update) {
                    MapsModel::updateMapCell($id, 'api', serialize($api));
                }
            }
        }

        update_option('wp2leads_3_0_4_update', 1);
    }

    public static function wp2leads_3_0_11_update() {
        update_option('wp2leads_3_0_11_update', 1);
    }

    public static function wp2leads_3_0_12_update() {
        // return;
        global $wpdb;
        $table = MapsModel::get_table();
        $maps = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);

        if (!empty($maps)) {
            foreach ($maps as $map) {
                $id = $map['id'];
                $api = unserialize($map['api']);
                $info = unserialize($map['info']);
                if (empty($info['serverId'])) continue;

                // Map should be Woocommerce
                $need_update = false;

                foreach ($api['fields'] as $key => $field) {
                    if (!empty($field['type']) && in_array($field['type'], array('time', 'datetime', 'date')) && !empty($field['table_columns'])) {
                        $table_column = $field['table_columns'][0];

                        if ( 'posts.post_date_gmt' === $table_column && empty($field["gmt_to_local"]) ) {
                            $api['fields'][$key]['gmt_to_local'] = true;
                            $api['fields'][$key]['gmt'] = false;
                            $need_update = true;
                        }
                    }
                }

                if ($need_update) {
                    MapsModel::updateMapCell($id, 'api', serialize($api));
                }
            }
        }

        update_option('wp2leads_3_0_12_update', 1);
    }
}
