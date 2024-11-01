<?php


class BackgroundProcessManager {
    public static $iteration_limit = 500; // BackgroundProcessManager::get_iteration_limit()
    public static $maptoapi_bg_processes = array(
        'wp2leads_maptoapi_bg_in_process' => array(),
    );

    public static function get_iteration_limit() {
        return BackgroundProcessManager::$iteration_limit;
    }

    public static function terminate_maptoapi_bg_in_process_for_not_existed_maps() {
        $map_not_exists = array();

        $prepare_in_progress = BackgroundProcessManager::get_prepare_bg_in_process();
        $load_in_progress = BackgroundProcessManager::get_load_bg_in_process();

        if (!empty($load_in_progress)) {
            foreach ($load_in_progress as $map_id => $count) {
                $map_object = MapsModel::get($map_id);

                if (empty($map_object)) {
                    $map_not_exists[] = $map_id;
                }
            }
        }

        if (!empty($prepare_in_progress)) {
            foreach ($prepare_in_progress as $map_id => $count) {
                $map_object = MapsModel::get($map_id);

                if (empty($map_object)) {
                    $map_not_exists[] = $map_id;
                }
            }
        }

        foreach (BackgroundProcessManager::$maptoapi_bg_processes as $bg_process => $bg_process_data) {
            $bg_process_in_progress = BackgroundProcessManager::get_transient( $bg_process );

            if (!empty($bg_process_in_progress)) {
                foreach ($bg_process_in_progress as $map_id => $process) {
                    $map_object = MapsModel::get($map_id);

                    if (empty($map_object)) {
                        $map_not_exists[] = $map_id;
                    }
                }
            }
        }

        if (!empty($map_not_exists)) {
            $map_not_exists = array_unique($map_not_exists);

            foreach ($map_not_exists as $map_id) {
                BackgroundProcessManager::terminate_map_to_api($map_id);
            }
        }
    }

    public static function terminate_map_to_api($map_id) {
        global $wpdb;

        // Delete Loading BG Process
        $load_in_progress = BackgroundProcessManager::get_transient( 'wp_wp2leads_maptoapi_load__' . $map_id);
        if (!empty($load_in_progress)) $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wp2leads_maptoapi_load_batch_".$map_id."%'" );
        BackgroundProcessManager::delete_transient( 'wp_wp2leads_maptoapi_load__' . $map_id);

        // Delete Preparing BG Process
        $prepare_in_progress = BackgroundProcessManager::get_transient( 'wp_wp2leads_maptoapi_prepare__' . $map_id );
        if (!empty($prepare_in_progress)) $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wp2leads_maptoapi_prepare_batch_".$map_id."%'" );
        BackgroundProcessManager::delete_transient( 'wp_wp2leads_maptoapi_prepare__' . $map_id);

        // Delete Transfering BG Process
        $maptoapi_bg_in_process = BackgroundProcessManager::get_transient('wp2leads_maptoapi_bg_in_process');

        if ($maptoapi_bg_in_process && !empty($maptoapi_bg_in_process[$map_id])) {

            $bg_total = 0;
            $bg_done = 0;
            $bg_count = 0;
            $bg_new = 0;
            $bg_updated = 0;

            foreach ($maptoapi_bg_in_process[$map_id] as $batch_key => $transfer_data) {
                $bg_total += $transfer_data['total'];
                $bg_done += $transfer_data['done'];
                $bg_count += $transfer_data['count'];
                $bg_new += $transfer_data['new'];
                $bg_updated += $transfer_data['updated'];

                $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wp_wp2leads_maptoapi_batch_".$batch_key."%'" );
            }


            unset($maptoapi_bg_in_process[$map_id]);

            $total_transferred = $bg_new + $bg_updated;

            if ($total_transferred > 0) {
                $data = array(
                    'time' => time(),
                    'map_id' => $map_id,
                    'transfer_type' => 'manually',
                    'statistics' => array (
                        __( 'Total Amount', 'wp2leads' ) => $bg_total,
                        __( 'New subscribers', 'wp2leads' ) => $bg_new,
                        __( 'Updated subscribers', 'wp2leads' ) => $bg_updated,
                        __( 'Total transferred', 'wp2leads' ) => $total_transferred
                    )
                );

                StatisticsManager::saveStatistics($data);
            }

            if (empty($maptoapi_bg_in_process)) {
                delete_transient('wp2leads_maptoapi_bg_in_process');
            } else {
                BackgroundProcessManager::set_transient('wp2leads_maptoapi_bg_in_process', $maptoapi_bg_in_process);
            }
        }

        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wp2lead_map_to_api_results_load__".$map_id."%'" );
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wp2lead_map_to_api_results__".$map_id."%'" );

        return true;
    }

    public static function clear_map_to_api_transient() {
        global $wpdb;

        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wp2lead_map_to_api_results_load__%'" );
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wp2lead_map_to_api_results__%'" );
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wp2lead_cron_map_to_api_results__%'" );
    }

    public static function get_transient($name) {
        global $wpdb;
        $transient_name = '_transient_' . $name;
        $sql = "SELECT * FROM {$wpdb->options} WHERE option_name = '{$transient_name}'";
        $result = $wpdb->get_row( $sql, ARRAY_A );

        if (!empty($result)) {
            $value = maybe_unserialize($result['option_value']);
        } else {
            $value = false;
        }

        return $value;
    }

    public static function set_transient($name, $value) {
        global $wpdb;
        $transient_name = '_transient_' . $name;
        $value = maybe_serialize($value);

        $sql = "SELECT * FROM {$wpdb->options} WHERE option_name = '{$transient_name}'";
        $update = $wpdb->get_row( $sql, ARRAY_A );

        if (!empty($update)) {
            $data = array(
                'option_value' => $value,
                'autoload' => 'no'
            );

            $result = $wpdb->update( $wpdb->options,
                $data,
                array( 'option_name' => $transient_name )
            );
        } else {
            $data = array(
                'option_name' => $transient_name,
                'option_value' => $value,
                'autoload' => 'no'
            );

            $result = $wpdb->insert( $wpdb->options, $data );
        }
    }

    public static function delete_transient($name) {
        global $wpdb;
        $transient_name = '_transient_' . $name;
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%".$transient_name."%'" );
    }

    public static function get_load_bg_in_process() {
        global $wpdb;
        $sql = "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_wp2leads_maptoapi_load__%'";
        $result = $wpdb->get_results( $sql, ARRAY_A );
        if (empty($result)) return false;
        $maps = [];

        foreach ($result as $item) {
            $option_name_array = explode('__', $item['option_name']);
            $map_id = $option_name_array[1];
            $maps[$map_id] = $item['option_value'];
        }

        return $maps;
    }

    public static function get_prepare_bg_in_process() {
        global $wpdb;
        $sql = "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_wp2leads_maptoapi_prepare__%'";
        $result = $wpdb->get_results( $sql, ARRAY_A );
        if (empty($result)) return false;
        $maps = [];

        foreach ($result as $item) {
            $option_name_array = explode('__', $item['option_name']);
            $map_id = $option_name_array[1];
            $maps[$map_id] = $item['option_value'];
        }

        return $maps;
    }
}