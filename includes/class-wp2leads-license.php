<?php
/**
 * Class Wp2leads_License
 *
 * @version 1.0.0
 */
class Wp2leads_License {

    public static $wp2l_license_check = false;

    public static $sw_check_wp = false;
    public static $sw_license_wp = 0;
    public static $multiplicator_free_period = 6;

    public static $free_actions = array (
        'view_data',
        'use_own_map',
        'use_default_map',
        'use _imported _map',
        'create_own_map',
        'duplicate_map',
    );

    public static $essential_actions = array (
        'transfer_data'
    );

    public static $pro_actions = array (
        'export_own_map',
        'share_own_map',
        'sell_own_map'
    );

    /**
     * @param $license_email
     * @param $license_key
     * @param string $event
     *
     * @return array|mixed|object
     */
    public static function server_request($license_email, $license_key, $site = '', $event = 'test') {
        if ( !$site ) {
            $site = Wp2leads_License::get_current_site();
        }

        $license_info = Wp2leads_License::get_lecense_info();

        $parameters = 'license_email='.$license_email.'&license_key='.$license_key.'&site_url='.$site.'&event='.$event;

        if (!empty($license_info['multiplicator_validation_link'])) {
            $parameters .= '&ktcc_url='.$license_info['multiplicator_validation_link'];
        }

        if (!empty($license_info['imprint_validation_link'])) {
            $parameters .= '&imprint_url='.$license_info['imprint_validation_link'];
        }

        $request = wp_remote_get(
            base64_decode(
                'aHR0cHM6Ly93cDJsZWFkcy1mb3Ita2xpY2stdGlwcC5jb20vc2VydmVyL3dwMmxlYWRfY2hlY2tfbGljZW5zZS5waHA='
            ) . '?' . $parameters
        );

        if (is_wp_error($request)) {
            return false;
        }

        $response_code = $request['response']['code'];

        if (200 !== $response_code) {
            return false;
        }

        $response = json_decode(wp_remote_retrieve_body( $request ), true);

        return $response;
    }

    /**
     * @param null $param
     *
     * @return mixed|void
     */
    public static function get_lecense_info($param = null) {
        $wp2l_license = get_option('wp2l_license', array(
            'email' => '',
            'key' => '',
            'secured_key' => '',
            'version' => '',
        ));

        if ($param) {
            return $wp2l_license[$param];
        }

        return $wp2l_license;
    }

    /**
     * @return null|string|string[]
     */
    public static function get_current_site() {
        $site_url = $_SERVER['HTTP_HOST'];
        $site_url = preg_replace('/^http:\/\//i', "", $site_url );
        $site_url = preg_replace('/^https:\/\//i', "", $site_url );
        $site_url = preg_replace('/^www./i', "", $site_url );

        return $site_url;
    }

    /**
     * @param $license_key
     *
     * @return string
     */
    public static function wp2l_secure_license_key( $license_key ) {
        $parts = explode('-', $license_key);

        foreach ($parts as $index => $part) {
            if ($index >= 2 && $index <= 5) {
                $parts[$index] = 'XXXXX';
            }
        }

        return implode('-', $parts);
    }

    /**
     * @return array|mixed|object
     */
    public static function is_site_active() {
        $wp2l_license = get_option('wp2l_license');

        $result = Wp2leads_License::server_request(
            $wp2l_license['email'],
            $wp2l_license['key'],
            '',
            'check'
        );

        return $result;
    }

    /**
     * @param $action
     * @param $result
     */
    public static function delete_server_status_messages($action, $result) {
        delete_transient('wp2l_no_server_response');
        delete_transient('wp2l_no_server_response_timeout');
        delete_transient('wp2l_last_paid_day');
        delete_transient('wp2l_payment_issue_message');
        delete_transient('wp2l_license_not_active');

        delete_transient('wp2l_payment_missed');
        delete_transient('wp2l_payment_missed_message');

        if ( 'activate' === $action ) {

        } elseif ( 'deactivate' === $action ) {

        } elseif ( 'update' === $action ) {

        }
    }

    /**
     * Check if transfer for local map is allowed
     *
     * @param $map_id
     *
     * @return bool
     */
    public static function is_map_transfer_allowed($map_id) {
        $wp2l_plugin_version_status = get_transient('wp2l_plugin_version_status');

        if (false !== $wp2l_plugin_version_status && -1 === (int) $wp2l_plugin_version_status) {
            return false;
        }

        if (Wp2leads_License::is_action_allowed('transfer_data') && Wp2leads_License::is_action_allowed('use_own_map')) {
            return true;
        }
        $wp2l_is_dev = get_transient('wp2l_license_level');

        if ($wp2l_is_dev) {
            $license_level = self::get_license_level(true);
        } else {
            $license_level = self::get_license_level();
        }

        $map = MapsModel::get($map_id);
        $map_info = $map->info;
        $decoded_map_info = unserialize($map_info);
        $is_map_on_server = MapBuilderManager::is_map_on_server($decoded_map_info);


        if ($is_map_on_server) {
            $is_map_owner = MapBuilderManager::is_map_owner($decoded_map_info);

//            if ($is_map_owner && !Wp2leads_License::is_action_allowed('use_own_map')) {
//                return false;
//            }

            $public_map_version = $decoded_map_info['publicMapKind'];

            if ($license_level === 'essent' && ($public_map_version === 'essent' || $public_map_version === 'free')) {
                return true;
            } else if ($license_level === 'free' && $public_map_version === 'free') {
                return true;
            }

            return false;
        } else {
            if ( !Wp2leads_License::is_action_allowed('transfer_data') ) {
                return false;
            }

            if (
                !empty($decoded_map_info['domain']) &&
                $decoded_map_info['domain'] === Wp2leads_Admin::get_site_domain() &&
                !Wp2leads_License::is_action_allowed('use_own_map')
            ) {
                return false;
            }

            return true;
        }
    }

    /**
     * @param $status
     */
    public static function set_server_status_messages($action, $result) {

        if ( 'activate' === $action ) {
            if ( 'last_paid_day' === $result['status'] ) {
                set_transient('wp2l_last_paid_day', 1);
                set_transient('wp2l_last_paid_day_message', 1);
            } elseif (
                'on_refund' === $result['status'] ||
                'on_chargeback' === $result['status'] ||
                'on_rebill_cancelled' === $result['status']
            ) {
                set_transient('wp2l_payment_issue', 1);
                set_transient('wp2l_payment_issue_message', 1);
            } elseif ( 'on_payment_missed' === $result['status'] ) {
                set_transient('wp2l_payment_missed', 1);
                set_transient('wp2l_payment_missed_message', 1);
            }

        } elseif ( 'deactivate' === $action ) {

        } elseif ( 'update' === $action ) {
            if ( 'last_paid_day' === $result['status'] ) {
                set_transient('wp2l_last_paid_day', 1);
                set_transient('wp2l_last_paid_day_message', 1);
            } elseif (
                'on_refund' === $result['status'] ||
                'on_chargeback' === $result['status'] ||
                'on_rebill_cancelled' === $result['status']
            ) {
                set_transient('wp2l_payment_issue', 1);
                set_transient('wp2l_payment_issue_message', 1);
            } elseif ( 'on_payment_missed' === $result['status'] ) {
                set_transient('wp2l_payment_missed', 1);
                set_transient('wp2l_payment_missed_message', 1);
            }
        }
    }

    /**
     * Check and compare plugin version for allowed min required plugin version
     *
     * We are checking plugin version to prevent issues with using KT API
     *
     * @return array|bool
     */
    public static function check_plugin_version() {
        $license_info = Wp2leads_License::get_lecense_info();
        $license_email = !empty($license_info['email']) ? $license_info['email'] : '';
        $license_key = !empty($license_info['key']) ? $license_info['key'] : '';
        $site_url = Wp2leads_License::get_current_site();

        $parameters = array (
            'license_email' => $license_email,
            'license_key' => $license_key,
            'site_url' => $site_url,
            'event' => 'check_version',
            'version'   =>  WP2LEADS_VERSION
        );

        $request = wp_remote_post(
            base64_decode('aHR0cHM6Ly93cDJsZWFkcy1mb3Ita2xpY2stdGlwcC5jb20vc2VydmVyL3dwMmxfY2hlY2tfdmVyc2lvbi5waHA='),
            array(
                'body'    => $parameters,
            )
        );

        if (is_wp_error($request)) {
            return false;
        }

        $response = json_decode(wp_remote_retrieve_body( $request ), true);

        if (200 === $response['code']) {
            $plugin_version_status = $response['body']['plugin_version_status'];
            $plugin_allowed_versions = $response['body']['plugin_allowed_versions'];

            return $plugin_version = array(
                'plugin_version_status' => $plugin_version_status,
                'plugin_allowed_versions' => $plugin_allowed_versions,
            );
        }

        return false;
    }

    /**
     *
     */
    public static function set_plugin_version_status() {
        $plugin_version = Wp2leads_License::check_plugin_version();

        if (false === $plugin_version) {
            delete_transient('wp2l_version_status_set');
            delete_transient('wp2l_plugin_allowed_versions');
        } else {
            set_transient('wp2l_plugin_version_status', $plugin_version['plugin_version_status']);
            set_transient('wp2l_plugin_allowed_versions', $plugin_version['plugin_allowed_versions']);
        }
    }

    /**
     *
     */
    public static function set_license() {
        $wp2l_license_not_active = get_transient('wp2l_license_not_active');
        $wp2l_license_not_active_timeout = get_transient('wp2l_license_not_active_timeout');

        $wp2l_no_server_response = get_transient('wp2l_no_server_response');
        $wp2l_no_server_response_timeout = get_transient('wp2l_no_server_response_timeout');

        if ($wp2l_no_server_response && !$wp2l_no_server_response_timeout) {
            $to_remove = true;
        }
        $wp2l_license = get_option('wp2l_license');

        if (empty($wp2l_license)) { // First plugin run
            $wp2l_license_new = array(
                'email' =>  '',
                'key'   =>  '',
                'secured_key'   =>  '',
                'version'   =>  '',
            );

            update_option('wp2l_license', $wp2l_license_new);
        } else if( $wp2l_license_not_active && !$wp2l_license_not_active_timeout ) { // Check if plugin need to be deactivated due to missing payment
            $wp2l_license_new = array(
                'email' =>  '',
                'key'   =>  '',
                'secured_key'   =>  '',
                'version'   =>  '',
            );

            delete_transient('wp2l_license_not_active');

            update_option('wp2l_license', $wp2l_license_new);
        } else {
            Wp2leads_License::update_license();
        }
    }

    /**
     * Update license info from license server locally
     *
     * @return bool
     */
    public static function update_license() {
        $lecense_info = Wp2leads_License::get_lecense_info();

        $license_email = !empty($lecense_info['email']) ? $lecense_info['email'] : '';
        $license_key = !empty($lecense_info['key']) ? $lecense_info['key'] : '';
        $secured_key = !empty($lecense_info['secured_key']) ? $lecense_info['secured_key'] : '';
        $ktcc_url = !empty($lecense_info['ktcc_url']) ? $lecense_info['ktcc_url'] : '';
        $imprint_url = !empty($lecense_info['imprint_url']) ? $lecense_info['imprint_url'] : '';
        $version = !empty($lecense_info['version']) ? $lecense_info['version'] : '';

        $result = Wp2leads_License::server_request($license_email, $license_key, '', 'check');

        if (!$result) {
            set_transient('wp2l_no_server_response', 1);
            set_transient('wp2l_no_server_response_timeout', 1, 48 * 60 * 60);

            $wp2l_license_new = array(
                'email'         =>  $license_email,
                'key'           =>  $license_key,
                'secured_key'   =>  $secured_key,
                'version'       =>  $version,
                'ktcc_url'      =>  $ktcc_url,
                'imprint_url'   =>  $imprint_url,
            );
        } else {

            do_action( 'wp2lead_after_license_server_response', 'update', $result);

            if (200 === $result['code']) {
                delete_transient('wp2l_license_not_active');

                $wp2l_license_new = array(
                    'email'         =>  $license_email,
                    'key'           =>  $license_key,
                    'secured_key'   =>  $secured_key,
                    'version'       =>  $result['body']['version'],
                );

                if (!empty($result["body"]["license"]['activated_at'])) {
                    $wp2l_license_new['license_activated_at'] = $result["body"]["license"]['activated_at'];
                }

                $license_meta = !empty($result["body"]["license"]["meta"]) ? $result["body"]["license"]["meta"] : array();
                $site_meta = !empty($result["body"]["site"]["meta"]) ? $result["body"]["site"]["meta"] : array();

                if (!empty($license_meta)) {
                    foreach ($license_meta as $key => $value) {
                        if ('activated_at' === $key) {
                            $wp2l_license_new['license_activated_at'] = $value;
                        } else {
                            $wp2l_license_new[$key] = $value;
                        }
                    }
                }

                if (!empty($site_meta)) {
                    foreach ($site_meta as $key => $value) {
                        if ('activated_at' === $key) {
                            $wp2l_license_new['site_activated_at'] = $value;
                        } else {
                            $wp2l_license_new[$key] = $value;
                        }
                    }
                }

                KlickTippManager::license_changed('free', $result['body']['version']);
            } else if (402 === $result['code']) {
                set_transient('wp2l_license_not_active', 1);
                delete_transient('wp2l_last_paid_day');

                if (!get_transient('wp2l_license_not_active_timeout')) {
                    set_transient('wp2l_license_not_active_timeout', 1, 48 * 60 * 60);
                }

                $wp2l_license_new = array(
                    'email'         =>  $license_email,
                    'key'           =>  $license_key,
                    'secured_key'   =>  $secured_key,
                    'version'       =>  '',
                );

            } else {
                delete_transient('wp2l_last_paid_day');
                $wp2l_license_new = array(
                    'email'         =>  '',
                    'key'           =>  '',
                    'secured_key'   =>  '',
                    'version'       =>  '',
                );
            }
        }

        update_option('wp2l_license', $wp2l_license_new);

        return true;
    }

    /**
     * Activate license
     *
     * @param $license_email
     * @param $license_key
     *
     * @return array
     */
    public static function activate_license($license_email, $license_key) {
        if ( '' === $license_email || '' === $license_key ) {
            return array('error' => 1, 'success' => 0, 'message' => __('Please fill in license email and license key'));
        }

        $license_info = Wp2leads_License::get_lecense_info();

        if ($license_key === $license_info['secured_key']) {
            return array('error' => 1, 'success' => 0, 'message' => __('This action are not allowed, please enter correct license email and license key'));
        }

        $result = Wp2leads_License::server_request($license_email, $license_key, '', 'activate');

        if (!$result) {
            return array('error' => 1, 'success' => 0, 'message' => __('No server response'));
        } else {

            do_action( 'wp2lead_after_license_server_response', 'activate', $result);

            $secured_key = Wp2leads_License::wp2l_secure_license_key($license_key);

            if (204 === $result['code']) { // License is correct but no available licenses for site

                set_transient('wp2l_activation_in_progress', 1);
                set_transient('wp2l_activation_in_progress_timeout', 1, 3 * 60);

                $wp2l_license_new = array(
                    'email' =>  $license_email,
                    'key'   =>  $license_key,
                    'secured_key'   =>  $secured_key,
                    'version'   =>  ''
                );

                update_option('wp2l_license', $wp2l_license_new);

                return array('error' => 0, 'success' => 1, 'status' => 204, 'message' => __('There is no available license, you can move license key from another site'));
            } else if (200 === $result['code']) { // Activation success

                set_transient('wp2l_activation_in_progress', 1);
                set_transient('wp2l_activation_in_progress_timeout', 1, 3 * 60);
                delete_transient('wp2l_license_not_active');

                $wp2l_license_new = array(
                    'email' =>  $license_email,
                    'key'   =>  $license_key,
                    'secured_key'   =>  $secured_key,
                    'version'   =>  $result['body']['version']
                );

                if (!empty($result["body"]["license"]['activated_at'])) {
                    $wp2l_license_new['license_activated_at'] = $result["body"]["license"]['activated_at'];
                }

                $license_meta = !empty($result["body"]["license"]["meta"]) ? $result["body"]["license"]["meta"] : array();
                $site_meta = !empty($result["body"]["site"]["meta"]) ? $result["body"]["site"]["meta"] : array();

                if (!empty($license_meta)) {
                    foreach ($license_meta as $key => $value) {
                        if ('activated_at' === $key) {
                            $wp2l_license_new['license_activated_at'] = $value;
                        } else {
                            $wp2l_license_new[$key] = $value;
                        }
                    }
                }

                if (!empty($site_meta)) {
                    foreach ($site_meta as $key => $value) {
                        if ('activated_at' === $key) {
                            $wp2l_license_new['site_activated_at'] = $value;
                        } else {
                            $wp2l_license_new[$key] = $value;
                        }
                    }
                }

                update_option('wp2l_license', $wp2l_license_new);

                KlickTippManager::license_changed('free', $result['body']['version']);

                return array('error' => 0, 'success' => 1, 'status' => 200, 'message' => __('Your site have been activated successfully'));
            } else {

                if (402 === $result['code']) {
                    return array('error' => 1, 'success' => 0, 'message' => __('Your license are currently inactive. Possible reason: Missing payment.'));
                }

                // First check if activation was done by mistake by
                $wp2l_license = get_option('wp2l_license');
                $result = Wp2leads_License::server_request($wp2l_license['email'], $wp2l_license['key'], '', 'activate');

                if (403 === $result['code']) {
                    return array('error' => 1, 'success' => 0, 'message' => __('This action are not allowed, please enter correct license email and license key'));
                } else {
                    $wp2l_license_new = array(
                        'email' =>  '',
                        'key'   =>  '',
                        'secured_key'   =>  '',
                        'version'   =>  ''
                    );

                    update_option('wp2l_license', $wp2l_license_new);

                    return array('error' => 1, 'success' => 0, 'message' => __('Please, use correct email and key'));
                }
            }

        }
    }

    public static function get_key($license_email, $license_key) {
        if ( '' === $license_email || '' === $license_key ) {
            return array('error' => 1, 'success' => 0, 'message' => __('Please fill in license email and license key'));
        }

        $result = Wp2leads_License::server_request($license_email, $license_key, '', 'get_key');

        if (!$result) {
            return array('error' => 1, 'success' => 0, 'message' => __('No server response'));
        } else {
            if (200 == $result['code']) {
                return array('error' => 0, 'success' => 1, 'status' => 200, 'message' => __('We sent license key to your email'));
            } else {
                return array('error' => 1, 'success' => 0, 'message' => __('No server response'));
            }
        }
    }

    public static function validate_ktcc($license_email, $license_key) {
        if ( '' === $license_email || '' === $license_key ) {
            return array('error' => 1, 'success' => 0, 'message' => __('Please fill in license email and license key'));
        }

        $result = Wp2leads_License::server_request($license_email, $license_key, '', 'add_validation');

        if (!$result) {
            return array('error' => 1, 'success' => 0, 'message' => __('Activation failed: No server response'));
        } else {
            if (200 == $result['code']) {
                $license_info = Wp2leads_License::get_lecense_info();

                $license_info['version'] = $result['body']['version'];

                $license_meta = !empty($result["body"]["license"]["meta"]) ? $result["body"]["license"]["meta"] : array();
                $site_meta = !empty($result["body"]["site"]["meta"]) ? $result["body"]["site"]["meta"] : array();

                if (!empty($result["body"]["license"]['activated_at'])) {
                    $wp2l_license_new['license_activated_at'] = $result["body"]["license"]['activated_at'];
                }

                if (!empty($license_meta)) {
                    foreach ($license_meta as $key => $value) {
                        if ('activated_at' === $key) {
                            $wp2l_license_new['license_activated_at'] = $value;
                        } else {
                            $wp2l_license_new[$key] = $value;
                        }
                    }
                }

                if (!empty($site_meta)) {
                    foreach ($site_meta as $key => $value) {
                        if ('activated_at' === $key) {
                            $wp2l_license_new['site_activated_at'] = $value;
                        } else {
                            $wp2l_license_new[$key] = $value;
                        }
                    }
                }

                update_option('wp2l_license', $license_info);

                return array('error' => 0, 'success' => 1, 'status' => 200, 'message' => __('Your site have been updated successfully'));
            } else {
                return array('error' => 1, 'success' => 0, 'message' => __('Activation failed: Please, use correct data'));
            }
        }
    }

    /**
     * Activate license
     *
     * @param $license_email
     * @param $license_key
     *
     * @return array
     */
    public static function deactivate_license($license_email, $license_key) {
        if ( '' === $license_email || '' === $license_key ) {
            return array('error' => 1, 'success' => 0, 'message' => __('Please fill in license email and license key'));
        }

        $wp2l_license = get_option('wp2l_license');

        $result = Wp2leads_License::server_request($wp2l_license['email'], $wp2l_license['key'], '', 'deactivate');

        if (!$result) {
            return array('error' => 1, 'success' => 0, 'message' => __('Deactivation failed: No server response'));
        } else {

            do_action( 'wp2lead_after_license_server_response', 'deactivate', $result);

            if (200 === $result['code']) {

                $wp2l_license_new = array(
                    'email' =>  '',
                    'key'   =>  '',
                    'secured_key'   =>  '',
                    'version'   =>  ''
                );

                update_option('wp2l_license', $wp2l_license_new);

                return array('error' => 0, 'success' => 1, 'message' => __('Your site have been deactivated successfully'));
            } else {

                update_option('wp2l_license', $wp2l_license);

                return array('error' => 1, 'success' => 0, 'message' => __('Deactivation failed'));
            }

        }
    }

    /**
     * Remove site license completely
     *
     * @param $license_email
     * @param $license_key
     * @param $site
     *
     * @return array
     */
    public static function remove_license($license_email, $license_key, $site) {
        if ( !$license_email || !$license_key ) {
            return array('error' => 1, 'success' => 0, 'message' => __('Please fill in license email and license key'));
        }

        if ( !$site  ) {
            return array('error' => 1, 'success' => 0, 'message' => __('Please select site to remove'));
        }

        $result = Wp2leads_License::server_request($license_email, $license_key, $site, 'delete');

        if (200 === $result['code']) {
            return array('error' => 0, 'success' => 1, 'message' => __('Site have been deleted successfully'));
        } else {
            return array('error' => 1, 'success' => 0, 'message' => __('Deactivation failed'));
        }
    }

    /**
     * Complete activation process
     *
     * @return array
     */
    public static function complete_license_activation() {
        $wp2l_license = get_option('wp2l_license');
        $license_key = $wp2l_license['key'];
        $hashed_key = md5 ( $license_key );
        $wp2l_license['key'] = $hashed_key;

        $result = update_option('wp2l_license', $wp2l_license);

        if ($result) {
            delete_transient( 'wp2l_activation_in_progress' );
            return array('error' => 0, 'success' => 1, 'message' => __('License managing completed'));
        }

        return array('error' => 1, 'success' => 0, 'message' => __('License managing did not completed'));
    }

    /**
     * Get already activated site's list for current licenses
     *
     * @return bool|string|void
     */
    public static function get_license_list() {
        $wp2l_license = get_option('wp2l_license');

        $license_email = $wp2l_license['email'];
        $license_key = $wp2l_license['key'];

        $result = Wp2leads_License::server_request($license_email, $license_key, '', 'get_all');

        if (200 === $result['code']) {
            return $result['body']['site_list'];
        } elseif (204 === $result['code']) {
            return __('No active sites');
        } else {
            return false;
        }
    }

    /**
     * Get number of available licenses for current license data
     *
     * @param bool $total
     *
     * @return int
     */
    public static function count_licenses($total = true) {
        $wp2l_license = get_option('wp2l_license');

        $license_email = $wp2l_license['email'];
        $license_key = $wp2l_license['key'];

        $event = $total ? 'count_all' : 'count_available';

        $result = Wp2leads_License::server_request($license_email, $license_key, '', $event);

        if (200 === $result['code']) {
            return $result['body']['total'];
        } else {
            return 0;
        }
    }

    /**
     * Check if current url allowed for dev mode
     */
    public static function is_dev_allowed() {
        $wp2l_license = get_option('wp2l_license');
        $current_url = Wp2leads_License::get_current_site();

        $allowed_urls = array(
            'd3AybGVhZHMubG9j',
            'ZHVtbXkuc2FudGVncmEtaW50ZXJuYXRpb25hbC5jb20=',
            'd3AybGVhZHMzLmxvYw==',
            'd3AybGVhZHMyLmxvYw==',
            'cGx1Z2luLXRlc3QuZGU=',
			'bWFwcy53cDJsZWFkcy5jb20=',
			'dG9iaWFzLmR1bW15',
        );

        return (in_array( base64_encode ( $current_url ), $allowed_urls ) && 'pro' === $wp2l_license['version']);
    }

    public static function get_license_level($dev = false) {
        $is_dev_allowed = Wp2leads_License::is_dev_allowed();
        $wp2l_license = get_option('wp2l_license');

        if ('ktcc' === $wp2l_license["version"]) {
            if (
                empty($wp2l_license["license_activated_at"]) ||
                empty($wp2l_license["site_activated_at"]) ||
                empty($wp2l_license["multiplicator_validation_status"]) ||
                empty($wp2l_license["imprint_validation_status"])
            ) {
                $wp2l_license_version = 'free';
            } elseif (!empty($wp2l_license["multiplicator_validation_status"]) && 'unapproved' === $wp2l_license["multiplicator_validation_status"]) {
                $wp2l_license_version = 'free';
            } elseif (!empty($wp2l_license["imprint_validation_status"]) && 'unapproved' === $wp2l_license["imprint_validation_status"]) {
                $wp2l_license_version = 'free';
            } else {
                $need_check = false;

                if (empty($wp2l_license["multiplicator_validation_link"]) || empty($wp2l_license["imprint_validation_link"])) {
                    $need_check = true;
                } elseif (
                    'pending' === $wp2l_license["imprint_validation_status"] || 'pending' === $wp2l_license["multiplicator_validation_status"]
                ) {
                    $need_check = true;
                }

                if (!$need_check) {
                    $wp2l_license_version = 'pro';
                } else {
                    $first_activation = $wp2l_license["license_activated_at"];

                    if (empty($wp2l_license["multiplicator_validation_link"]) || 'pending' === $wp2l_license["multiplicator_validation_status"]) {
                        $first_activation = $wp2l_license["license_activated_at"];
                    } elseif (empty($wp2l_license["imprint_validation_link"]) || 'pending' === $wp2l_license["imprint_validation_status"]) {
                        $first_activation = $wp2l_license["site_activated_at"];
                    }

                    $now = time();
                    $period = Wp2leads_License::$multiplicator_free_period;
                    $expire_period = $first_activation + ($period * 24 * 60 * 60);

                    if ($now < $expire_period) {
                        $wp2l_license_version = 'pro';
                    } else {
                        $wp2l_license_version = 'free';
                    }
                }
            }
        } else {
            $wp2l_license_version = !empty($wp2l_license['version']) ? $wp2l_license['version'] : 'free';
        }

        $wp2l_license_version_dev = get_transient('wp2l_license_level');

        if ($dev && $is_dev_allowed && false !== $wp2l_license_version_dev) {
            $wp2l_license_version = $wp2l_license_version_dev;
        }

        return $wp2l_license_version;
    }

    public static function is_export_allowed() {
        $wp2l_is_dev = get_transient('wp2l_license_level');

        if ($wp2l_is_dev) {
            $license_level = self::get_license_level(true);
        } else {
            $license_level = self::get_license_level();
        }

        if ('free' !== $license_level) {
            return true;
        }

        $user_id = get_current_user_id();

        return get_option('wp2l_policy_confirmed_' . $user_id);
    }

    public static function save_policy_confirmed_option() {
        $user_id = get_current_user_id();

        return update_option('wp2l_policy_confirmed_' . $user_id, 1);
    }

    public static function is_user_level($level) {
        $wp2l_is_dev = get_transient('wp2l_license_level');

        if ($wp2l_is_dev) {
            $license_level = self::get_license_level(true);
        } else {
            $license_level = self::get_license_level();
        }

        return $license_level === $level;
    }

    public static function is_action_allowed( $action ) {
        $allowed_actions = self::$free_actions;

        $wp2l_is_dev = get_transient('wp2l_license_level');

        if ($wp2l_is_dev) {
            $license_level = self::get_license_level(true);
        } else {
            $license_level = self::get_license_level();
        }

        switch ( $license_level ) {
            case 'essent':
                $allowed_actions = array_merge( $allowed_actions, self::$essential_actions );
                break;
            case 'pro':
                $allowed_actions = array_merge( $allowed_actions, self::$essential_actions, self::$pro_actions );
                break;
        }

        return in_array( $action, $allowed_actions );
    }
}

?>
