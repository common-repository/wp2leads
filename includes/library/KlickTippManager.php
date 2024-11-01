<?php
/**
 * Class KlickTippManager
 */
class KlickTippManager {

    /**
     * @param $map_id
     * @param $data_for_transfer
     * @param string $type
     *
     * @return array|bool
     */
    public static function transfer_data_to_kt( $map_id, $data_for_transfer, $type = 'manually' ) {
        $connector = new Wp2leads_KlicktippConnector();
        $logged_in = $connector->login(get_option('wp2l_klicktipp_username'), get_option('wp2l_klicktipp_password'));

        if( $logged_in ) {
            $available_tags = $connector->tag_index();
            $detach_tags = ApiHelper::getDetachTags($map_id);

            $added_subscribers = array();
            $existed_subscribers = array();
            $failed_subscribers = array();
            $last_transferred_time = time();

            foreach ($data_for_transfer as $email => $data) {
                $tags = ApiHelper::getTagsIds($data['tags'], $available_tags, $connector);

                $result = self::transfer_subscriber_to_kt($map_id, $connector, $email, $data, $tags, $detach_tags);

                if ( $result ) {
                    if ($result['added_subscriber']) {
                        $added_subscribers[] = $result['subscriber'];
                    } else if ($result['existed_subscriber']) {
                        $existed_subscribers[] = $result['subscriber'];
                    } else if ($result['failed_subscriber']) {
                        $failed_subscriber = $result['failed_subscriber'];

                        if (is_array($failed_subscriber)) {
                            $data = array(
                                'map_id' => $map_id,
                                'user_email' => $failed_subscriber['email'],
                                'user_data' => serialize($failed_subscriber['data']),
                                'user_status'  => 'failed',
                                'time' => date('Y-m-d H:i:s', $last_transferred_time)
                            );

                            $failed_id = FailedTransferModel::insert($data);

                            if ($failed_id) {
                                $failed_subscribers[] = $failed_id;
                            }
                        }
                    }
                }
            }

            $new_subscribers_amount = count($added_subscribers);
            $updated_subscribers_amount = count($existed_subscribers);
            $failed_subscribers_amount = count($failed_subscribers);
            $total_transferred = $new_subscribers_amount + $updated_subscribers_amount;
            $available_users = count($data_for_transfer);

            $counters = array('unique' => $new_subscribers_amount);

            ApiHelper::setSubscribersCounter($map_id, $counters);

            self::save_statistics(
                $map_id,
                $available_users,
                $new_subscribers_amount,
                $updated_subscribers_amount,
                $failed_subscribers,
                $total_transferred,
                $last_transferred_time,
                $type
            );

            return array(
                'last_transferred_time' => $last_transferred_time,
                'added_subscribers' => $new_subscribers_amount,
                'existed_subscribers' => $updated_subscribers_amount,
                'failed_subscribers' => $failed_subscribers_amount
            );

        } else {
            return false;
        }
    }

    public static function save_statistics($map_id, $available, $created, $updated, $failed, $transferred, $time, $type) {
        if (($transferred + count($failed)) > 0) {
            $data = array(
                'time' => $time,
                'map_id' => $map_id,
                'transfer_type' => $type,
                'statistics' => array(
                    __( 'Total Amount', 'wp2leads' ) => $available > 0 ? $available : 0,
                    __( 'New subscribers', 'wp2leads' ) => $created,
                    __( 'Updated subscribers', 'wp2leads' ) => $updated,
                    __( 'Total transferred', 'wp2leads' ) => $transferred,
                    __( 'Actual subscribers', 'wp2leads' ) => $available - $transferred - count($failed) ,
                    __( 'Failed subscribers', 'wp2leads' ) => $failed
                )
            );

            StatisticsManager::saveStatistics($data);
        }
    }


    /**
     * @param $old_license
     * @param $new_license
     *
     * @since 1.0.2.5
     */
    public static function license_changed($old_license, $new_license) {
        global $wpdb;

        if ('pro' === $new_license || 'ktcc' === $new_license) {
            $wpdb->delete( $wpdb->options, array( 'option_name' => 'wp2lead_transfer_limit__counter' ) );
            $wpdb->delete( $wpdb->options, array( 'option_name' => 'wp2lead_transfer_limit__counter_timeout' ) );

            return;
        }

        if ($old_license === $new_license) {
            return;
        }

        if ('free' === $old_license && 'essent' === $new_license) {
            return;
        }

        return;
    }

    /**
     * Get pro limitation settings
     *
     * @return array|bool
     */
    public static function get_initial_kt_limitation() {
        global $wpdb;

        $limit_users = $wpdb->get_row( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE 'wp2lead_transfer_limit_users'", ARRAY_A );
        $limit_message = $wpdb->get_row( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE 'wp2lead_transfer_limit_message'", ARRAY_A );
        $limit_days = $wpdb->get_row( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE 'wp2lead_transfer_limit_days'", ARRAY_A );

        if (empty($limit_users) || empty($limit_message) || empty($limit_days)) {
            return false;
        }

        return array(
            'limit_users' => $limit_users['option_value'],
            'limit_message' => $limit_message['option_value'],
            'limit_days' => $limit_days['option_value'],
        );
    }

    /**
     * Set pro limitation settings
     *
     * @return bool
     */
    public static function set_initial_kt_limitation() {
        global $wpdb;

        $limit_users = $wpdb->get_row( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE 'wp2lead_transfer_limit_users'", ARRAY_A );
        $limit_message = $wpdb->get_row( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE 'wp2lead_transfer_limit_message'", ARRAY_A );
        $limit_days = $wpdb->get_row( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE 'wp2lead_transfer_limit_days'", ARRAY_A );

        if ($limit_users && $limit_message && $limit_days) {
            return true;
        }

        $response = wp_remote_get("https://www.klick-tipp.com/api/split/19ygz7uaz1fzkzbbe9?ip=".$_SERVER["REMOTE_ADDR"]."&cookie=".(isset($_COOKIE["KTSTC50Z55106"]) ? $_COOKIE["KTSTC50Z55106"] : -1)."");

        if (is_wp_error($response) || wp_remote_retrieve_response_code( $response ) !== 200) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $settings_array = explode(' ', $body);

        if (is_array($settings_array)) {
            $settings_limit_users = isset($settings_array[0]) ? $settings_array[0] : false;
            $settings_limit_message = isset($settings_array[1]) ? $settings_array[1] : false;
            $settings_limit_days = isset($settings_array[2]) ? $settings_array[2] : false;

            if ($settings_limit_users && $settings_limit_message && $settings_limit_days) {
                $wpdb->insert( $wpdb->options, array(
                    'option_name' => 'wp2lead_transfer_limit_users',
                    'option_value' => $settings_limit_users,
                    'autoload' => 'no'
                ));

                $wpdb->insert( $wpdb->options, array(
                    'option_name' => 'wp2lead_transfer_limit_message',
                    'option_value' => $settings_limit_message,
                    'autoload' => 'no'
                ));

                $wpdb->insert( $wpdb->options, array(
                    'option_name' => 'wp2lead_transfer_limit_days',
                    'option_value' => $settings_limit_days,
                    'autoload' => 'no'
                ));
            }
        }
    }

    /**
     * Get pro limitation settings
     *
     * @return array|bool
     */
    public static function get_transfer_counter() {
        global $wpdb;

        $limit_counter = $wpdb->get_row( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE 'wp2lead_transfer_limit__counter'", ARRAY_A );
        $limit_counter_timeout = $wpdb->get_row( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE 'wp2lead_transfer_limit__counter_timeout'", ARRAY_A );

        if (empty($limit_counter) || empty($limit_counter_timeout)) {
            return false;
        }

        return array(
            'limit_counter' => $limit_counter['option_value'],
            'limit_counter_timeout' => $limit_counter_timeout['option_value'],
        );
    }

    /**
     * Get pro limitation settings
     *
     * @return array|bool
     */
    public static function reset_transfer_counter() {
        global $wpdb;

        $kt_counter = KlickTippManager::get_transfer_counter();

        if (!$kt_counter) {
            return false;
        }

        $kt_limit_counter = $kt_counter['limit_counter'];
        $kt_limit_counter_timeout = $kt_counter['limit_counter_timeout'];

        if (time() > $kt_limit_counter_timeout) {
            $wpdb->delete( $wpdb->options, array( 'option_name' => 'wp2lead_transfer_limit__counter' ) );
            $wpdb->delete( $wpdb->options, array( 'option_name' => 'wp2lead_transfer_limit__counter_timeout' ) );
        }
    }

    public static function increment_transfer_counter($map_id) {
        global $wpdb;
        $is_transfer_allowed = Wp2leads_License::is_map_transfer_allowed( (int) $map_id );

        if ($is_transfer_allowed) {
            return;
        }

        $kt_limitation = KlickTippManager::get_initial_kt_limitation();

        if (!$kt_limitation) {
            return;
        }

        $wp2_leads_counter = $wpdb->get_results( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE 'wp2lead_transfer_limit__counter'", ARRAY_A );

        if (empty($wp2_leads_counter)) {
            $count = 1;
            $kt_limit_days = (int) $kt_limitation['limit_days'];
            $timeout = time() + 60 * 60 * 24 * $kt_limit_days;

            $wpdb->insert( $wpdb->options, array(
                'option_name' => 'wp2lead_transfer_limit__counter',
                'option_value' => $count,
                'autoload' => 'no'
            ));

            $wpdb->insert( $wpdb->options, array(
                'option_name' => 'wp2lead_transfer_limit__counter_timeout',
                'option_value' => $timeout,
                'autoload' => 'no'
            ));
        } else {
            $count = (int) $wp2_leads_counter[0]['option_value'];
            ++$count;

            $wpdb->update( $wpdb->options,
                array('option_value' => $count),
                array('option_name' => 'wp2lead_transfer_limit__counter'),
                array('%d'),
                array('%s')
            );
        }
    }

    /**
     * @param Wp2leads_KlicktippConnector $connector
     * @param $email
     * @param $data
     * @param $tags
     * @param $detach_tags
     *
     * @return array
     */
    public static function transfer_subscriber_to_kt($map_id, Wp2leads_KlicktippConnector $connector, $email, $data, $tags, $detach_tags) {

        if (!Wp2leads_License::is_map_transfer_allowed( (int) $map_id )) {
            $kt_limitation = KlickTippManager::get_initial_kt_limitation();

            if (!$kt_limitation) {
                return false;
            }
            $kt_counter = KlickTippManager::get_transfer_counter();

            if ($kt_counter) {
                $kt_limit_users = $kt_limitation['limit_users'];
                $kt_limit_message = $kt_limitation['limit_message'];
                $kt_limit_days = $kt_limitation['limit_days'];

                $kt_limit_counter = $kt_counter['limit_counter'];
                $kt_limit_counter_timeout = $kt_counter['limit_counter_timeout'];
                $kt_limit_counter_timeout_left = $kt_limit_counter_timeout - time();

                $kt_limit_counter_left = (int) $kt_limit_users - (int) $kt_limit_counter;

                if (0 > $kt_limit_counter_left || 0 === $kt_limit_counter_left) {
                    return false;
                }
            }
        }

        $subscriber_exist = $connector->subscriber_search($email);

        if (!empty($data["fields"])) {
            foreach ($data["fields"] as $field_id => $field_value) {
                $data["fields"][$field_id] = ApiHelper::remove_wp_emoji(trim($field_value));
            }
        }

        if (!empty($data['manually_tags'])) {
            $tags = array_merge($tags, $data['manually_tags']);
        }

        $detach_tags = array_merge($detach_tags, $data['detach_tags']);

        foreach ($tags as $key => $tag_id) {
            if (in_array($tag_id, $detach_tags)) {
                unset($tags[$key]);
            }
        }

        array_values($tags);

        // Add new Subscriber
        if ( !$subscriber_exist ) {

            if (!empty($data["fields"]["fieldLeadValue"])) {
                $fieldLeadValue = $data["fields"]["fieldLeadValue"];

                $fieldLeadValueArray = explode('::', $fieldLeadValue);

                if (count($fieldLeadValueArray) == 2) {
                    $fieldLeadValue = $fieldLeadValueArray[1];
                    $newFieldLeadValue  = (int)$fieldLeadValue;
                    $data["fields"]["fieldLeadValue"] = (string) $newFieldLeadValue;
                }
            }

            $subscriber = $connector->subscribe($email, $data['optin'], 0, $data['fields'], '');

            if ($subscriber) {
                foreach ($tags as $tag_id) {
                    $connector->tag($email, $tag_id);
                }

                self::increment_transfer_counter($map_id);

                do_action('wp2leads_transfer_user_created', $map_id, $email, $data, $tags, $detach_tags);

                return array( 'subscriber' => $subscriber, 'added_subscriber' => true, 'existed_subscriber' => false, 'failed_subscriber' => false );
            } else {
                $error = $connector->get_last_error(false);

                $failed_subscriber = array(
                    'email' => $email,
                    'data'  => array(
                        'optin' => $data['optin'],
                        'fields' => $data['fields'],
                        'tags'  =>  $tags,
                        'detach_tags'  => $detach_tags,
                        'reason' => $error
                    )
                );

                return array( 'subscriber' => false, 'added_subscriber' => false, 'existed_subscriber' => false, 'failed_subscriber' => $failed_subscriber );
            }
        } else { // Maybe update Subscriber
            $subscriber_changed = false;
            $subscriber = (array) $connector->subscriber_get($subscriber_exist);

            $subscriber_re = $connector->subscribe($email, $data['optin'], 0, $data['fields'], '');
            $tag_index = $connector->tag_index();

            if (!empty($data["fields"]["fieldLeadValue"])) {
                $fieldLeadValue = $data["fields"]["fieldLeadValue"];

                $fieldLeadValueArray = explode('::', $fieldLeadValue);

                if (count($fieldLeadValueArray) == 2) {
                    $fieldLeadValue = $fieldLeadValueArray[1];
                    $apiFieldLeadValue = $subscriber["fieldLeadValue"];
                    $newFieldLeadValue  = (int)$fieldLeadValue + (int)$apiFieldLeadValue;
                    $data["fields"]["fieldLeadValue"] = (string) $newFieldLeadValue;
                }
            }

            foreach ($data['fields'] as $code => $field_value) {
                if (isset($subscriber[$code])) {
                    if ($subscriber[$code] !== $field_value && html_entity_decode($subscriber[$code]) !== $field_value) {
                        $subscriber_changed = $connector->subscriber_update($subscriber_exist, $data['fields']);
                        break;
                    }
                }
            }

            foreach ($detach_tags as $tag_id) {
                if (empty($subscriber['tags']) || !is_array($subscriber['tags'])) {
                    $subscriber['tags'] = array();
                }
                if ( in_array( $tag_id, $subscriber['tags'] ) ) {
                    $response = $connector->untag($email, $tag_id);

                    if ($response) {
                        $subscriber_changed = true;
                    }
                }
            }

            foreach ($tags as $tag_id) {
                if (empty($subscriber['tags']) || !in_array($tag_id, $subscriber['tags'])) {
                    $response = $connector->tag($email, $tag_id);

                    if ($response) {
                        $subscriber_changed = true;
                    }
                }
            }

            if (!empty($data['detach_auto_tags']) && !empty($tag_index) && is_array($tag_index)) {
                foreach ($data['detach_auto_tags'] as $tag_name) {
                    $tag_name = ApiHelper::filterBeforeOutput(ApiHelper::filterForbidenKTSymbols(ApiHelper::remove_wp_emoji(trim($tag_name))));
                    $tag_id = array_search($tag_name, $tag_index);

                    if (false !== $tag_id && isset($subscriber["manual_tags"][$tag_id])) {
                        $response = $connector->untag($email, $tag_id);

                        if ($response) {
                            $subscriber_changed = true;
                        }
                    }
                }
            }

            if ( $subscriber_changed ) {
                self::increment_transfer_counter($map_id);

                do_action('wp2leads_transfer_user_updated', $map_id, $email, $data, $tags, $detach_tags);

                return array( 'subscriber' => $subscriber, 'added_subscriber' => false, 'existed_subscriber' => true, 'failed_subscriber' => false );
            }

            return array( 'subscriber' => $subscriber, 'added_subscriber' => false, 'existed_subscriber' => false, 'failed_subscriber' => false );
        }
    }

    public static function is_map_transfer_in_bg($map_id) {
        $wp2leads_map_to_api_prepare_in_progress = BackgroundProcessManager::get_transient( 'wp_wp2leads_maptoapi_prepare__' . $map_id );
        $wp2leads_map_to_api_load_in_progress = BackgroundProcessManager::get_transient( 'wp_wp2leads_maptoapi_load__' . $map_id );
        $wp2l_map_to_api_in_progress = BackgroundProcessManager::get_transient( 'wp2leads_maptoapi_bg_in_process' );

        if (!empty($wp2leads_map_to_api_prepare_in_progress)) {
            return true;
        }

        if (!empty($wp2leads_map_to_api_load_in_progress)) {
            return true;
        }

        if ($wp2l_map_to_api_in_progress) {
            foreach ($wp2l_map_to_api_in_progress as $map_transfer_id => $process) {
                if ((int)$map_transfer_id === (int)$map_id) {
                    foreach ($process as $pi => $data) {
                        if (empty($data['total'])) {
                            unset($process[$pi]);
                        }
                    }

                    if (empty($process)) {
                        unset($wp2l_map_to_api_in_progress[$map_transfer_id]);
                    }
                }
            }

            if (empty($wp2l_map_to_api_in_progress)) {
                BackgroundProcessManager::delete_transient('wp2leads_maptoapi_bg_in_process');
                return false;
            } else {
                BackgroundProcessManager::set_transient('wp2leads_maptoapi_bg_in_process', $wp2l_map_to_api_in_progress);
                return true;
            }
        }

        return false;
    }

	/**
     * @param Wp2leads_KlicktippConnector $connector
     * @param $old_name
     * @param $new_name
     *
     * @return array ( tag_id => new_tag_name )
     */

	public static function update_tag_name( $old_name, $new_name ) {
		// search the tag
		$connector = new Wp2leads_KlicktippConnector();
        $logged_in = $connector->login(get_option('wp2l_klicktipp_username'), get_option('wp2l_klicktipp_password'));

		if ( ! $logged_in) return false;

        $available_tags = (array)$connector->tag_index();
		$needle_key = array_search( $old_name, $available_tags );

		if ( $needle_key === FALSE ) return false;

		if ( $connector->tag_update( $needle_key, $new_name ) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function autologin_kt_invite($subscriberid) {
        $connector = new Wp2leads_KlicktippConnector();
        $logged_in = $connector->login();
        if( !$logged_in ) return false;

        $subscriber = $connector->subscriber_get($subscriberid);
        if (empty($subscriber->email)) return false;

        $email = $subscriber->email;
        $fname = !empty($subscriber->fieldFirstName) ? $subscriber->fieldFirstName : '';
        $lname = !empty($subscriber->fieldLastName) ? $subscriber->fieldLastName : '';

        $user_meta_fields = [
            'first_name' => $fname,
            'last_name' => $lname,
        ];

        // Get user by KT ID first
        $user_query = new WP_User_Query( [
            'meta_key'    => 'kt_subscriberid',
            'meta_value'    => $subscriberid,
        ] );

        $users = $user_query->get_results();
        $imported = false;
        $created = false;

        if (!empty($users[0])) {
            $imported = true;
            $existed_wp_user = $users[0];
        } else {
            $existed_wp_user = get_user_by( 'email', $email );
        }

        if (empty($existed_wp_user)) {
            $new_user_args = [
                'user_login'    => sanitize_email( $email ),
                'user_email'    => sanitize_email( $email ),
                'user_pass'     => wp_generate_password( absint( 15 ), true, false ),
                'role'          => 'subscriber',
            ];

            $display_name_array = [];

            if (!empty($fname)) {
                $display_name_array[] = $fname;
                $new_user_args['first_name'] = $fname;
            }

            if (!empty($lname)) {
                $display_name_array[] = $lname;
                $new_user_args['last_name'] = $lname;
            }

            if (!empty($display_name_array)) {
                $new_user_args['display_name'] = implode(' ', $display_name_array);
            }

            $user_id        = wp_insert_user( $new_user_args );
            $existed_wp_user = get_user_by( 'ID', $user_id );
        } else {
            $user_id        = $existed_wp_user->ID;
        }

        if (empty($imported)) {
            update_user_meta( $user_id, 'kt_subscriberid', $subscriberid );
        }

        if (empty($created)) {
            global $wpdb;
            $sql = "SELECT * FROM {$wpdb->usermeta} WHERE user_id = '{$user_id}'";
            $usermeta = $wpdb->get_results($sql, ARRAY_A);

            if (!empty($usermeta)) {
                foreach ($usermeta as $data) {
                    $meta_key = $data['meta_key'];
                    $meta_value = $data['meta_value'];

                    if (!empty($user_meta_fields[$meta_key]) && empty($meta_value)) {
                        update_user_meta( $user_id, $meta_key, $user_meta_fields[$meta_key] );
                    }

                    if (!empty($user_meta_fields[$meta_key])) {
                        unset($user_meta_fields[$meta_key]);
                    }
                }
            }
        }

        if (!empty($user_meta_fields)) {
            foreach ($user_meta_fields as $meta_key => $meta_value) {
                if (!empty($meta_value)) {
                    update_user_meta( $user_id, $meta_key, $meta_value );
                }
            }
        }

        do_action('wp2leads_member_kt_invite', $user_id, $subscriber, $created);

        if (!is_user_logged_in()) {
            wp_set_current_user( $user_id, $existed_wp_user->user_login );
            wp_set_auth_cookie( $user_id );
        }
    }
}
