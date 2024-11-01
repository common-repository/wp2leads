<?php
/**
 * Created by PhpStorm.
 * User: oleksii.khodakivskyi
 * Date: 19.08.18
 * Time: 12:19
 */

class Wp2LeadsCron {
    private static $cron_status_option_name = 'wp2leads_cron_maps';
    private $default_interval = 10 * 60;
    private $scheduled_maps = array();

    public function __construct() {
        $this->scheduled_maps = $this->getScheduledMaps();

        $this->addFilter();
        $this->addAction();
    }

    /**
     * Add Cron Filter
     *
     * @access private
     * @return void
     */
    private function addFilter() {
        add_filter('cron_schedules', array($this, 'cron_time_intervals'));
    }

    /**
     * Add the WP Cron Action
     *
     * @access private
     * @return void
     */
    private function addAction() {
        add_action('wp2leads_send_data_to_klick_tipp', array($this, 'sendData'));
    }

    /**
     * Set the schedule hooks
     *
     * @access public
     * @return void
     */
    public function setScheduleHook() {
        if (!wp_next_scheduled('wp2leads_send_data_to_klick_tipp')) {
            wp_schedule_event(time(), 'ten_min', 'wp2leads_send_data_to_klick_tipp');
        }
    }

    /**
     * Clear the schedule hooks
     *
     * @access public
     * @return void
     */
    public function clearScheduleHook() {
        wp_clear_scheduled_hook('wp2leads_send_data_to_klick_tipp');
    }

    /**
     * Add new schedule for wordpress cron
     *
     * @access public
     * @return array
     */
    public function cron_time_intervals( $schedules ) {
        $schedules['ten_min'] = array(
            'interval'=> $this->default_interval,
            'display'=> __( 'Every 10 minutes', 'wp2leads' )
        );

        return $schedules;
    }

    public static function getScheduledMaps() {
        return json_decode(get_option(self::$cron_status_option_name), true);
    }

    public static function isMapTransfering($map_id) {
        global $wpdb;

        $wp2leads_cron_loading_key = 'wp2leads_cron_load_batch__' . $map_id; //Loading added to Background
        $wp2leads_cron_loading = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $wp2leads_cron_loading_key . "%';" );

        if (0 < $wp2leads_cron_loading) {
            return true;
        }

        $wp2leads_cron_preparing_key = 'wp2leads_cron_prepare_batch__' . $map_id; //Preparation added to Background
        $wp2leads_cron_preparing = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $wp2leads_cron_preparing_key . "%';" );

        if (0 < $wp2leads_cron_preparing) {
            return true;
        }

        $wp2leads_cron_transfering_key = 'wp2leads_cron_transfer_batch__' . $map_id; //Preparation added to Background
        $wp2leads_cron_transfering = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $wp2leads_cron_transfering_key . "%';" );

        if (0 < $wp2leads_cron_transfering) {
            return true;
        }

        $wp2leads_cron_loaded_key = 'wp2lead_cron_map_to_api_results__' . $map_id;
        $wp2leads_cron_loaded = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $wp2leads_cron_loaded_key . "%';" );

        if (0 < $wp2leads_cron_loaded) {
            return true;
        }

        $wp2leads_cron_prepared_key = 'wp2lead_cron_map_to_api_prepared__' . $map_id;
        $wp2leads_cron_prepared = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $wp2leads_cron_prepared_key . "%';" );

        if (0 < $wp2leads_cron_prepared) {
            return true;
        }

        return false;
    }

    public static function getScheduledMapsIds() {
        $sheduled_maps = array();
        $option_value = json_decode(get_option(self::$cron_status_option_name), true);

        foreach ($option_value as $key => $data) {
            $sheduled_maps[] = $data['id'];
        }

        return $sheduled_maps;
    }

    public static function save_cron_schedule($map_id, $cron_status, $date_base) {
        global $wpdb;

        $action = '';

        if (!$cron_status && empty($date_base)) {
            $action = 'remove_cron_schedule';
        } elseif (!$cron_status) {
            $action = 'disable_cron_schedule';
        } else {
            $action = 'enable_cron_schedule';
        }

        $existed_cron_maps = self::getScheduledMaps();

        // Check if no map scheduled
        if (empty($existed_cron_maps)) {
            if ($action === 'remove_cron_schedule') {
                return array(
                    'success' => true,
                    'status' => '',
                    'message' => __('Cron task removed successfuly', 'wp2leads'),
                    'status_text' => __( 'Cron not set up', 'wp2leads' )
                );
            }

            $existed_cron_maps = array();
        }

        $is_map_transfering = self::isMapTransfering($map_id);

        $key = 'map_'. $map_id;

        if ($action === 'remove_cron_schedule') {
            if (isset($existed_cron_maps[$key])) {
                if (!$is_map_transfering) {
                    unset($existed_cron_maps[$key]);

                    if (0 === count($existed_cron_maps)) {
                        delete_option(self::$cron_status_option_name);
                    } else {
                        $cron_status = json_encode($existed_cron_maps);
                        $result = update_option(self::$cron_status_option_name, $cron_status);
                    }
                } else {
                    $existed_cron_maps[$key]['status_to_change'] = $action;
                    $cron_status = json_encode($existed_cron_maps);
                    $result = update_option(self::$cron_status_option_name, $cron_status);
                }
            }

            $return = array(
                'success' => 1,
                'message' => __('Cron task removed successfuly', 'wp2leads'),
                'status' => '',
                'status_text' => __('Cron not set up', 'wp2leads')
            );

            return $return;
        }

        if ($action === 'disable_cron_schedule' && $is_map_transfering) {
            $existed_cron_maps[$key]['status_to_change'] = $action;
        } else {
            $existed_cron_maps[$key]['id'] = $map_id;
            $existed_cron_maps[$key]['status'] = $cron_status;
            $existed_cron_maps[$key]['date_base'] = $date_base;

            if (!empty($existed_cron_maps[$key]['status_to_change'])) {
                unset($existed_cron_maps[$key]['status_to_change']);
            }
        }

        $cron_list = json_encode($existed_cron_maps);
        $old_cron_list = get_option( self::$cron_status_option_name );

        if ( $cron_list === $old_cron_list ) {
            $result = true;
        } else {
            $result = update_option(self::$cron_status_option_name, $cron_list);
        }

        if ($result) {
            if ($cron_status) {
                $message = __('Map was successfully scheduled by cron.', 'wp2leads');
                $class = 'active';
                $text = __( 'Cron enabled', 'wp2leads' );
            } else {
                $message = __('Map was successfully unscheduled by cron.', 'wp2leads');
                $class = 'disabled';
                $text = __( 'Cron disabled', 'wp2leads' );
            }

            $return = array(
                'success' => 1,
                'message' => $message,
                'status' => $class,
                'status_text' => $text
            );

            return $return;
        }

        $return = array(
            'error' => true,
            'message' => __('Something went wrong', 'wp2leads')
        );

        return $return;
    }

    /**
     * Execute plugin cron jobs
     *
     * Debug cron with the following url
     * http://[URL]/wp-cron.php?doing_cron
     *
     * @return void
     */
    public function sendData() {
        global $wpdb;

        if(empty($this->getScheduledMaps())) {
            return;
        }

        foreach ($this->getScheduledMaps() as $key => $data) {
            $is_transfer_allowed = Wp2leads_License::is_map_transfer_allowed($data['id']);
            $map = MapsModel::get($data['id']);
            $decodedMap = unserialize($map->mapping);

            if (empty($decodedMap['dateTime'])) {
                $existed_cron_maps = $this->getScheduledMaps();
                unset($existed_cron_maps['map_' . $data['id']]);
                update_option(self::$cron_status_option_name, json_encode($existed_cron_maps));
            } elseif ( $is_transfer_allowed && $data['status']) {
                $existed_cron_maps = $this->getScheduledMaps();
                $date = time();

                if (!empty($data['last_check'])) {
                    $wp2leads_cron_loading_key = 'wp2leads_cron_load_batch__' . $data['id']; //Loading added to Background
                    $wp2leads_cron_loading = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $wp2leads_cron_loading_key . "%';" );

                    $wp2leads_cron_preparing_key = 'wp2leads_cron_prepare_batch__' . $data['id']; //Preparation added to Background
                    $wp2leads_cron_preparing = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $wp2leads_cron_preparing_key . "%';" );

                    $wp2leads_cron_transfering_key = 'wp2leads_cron_transfer_batch__' . $data['id']; //Preparation added to Background
                    $wp2leads_cron_transfering = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $wp2leads_cron_transfering_key . "%';" );

                    // Check if any of cron BG processes is initiating for current map
                    if (0 < $wp2leads_cron_loading || 0 < $wp2leads_cron_preparing || 0 < $wp2leads_cron_transfering) { // Loading data is running
                        $is_loading = true;
                        update_option(self::$cron_status_option_name, json_encode($existed_cron_maps));
                        continue;
                    }

                    // Check if Data loaded and ready for preparing
                    $wp2leads_cron_loaded_key = 'wp2lead_cron_map_to_api_results__' . $data['id'];
                    $wp2leads_cron_loaded = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $wp2leads_cron_loaded_key . "%';" );

                    if (0 < $wp2leads_cron_loaded) {
                        $data_loaded = true;

                        $count = Wp2leads_Background_Cron_Prepare::cron_prepare_bg($data['id']);
                        update_option(self::$cron_status_option_name, json_encode($existed_cron_maps));
                        continue;
                    }

                    // Check if data ready for transfer
                    $wp2leads_cron_prepared_key = 'wp2lead_cron_map_to_api_prepared__' . $data['id'];
                    $wp2leads_cron_prepared = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $wp2leads_cron_prepared_key . "%';" );

                    if (0 < $wp2leads_cron_prepared) {
                        $data_loaded = true;

                        $count = Wp2leads_Background_Cron_Transfer::cron_transfer_bg($data['id']);
                        update_option(self::$cron_status_option_name, json_encode($existed_cron_maps));
                        continue;
                    }

                    if (!empty($existed_cron_maps['map_' . $data['id']]['status_to_change'])) {
                        $action = $existed_cron_maps['map_' . $data['id']]['status_to_change'];

                        unset($existed_cron_maps['map_' . $data['id']]['status_to_change']);

                        if ($action === 'disable_cron_schedule') {
                            $existed_cron_maps['map_' . $data['id']]['status'] = false;
                        } elseif ($action === 'remove_cron_schedule') {
                            unset($existed_cron_maps['map_' . $data['id']]);
                        }

                        if (0 === count($existed_cron_maps)) {
                            delete_option(self::$cron_status_option_name);
                        } else {
                            update_option(self::$cron_status_option_name, json_encode($existed_cron_maps));
                        }

                        continue;
                    }

                    $from_time = $data['last_check'];
                    $till_time = $date;

                    $count = Wp2leads_Background_Cron_Load::cron_load_bg($data['id'], $from_time, $till_time);

                    $existed_cron_maps['map_' . $data['id']]['last_check'] = $date;
                } else {
                    $existed_cron_maps['map_' . $data['id']]['last_check'] = $date;
                }

                update_option(self::$cron_status_option_name, json_encode($existed_cron_maps));
            }
        }
    }

}