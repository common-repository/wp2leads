<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/admin
 * @version 1.0.2.0
 * @since  1.0.0.0
 */
class Wp2leads_Admin_Ajax {
    public function save_map_before_transfer() {
        $map_id = trim($_POST['mapId']);

        if (!empty($map_id)) {
            $sendApiSettings = trim($_POST['map']);

            $map_to_save = array(
                'map_id'    => $map_id,
                'api'       =>  $sendApiSettings
            );

            if (!empty($_POST["recomended_tags_prefixes"])) {
                MapBuilderManager::update_recomended_tags_prefixes($map_id, $_POST["recomended_tags_prefixes"]);
            }

            $map_global_prefix = !empty($_POST['global_tag_prefix']) ? trim($_POST['global_tag_prefix']) : false;

            if (!$map_global_prefix) {
                delete_option('wp2l_klicktipp_tag_prefix');
            } else {
                $updated = update_option( 'wp2l_klicktipp_tag_prefix', $map_global_prefix );
            }

            $hasSucceeded = MapsModel::update($map_to_save);

            $response = array('success' => 1, 'error' => 0, 'message' => __('Map saved', 'wp2leads'));

            echo json_encode($response);
            wp_die();
        }

        $response = array('success' => 0, 'error' => 1, 'message' => __('No map ID', 'wp2leads'));

        echo json_encode($response);
        wp_die();
    }

    /**
     * Prepare Data for transfer to KT in Background
     *
     * @since 1.0.0.0
     */
    public function prepare_data_for_klicktipp_bg() {
        global $wpdb;
        $data_for_transfer = array();
        $map_id = trim($_POST['mapId']);

        if (!empty($map_id)) {
            $map = MapsModel::get( $map_id );

            $sendApiSettings = trim($_POST['map']);

            $map_to_save = array(
                'map_id'    => $map_id,
                'api'       =>  $sendApiSettings
            );

            $map_global_prefix = !empty($_POST['global_tag_prefix']) ? trim($_POST['global_tag_prefix']) : false;

            if (!$map_global_prefix) {
                delete_option('wp2l_klicktipp_tag_prefix');
            } else {
                $updated = update_option( 'wp2l_klicktipp_tag_prefix', $map_global_prefix );
            }

            if (!empty($_POST["recomended_tags_prefixes"])) {
                MapBuilderManager::update_recomended_tags_prefixes($map_id, $_POST["recomended_tags_prefixes"]);
            }

            $hasSucceeded = MapsModel::update($map_to_save);

            $decodedSendApiSettings = json_decode(stripslashes($sendApiSettings), true);

            $limit = !empty($_POST['limit']) ? $_POST['limit'] : 100000;
            $offset = !empty($_POST['offset']) ? $_POST['offset'] : 0;

            $start = $offset;
            $end = $offset + $limit - 1;

            $saved_results_transient = $wpdb->get_results( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE '%transient_wp2lead_map_to_api_results__". $map_id . '__' . $start . '__' . $end ."%'", ARRAY_A );

            if (count($saved_results_transient) > 0) {
                $mapping = unserialize($map->mapping);
                $results = unserialize($saved_results_transient[0]['option_value']);

                $tags_prefix = ApiHelper::get_map_tags_prefix($map_id);

                $date_to_compare = array();

                if (
                    !empty($mapping['dateTime']) && is_array($mapping['dateTime']) &&
                    (!empty($decodedSendApiSettings['start_date_data']) || !empty($decodedSendApiSettings['end_date_data']))
                ) {
                    $date_to_compare = array(
                        'fields' => $mapping['dateTime']
                    );

                    if (!empty($decodedSendApiSettings['start_date_data'])) {
                        $date_to_compare['date_range']['start'] = $decodedSendApiSettings['start_date_data'];
                    }

                    if (!empty($decodedSendApiSettings['end_date_data'])) {
                        $date_to_compare['date_range']['end'] = $decodedSendApiSettings['end_date_data'];
                    }
                }

                $kt_limited = false;

                if (!Wp2leads_License::is_map_transfer_allowed($map_id)) {
                    $kt_limitation = KlickTippManager::get_initial_kt_limitation();

                    if ($kt_limitation) {
                        $kt_counter = KlickTippManager::get_transfer_counter();

                        if (!$kt_counter) {
                            $kt_limited = (int) $kt_limitation['limit_users'];
                        } else {
                            $kt_limited = (int) $kt_limitation['limit_users'] - (int) $kt_counter['limit_counter'];
                        }
                    }
                }

                $data_for_transfer = ApiHelper::prepareDataForTransfer($decodedSendApiSettings, $results, $tags_prefix, $date_to_compare);
                set_transient('wp2lead_klicktipp_data_for_transfer__' . $map_id . '__' . $start . '__' . $end, $data_for_transfer, 6 * HOUR_IN_SECONDS);

                $response = array('success' => 1, 'error' => 0, 'message' => __('Ready for transfer', 'wp2leads'), 'count' => count($data_for_transfer), 'kt_limited' => $kt_limited, 'result' => $data_for_transfer);

                echo json_encode($response);
                wp_die();
            }

            $response = array('success' => 0, 'error' => 1, 'message' => __('No data for transfer', 'wp2leads'));

            echo json_encode($response);
            wp_die();
        }

        $response = array('success' => 0, 'error' => 1, 'message' => __('No map ID', 'wp2leads'));

        echo json_encode($response);
        wp_die();
    }

    public function get_transfer_modal_data_info() {
        $map_id = trim($_POST['mapId']);

        if (!empty($map_id)) {
            $totally_transfered = StatisticsManager::getTotallyTransferedData($map_id);
            $last_transfered = '';
            $last_transfered_cron = '';
            $crondate_unix = '';

            if ($totally_transfered['time']) {
                $last_transfered = StatisticsManager::convertTimeToLocal($totally_transfered['time']);
            }

            if ($totally_transfered['crontime']) {
                $last_transfered_cron = StatisticsManager::convertTimeToLocal($totally_transfered['crontime']);

                $crondate = new DateTime($last_transfered_cron);
                $crondate_unix = $crondate->format("U");
            }

            $response = array (
                'error' => 0,
                'success' => 1,
                'totally_unique' => $totally_transfered['unique'],
                'totally_all' => $totally_transfered['all'],
                'last_transfered' => $last_transfered,
                'last_transfered_cron' => $last_transfered_cron,
                'last_transfered_cron_unix' => $crondate_unix,
            );

            echo json_encode($response);

            wp_die();
        }

        $response = array('success' => 0, 'error' => 1, 'message' => __('No map ID', 'wp2leads'));

        echo json_encode($response);
        wp_die();
    }

    public function delete_selected_statistics() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $ids = !empty($_POST['statistics_ids']) ? $_POST['statistics_ids'] : false;

        if (empty($ids)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('No statistics IDs', 'wp2leads-wtsr'));

            echo json_encode($response);
            wp_die();
        }

        $counter = 0;

        foreach ($ids as $id) {
            $result = StatisticsModel::delete($id);

            if ($result) {
                $counter++;
            }
        }

        if (empty($counter)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('No items deleted', 'wp2leads-wtsr'));

            echo json_encode($response);
            wp_die();
        }

        $response = array('success' => 1, 'error' => 0, 'message' => $counter . ' ' . __('item(s) deleted', 'wp2leads-wtsr'));

        echo json_encode($response);
        wp_die();
    }

    public function delete_statistic_item() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );

        $id = trim($_POST['id']);

        if (!empty($id)) {
            $result = StatisticsModel::delete($id);

            if ($result) {
                $response = array('success' => 1, 'error' => 0, 'message' => __('Item deleted successfuly', 'wp2leads'));

                echo json_encode($response);
                wp_die();
            }

            $response = array('success' => 0, 'error' => 1, 'message' => __('Can not delete this item', 'wp2leads'));

            echo json_encode($response);
            wp_die();
        }

        $response = array('success' => 0, 'error' => 1, 'message' => __('No item ID', 'wp2leads'));

        echo json_encode($response);
        wp_die();
    }

    public function load_possible_tags_cloud() {
        $map_id = trim($_POST['mapId']);
        $map = $_POST['map'];
        $email = '';
        $user_data = $_POST['userData'];
        $tags_prefix = trim($_POST['tagsPrefix']);
        $tags_cloud = json_decode(stripslashes($_POST['tagsCloud']), true);

        if ( empty( $_POST['email'] ) ) {
            $response = array(
                'error' => 1,
                'success' => 0,
                'tags' => array(),
                'message' => __('Cannot set tags for current User: <br><strong>Email field below is not filled.</strong>', 'wp2leads'),
            );

            echo json_encode($response);
            wp_die();
        }

        if ($map_id && $map) {

            $kt_tags = array();
            $results = array();
            $results[] = (object) json_decode(stripslashes($user_data), true);

            if ( !empty( $_POST['email'] ) ) {

                if (!is_array($_POST['email'])) {
                    $email_array = array($_POST['email']);
                } else {
                    $email_array = $_POST['email'];
                }

                foreach ($email_array as $email_item) {
                    $email_value = trim($email_item);
                    $email_column_valid = filter_var($email_value, FILTER_VALIDATE_EMAIL);

                    if ($email_column_valid) {
                        $email = $email_column_valid;
                        break;
                    }
                }

                if (empty($email)) {
                    $response = array(
                        'error' => 1,
                        'success' => 0,
                        'tags' => array(),
                        'message' => __('Cannot set tags for current User: <br><strong>Email field below is not filled.</strong>', 'wp2leads'),
                    );

                    echo json_encode($response);
                    wp_die();
                }

                $connector = new Wp2leads_KlicktippConnector();
                $logged_in = $connector->login(get_option('wp2l_klicktipp_username'), get_option('wp2l_klicktipp_password'));

                if($logged_in) {
                    $subscriber_id = $connector->subscriber_search($email);

                    if ($subscriber_id) {
                        $subscriber = (array) $connector->subscriber_get($subscriber_id);

                        if (!empty($subscriber['tags'])) {
                            $existed_tags = array();

                            foreach ($subscriber['tags'] as $key => $tag) {
                                $existed_tags[$tag] = !empty($tags_cloud[$tag]) ? $tags_cloud[$tag] : '';

                                if (!$existed_tags[$tag]) {
                                    unset($subscriber['tags'][$key]);
                                }
                            }

                            $kt_tags = array_merge($kt_tags, $subscriber['tags']);
                        }
                    }
                }
            }

            $decoded_map = json_decode(stripslashes($map), true);
            $data_for_transfer = ApiHelper::prepareDataForDisplay($decoded_map, $results, $tags_prefix);
            $tags = $data_for_transfer[$email]['tags'];

            foreach ($tags as $key => $tag) {
                $tag = str_replace('&#38;', '+', $tag);
                $tag = str_replace('&amp;', '+', $tag);
                $tag = str_replace('&', '+', $tag);

                $tags[$key] = $tag;
            }

            $manually_tags = $data_for_transfer[$email]['manually_tags'];
            $detach_tags = array_unique(array_merge($data_for_transfer[$email]['detach_tags'], $decoded_map['detach_tags']['tag_ids']));

            if (!empty($data_for_transfer[$email]['detach_auto_tags'])) {
                foreach ( $data_for_transfer[$email]['detach_auto_tags'] as $tag_name ) {
                    $key = array_search($tag_name, $tags_cloud);

                    if (false !== $key) {
                        $detach_tags[] = (string) $key;
                    }
                }
            }

            $detach_tags = array_unique($detach_tags);

            unset($email);
            unset($map);
            unset($map);
            unset($map);

            ob_start();

            include_once 'partials/ajax/possible-tags-cloud.php';
            $possible_tags_cloud = ob_get_clean();

            $response = array(
                'success' => 1,
                'error' => 0,
                'message' => __('Success', 'wp2leads'),
                'kt_tags' => $kt_tags,
                'tags' => $tags,
                'manually_tags' => $manually_tags,
                'detach_tags' => $detach_tags,
                'possible_tags_cloud' => $possible_tags_cloud,
            );

            echo json_encode($response);
            wp_die();
        }

        $response = array('success' => 0, 'error' => 1, 'message' => __('No map ID', 'wp2leads'));

        echo json_encode($response);
        wp_die();
    }

    /**
     * Prepare data for Klick Tip
     */
    public function ajax_prepare_data_for_klicktipp() {
        $data_for_transfer = array();
        $map_id = trim($_POST['map_id']);

        if (!empty($map_id)) {
            $result_limit = MapBuilderManager::get_map_query_results_limit();

            if (!$result_limit) {
                $result_limit = 1000;
            }

            $map = MapsModel::get( $map_id );
            $decodedMap = unserialize($map->mapping);
            $decodedApiSettings = unserialize($map->api);
            $sendApiSettings = trim($_POST['map']);
            $decodedSendApiSettings = json_decode(stripslashes($sendApiSettings), true);

            $prepared_map = $map;
            $prepared_map->api = serialize($decodedSendApiSettings);


            $results = MapsModel::get_map_query_results($decodedMap, $result_limit, 0, true, $map_id);
            $tags_prefix = ApiHelper::get_map_tags_prefix($map_id);
            $data_for_transfer = ApiHelper::prepareDataForTransfer($decodedSendApiSettings, $results, $tags_prefix);

            set_transient('klicktipp_data_for_transfer', $data_for_transfer);
        }

        $totally_transfered = StatisticsManager::getTotallyTransferedData($map_id);
        // $available_users = count( $data_for_transfer ) - ApiHelper::getSubscribersCounter($map_id)['unique'];
        $available_users = count( $data_for_transfer );
        $last_transfered = '';
        $last_transfered_cron = '';
        $crondate_unix = '';

        if ($totally_transfered['time']) {
            $last_transfered = StatisticsManager::convertTimeToLocal($totally_transfered['time']);
        }

        if ($totally_transfered['crontime']) {
            $last_transfered_cron = StatisticsManager::convertTimeToLocal($totally_transfered['crontime']);

            $crondate = new DateTime($last_transfered_cron);
            $crondate_unix = $crondate->format("U");
        }

        $response = array (
            'error' => 0,
            'success' => 1,
            'available_users' => $available_users > 0 ? $available_users : 0,
            'totally_unique' => $totally_transfered['unique'],
            'totally_all' => $totally_transfered['all'],
            'last_transfered' => $last_transfered,
            'last_transfered_cron' => $last_transfered_cron,
            'last_transfered_cron_unix' => $crondate_unix,
        );

        if ( 0 === $response['available_users'] ) {
            $response['notice']  = '<div class="notice notice-warning inline">';
            $response['notice'] .= '<h4>'.__("There is no data to transfer", "wp2leads").'</h4>';
            $response['notice'] .= '</div>';
        }

        echo json_encode($response);

        wp_die();
    }

    public function get_map_rows_count() {
        if (!empty($_POST['mapId'])) {
            $map_id = trim($_POST['mapId']);
            $map = MapsModel::get($map_id);
            $mapping = unserialize($map->mapping);
        } elseif (!empty($_POST['map'])) {
            $map = trim($_POST['map']);
            $mapping = json_decode(stripslashes($map), true);
        }

        $rows_count = MapsModel::get_map_query_rows_count($mapping);

        echo json_encode(array('error' => 0, 'success' => 1, 'message' => count($rows_count)));

        wp_die();
    }

    public function transfer_all_data_to_klicktip_bg() {
        global $wpdb;
        $map_id = trim($_POST['mapId']);
        $sendApiSettings = trim($_POST['map']);

        if ( !empty($map_id) ) {
            $map_to_save = array(
                'map_id'    => $map_id,
                'api'       =>  $sendApiSettings
            );

            $hasSucceeded = MapsModel::update($map_to_save);
            $limit = !empty($_POST['limit']) ? $_POST['limit'] : 100000;
            $offset = !empty($_POST['offset']) ? $_POST['offset'] : 0;

            $start = $offset;
            $end = $offset + $limit - 1;

            $saved_prepared_data_transient = $wpdb->get_results( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE '%transient_wp2lead_klicktipp_data_for_transfer__". $map_id . '__' . $start . '__' . $end ."%'", ARRAY_A );

            $wpdb->delete($wpdb->options, array(
                'option_name' => '_transient_wp2lead_klicktipp_data_for_transfer__' . $map_id . '__' . $start . '__' . $end,
            ));

            if (count($saved_prepared_data_transient) > 0) {
                $data_for_transfer = unserialize($saved_prepared_data_transient[0]['option_value']);

                $count = Wp2leads_Background_MapToApi::maptoapi_bg($map_id, $data_for_transfer);

                $response = array('success' => 1, 'error' => 0, 'message' => __('Ready for transfer', 'wp2leads'), 'count' => $count, 'result' => $data_for_transfer);

                echo json_encode($response);
                wp_die();
            }

            $response = array('success' => 0, 'error' => 1, 'message' => __('No data to transfer', 'wp2leads'));

            echo json_encode($response);
            wp_die();
        }

        $response = array('success' => 0, 'error' => 1, 'message' => __('No map ID', 'wp2leads'));

        echo json_encode($response);
        wp_die();
    }

    public function terminate_all_map_to_api() {
        $wp2l_map_to_api_in_progress = BackgroundProcessManager::get_transient( 'wp2leads_maptoapi_bg_in_process' );
        $prepare_in_progress = BackgroundProcessManager::get_prepare_bg_in_process();
        $load_in_progress = BackgroundProcessManager::get_load_bg_in_process();


        $terminate_errors = array();
        $terminate_success = array();

        BackgroundProcessManager::clear_map_to_api_transient();

        if (!empty($wp2l_map_to_api_in_progress) || !empty($load_in_progress) || !empty($prepare_in_progress)) {
            $maps_to_terminate = array();

            if (!empty($wp2l_map_to_api_in_progress)) {
                foreach ($wp2l_map_to_api_in_progress as $map_id => $process) {
                    $maps_to_terminate[] = $map_id;
                }
            }

            if (!empty($load_in_progress)) {
                foreach ($load_in_progress as $map_id => $count) {
                    $maps_to_terminate[] = $map_id;
                }
            }

            if (!empty($prepare_in_progress)) {
                foreach ($prepare_in_progress as $map_id => $count) {
                    $maps_to_terminate[] = $map_id;
                }
            }

            if (!empty($maps_to_terminate)) {
                $maps_to_terminate = array_unique($maps_to_terminate);

                foreach ($maps_to_terminate as $map_to_terminate) {
                    $map_object = MapsModel::get($map_to_terminate);

                    $map_object_name = !empty($map_object) ? $map_object->name : __('Map deleted', 'wp2leads');

                    $terminate_result = BackgroundProcessManager::terminate_map_to_api($map_to_terminate);

                    if (!$terminate_result) {
                        $terminate_errors[$map_id] = $map_object_name;
                    } else {
                        $terminate_success[$map_id] = $map_object_name;
                    }

                }
            }

            $response = array('success' => 1, 'error' => 0, 'message' => __('Maps successfully terminated', 'wp2leads'));

            echo json_encode($response);
            wp_die();
        }

        $response = array('success' => 0, 'error' => 1, 'message' => __('No background transfer currently running.', 'wp2leads'));

        echo json_encode($response);
        wp_die();
    }

    public function refresh_all_map_to_api() {
        $maps_prepare_in_progress = BackgroundProcessManager::get_prepare_bg_in_process();
        $maps_load_in_progress = BackgroundProcessManager::get_load_bg_in_process();
        $wp2l_map_to_api_in_progress = BackgroundProcessManager::get_transient( 'wp2leads_maptoapi_bg_in_process' );

        if ($wp2l_map_to_api_in_progress) {
            foreach ($wp2l_map_to_api_in_progress as $map_transfer_id => $process) {
                foreach ($process as $pi => $data) {
                    if (empty($data['total'])) {
                        unset($process[$pi]);
                    }
                }

                if (empty($process)) {
                    unset($wp2l_map_to_api_in_progress[$map_transfer_id]);
                }
            }

            if (empty($wp2l_map_to_api_in_progress)) {
                $wp2l_map_to_api_in_progress = false;
                BackgroundProcessManager::delete_transient('wp2leads_maptoapi_bg_in_process');
            } else {
                BackgroundProcessManager::set_transient('wp2leads_maptoapi_bg_in_process', $wp2l_map_to_api_in_progress);
            }
        }

        ob_start();

        if ($wp2l_map_to_api_in_progress || $maps_load_in_progress || $maps_prepare_in_progress) {
            include plugin_dir_path(  WP2LEADS_PLUGIN_FILE ) . 'admin/partials/ajax/running-background-processes.php';
        } else {
            ?>
            <p><strong><?php _e('WP2Leads', 'wp2leads') ?></strong>: <?php _e('No background transfer currently running.', 'wp2leads') ?></p>
            <?php
        }

        $html = ob_get_clean();

        $response = array('success' => 1, 'error' => 0, 'html' => $html);

        echo json_encode($response);
        wp_die();
    }

    public function get_map_to_api_statistics() {

        ob_start();
        // TODO - Use BG Manager
        include plugin_dir_path(  WP2LEADS_PLUGIN_FILE ) . 'admin/partials/wp2leads-admin-maptoapi-bg-in-process-notices.php';

        $html = ob_get_clean();

		if ( $html ) {
			$response = array('success' => 1, 'error' => 0, 'html' => $html);
		} else {
			$response = array('success' => 0, 'error' => 1, 'message' => __('Transfer is complete', 'wp2leads'));
		}

        echo json_encode($response);
        wp_die();
    }

    public function terminate_map_to_api() {
        global $wpdb;

        $map_id = trim($_POST['mapId']);

        if ( empty($map_id) ) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('No map ID', 'wp2leads'));

            echo json_encode($response);
            wp_die();
        }

        $terminate_result = BackgroundProcessManager::terminate_map_to_api($map_id);

        if (!$terminate_result) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Something went wrong', 'wp2leads'));

            echo json_encode($response);
            wp_die();
        }

        $response = array('success' => 1, 'error' => 0, 'message' => __('Map with ID ' . $map_id . ' successfully terminated', 'wp2leads'));

        echo json_encode($response);
        wp_die();
    }

    public function update_existed_tag_fieldset_list() {
        $map_id = trim($_POST['mapId']);
        $sendApiSettings = trim($_POST['map']);
        $decodedSendApiSettings = json_decode(stripslashes($sendApiSettings), true);

        if ( empty($map_id) ) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('No map ID', 'wp2leads'));

            echo json_encode($response);
            wp_die();
        }

        $connector = new Wp2leads_KlicktippConnector();
        $logged_in = $connector->login();
        $connector->get_last_error();

        if ($logged_in) {
            $fields = $connector->field_index();
            $tags = (array) $connector->tag_index();
            asort($tags, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
            $optins = $connector->subscription_process_index();
        }

        ob_start();
        include_once 'partials/ajax/existet-tag-fieldset-list.php';
        $tags_list = ob_get_clean();

        ob_start();
        include_once 'partials/ajax/existet-detach-tag-fieldset-list.php';
        $detach_tags_list = ob_get_clean();

        $response = array(
            'success' => 1,
            'error' => 0,
            'message' => __('Success', 'wp2leads'),
            'tags_list' => $tags_list,
            'detach_tags_list' => $detach_tags_list
        );

        echo json_encode($response);
        wp_die();


    }

    public function transfer_current_to_klicktipp() {
        global $wpdb;
        $map_id = trim($_POST['mapId']);
        $current_email = trim($_POST['email']);
        $current_data = json_decode(stripslashes($_POST['mapResult']), true);
        $sendApiSettings = trim($_POST['map']);

        if ( empty($map_id) ) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('No map ID', 'wp2leads'));

            echo json_encode($response);
            wp_die();
        }

        if ( empty($current_email) ) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('No user email', 'wp2leads'));

            echo json_encode($response);
            wp_die();
        }

        $map_global_prefix = !empty($_POST['global_tag_prefix']) ? trim($_POST['global_tag_prefix']) : false;

        if (!$map_global_prefix) {
            delete_option('wp2l_klicktipp_tag_prefix');
        } else {
            $updated = update_option( 'wp2l_klicktipp_tag_prefix', $map_global_prefix );
        }

        if (!empty($_POST["recomended_tags_prefixes"])) {
            MapBuilderManager::update_recomended_tags_prefixes($map_id, $_POST["recomended_tags_prefixes"]);
        }

        $map_to_save = array(
            'map_id'    => $map_id,
            'api'       =>  $sendApiSettings
        );

        $hasSucceeded = MapsModel::update($map_to_save);

        if (!Wp2leads_License::is_map_transfer_allowed($map_id)) {
            $kt_limitation = KlickTippManager::get_initial_kt_limitation();

            if (!$kt_limitation) {
                $result = false;
            } else {
                $kt_counter = KlickTippManager::get_transfer_counter();

                if (!$kt_counter) {
                    $is_transfer_allowed = true;
                } else {
                    $kt_limit_users = $kt_limitation['limit_users'];
                    $kt_limit_counter = $kt_counter['limit_counter'];

                    $kt_limit_counter_left = (int) $kt_limit_users - (int) $kt_limit_counter;

                    if (0 < $kt_limit_counter_left) {
                        $is_transfer_allowed = true;
                    } else {
                        $is_transfer_allowed = false;
                    }
                }

                if ($is_transfer_allowed) {
                    $results = array();
                    $results[] = (object) $current_data;
                    $decodedSendApiSettings = json_decode(stripslashes($sendApiSettings), true);
                    $tags_prefix = ApiHelper::get_map_tags_prefix($map_id);
                    $data_for_transfer = ApiHelper::prepareDataForTransfer($decodedSendApiSettings, $results, $tags_prefix);
                    $result = KlickTippManager::transfer_data_to_kt($map_id, $data_for_transfer);
                } else {
                    $kt_limit_days = $kt_limitation['limit_days'];
                    $message = sprintf( __( 'You have exceeded your Pro Version limit for %s users per %s days.', 'wp2leads' ), $kt_limit_users, $kt_limit_days );

                    echo json_encode(array('error' => 1, 'success' => 0, 'message' => $message));
                    wp_die();
                }
            }
        } else {
            $results = array();
            $results[] = (object) $current_data;
            $decodedSendApiSettings = json_decode(stripslashes($sendApiSettings), true);
            $tags_prefix = ApiHelper::get_map_tags_prefix($map_id);
            $data_for_transfer = ApiHelper::prepareDataForTransfer($decodedSendApiSettings, $results, $tags_prefix);
            $result = KlickTippManager::transfer_data_to_kt($map_id, $data_for_transfer);
        }


        if ( $result ) {
            $totally_transfered = StatisticsManager::getTotallyTransferedData($map_id);
            $new_subscribers_amount = $result['added_subscribers'];
            $updated_subscribers_amount = $result['existed_subscribers'];
            $failed_subscribers_amount = $result['failed_subscribers'];
            $total_transferred = $new_subscribers_amount + $updated_subscribers_amount;
            $last_transferred_time = ApiHelper::convertTimeToLocal($result['last_transferred_time']);

            $response = array(
                'error' => 0,
                'success' => 1,
                'added_subscribers' => $new_subscribers_amount,
                'existed_subscribers' => $updated_subscribers_amount,
                'failed_subscribers' => $failed_subscribers_amount,
                'total_transferred' => $total_transferred,
                'last_transferred_time' => $last_transferred_time,
                'totally_unique' => $totally_transfered['unique'],
                'totally_all' => $totally_transfered['all'],
                'totally_failed' => $totally_transfered['failed']
            );

            if (empty($total_transferred) && empty($failed_subscribers_amount)) {
                $response['info_message'] = __('Current contact is up to date - no transfer needed', 'wp2leads');
            }

            echo json_encode($response);

            wp_die();
        } else {
            echo json_encode(array('error' => 1, 'success' => 0, 'message' => __('You can not transfer this user', 'wp2leads')));
            wp_die();
        }

        $response = array('success' => 0, 'error' => 1, 'message' => __('No data to transfer', 'wp2leads'));

        echo json_encode($response);
        wp_die();
    }

    /**
     * Transfer data to Klick Tipp
     */
    public function ajax_transfer_to_klicktipp() {

        $map_id = trim($_POST['map_id']);
        $is_transfer_allowed = Wp2leads_License::is_map_transfer_allowed($map_id);

        if ($is_transfer_allowed && !empty($map_id)) {
            $data_for_transfer = get_transient('klicktipp_data_for_transfer');

            if (!empty($_POST['email'])) {
                $data_for_transfer = ApiHelper::getCurrentUserData($data_for_transfer, $_POST['email']);
            }

            if ( empty( $data_for_transfer ) ) {
                echo json_encode(['error' => 1, 'success' => 0]);
                wp_die();
            }

            $result = KlickTippManager::transfer_data_to_kt($_POST['map_id'], $data_for_transfer);

            if ( $result ) {
                $totally_transfered = StatisticsManager::getTotallyTransferedData($map_id);
                $new_subscribers_amount = $result['added_subscribers'];
                $updated_subscribers_amount = $result['existed_subscribers'];
                $total_transferred = $new_subscribers_amount + $updated_subscribers_amount;
                $last_transferred_time = ApiHelper::convertTimeToLocal($result['last_transferred_time']);

                echo json_encode([
                    'error' => 0,
                    'success' => 1,
                    'added_subscribers' => $new_subscribers_amount,
                    'existed_subscribers' => $updated_subscribers_amount,
                    'total_transferred' => $total_transferred,
                    'last_transferred_time' => $last_transferred_time,
                    'totally_unique' => $totally_transfered['unique'],
                    'totally_all' => $totally_transfered['all'],
                ]);

                wp_die();
            } else {
                echo json_encode(array('error' => 1, 'success' => 0, 'message' => __('You can not transfer this user', 'wp2leads')));
                wp_die();
            }
        } else {
            echo json_encode(array('error' => 1, 'success' => 0, 'message' => __('Transfer is not allowed', 'wp2leads')));
            wp_die();
        }
    }

    public function import_maps() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $mapimportids = !empty($_POST['mapimportids']) ? $_POST['mapimportids'] : false;

        if (!$mapimportids || count($mapimportids) === 0) {
            $response = array(
                'error' => 1,
                'success' => 0,
                'message' => __('Please select at least one map to import', 'wp2leads'),
            );

            echo json_encode($response);
            wp_die();
        }

        $mapids = array();

        foreach ($mapimportids as $mapimportid) {
            $mapids[] = $mapimportid['mapId'];
        }

        $result = MapBuilderManager::import_maps_from_server($mapids);

        if (!$result) {
            $response = array(
                'error' => 1,
                'success' => 0,
                'message' => __('Something went wrong, please try later', 'wp2leads'),
            );

            echo json_encode($response);
            wp_die();
        }

        $count_result = count($result);
        $last_map_id = $result[0];

        $response = array(
            'error' => 0,
            'success' => 1,
            'message' => $count_result . __(' Maps successfuly imported from server', 'wp2leads'),
        );

        if (!empty($last_map_id)) {
            $response['map_id'] = $last_map_id;
        }

        echo json_encode($response);
        wp_die();
    }

	public function magic_import() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );

		// detect map public ID
		$new_map_id = MapBuilderManager::import_map_by_public_id($_POST['map_id'], $_POST['name']);

		// get the new map from DB
		$map = MapsModel::get($new_map_id);

		// get fields that we need
		$form_fields = vxcf_form::get_form_fields($_POST['type'] . '_' . $_POST['form_id']);

		$mapping = unserialize($map->mapping);

		$mapping['selects_only'] = array();


		foreach($form_fields as $field) {
			$mapping['selects_only'][] = 'v.vxcf_leads_detail-' .$field['name'];
			if (!in_array('v.vxcf_leads_detail-' .$field['name'], $mapping['selects'])) $mapping['selects'][] = 'v.vxcf_leads_detail-' .$field['name'];
		}

		// add required fields
		$mapping['selects_only'][] = $mapping['keyBy'];
		$mapping['selects_only'][] = 'vxcf_leads.created';
		$mapping['selects_only'][] = 'vxcf_leads.updated';
		$mapping['selects_only'][] = 'vxcf_leads.form_id';

		$mapping['excludes'] = array();

		foreach ($mapping['selects'] as $s) {
			if (!in_array($s, $mapping['selects_only'])) $mapping['excludes'][] = $s;
		}

		// comparsion
		$mapping['comparisons'] = array(
			array (
				'tableColumn' => 'vxcf_leads.form_id',
				'conditions' => array (
					array (
						'operator' => 'like',
						'string' => $_POST['type'] . '_' . $_POST['form_id'],
					),
				),
			),
		);

		$mapping['form_code'] = $_POST['type'] . '_' . $_POST['form_id'];

		// add fake entry
		$vxcf_data = vxcf_form::get_data_object();
		if ( !$vxcf_data->get_entries($mapping['form_code'], 1)['result'] ) {
			Wp2leads_MagicImport::create_fake_entry($mapping['form_code']);
		}

		// save cell
		if( MapsModel::updateMapCell($new_map_id, 'mapping', serialize($mapping)) === false) {
			$updated = false;
		} else {
			$updated = true;
		}

		// clean api cell
		$api = unserialize($map->api);

		$api['fields'] = array();
		$api['connected_for_tags'] = array(
			'tags' => array(),
			'tags_concat' => array(),
			'separators' => array()
		);
		$api['replace_table'] = array();

		$api['tags_prefix'] = $map->name;

		MapsModel::updateMapCell($new_map_id, 'api', serialize($api));

		$info_array = unserialize($map->info);
		$info_array['domain'] = Wp2leads_Admin::get_site_domain();
		$info_array['search'] = '';
        $info_array['searchTable'] = '';
        $info_array['possibleUsedTags'] = array(
			'standartTags' => array(),
			'userInputTags' => array(),
		);
        $info_array['publicMapId'] = '';
        $info_array['publicMapHash'] = '';
        $info_array['publicMapContent'] = '';
        $info_array['publicMapOwner'] = '';
        $info_array['publicMapStatus'] = '';
        $info_array['publicMapVersion'] = '';
		$info_array['initial_settings'] = true;
		$popup = 'magig_step';

		MapsModel::updateMapCell($new_map_id, 'info', serialize($info_array));

		// calculate tags
		ob_start();

		include('partials/wp2leads-admin-tags_edit_popup-template.php');

		$html = ob_get_clean();
		$include_id_url = true;

		$response = array(
            'error' => !$updated,
            'success' => $updated,
            'message' => __('Map was imported', 'wp2leads' ),
			'map_id' => $new_map_id,
			'redirect' => home_url() . '/wp-admin/admin.php?page=wp2l-admin&tab=map_to_api&active_mapping=' . $new_map_id . '&start_step=9&come_from=magic',
			'map_title' => $map->name,
			'tags' => $html,
			'form_code' => $_POST['type'] . '_' . $_POST['form_id']
        );

        echo json_encode($response);
        wp_die();
	}

	public function get_edit_replacements_popup() {
		$map = MapsModel::get($_POST['map_id']);
		$mapping = unserialize($map->mapping);
		$api = unserialize($map->api);
		$edited = true;
		$form_code = explode('_', $mapping['form_code']);
		$form_fields = vxcf_form::get_form_fields($mapping['form_code']);
		$form_type = $form_code[0];
		$form_id = $form_code[1];
		$popup = 'replacements';

		ob_start();
		include('partials/wp2leads-admin-tags_edit_popup-template.php');
		$html = ob_get_clean();

		$response = array(
            'error' => 0,
            'success' => 1,
            'message' => '',
			'map_title' => $map->name,
			'tags' => $html,
			'redirect' => home_url() . '/wp-admin/admin.php?page=wp2l-admin&tab=map_to_api&active_mapping=' . $_POST['map_id'] . '&show_form_message=1',
			'form_code' => $mapping['form_code']
        );

        echo json_encode($response);
        wp_die();
	}

	public function magic_import_step2() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
		global $wpdb;

		// get the new map from DB
		$map = MapsModel::get($_POST['map_id']);

		if (!$map) {
			echo json_encode(array(
				'error' => 1,
				'success' => 0,
				'message' => __('Something went wrong.', 'wp2leads' ),
			));
			wp_die();
		}

		// check map name
		if($_POST['name'] !== $map->name) {

			if (MapBuilderManager::get_map_id_by_name($_POST['name'])) {
				echo json_encode(array(
					'error' => 1,
					'success' => 0,
					'message' => __('You have the map with this name. Choose another name.', 'wp2leads' ),
				));
				wp_die();
			} else {
				MapsModel::updateMapCell($_POST['map_id'], 'name', esc_sql($_POST['name']));
			}
		}

		// change tags
		$api = unserialize($map->api);
		$api['tags_prefix'] = $_POST['name'];
		$form_code = $_POST['form_code'];
		$api['show_magic_popup'] = 0;
		$info = unserialize($map->info);
		if ( !empty($info['initial_settings']) ) Wp2leads_Notices::add_warning(__("Don't forget to check Fields setting after changing Replacements", 'wp2leads'), 1);

		$connector = new Wp2leads_KlicktippConnector();
		// check named tag
		$added_tags_count = 0;
		$changed_tags_count = 0;

		if ( !empty($_POST['nametag']) ) {

			$logged_in = $connector->login();

			if ($logged_in) {
				// try to add one
				$result = $connector->tag_create(trim($_POST['name']));
				if ($result) {
					$code = $result;
					$added_tags_count++;
				} else {
					$code = array_search(trim($_POST['name']), $connector->tag_index());
				}

				if ($code) {
					$api['manually_selected_tags']['tag_ids'][] = $code;
				}
			}

			$api['losted_manually_selected_tags'][] = trim($_POST['name']);
			$api['losted_name'] = trim($_POST['name']);
		}

		$form_fields = vxcf_form::get_form_fields($form_code);
		$info = unserialize($map->info);
		$mapping = unserialize($map->mapping);

		$check =  json_decode(stripslashes($_POST['fields']), true);

		$taggs = array();
		$taggs_for_update = array();

		$check2 = array();
		foreach ($check as $c) {
			$check2[] = $c['value'];
		}

		// create templates
		$mapping['replace_table'] = array();

		foreach($form_fields as $field) {
			$arr = array();

			foreach ($check as $c) {
				if ($c['value'] == $field['name']) {
					$arr = $c;
				}
			}

			if ($arr) {
				$in_array = false;
				foreach ($info['possibleUsedTags']['userInputTags'] as $v) {
					if (strstr($v['title'], $field['label'])) {
						$in_array = true;
					}
				}

				if ($in_array) {
					$info['possibleUsedTags']['userInputTags'][] = array(
						'title' => __('Autotag: ', 'wp2leads') . $field['label'],
						'fromTable' => 'vxcf_leads_detail',
						'selects' => array ('vxcf_leads_detail.value', 'vxcf_leads_detail.name'),
						'tagColumn' => 'vxcf_leads_detail.value',
						'groupBy' => 'vxcf_leads_detail.value',
						'joins' => array(),
						'comparisons' => array (
							array (
								'tableColumn' => 'vxcf_leads_detail.name',
								'conditions' => array (
									array (
										'operator' => 'like',
										'string' => $field['name'],
									),
								),
							),
						),
					);
				}

				if ($field['type'] == 'radio' || $field['type'] == 'checkbox' || $field['type'] == 'select') {

					foreach ($field['values'] as $val) {

						if (!in_array($val['value'], $info['possibleUsedTags']['standartTags'])) $info['possibleUsedTags']['standartTags'][] = $val['value'];

						$preprefix = '';

						if ($arr['label_prefix']) {
							$preprefix = '; ';
							$preprefix .= $arr['label_prefix'];
							$preprefix .= ': ';
						}

						$new_tag = $api['tags_prefix'] . $preprefix . $val['value'];
						$old_tag = '';

						// try to update tags insted of adding new
						if ( isset($_POST['update_tags']) && $_POST['update_tags'] && isset($_POST['update_tags'][$arr['label_prefix']])) {
							$old_tag = $api['tags_prefix'];
							$old_tag .= '; ';
							$old_tag .= $_POST['update_tags'][$arr['label_prefix']];
							$old_tag .= ': ';
							$old_tag .= $val['value'];
						}

						if ( !$old_tag ) {
							$taggs[] = $new_tag;
						} else if ( !KlickTippManager::update_tag_name($old_tag, $new_tag) ) {
							$taggs[] = $new_tag;
						} else {
							$changed_tags_count++;
						}

					}
				}

				// replacements
				if ($arr['label_prefix']) {
					$mapping['replace_table']['v.vxcf_leads_detail-' . $field['name']] = '; ' . $arr['label_prefix'] . ': ';
				}

				// auto tags
				// single
				if ($field['type'] == 'radio') {
					if (!in_array('v.vxcf_leads_detail-' . $field['name'], $api['connected_for_tags']['tags'])) $api['connected_for_tags']['tags'][] = 'v.vxcf_leads_detail-' . $field['name'];
				}

				// concat
				if ($field['type'] == 'checkbox' || $field['type'] == 'select') {
					if (!in_array('v.vxcf_leads_detail-' . $field['name'], $api['connected_for_tags']['tags_concat'])) $api['connected_for_tags']['tags_concat'][] = 'v.vxcf_leads_detail-' . $field['name'];
				}
			}

			// search for the email field
			if ($field['type'] == 'email') {
				$api['fields']['api_email'] = array (
					'name' => 'Email',
					'table_columns' => array ('v.vxcf_leads_detail-' . $field['name']),
					'gmt' => false,
				);
			}
		}

		// send tags
		if ($taggs) {

			if ($connector->login()) {
				foreach ( $taggs as $tag ) {
					$result = $connector->tag_create(trim($tag));
					if ($result) {
						$added_tags_count++;
					} else {
						$api['losted_tags'][] = $tag;
					}
				}
			} else {
				$api['losted_tags'] = $taggs; // store losted tags to add them in future
			}
		}

		if (in_array('form_id', $check2) && !isset($_POST['edit'])) {
			$info['possibleUsedTags']['userInputTags'][] = array (
				'title' => __('Autotag: ', 'wp2leads') . 'ID',
				'fromTable' => 'vxcf_leads',
				'selects' => array ('vxcf_leads.form_id'),
				'tagColumn' => 'vxcf_leads.form_id',
				'groupBy' => 'vxcf_leads.form_id',
				'joins' => array (),
				'comparisons' => array (),
			);

			// add form id like a tag
			if ( !empty($form_code)) {
				$result = $connector->tag_create(trim($form_code));
				if ($result) {
					$added_tags_count++;
				}
			}
		}

		if (in_array('form_url', $check2) && !isset($_POST['edit'])) {
			$info['possibleUsedTags']['userInputTags'][] = array (
				'title' => __('Autotag: ', 'wp2leads') . 'URL',
				'fromTable' => 'vxcf_leads',
				'selects' => array('vxcf_leads.url'),
				'tagColumn' => 'vxcf_leads.url',
				'groupBy' => 'vxcf_leads.url',
				'joins' => array(),
				'comparisons' => array(),
			);

			// add form url
			$url = $wpdb->get_var( "SELECT url FROM " . $wpdb->prefix . "vxcf_leads WHERE form_id = '" . $form_code . "'" );

			if ( !empty($url)) {
				$result = $connector->tag_create(trim($url));
				if ($result) {

					$added_tags_count++;
				}
			}
		}

		if ( $added_tags_count ) {
			Wp2leads_Notices::add_notice( sprintf(__('%s tags added to Klick-Tipp. Update your settings on Klick-Tipp.', 'wp2leads'), $added_tags_count), 1 );
		}

		if ( $changed_tags_count )  {
			Wp2leads_Notices::add_notice( sprintf(__('%s tags were renamed on Klick-Tipp.', 'wp2leads'), $changed_tags_count), 1 );
		}

		MapsModel::updateMapCell($_POST['map_id'], 'mapping', serialize($mapping));
		MapsModel::updateMapCell($_POST['map_id'], 'api', serialize($api));
		MapsModel::updateMapCell($_POST['map_id'], 'info', serialize($info));

        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wp2lead_map_to_api_results__". $_POST['map_id'] ."%'" );

		echo json_encode(array(
			'error' => 0,
			'success' => 1,
			'message' => ''
		));
		wp_die();
	}

	public function get_map_plugins() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
		$response = array(
            'error' => 0,
            'success' => 1,
            'message' => '',
			'required' => '',
			'recommend' => ''
        );

		$required_html = '';
		$recommend_html = '';

		$pl = new Wp2leads_RequiredPlugins();
		$rqp = $pl->check_map_plugins($_POST['map_id']);
		$rcp = $pl->check_map_recommends($_POST['map_id']);

		if ($rqp) {
			foreach ($rqp as $key => $plugin) {
				$required_html .= '<label><input type="checkbox" value="' . $key . '" checked="checked" disabled> <span>' . $plugin['label'] . '</span> <span class="response"></span></label>';
			}

			$response['required'] = $required_html;
		}

		if ($rcp) {
			foreach ($rcp as $key => $plugin) {
				$recommend_html .= '<label><input type="checkbox" value="' . $key . '"> <span>' . $plugin['label'] . '</span> <span class="response"></span></label>';
			}

			$response['recommend'] = $recommend_html;
		}

        echo json_encode($response);
        wp_die();
	}

	public function check_plugin_by_slug() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
		$response = array(
            'error' => 0,
            'success' => 1,
            'message' => '',
        );

		$pl = new Wp2leads_RequiredPlugins();

		$i_and_a = $pl->activate_and_install_plugin($_POST['map_id'], $_POST['plugin_slug']);
		$response['result'] = $i_and_a;

		if ($i_and_a['result']) {
			$response['success'] = 0;
			$response['error'] = 1;
			$response['message'] = '<span class="error">' . __('This plugin can not be installed automatically', 'wp2leads') . '</span>';
		} else {
			if (!empty($i_and_a["redirect"])) {
                $response['redirect'] = $i_and_a["redirect"];
                $response['message'] = $i_and_a['message'] ? $i_and_a['message'] : '';
            } else {
                $response['message'] = $i_and_a['message'] ? '<div class="wp2leads-notice wp2leads-notice-warning wp2leads-notice-info"><p>' . $i_and_a['message'] . '</p></div>' : '';
            }
		}

		echo "&&&";
		echo json_encode($response);
        wp_die();
	}

	public function update_magic_content() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );

		$response = array(
            'error' => 0,
            'success' => 1,
            'message' => '',
        );

		$func = 'get_' . $_POST['magic_id'] . '_html';
		$response['html'] = Wp2leads_MagicImport::$func();
		echo json_encode($response);
        wp_die();
	}
    public function import_pending_maps() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $mapimportids = !empty($_POST['mapimportids']) ? $_POST['mapimportids'] : false;

        if (!$mapimportids || count($mapimportids) === 0) {
            $response = array(
                'error' => 1,
                'success' => 0,
                'message' => __('Please select at least one map to import', 'wp2leads'),
            );

            echo json_encode($response);
            wp_die();
        }

        $mapids = array();

        foreach ($mapimportids as $mapimportid) {
            $mapids[] = $mapimportid['mapId'];
        }

        $result = MapBuilderManager::import_pending_maps_from_server($mapids);

        if (!$result) {
            $response = array(
                'error' => 1,
                'success' => 0,
                'message' => __('Something went wrong, please try later', 'wp2leads'),
            );

            echo json_encode($response);
            wp_die();
        }

        $count_result = count($result);
        $last_map_id = $result[0];

        $response = array(
            'error' => 0,
            'success' => 1,
            'message' => $count_result . __(' Maps successfuly imported from server', 'wp2leads'),
        );

        if (!empty($last_map_id)) {
            $response['map_id'] = $last_map_id;
        }

        echo json_encode($response);
        wp_die();
    }

    public function save_policy_confirmed() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $result = Wp2leads_License::save_policy_confirmed_option();

        $response = array(
            'error' => 0,
            'success' => 1,
        );

        echo json_encode($response);
        wp_die();
    }

    public function export_maps() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $mapuploadids = !empty($_POST['mapuploadids']) ? $_POST['mapuploadids'] : false;
        $mapupdateids = !empty($_POST['mapupdateids']) ? $_POST['mapupdateids'] : false;

		if ($mapuploadids && $mapupdateids) {
            $count1 = count($mapuploadids);
            $result1 = MapBuilderManager::export_maps_to_server($mapuploadids);

			$count2 = count($mapupdateids);
            $result2 = MapBuilderManager::update_maps_on_server($mapupdateids);

            $response = array(
                'error' => 0,
                'success' => 1,
                'message' => $count1 . __(' Maps successfuly uploaded to server', 'wp2leads') . ' & ' . $count2 . __(' Maps successfuly updated on server', 'wp2leads'),
            );

            echo json_encode($response);
            wp_die();
        }

        if ($mapuploadids) {
            $count = count($mapuploadids);
            $result = MapBuilderManager::export_maps_to_server($mapuploadids);

            $response = array(
                'error' => 0,
                'success' => 1,
                'message' => $count . __(' Maps successfuly uploaded to server', 'wp2leads'),
            );

            echo json_encode($response);
            wp_die();
        }

        if ($mapupdateids) {
            $count = count($mapupdateids);
            $result = MapBuilderManager::update_maps_on_server($mapupdateids);

            $response = array(
                'error' => 0,
                'success' => 1,
                'message' => $count . __(' Maps successfuly updated on server', 'wp2leads'),
            );

            echo json_encode($response);
            wp_die();
        }

        $response = array(
            'error' => 1,
            'success' => 0,
            'message' => __('Please select at least one map to export', 'wp2leads'),
        );

        echo json_encode($response);
        wp_die();
    }

    public function import_from_remote() {

        $request = wp_remote_get(base64_decode('aHR0cHM6Ly93cDJsZWFkcy1mb3Ita2xpY2stdGlwcC5jb20vc2VydmVyL2RsL3dwMmxlYWRzLW1hcHMuanNvbg=='));

        if( is_wp_error( $request ) ) {
            $response = array(
                'error' => 1,
                'success' => 0,
                'message' => __('Something went wrong', 'wp2leads'),
            );

            echo json_encode($response);
            wp_die();
        }

        $body = wp_remote_retrieve_body( $request );
        $decoded = json_decode( $body );
        $error = false;

        if (json_last_error() === JSON_ERROR_NONE) {
            if(MapBuilderManager::contains_valid_json_maps($decoded)) {
                MapBuilderManager::ingest_uploaded_json_maps($decoded);
            } else {
                $error = true;
            }
        } else {
            $error = true;
        }

        if($error) {
            $response = array(
                'error' => 1,
                'success' => 0,
                'message' => __('Something went wrong', 'wp2leads'),
            );

            echo json_encode($response);
            wp_die();
        }

        $maps_count = count($decoded);

        $response = array(
            'error' => 0,
            'success' => 1,
            'message' => $maps_count . __(' maps imported successfuly', 'wp2leads'),
        );

        echo json_encode($response);
        wp_die();
    }

    public function maps_actions_run() {

        $action = !empty($_POST['run']) ? sanitize_text_field($_POST['run']) : false;
        $maps_ids = !empty($_POST['maps']) ? $_POST['maps'] : false;

        if (!$action) {
            $response = array(
                'error' => 1,
                'success' => 0,
                'message' => __('Please, select an action', 'wp2leads'),
            );

            echo json_encode($response);
            wp_die();
        }

        if (!$maps_ids) {
            $response = array(
                'error' => 1,
                'success' => 0,
                'message' => __('Please, select at least one map', 'wp2leads'),
            );

            echo json_encode($response);
            wp_die();
        }

        $maps_count = count($maps_ids);

        foreach ($maps_ids as $map_id) {
            $result = MapsModel::delete($map_id);
        }

        $response = array(
            'error' => 0,
            'success' => 1,
            'message' => $maps_count . __(' map(s) deleted successfuly', 'wp2leads'),
        );

        echo json_encode($response);
        wp_die();
    }

    public function ajax_get_subscriber_tags_from_klicktipp() {
        $response = array(
            'error' => 1,
            'success' => 0,
            'tags' => array(),
            'message' => __('Cannot get tags for current User: ', 'wp2leads'),
        );

        if ( !empty( $_POST['email'] ) ) {
            if (!is_array($_POST['email'])) {
                $email_array = array($_POST['email']);
            } else {
                $email_array = $_POST['email'];
            }

            $connector = new Wp2leads_KlicktippConnector();
            $logged_in = $connector->login(get_option('wp2l_klicktipp_username'), get_option('wp2l_klicktipp_password'));

            if($logged_in) {
                foreach ($email_array as $email_item) {
                    $email_value = trim($email_item);
                    $email_column_valid = filter_var($email_value, FILTER_VALIDATE_EMAIL);

                    if ($email_column_valid) {
                        $email = $email_column_valid;
                        break;
                    }
                }

                if (empty($email)) {
                    $response['error'] = 1;
                    $response['success'] = 0;
                    $response['message'] .= ' ' . __('<br><strong>Email field below is not filled for current user.</strong>', 'wp2leads');

                    echo json_encode($response);
                    wp_die();
                }

                $subscriber_id = $connector->subscriber_search($email);

                if ($subscriber_id) {
                    $available_tags = $connector->tag_index();
                    $subscriber = (array) $connector->subscriber_get($subscriber_id);

                    if (!empty($subscriber['tags'])) {
                        $existed_tags = array();

                        foreach ($subscriber['tags'] as $key => $tag) {
                            $existed_tags[$tag] = !empty($available_tags[$tag]) ? $available_tags[$tag] : '';

                            if (!$existed_tags[$tag]) {
                                unset($subscriber['tags'][$key]);
                            }
                        }

                        $response['tags'] = array_merge($response['tags'], $subscriber['tags']);

                        $response['error'] = 0;
                        $response['success'] = 1;
                    }
                } else {

                    $response['error'] = 1;
                    $response['success'] = 0;
                    $response['message'] .= __('<br><strong>User is not in Klick-Tipp.</strong>', 'wp2leads');
                }
            }
        } else {
            $response['error'] = 1;
            $response['success'] = 0;
            $response['message'] .= ' ' . __('<br><strong>Email field below is not filled.</strong>', 'wp2leads');
        }

        echo json_encode($response);
        wp_die();
    }

    public function ajax_save_new_map()
    {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );

        if ($_POST['map_id']) {
            global $wpdb;
            $map_id = $_POST['map_id'];
            $new_map = 0;

            $map_global_prefix = !empty($_POST['global_tag_prefix']) ? trim($_POST['global_tag_prefix']) : false;
            $tab = 'map_to_api';

            if (!empty($_POST['tab'])) {
                $tab = $_POST['tab'];
            }

            if ('map_builder' !== $tab && !empty($_POST['api'])) {
                if (!$map_global_prefix) {
                    delete_option('wp2l_klicktipp_tag_prefix');
                } else {
                    $updated = update_option( 'wp2l_klicktipp_tag_prefix', $map_global_prefix );
                }

                if (!empty($_POST["recomended_tags_prefixes"])) {
                    MapBuilderManager::update_recomended_tags_prefixes($map_id, $_POST["recomended_tags_prefixes"]);
                }
            }

            $hasSucceeded = MapsModel::update($_POST);

            $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wp2lead_map_to_api_results__". $_POST['map_id'] ."%'" );
        } else {

            if (!empty($_POST["deleteOriginalMapId"])) {
                $original_map_id = sanitize_text_field($_POST["deleteOriginalMapId"]);
                $original_map = MapsModel::get($original_map_id);

                if (!empty($original_map)) {
                    $original_map_nfo = unserialize($original_map->info);

                    if (isset($original_map_nfo['map_kind'])) {
                        $_POST['map_kind'] = $original_map_nfo['map_kind'];
                    }

                    if (isset($original_map_nfo['initial_settings'])) {
                        $_POST['initial_settings'] = $original_map_nfo['initial_settings'];
                    }

                    $modules_map = Wp2leads_Transfer_Modules::get_modules_map();
                    $old_map_module = false;

                    foreach ($modules_map as $module_key => $module_maps) {
                        foreach ($module_maps as $mid => $status) {
                            if ($mid == $original_map_id) {
                                unset($modules_map[$module_key][$mid]);
                                $old_map_module = $module_key;
                                $old_map_module_status = $status;

                                continue 2;
                            }
                        }
                    }

                    $modules_list = json_encode($modules_map);
                    update_option('wp2leads_module_maps', $modules_list);

                    $cron_maps = Wp2LeadsCron::getScheduledMaps();

                    if (!empty( $cron_maps[ 'map_' . $original_map_id ]) && !empty( $cron_maps[ 'map_' . $original_map_id ])) {
                        $old_cron_map = $cron_maps[ 'map_' . $original_map_id ];
                        Wp2LeadsCron::save_cron_schedule($original_map_id, false, false);
                    }

                    MapsModel::delete($original_map_id);
                }
            }

            $new_map = 1;
            $hasSucceeded = MapsModel::create($_POST);
        }

        if ($hasSucceeded) {
            if (!empty($old_map_module) && !empty($old_map_module_status)) {
                Wp2leads_Transfer_Modules::save_module_map($hasSucceeded['map_id'], $old_map_module, $old_map_module_status);
            }

            if (!empty($old_cron_map)) {
                Wp2LeadsCron::save_cron_schedule($hasSucceeded['map_id'], $old_cron_map['status'], $old_cron_map['date_base']);
            }

            $map_owner = !empty($hasSucceeded['map_owner']) ? $hasSucceeded['map_owner'] : '';
            echo json_encode(['error' => 0, 'success' => 1, 'map_id' => $hasSucceeded['map_id'], 'mapping' => $hasSucceeded['mapping'], 'map_owner' => $map_owner, 'new_map' => $new_map]);
        } else {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
        }

        wp_die();
    }

    public function ajax_map_delete()
    {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );

        if (MapsModel::delete($_POST['map_id'])) {
            echo json_encode(['error' => 0, 'success' => 1]);
        } else {
            echo json_encode(['error' => 1, 'success' => 0]);
        }

        wp_die();
    }

    public function ajax_get_another_page_data() {
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            if (!isset($_POST['page_number']) || !isset($_POST['direction']) || !isset($_POST['active_mapping'])) {
                wp_die();
            }

            $offset = $_POST['page_number'] - 1;
            $rows_count = intval($_POST['rows_count']);

            if ($offset < 0) {
                $offset = 0;
            }

            $decodedMap = (array) unserialize(get_transient('wp2leads_map_' . $_POST['active_mapping']));

            $all_results = MapsModel::get_map_query_results($decodedMap, 1000, 0);

            $results = array(
                $all_results[$offset]
            );

            $paths = ApiHelper::get_paths($results, $decodedMap);

            ob_start();
            $next_page = $offset + 2;

            if ($next_page > ($rows_count + 1)) {
                $next_page = $rows_count + 1;
            }

            $prev_page = $next_page - 2;

            include_once 'partials/ajax/available_options.php';
            $available_options = ob_get_clean();

            echo $available_options;
            wp_die();
        }
    }

    public function get_map_query_results_limit() {
        $limit = MapBuilderManager::get_map_query_results_limit();
        $result = array('success' => 1, 'limit' => $limit);

        echo json_encode($result);
        die();
    }

    public function get_available_options_data() {
        $map_id = $_POST['mapId'];
        $page_number = !empty($_POST['pageNumber']) ? $_POST['pageNumber'] : false;
        $map_result = json_decode(stripslashes($_POST['mapResult']), true);
        $rows_count = $_POST['count'];
        $decodedMap = (array) unserialize(get_transient('wp2leads_map_' . $map_id));
        $map_result = apply_filters('wp2leads_available_options_output', $map_result, $decodedMap);
        $paths = ApiHelper::get_options_paths($map_result, $decodedMap);

        if (!$page_number) {
            $next_page = 2;
            $prev_page = 0;
        } else {
            $next_page = $page_number + 1;
            $prev_page = $page_number - 1;
        }

        ob_start();
        include_once 'partials/ajax/available_options_page.php';
        $available_options = ob_get_clean();

        // $response = json_encode(array('error' => 0, 'success' => 1, 'availableOptions' => utf8_encode($available_options)));
        $response = json_encode(array('error' => 0, 'success' => 1, 'availableOptions' => $available_options));

        if (json_last_error()) {
            $response = json_encode(array('error' => 0, 'success' => 1, 'availableOptions' => utf8_encode($available_options)));
        }

        echo $response;
        wp_die();
    }

    public function get_map_query_results_by_map_id_limited() {
        global $wpdb;

        $map_id = !empty($_POST['mapId']) ? $_POST['mapId'] : false;

        if (!$map_id) {
            $response = array('success' => 0, 'error' => 1, 'result' => __( 'Select map ID', 'wp2leads' ));
            echo json_encode($response);

            wp_die();
        }

        $limit = !empty($_POST['limit']) ? $_POST['limit'] : 100000;
        $offset = !empty($_POST['offset']) ? $_POST['offset'] : 0;

        $start = $offset;
        $end = $offset + $limit - 1;

        $map = MapsModel::get($map_id);
        $mapping = unserialize($map->mapping);
        $result = MapsModel::get_map_query_results($mapping, $limit, $offset, true, $map_id);
        $count = count($result);

        $response = array('success' => 1, 'error' => 0, 'result' => $result);

        echo json_encode($response);
        wp_die();
    }

    public function save_module_status() {
        $map_id = trim($_POST['mapId']);

        if (empty($map_id)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('No map ID', 'wp2leads'));

            echo json_encode($response);
            wp_die();
        }

        $module_key = trim($_POST['moduleKey']);

        if (empty($module_key)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('No module selected', 'wp2leads'));

            echo json_encode($response);
            wp_die();
        }

        $module_status = $_POST['moduleStatus'] === 'true' ? true : false;

        if ($module_status) {
            $cron_maps = Wp2LeadsCron::getScheduledMaps();

            if (!empty( $cron_maps[ 'map_' . $map_id ]) && !empty( $cron_maps[ 'map_' . $map_id ])) {
                $cron_map = $cron_maps[ 'map_' . $map_id ];

                $cron_result = Wp2LeadsCron::save_cron_schedule($map_id, false, $cron_map['date_base']);

                if (!empty($cron_result['success'])) {
                    $cron_response = array(
                        'status'      => $cron_result['status'],
                        'status_text' => $cron_result['status_text']
                    );
                }
            }
        }

        $result = Wp2leads_Transfer_Modules::save_module_map($map_id, $module_key, $module_status);

        if (!empty($result['success'])) {
            $response = array(
                'success' => 1,
                'error' => 0,
                'message' => $result['message']
            );

            if (!empty($cron_response)) {
                $response['cron'] = $cron_response;
            }

            echo json_encode($response);
            wp_die();
        } else {
            $response = array('success' => 0, 'error' => 1, 'message' => $result['message']);

            echo json_encode($response);
            wp_die();
        }
    }

    public function save_cron_status() {
        $map_id = trim($_POST['map_id']);

        if (empty($map_id)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('No map ID', 'wp2leads'));

            echo json_encode($response);
            wp_die();
        }

        $cron_status = !empty( $_POST['cron_status'] ) ? $_POST['cron_status'] : false;

        if ($cron_status) {
            $cron_status = $cron_status === 'true' ? true : false;
        }

        $date_base = !empty( $_POST['date_base_for_cron'] ) ? $_POST['date_base_for_cron'] : array();

        if ($cron_status && empty($date_base)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Select at least one column for set up cron', 'wp2leads'));

            echo json_encode($response);
            wp_die();
        }

        if ($cron_status) {
            $transfer_modules_enabled = Wp2leads_Transfer_Modules::get_modules_map();

            foreach ($transfer_modules_enabled as $transfer_module => $maps) {
                if (!empty($maps[$map_id])) {
                    unset($transfer_modules_enabled[$transfer_module][$map_id]);

                    $module_result = update_option('wp2leads_module_maps', json_encode($transfer_modules_enabled));

                    $module_response = array (
                        'success' => 1
                    );

                    continue;
                }
            }
        }

        $result = Wp2LeadsCron::save_cron_schedule($map_id, $cron_status, $date_base);

        if (!empty($result['success'])) {
            $response = array(
                'success' => 1,
                'error' => 0,
                'message' => $result['message'],
                'status' => $result['status'],
                'status_text' => $result['status_text']
            );

            if (!empty($module_response)) {
                $response['module'] = $module_response;
            }

            echo json_encode($response);
            wp_die();
        } else {
            $response = array('success' => 0, 'error' => 1, 'message' => $result['message']);

            echo json_encode($response);
            wp_die();
        }
    }

    public function change_transfer_module() {

        if (empty($_POST['selectedModule'])) {
            $response = array(
                'success' => 1,
                'error' => 0,
                'moduleDescription' => '',
                'moduleInstruction' => '',
            );

            echo json_encode($response);
            wp_die();
        }

        $selected_module = trim($_POST['selectedModule']);
        $allowed_module = trim($_POST['allowed']);
        $transfer_modules = Wp2leads_Transfer_Modules::get_transfer_modules_class_names();

        $class_name = $transfer_modules[$selected_module];
        $module_description = $class_name::get_description();
        $module_instruction = $class_name::get_instruction();

        if ('notexisted' === $allowed_module) {
            $module_instruction .= '<p class="warning-text"><strong><small>' . __("This module could not be used with this map as far as it doesn't contain required column.", 'wp2leads') . '</small></strong></p>';
        }

        $response = array(
            'success' => 1,
            'error' => 0,
            'moduleDescription' => '<p style="margin: 10px 10px 0 10px">' . $module_description . '</p>',
            'moduleInstruction' => $module_instruction,
        );

        echo json_encode($response);
        wp_die();
    }


    public function add_all_recommended_klick_tip_tags($taggs = array()) {
        $tags_to_create = !empty($_POST['tagsToCreate']) ? $_POST['tagsToCreate'] : false;
        $map_id = !empty($_POST['mapId']) ? $_POST['mapId'] : false;
        $tags_set_id = !empty($_POST['tagsSetId']) ? $_POST['tagsSetId'] : false;

        if (!$map_id) {
            $response = array('success' => 0, 'error' => 1, 'message' => __( 'No map id', 'wp2leads' ));
            echo json_encode($response);

            wp_die();
        }

        if (!$tags_to_create && !$taggs) {
            $response = array('success' => 0, 'error' => 1, 'message' => __( 'Select at least one tag to create', 'wp2leads' ));
            echo json_encode($response);

            wp_die();
        }

        $tags_to_create_decoded = json_decode(stripslashes($tags_to_create), true);

        $count = Wp2leads_Background_Tags_Create::run_bg($tags_to_create_decoded, $map_id, $tags_set_id);

        $response = array('success' => 1, 'error' => 0, 'message' => __( $count . ' tag(s) out started creating in background successfully.', 'wp2leads' ));
        echo json_encode($response);

        wp_die();
    }

    /**
     * Create recommended tags on new map settings
     *
     * Used on initial Map To API settings
     */
    public function add_recommended_klick_tip_tags($taggs = array(), $is_ajax = true) {

        $tags_to_create = !empty($_POST['tagsToCreate']) ? $_POST['tagsToCreate'] : false;

        if (!$tags_to_create && !$taggs && $is_ajax) {
            $response = array('success' => 0, 'error' => 1, 'message' => __( 'Select at least one tag to create', 'wp2leads' ));
            echo json_encode($response);

            wp_die();
        }

		if (!$tags_to_create && !$taggs && !$is_ajax) {
			return false;
		}

		if ($taggs) $tags_to_create = $taggs;

        $create_result = false;
        $tags_to_create_count = count($tags_to_create);
        $tags_created_count = 0;

        $klick_tip_connector = new Wp2leads_KlicktippConnector();
        $login_response = $klick_tip_connector->login();

        if ($login_response) {
            foreach ($tags_to_create as $tag) {
                $result = $klick_tip_connector->tag_create(trim($tag));

                if ($result) {
                    $create_result = true;
                    $tags_created_count++;
                }
            }
        } else {

			if ($taggs) {
				return false;
			} else {
				if ($is_ajax) {
					$response = array('success' => 0, 'error' => 1, 'message' => __( 'Sorry, we could not connect to your Klick-Tipp account. Please try later.', 'wp2leads' ));
					echo json_encode($response);

					wp_die();
				} else {
					return false;
				}
			}
        }

        if (!$create_result) {
			if ($taggs) {
				return false;
			} else {
				if ($is_ajax) {
					$response = array('success' => 0, 'error' => 1, 'message' => __( 'Sorry, something went wrong.', 'wp2leads' ));
					echo json_encode($response);

					wp_die();
				} else {
					return false;
				}
			}
        }

		if ($taggs) {
			return true;
		} else {
			if ($is_ajax) {
				$response = array('success' => 1, 'error' => 0, 'message' => __( $tags_created_count . ' tag(s) out of ' . $tags_to_create_count . ' created successfully.', 'wp2leads' ));
				echo json_encode($response);

				wp_die();
			} else {
				return false;
			}
		}

    }

    public function get_recomended_tags_result() {
        global $wpdb;
        $mapping = isset($_POST['mapping']) ? $_POST['mapping'] : false;
        $amount = isset($_POST['amount']) ? $_POST['amount'] : NULL;
        $map_prefix = isset($_POST['mapPrefix']) ? $_POST['mapPrefix'] : '';
        $prefix = !empty($mapping['prefix']) ? trim($mapping['prefix']) . ' ' : '';
        if (isset($_POST['prefix'])) $prefix = !empty($_POST['prefix']) ? trim($_POST['prefix']) . ' ' : '';
        if (empty(trim($prefix))) $prefix = '';

        $recomended_tags = MapBuilderManager::get_recomended_tags($mapping, $prefix, $amount);

        if (!empty($recomended_tags)) {
            $response = array('success' => 1, 'error' => 0, 'count' => $recomended_tags['count'], 'result' => $recomended_tags['result'], 'tags' => $recomended_tags['tags']);
            echo json_encode($response);

            wp_die();
        }

        $response = array('success' => 0, 'error' => 1, 'message' => __( 'Sorry, something went wrong.', 'wp2leads' ));
        echo json_encode($response);

        wp_die();
    }

    public function get_all_columns_for_recomended_tags() {
        $mapping = isset($_POST['mapping']) ? $_POST['mapping'] : false;

        if ($mapping) {
            $columns = array();
            $main_columns = MapsModel::fetch_columns_for_table($mapping['fromTable']);

            foreach ($main_columns as $column) {
                $columns[] = $mapping['fromTable'] . '.' . $column;
            }

            if (!empty($mapping['joins'])) {
                foreach ($mapping['joins'] as $join) {
                    $join_columns = MapsModel::fetch_columns_for_table($join['joinTable']);

                    foreach ($join_columns as $column) {
                        $columns[] = $join['joinTable'] . '.' . $column;
                    }
                }
            }

            $unique_columns = array_values(array_unique($columns));

            $response = array('success' => 1, 'error' => 0, 'columns' => $unique_columns);
            echo json_encode($response);

            wp_die();
        }

        $response = array('success' => 0, 'error' => 1, 'message' => __( 'Sorry, something went wrong.', 'wp2leads' ));
        echo json_encode($response);

        wp_die();
    }

    public function check_limit() {
        $map_id = !empty($_POST['mapId']) ? $_POST['mapId'] : false;

        if (!$map_id) {
            $response = array('success' => 0, 'error' => 1, 'message' => __( 'No map ID', 'wp2leads' ));
            echo json_encode($response);

            wp_die();
        }

        if (Wp2leads_License::is_map_transfer_allowed($map_id)) {
            $response = array('success' => 1, 'error' => 0);
            echo json_encode($response);

            wp_die();
        }

        $kt_limitation = KlickTippManager::get_initial_kt_limitation();

        if (!$kt_limitation) {
            $response = array('success' => 0, 'error' => 1, 'message' => __( 'You are not allowed to transfer current map', 'wp2leads' ));
            echo json_encode($response);

            wp_die();
        }
        $kt_counter = KlickTippManager::get_transfer_counter();

        if (!$kt_counter) {
            $response = array('success' => 1, 'error' => 0);
            echo json_encode($response);

            wp_die();
        }

        $kt_limit_users = $kt_limitation['limit_users'];
        $kt_limit_message = $kt_limitation['limit_message'];
        $kt_limit_days = $kt_limitation['limit_days'];
        $kt_limit_counter = $kt_counter['limit_counter'];
        $kt_limit_counter_timeout = $kt_counter['limit_counter_timeout'];
        $kt_limit_counter_timeout_left = $kt_limit_counter_timeout - time();
        $kt_limit_counter_left = (int) $kt_limit_users - (int) $kt_limit_counter;

        if (0 < $kt_limit_counter_left) {
            $response = array('success' => 1, 'error' => 0);
            echo json_encode($response);

            wp_die();
        }

        $response = array('success' => 1, 'error' => 0, 'limit' => 1);
        echo json_encode($response);

        wp_die();
    }

    public function update_api_fields() {
        $connector = new Wp2leads_KlicktippConnector();
        $logged_in = $connector->login();

        if (!$logged_in) {
            $logged_in_error = $connector->get_last_error(false);
            $response = array('success' => 0, 'error' => 1, 'message' => $logged_in_error);
            echo json_encode($response);

            wp_die();
        }

        $fields = $connector->field_index();

        if (!$fields) {
            $logged_in_error = $connector->get_last_error(false);
            $response = array('success' => 0, 'error' => 1, 'message' => $logged_in_error);
            echo json_encode($response);

            wp_die();
        }

        $default_api_fields_types = ApiHelper::getDefaultApiFieldsTypes();

        foreach ($fields as $field_slug => $field_name) {
            if (!empty($default_api_fields_types['api_' . $field_slug])) {
                unset ($fields[$field_slug]);
            }
        }

        $response = array('success' => 1, 'error' => 0, 'message' => __( 'Fields updated successfully', 'wp2leads' ), 'fields' => $fields);
        echo json_encode($response);

        wp_die();
    }

    /**
     * Run transfer data without total data calculation
     */
    public function transfer_data_immediately() {
        global $wpdb;
        $map_id = !empty($_POST['mapId']) ? $_POST['mapId'] : false;

        if (!$map_id) {
            $response = array('success' => 0, 'error' => 1, 'message' => __( 'No map ID', 'wp2leads' ));
            echo json_encode($response);

            wp_die();
        }

        // Clear cached data
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wp2lead_map_to_api_results_load__".$map_id."%'" );
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wp2lead_map_to_api_results__".$map_id."%'" );

        $count = Wp2leads_Background_Maptoapi_Load::run($map_id);

        $response = array('success' => 1, 'error' => 0, 'message' => __( 'Background transferring started', 'wp2leads' ));
        echo json_encode($response);

        wp_die();
    }

    public function is_map_transfer_in_bg() {
        $map_id = !empty($_POST['mapId']) ? $_POST['mapId'] : false;

        if (!$map_id) {
            $response = array('success' => 0, 'error' => 1, 'message' => __( 'No map ID', 'wp2leads' ));
            echo json_encode($response);

            wp_die();
        }

        $is_map_transfer_in_bg = KlickTippManager::is_map_transfer_in_bg($map_id);

        $message = $is_map_transfer_in_bg ? 1 : 0;

        $response = array('success' => 1, 'error' => 0, 'message' => $message);
        echo json_encode($response);

        wp_die();
    }

    /**
     * Save initial setting for map
     */
    public function save_map_to_api_initial_settings() {
        $map_id = !empty($_POST['mapId']) ? $_POST['mapId'] : false;

        if (!$map_id) {
            $response = array('success' => 0, 'error' => 1, 'message' => __( 'Select map ID', 'wp2leads' ));
            echo json_encode($response);

            wp_die();
        }

        if (!empty($_POST["recomended_tags_prefixes"])) {
            MapBuilderManager::update_recomended_tags_prefixes($map_id, $_POST["recomended_tags_prefixes"]);
        }

        $connector = new Wp2leads_KlicktippConnector();
        $logged_in = $connector->login();

        if (!$logged_in) {
            $logged_in_error = $connector->get_last_error(false);
            $response = array('success' => 0, 'error' => 1, 'message' => $logged_in_error);
            echo json_encode($response);

            wp_die();
        }

        $fields = $connector->field_index();

        if (!$fields) {
            $logged_in_error = $connector->get_last_error(false);
            $response = array('success' => 0, 'error' => 1, 'message' => $logged_in_error);
            echo json_encode($response);

            wp_die();
        }

        global $wpdb;
        $table = MapsModel::get_table();

        $global_prefix = $_POST['globalPrefix'] ? trim($_POST['globalPrefix']) : false;
        $map_prefix = $_POST['mapPrefix'] ? trim($_POST['mapPrefix']) : false;
        $start_date_data = $_POST['start_date_data'] ? trim($_POST['start_date_data']) : false;
        $end_date_data = $_POST['end_date_data'] ? trim($_POST['end_date_data']) : false;
        $api_field_types = !empty($_POST['api_field_types']) ? $_POST['api_field_types'] : false;
        $api_non_existed_api_fields = !empty($_POST['nonExisted']) ? $_POST['nonExisted'] : false;

        $map = MapsModel::get($map_id);
        $api = $old_api = unserialize($map->api);
        $mapping = unserialize($map->mapping);
        $info = unserialize($map->info);

        if (!$api) {
            $api = array();
        }

        $default_api_fields_types = ApiHelper::getDefaultApiFieldsTypes();
        $new_api_fields_types = $default_api_fields_types;

        if (!empty($api_field_types) && is_array($api_field_types)) {
            foreach ($api_field_types as $key => $api_field_type_setting) {
                $new_api_fields_types = array_merge($new_api_fields_types, $api_field_type_setting);
            }
        } else {
            if (!empty($api['fields']) && is_array($api['fields'])) {

                foreach ($api['fields'] as $field_name => $field_value) {
                    $existed = false;
                    $field_id = 'email';

                    foreach ($fields as $id => $label) {
                        if ($label === $field_value['name']) {
                            $field_id = $id;
                            $existed = true;

                            continue;
                        }
                    }

                    if ($existed && empty($default_api_fields_types['api_' . $field_id])) {
                        $new_api_fields_types['api_' . $field_id] = $field_value['type'];
                    }
                }
            }
        }

        $info['initial_settings'] = true;

        if (!empty($api['fields']) && is_array($api['fields'])) {
            foreach ($api['fields'] as $field_name => $field_value) {
                if ('api_email' !== $field_name) {
                    $type = isset($new_api_fields_types[$field_name]) ? $new_api_fields_types[$field_name] : false;

                    if ($type) {
                        $api['fields'][$field_name]['type'] = $type;

                        unset($new_api_fields_types[$field_name]);
                    } else {
                        unset($api['fields'][$field_name]);
                    }
                } else {
                    unset($new_api_fields_types[$field_name]);
                }
            }
        }

        if (!empty($new_api_fields_types)) {
            foreach ($new_api_fields_types as $new_api_field_name => $new_api_field_type) {
                $api['fields'][$new_api_field_name] = array(
                    'name' => '',
                    'table_columns' => array(),
                    'type'  => $new_api_field_type
                );
            }
        }

        if (!empty($api_non_existed_api_fields)) {
            foreach($api_non_existed_api_fields as $new_api_field) {
                if (
                    !empty($new_api_field['slug']) &&
                    !empty($new_api_field['name']) &&
                    !empty($new_api_field['columns']) &&
                    !empty($new_api_field['type']) &&
                    !empty($new_api_field['field'][0])
                ) {
                    $field_id = ltrim($new_api_field['field'][0], 'api_');
                    $new_field_name = $fields[$field_id];

					// set GMT from llast map (for new fields)
					$gmt = isset( $old_api['fields'][$new_api_field['slug']] ) ? $old_api['fields'][$new_api_field['slug']]['gmt'] : false;

					$api['fields']['api_' . $field_id] = array(
						'name' => $new_field_name,
						'table_columns' => explode(', ', $new_api_field['columns']),
						'type' => $new_api_field['type'],
						'gmt' => $gmt
					);
                }
            }
        }

        if ('save' === $_POST['save']) {
            if (!$global_prefix) {
                delete_option('wp2l_klicktipp_tag_prefix');
            } else {
                $updated_global_prefix = update_option( 'wp2l_klicktipp_tag_prefix', $global_prefix );
            }

            if (!$map_prefix && !empty($api['tags_prefix'])) {
                unset($api['tags_prefix']);
            } else {
                if ($map_prefix) {
                    $api['tags_prefix'] = $map_prefix;
                }
            }

            if (!$start_date_data && !empty($api['start_date_data'])) {
                unset($api['start_date_data']);
            } else {
                if ($start_date_data) {
                    $api['start_date_data'] = $start_date_data;
                }
            }

            if (!$end_date_data && !empty($api['end_date_data'])) {
                unset($api['end_date_data']);
            } else {
                if ($end_date_data) {
                    $api['end_date_data'] = $end_date_data;
                }
            }

            $current_map_prefix = '';

            if ($global_prefix) {
                $current_map_prefix = $global_prefix;
            }

            if ($map_prefix) {
                $current_map_prefix = $map_prefix;
            }

            if (!empty($api['conditions'])) {
                $connector = new Wp2leads_KlicktippConnector();
                $logged_in = $connector->login();

                if ($logged_in) {
                    $available_tags = (array) $connector->tag_index();
                } else {
                    $available_tags = array();
                }

                $available_tags_to_lower = array_map('strtolower', $available_tags);

                if (!empty($api['conditions']['tags']) && is_array($api['conditions']['tags'])) {
                    foreach ($api['conditions']['tags'] as $key => $value) {
                        $prefix = !empty($value["prefix"]) ? trim($value["prefix"]) . ' ' : '';
                        $possible_tag = trim($current_map_prefix . ' ' . $prefix . $value['connectToName']);

                        if (in_array(strtolower($possible_tag), $available_tags_to_lower)) {
                            $tag_id = array_search(strtolower($possible_tag), $available_tags_to_lower);
                        } elseif (in_array(strtolower($value['connectToName']), $available_tags_to_lower)) {
                            $tag_id = array_search(strtolower($value['connectToName']), $available_tags_to_lower);
                        } elseif (!empty($available_tags[$value['connectTo']])) {
                            $tag_id = $value['connectTo'];
                        } else {
                            $tag_id = '';
                        }

                        $api['conditions']['tags'][$key]['connectTo'] = (string) $tag_id;
                    }
                }

                if (!empty($api['conditions']['detach_tags']) && is_array($api['conditions']['detach_tags'])) {
                    foreach ($api['conditions']['detach_tags'] as $key => $value) {
                        $prefix = !empty($value["prefix"]) ? trim($value["prefix"]) . ' ' : '';
                        $possible_tag = trim($current_map_prefix . ' ' . $prefix . $value['connectToName']);

                        if (in_array(strtolower($possible_tag), $available_tags_to_lower)) {
                            $tag_id = array_search(strtolower($possible_tag), $available_tags_to_lower);
                        } elseif (in_array(strtolower($value['connectToName']), $available_tags_to_lower)) {
                            $tag_id = array_search(strtolower($value['connectToName']), $available_tags_to_lower);
                        } elseif (!empty($available_tags[$value['connectTo']])) {
                            $tag_id = $value['connectTo'];
                        } else {
                            $tag_id = '';
                        }

                        $api['conditions']['detach_tags'][$key]['connectTo'] = (string) $tag_id;
                    }
                }
            }

			if (!empty($_POST['optIn'])) {
				$api['default_optin'] = $_POST['optIn'];
			}
        }

        $data = array(
            'time' => date('Y-m-d H:i:s'),
            'name' => $map->name,
            'mapping' => serialize($mapping),
            'info'  => serialize($info)
        );

        if (!empty($api)) {
            $data['api'] = serialize($api);
        }

        $update = $wpdb->update( $table, $data, array('id' => $map_id), array('%s', '%s', '%s', '%s', '%s'), array('%d') );

        if ($update) {
            $response = array('success' => 1, 'error' => 0, 'message' => __( 'Initial settings saved successfully', 'wp2leads' ));
            $map = MapsModel::get($map_id);

            do_action('wp2leads_save_map_to_api_initial_settings', $map_id, $map);
        } else {
            $response = array('success' => 0, 'error' => 1, 'message' => __( 'Something went wrong', 'wp2leads' ));
        }


        echo json_encode($response);

        wp_die();
    }

    public function get_map_query_results_by_map_id() {
        global $wpdb;

        $memory = (memory_get_usage()/1048576);

        $map_id = !empty($_POST['mapId']) ? $_POST['mapId'] : false;
        $nocache = !empty($_POST['nocache']) ? $_POST['nocache'] : true;

        if (!$map_id) {
            $response = array('success' => 0, 'error' => 1, 'result' => __( 'Select map ID', 'wp2leads' ));
            echo json_encode($response);

            wp_die();
        }

        $limit = !empty($_POST['limit']) ? $_POST['limit'] : 100000;
        $offset = !empty($_POST['offset']) ? $_POST['offset'] : 0;

        $start = $offset;
        $end = $offset + $limit - 1;

        if ($nocache) {
            $saved_results_transient = array();

            $wpdb->delete($wpdb->options, array(
                'option_name' => '_transient_wp2lead_map_to_api_results__' . $map_id . '__' . $start . '__' . $end,
            ));

            $wpdb->delete($wpdb->options, array(
                'option_name' => '_transient_timeout_wp2lead_map_to_api_results__' . $map_id . '__' . $start . '__' . $end,
            ));
        } else {
            $saved_results_transient = $wpdb->get_results( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE '%transient_wp2lead_map_to_api_results__". $map_id . '__' . $start . '__' . $end ."%'", ARRAY_A );
        }

        if (count($saved_results_transient) > 0) {
            $result = unserialize($saved_results_transient[0]['option_value']);
        } else {
            $map = MapsModel::get($map_id);
            $mapping = unserialize($map->mapping);
            $result = MapsModel::get_map_query_results($mapping, $limit, $offset, false);

            $memory = (memory_get_usage()/1048576);

            $ser_result = serialize($result);

            if (!$nocache) {
                $wpdb->insert( $wpdb->options, array(
                    'option_name' => '_transient_wp2lead_map_to_api_results__' . $map_id . '__' . $start . '__' . $end,
                    'option_value' => $ser_result,
                    'autoload' => 'no'
                ));

                $wpdb->insert( $wpdb->options, array(
                    'option_name' => '_transient_timeout_wp2lead_map_to_api_results__' . $map_id . '__' . $start . '__' . $end,
                    'option_value' => time() + 60 * 60 * 3,
                    'autoload' => 'no'
                ));
            }

            $memory = (memory_get_usage()/1048576);
        }

        $memory = (memory_get_usage()/1048576);

        $response = array('success' => 1, 'error' => 0, 'result' => $result);

        echo json_encode($response);
        wp_die();
    }

    public function ajax_fetch_map_query_results()
    {
        $map = !empty($_POST['map']) ? json_decode(stripslashes($_POST['map']), true) : false;

        if (!$map) {
            $map_id = !empty($_POST['map_id']) ? $_POST['map_id'] : false;

            $map_object = MapsModel::get($map_id);
            $map = unserialize($map_object->mapping);
        }

        $get_empty = !empty($_POST['get_empty']) ? false : true;

        $results = MapsModel::get_map_query_results($map, $_POST['limit'] ? $_POST['limit'] : 100000, (isset($_POST['offset']) && $_POST['offset']) ? $_POST['offset'] : 0, $get_empty, (isset($_POST['offset']) && $_POST['offset']) ? $_POST["map_id"] : null);

        echo json_encode($results);
        wp_die();
    }

    public function ajax_get_mapping() {
        $mapping = array();

        if (isset($_POST['map_id']) && $activeMap = MapsModel::get($_POST['map_id'])) {
            $mapping = unserialize($activeMap->mapping);
        }

        foreach ($mapping['selects'] as $key => $select) {
            if (isset($mapping['excludes']) && in_array($select, $mapping['excludes'])) {
                unset($mapping['selects'][$key]);
            }
        }

        $mapping['selects'] = array_values($mapping['selects']);

        echo json_encode($mapping);
        die();
    }

    /**
     *
     */
    public function settings_klick_tip_credentials() {
        $username = !empty($_POST['username']) ? $_POST['username'] : false;
        $password = !empty($_POST['password']) ? $_POST['password'] : false;

        if (!$username || !$password) {
            $response = array('success' => 0, 'error' => 1, 'message' => __( 'Fill in username and password, please.', 'wp2leads' ));
            echo json_encode($response);

            wp_die();
        }

        update_option('wp2l_klicktipp_username', $username);
        update_option('wp2l_klicktipp_password', $password);

        $connector = new Wp2leads_KlicktippConnector();
        $logged_in = $connector->login();

        if (!$logged_in) {
            delete_transient('wp2leads_upgrade_kt_package');
            $last_error = $connector->get_last_connector_error();

            $response = array('success' => 0, 'error' => 1, 'message' => $last_error['message']);
            echo json_encode($response);

            wp_die();
        } else {
            $optins = $connector->subscription_process_index();

            if (empty($optins)) {
                delete_transient('wp2leads_upgrade_kt_package');
                set_transient('wp2leads_upgrade_kt_package', 1);
                $response = array('success' => 1, 'error' => 0, 'message' => __( 'You are on Klick Tipp Standard packed with no API access! To connect please upgrade at least to Klick Tipp Premium!.', 'wp2leads' ));
                echo json_encode($response);

                wp_die();
            }
        }

        delete_transient('wp2leads_upgrade_kt_package');
        $response = array('success' => 1, 'error' => 0, 'message' => __( 'Authorization in KlickTipp was successful!.', 'wp2leads' ));
        echo json_encode($response);

        wp_die();
    }

    /**
     * Ajax license activation
     */
    public function ajax_license_activation() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $license_email = isset($_POST['licenseEmail']) ?$_POST['licenseEmail'] : '';
        $license_key = isset($_POST['licenseKey']) ? $_POST['licenseKey'] : '';
        $license_ktcc_url = isset($_POST['licenseKtccUrl']) ? $_POST['licenseKtccUrl'] : '';
        $license_imprint_url = isset($_POST['licenseImprintUrl']) ? $_POST['licenseImprintUrl'] : '';
        $license_key_hash = md5($license_key);

        $license_info = Wp2leads_License::get_lecense_info();

        if ($license_key_hash === $license_info["key"] || $license_key === $license_info["key"]) {
            $license_info['multiplicator_validation_link'] = $license_ktcc_url;
            $license_info['imprint_validation_link'] = $license_imprint_url;
        } else {
            $license_info['multiplicator_validation_link'] = '';
            $license_info['imprint_validation_link'] = '';
        }

        update_option('wp2l_license', $license_info);

        $result = Wp2leads_License::activate_license($license_email, $license_key);

        if (!empty($result['error'])) {
            $result['message'] = __('Activation failed:') . ' ' . $result['message'];
        } else {
            // $result['message'] = __('Activation failed:') . ' ' . $result['message'];
        }

        echo json_encode($result);
        die();
    }

    public function ajax_license_updation() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $license_email = isset($_POST['licenseEmail']) ?$_POST['licenseEmail'] : '';
        $license_key = isset($_POST['licenseKey']) ? $_POST['licenseKey'] : '';
        $license_ktcc_url = isset($_POST['licenseKtccUrl']) ? $_POST['licenseKtccUrl'] : '';
        $license_imprint_url = isset($_POST['licenseImprintUrl']) ? $_POST['licenseImprintUrl'] : '';
        $license_key_hash = md5($license_key);

        $license_info = Wp2leads_License::get_lecense_info();

        if ($license_key_hash === $license_info["key"] || $license_key === $license_info["key"]) {
            $license_info['multiplicator_validation_link'] = $license_ktcc_url;
            $license_info['imprint_validation_link'] = $license_imprint_url;
        } else {
            $license_info['multiplicator_validation_link'] = '';
            $license_info['imprint_validation_link'] = '';
        }

        update_option('wp2l_license', $license_info);

        $result = Wp2leads_License::activate_license($license_email, $license_key);

        if (!empty($result['error'])) {
            $result['message'] = __('Update failed:') . ' ' . $result['message'];
        } else {
            if (200 == $result['status']) {
                $result['message'] = __('Updated successfully');
            }
        }

        echo json_encode($result);
        die();
    }

    public function ajax_license_login() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $license_email = isset($_POST['licenseEmail']) ?$_POST['licenseEmail'] : '';
        $license_key = isset($_POST['licenseKey']) ? $_POST['licenseKey'] : '';
        $license_ktcc_url = isset($_POST['licenseKtccUrl']) ? $_POST['licenseKtccUrl'] : '';
        $license_imprint_url = isset($_POST['licenseImprintUrl']) ? $_POST['licenseImprintUrl'] : '';
        $license_key_hash = md5($license_key);

        $license_info = Wp2leads_License::get_lecense_info();

        if ($license_key_hash === $license_info["key"] || $license_key === $license_info["key"]) {
            $license_info['multiplicator_validation_link'] = $license_ktcc_url;
            $license_info['imprint_validation_link'] = $license_imprint_url;
        } else {
            $license_info['multiplicator_validation_link'] = '';
            $license_info['imprint_validation_link'] = '';
        }

        update_option('wp2l_license', $license_info);

        $result = Wp2leads_License::activate_license($license_email, $license_key);

        if (!empty($result['error'])) {
            $result['message'] = __('Login failed:') . ' ' . $result['message'];
        } else {
            if (200 == $result['status']) {
                $result['message'] = __('You logged in successfully');
            }
        }

        echo json_encode($result);
        die();
    }

    /**
     * Ajax license activation
     */
    public function ajax_license_validate_ktcc() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $license_email = isset($_POST['licenseEmail']) ?$_POST['licenseEmail'] : '';
        $license_key = isset($_POST['licenseKey']) ? $_POST['licenseKey'] : '';
        $license_ktcc_url = isset($_POST['licenseKtccUrl']) ? $_POST['licenseKtccUrl'] : '';
        $license_imprint_url = isset($_POST['licenseImprintUrl']) ? $_POST['licenseImprintUrl'] : '';

        $license_info = Wp2leads_License::get_lecense_info();

        $license_info['multiplicator_validation_link'] = $license_ktcc_url;
        $license_info['imprint_validation_link'] = $license_imprint_url;

        update_option('wp2l_license', $license_info);

        $result = Wp2leads_License::validate_ktcc($license_email, $license_info["key"]);

        echo json_encode($result);
        die();
    }

    /**
     * Ajax license activation
     */
    public function ajax_license_get_key() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $license_email = isset($_POST['licenseEmail']) ?$_POST['licenseEmail'] : '';
        $license_key = isset($_POST['licenseKey']) ? $_POST['licenseKey'] : '';
        $license_ktcc_url = isset($_POST['licenseKtccUrl']) ? $_POST['licenseKtccUrl'] : '';
        $license_imprint_url = isset($_POST['licenseImprintUrl']) ? $_POST['licenseImprintUrl'] : '';

        $license_info = Wp2leads_License::get_lecense_info();

        $license_info['multiplicator_validation_link'] = $license_ktcc_url;
        $license_info['imprint_validation_link'] = $license_imprint_url;

        update_option('wp2l_license', $license_info);

        $result = Wp2leads_License::get_key($license_email, $license_info["key"]);

        echo json_encode($result);
        die();
    }

    /**
     * Ajax license activation
     */
    public function ajax_modal_license_activation() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $license_email = isset($_POST['licenseEmail']) ? $_POST['licenseEmail'] : '';
        $license_key = isset($_POST['licenseKey']) ? $_POST['licenseKey'] : '';

        $result = Wp2leads_License::activate_license($license_email, $license_key);

        if ($result['success'] === 1 && $result['status'] === 200) {
            $complete = Wp2leads_License::complete_license_activation();
        }

        echo json_encode($result);
        die();
    }

    /**
     *  Ajax license deactivation
     */
    public function ajax_license_deactivation() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $license_email = isset($_POST['licenseEmail']) ? $_POST['licenseEmail'] : '';
        $license_key = isset($_POST['licenseKey']) ? $_POST['licenseKey'] : '';

        $result = Wp2leads_License::deactivate_license($license_email, $license_key);

        echo json_encode($result);
        die();
    }

    /**
     *  Ajax license removing
     */
    public function ajax_license_removing() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $license_email = isset($_POST['licenseEmail']) ? $_POST['licenseEmail'] : '';
        $license_key = isset($_POST['licenseKey']) ? $_POST['licenseKey'] : '';
        $site = isset($_POST['site']) ? $_POST['site'] : '';

        $result = Wp2leads_License::remove_license($license_email, $license_key, $site);

        echo json_encode($result);
        die();
    }

    /**
     *  Ajax complete activation process and encrypt license key
     */
    public function ajax_complete_activation() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $result = Wp2leads_License::complete_license_activation();

        echo json_encode($result);
        die();
    }

    public function save_results_to_transient() {
        $map_results = !empty($_POST['mapResults']) ? $_POST['mapResults'] : false;

        echo json_encode(array('success' => 1));
        die();
    }

    public function set_transient() {
        $transient_name = !empty($_POST['transient_name']) ? $_POST['transient_name'] : false;
        $transient_value = !empty($_POST['transient_value']) ? $_POST['transient_value'] : 1;

        set_transient($transient_name, $transient_value);
        $result = array('success' => 1);

        echo json_encode($result);
        die();
    }

    public function get_transient() {
        $transient_name = !empty($_POST['transient_name']) ? $_POST['transient_name'] : false;

        $value = get_transient($transient_name);
        $result = array('success' => 1, 'message' => $value);

        echo json_encode($result);
        die();
    }

    public function delete_transient() {
        $transient_name = !empty($_POST['transient_name']) ? $_POST['transient_name'] : false;

        delete_transient($transient_name);
        $result = array('success' => 1);

        echo json_encode($result);
        die();
    }

    /**
     *
     */
    public function ajax_get_global_table_search_results() {
        $table = sanitize_text_field($_POST['table']);
        $column = false;
        $order = false;

        if (!empty($_POST['column'])) {
            $column = sanitize_text_field($_POST['column']);
            $column = explode('(', $column);
            $column = trim($column[0]);
        }

        if (!empty($_POST['order'])) {
            $order = sanitize_text_field($_POST['order']);
        }

        echo json_encode(MapBuilderManager::get_table_search_results($table, $column, $order));

        wp_die();
    }

    /**
     *
     */
    public function ajax_get_global_multisearch_results() {
        $string = sanitize_text_field($_POST['string']);
        echo json_encode(MapBuilderManager::get_multisearch_results($string));

        wp_die();
    }

    public function ajax_get_single_multisearch_table() {
        $string = sanitize_text_field($_POST['string']);
        $table = sanitize_text_field($_POST['table']);
        $column = sanitize_text_field($_POST['column']);

        //echo json_encode(MapBuilderManager::get_multisearch_table($table, $column, $string));
        echo json_encode(MapBuilderManager::get_single_table($table, $string));

        wp_die();
    }

	public function update_imported_campaings() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
		$imported_campaings = get_option('wp2leads_campaign_list', array());
		if (!empty($_POST['id']) )$imported_campaings[] = $_POST['id'];
		$imported_campaings = update_option('wp2leads_campaign_list', 	$imported_campaings);

		$connector = new Wp2leads_KlicktippConnector();
        $logged_in = $connector->login();
        $connector->get_last_error();

        if ($logged_in) {
            $fields = $connector->field_index();
            $tags = (array) $connector->tag_index();
            asort($tags, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
            $optins = $connector->subscription_process_index();

			if ($optins) {
				foreach ( $optins as $key => $optin ) {  ?>
					<option value="<?php echo $key ?>" <?php
							if ($sp = $connector->subscription_process_get($key)) {
								echo 'data-confirm="'.$sp->pendingurl.'" ';
								echo 'data-thankyou="'.$sp->thankyouurl.'" ';
								echo 'data-default="'.__('Klick Tipp standard link', 'wp2leads').'" ';
							}
						?>><?php echo $optin ? $optin : __( 'Default Opt-In Process', 'wp2leads' ); ?></option><?php
				}
			}
        }

        wp_die();
    }

	// install plugins for the map
	function install_map_to_api_plugins() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
		$response = array(
            'error' => 0,
            'success' => 1,
            'message' => '',
        );

		$pl = new Wp2leads_RequiredPlugins();
		$plugins = $pl->check_map_plugins($_POST['map_id']);

		if ( !$plugins ) {
			$response['message'] = __('Something went wrong', 'wp2leads');
			echo "&&&";
			echo json_encode($response);
			wp_die();
		}

		foreach ( $plugins as $key => $plugin ) {
			$i_and_a = $pl->activate_and_install_plugin($_POST['map_id'], $key);

			if ( $i_and_a['result'] ) {
				$response['success'] = 0;
				$response['error'] = 1;
				$response['message'] = $plugin['label'] . __(' can not be installed automatically', 'wp2leads');
				echo "&&&";
				echo json_encode($response);
				wp_die();
			}
		}

		$response = array(
            'error' => 0,
            'success' => 1,
            'message' => __('Done!', 'wp2leads'),
        );

		echo "&&&";
		echo json_encode($response);
		wp_die();
	}
}
