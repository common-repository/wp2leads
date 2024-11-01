<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/admin
 */
class Wp2leads_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    private $user_name;

    private $user_password;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->user_name = get_option('wp2l_klicktipp_username');
        $this->user_password = get_option('wp2l_klicktipp_password');

        $this->load_classes();
    }

    /**
     * Loading some classes with additional logic
     */
    public function load_classes() {
        require_once plugin_dir_path( WP2LEADS_PLUGIN_FILE ) . 'includes/library/SystemHelper.php';
        require_once plugin_dir_path( WP2LEADS_PLUGIN_FILE ) . 'includes/library/BackgroundProcessManager.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/library/ApiHelper.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/library/MapsModel.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/library/MapBuilderManager.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/library/KlickTippManager.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/library/StatisticsModel.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/library/FailedTransferModel.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/library/MetaModel.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/library/StatisticsManager.php';
		/**
		 * The class responsible for checking maps without resourses
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-maps-activation.php';
    }

    public function enqueue_global_scripts()
    {
        wp_enqueue_script($this->plugin_name . '-global', plugin_dir_url(__FILE__) . 'js/wp2leads-global.js?' . time(),
            array( 'jquery'), $this->version, true);
		wp_enqueue_style($this->plugin_name . '-global', plugin_dir_url(__FILE__) . 'css/wp2leads-admin-global.css?' . time(), array(), $this->version, 'all');
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        $wp_scripts = wp_scripts();

        wp_enqueue_style($this->plugin_name . '-fastselect', plugin_dir_url(__FILE__) . 'css/fastselect.css', array(), '0.7.3', 'all');
        wp_enqueue_style($this->plugin_name . '-tokenize', plugin_dir_url(__FILE__) . 'css/tokenize2.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '-tipr', plugin_dir_url(__FILE__) . 'css/tipr.css', array(), '4.0.1', 'all');
        wp_enqueue_style($this->plugin_name . '-simplescroll', plugin_dir_url(__FILE__) . 'css/simple-scrollbar.css', array(), '', 'all');
        wp_enqueue_style(
            'jquery-ui-theme-smoothness',
            sprintf(
                '//ajax.googleapis.com/ajax/libs/jqueryui/%s/themes/smoothness/jquery-ui.css', // working for https as well now
                $wp_scripts->registered['jquery-ui-core']->ver
            )
        );
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wp2leads-admin.css?' . time(), array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name . '-breakpoints', plugin_dir_url(__FILE__) . 'js/jquery.breakpoints.min.js', array(), '1.6.0', true);
        wp_enqueue_script($this->plugin_name . '-fastselect', plugin_dir_url(__FILE__) . 'vendor/fastselect/fastselect.standalone.js', array(), '0.7.3', true);
        wp_enqueue_script($this->plugin_name . '-handlebars', plugin_dir_url(__FILE__) . 'js/handlebars-v4.0.11.js', array(), '4.0.11', true);
        wp_enqueue_script($this->plugin_name . '-blockUI', plugin_dir_url(__FILE__) . 'js/jquery.blockUI.js', array(), '2.7.0', true);
        wp_enqueue_script($this->plugin_name . '-lodash', plugin_dir_url(__FILE__) . 'js/lodash.min.js', array(), '4.17.5', true);

        wp_add_inline_script( $this->plugin_name . '-lodash', 'window.lodash = _.noConflict();', 'after' );

        wp_enqueue_script($this->plugin_name . '-tipr', plugin_dir_url(__FILE__) . 'js/tipr.min.js', array(), '4.0.1', true);
        wp_enqueue_script($this->plugin_name . '-simplescroll', plugin_dir_url(__FILE__) . 'js/simple-scrollbar.min.js', array(), '', true);
        wp_enqueue_script($this->plugin_name . '-floatthead', plugin_dir_url(__FILE__) . 'js/jquery.floatThead.min.js', array(), '', true);
        wp_enqueue_script($this->plugin_name . '-sticky', plugin_dir_url(__FILE__) . 'js/jquery.sticky.js', array(), '', true);

        wp_enqueue_script($this->plugin_name . '-wp2leads-general', plugin_dir_url(__FILE__) . 'js/wp2leads-general.js?' . time(), array('underscore', $this->plugin_name . '-blockUI'), '', true);

        if (!empty($_GET['tab']) && 'map_runner' !== $_GET['tab']) {
            if ('statistics' === $_GET['tab']) {
                wp_enqueue_script($this->plugin_name . '-statistics', plugin_dir_url(__FILE__) . 'js/wp2leads-statistics.js?' . time(), array('underscore'), '', true);
            } else {
                wp_enqueue_script($this->plugin_name . '-highlight', plugin_dir_url(__FILE__) . 'js/jquery.highlight.js', array(), '', true);
                wp_enqueue_script($this->plugin_name . '-tokenize', plugin_dir_url(__FILE__) . 'js/tokenize2.min.js', array(), '', true);

                wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wp2leads-admin.js?' . time(), array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), $this->version, true);

                wp_localize_script( $this->plugin_name, 'ktAdminObject', array(
                    'iteration_limit'         => BackgroundProcessManager::get_iteration_limit(),
                ) );
                wp_enqueue_script($this->plugin_name . '-klick-tip-api', plugin_dir_url(__FILE__) . 'js/klick-tip-api.js?' . time(), array(), '', true);

                wp_localize_script( $this->plugin_name . '-klick-tip-api', 'ktAPIObject', array(
                    'iteration_limit'         => BackgroundProcessManager::get_iteration_limit(),
                ) );
            }
        } else {
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wp2leads-runner.js?' . time(), array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ), $this->version, true);
        }

		if (!empty($_GET['page']) && 'wp2l-admin' == $_GET['page']) {
            wp_enqueue_script($this->plugin_name . '-tippy', plugin_dir_url(__FILE__) . 'js/tippy.js', array(), '6.2.7', false);
			 wp_enqueue_script($this->plugin_name . '-klick-tip-api', plugin_dir_url(__FILE__) . 'js/klick-tip-api.js?' . time(), array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), '', true);

            wp_localize_script( $this->plugin_name . '-klick-tip-api', 'ktAPIObject', array(
                'iteration_limit'         => BackgroundProcessManager::get_iteration_limit(),
            ) );
		}
		wp_enqueue_script($this->plugin_name . '-wp2leads-wizard', plugin_dir_url(__FILE__) . 'js/wp2leads-map-to-api-wizard.js?' . time(), array(), '', true);

		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-tooltip');

        wp_localize_script(
            $this->plugin_name . '-wp2leads-general', 'wp2leads_ajax_object', [
                'nonce' => wp_create_nonce('wp2leads_ajax_nonce')
            ]
        );
    }

    public function delete_server_status_messages($action, $result) {

        Wp2leads_License::delete_server_status_messages($action, $result);

    }

    public function set_server_status_messages($action, $result) {

        Wp2leads_License::set_server_status_messages($action, $result);

    }

    /**
     * Adding global admin notices
     *
     * @since    1.0.0
     */
    public function add_admin_notices() {
        $wp2l_activation_in_progress = get_transient( 'wp2l_activation_in_progress' );
        $wp2l_no_server_response = get_transient('wp2l_no_server_response');
        $wp2l_license_not_active = get_transient( 'wp2l_license_not_active' );
        $wp2l_last_paid_day = get_transient( 'wp2l_last_paid_day' );
        $wp2l_stop_soon_message = get_transient( 'wp2l_payment_missed' );
        $wp2l_plugin_version_status = get_transient('wp2l_plugin_version_status');

        include(dirname(__FILE__) . '/partials/wp2leads-admin-notices.php');
        include(dirname(__FILE__) . '/partials/wp2leads-admin-notices-limit-counter.php');
    }

    /**
     * Adding global admin notices
     *
     * @since    1.0.0
     */
    public function add_admin_map_to_api_bg_notices() {
        // TODO - Use BG Manager
        include(dirname(__FILE__) . '/partials/wp2leads-admin-maptoapi-bg-in-process-notices.php');
    }

    /**
     * Adding custom REST API Endpoint
     *
     * @since    1.0.0
     */
    public function rest_api_init() {
        $controller = new Wp2leads_Rest_Api_Events;
        $controller->register_routes();

        $cron_controller = new Wp2leads_Cron_Rest_Api_Events;
        $cron_controller->register_routes();
    }

    public function set_license() {
        $is_version_status_checked = get_transient('wp2l_version_status_set');
        $wp2l_plugin_version_status = get_transient('wp2l_plugin_version_status');
        $wp2l_plugin_allowed_versions = get_transient('wp2l_plugin_allowed_versions');

        KlickTippManager::set_initial_kt_limitation();
        KlickTippManager::reset_transfer_counter();

        if (
            false === $wp2l_plugin_allowed_versions ||
            false === $wp2l_plugin_allowed_versions ||
            -1 === (int) $wp2l_plugin_version_status ||
            0 === (int) $wp2l_plugin_version_status ||
            !$is_version_status_checked
        ) {
            set_transient('wp2l_version_status_set', 1, 6 * 60 * 60);
            set_transient('wp2l_plugin_allowed_versions', 1, 6 * 60 * 60);
            Wp2leads_License::set_plugin_version_status();
        }

        $wp2l_activation_in_progress = get_transient('wp2l_activation_in_progress');

        if ( $wp2l_activation_in_progress ) {
            $wp2l_activation_in_progress_timeout = get_transient('wp2l_activation_in_progress_timeout');

            if (!$wp2l_activation_in_progress_timeout) {
                Wp2leads_License::complete_license_activation();
                delete_transient('wp2l_activation_in_progress');
            } else {
                return;
            }
        }

        $is_checked = get_transient('wp2l_license_set');

        if (!$is_checked) {
            Wp2leads_License::set_license();
            set_transient('wp2l_license_set', 1, 6 * 60 * 60);
        }
    }

    public function check_map_server_connection() {
        $is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

        if ($is_ajax) {
            return;
        }

        delete_transient('wp2l_no_map_server_response');

        if (!Wp2leads_Catalog::test_server_connection()) {
            set_transient('wp2l_no_map_server_response', 1);
        }
    }

    public function klicktipp_getSpeed() {
        return get_option('wp2l_klicktipp_speed', 50);
    }

    public function update_db_check()
    {
        require_once dirname(dirname(__FILE__)) . '/includes/class-wp2leads-activator.php';
        Wp2leads_Activator::activate();
    }

    public function check_updated_features() {
        $is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

        if ($is_ajax) {
            return;
        }
        // Update correct GMT
        $check_3_0_4_update = get_option('wp2leads_3_0_4_update');

        if (empty($check_3_0_4_update)) {
            require_once dirname(dirname(__FILE__)) . '/includes/class-wp2leads-updates.php';

            Wp2leads_Updates::wp2leads_3_0_4_update();
        }

        // Update correct GMT
        $check_3_0_11_update = get_option('wp2leads_3_0_11_update');

        if (empty($check_3_0_11_update)) {
            require_once dirname(dirname(__FILE__)) . '/includes/class-wp2leads-updates.php';

            Wp2leads_Updates::wp2leads_3_0_11_update();
        }

        // Update correct GMT
        $check_3_0_12_update = get_option('wp2leads_3_0_12_update');

        if (empty($check_3_0_12_update)) {
            require_once dirname(dirname(__FILE__)) . '/includes/class-wp2leads-updates.php';

            Wp2leads_Updates::wp2leads_3_0_12_update();
        }
    }

    public function add_plugin_screen_link($links)
    {
        $links[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=wp2l-admin&tab=catalog')) . '">' . __('Catalog', 'wp2leads') . '</a>';
		$links[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=wp2l-admin&tab=catalog&welcome=1')) . '"> ' . __('Start', 'wp2leads') . '</a>';
        return $links;
    }

    /**
     * Add Admin top level menu
     */
    public function add_menu()
    {
        add_menu_page('WP2LEADS', 'WP2LEADS', 'administrator', 'wp2l-admin', [$this, 'render_settings_page'], '', '84.1');

        add_submenu_page(
            'wp2l-admin',
            __( 'Solution & Support',
                'wp2leads' ),
            __( 'Support', 'wp2leads' ),
            'administrator',
            'wp2leads_support',
            [$this, 'render_support_submenu_page']
        );

        $update_count = '';
        $changelog_version = get_option('wp2leads_changelog_version');
        $current_version = WP2LEADS_VERSION;

        if (!$changelog_version || $changelog_version !== $current_version) {
            $update_count = ' <span class="update-plugins %1$d"><span class="plugin-count">1</span></span>';
        }

        add_submenu_page(
            'wp2l-admin',
            __( 'WP2LEADS Changelog', 'wp2leads' ),
            __( 'Changelog', 'wp2leads' ) . $update_count,
            'administrator',
            'wp2leads_changelog',
            [$this, 'render_changelog_submenu_page']
        );
    }

    public function allowed_mimes($mimes)
    {
        $mimes['json'] = 'application/json';

        return $mimes;
    }

    public function ajax_fetch_all_columns_for_map() {
        $options = array(
            'is_new_map' => $_POST['is_new_map'],
            'map_id' => $_POST['map_id'],
            'new_map' => isset($_POST['new_map']) ? json_decode(stripslashes($_POST['new_map']), true) : null
        );

        $columns = MapsModel::fetch_all_columns_for_map($options);

        $columns = apply_filters('wp2leads_all_columns_for_map', $columns, $_POST['map_id']);

        echo json_encode($columns);
        wp_die();
    }

    public function ajax_fetch_tables()
    {
        echo json_encode(MapBuilderManager::fetch_tables());
        wp_die();
    }

    public function ajax_fetch_column_options()
    {
        $indexes_sent = !empty($_POST['indexes']) ? $_POST['indexes'] : false;

        $indexes = false;

        if ('2' === $indexes_sent) {
            $indexes = true;
        }

        // $indexes = false;
        $columns = $this->fetch_columns_for_table($_POST['table'], $indexes);

        echo json_encode($columns);
        wp_die();
    }

    public function ajax_settigs_klicktipp() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        global $wpdb;

        $klicktipp_username = isset($_POST['klicktipp']['username']) ? $_POST['klicktipp']['username'] : "";
        $klicktipp_password = isset($_POST['klicktipp']['password']) ? $_POST['klicktipp']['password'] : "";
        $klicktipp_speed = isset($_POST['klicktipp']['speed']) ? $_POST['klicktipp']['speed'] : 50;

        update_option('wp2l_klicktipp_username', $klicktipp_username);
        update_option('wp2l_klicktipp_password', $klicktipp_password);
        update_option('wp2l_klicktipp_speed', $klicktipp_speed);

        if (empty($klicktipp_username) && empty($klicktipp_password)) {
            $response = array('success' => 1, 'error' => 0, 'auth' => 1, 'message' => __( 'KlickTipp account disabled!', 'wp2leads' ));
            echo json_encode($response);

            wp_die();
        }

        if (empty($klicktipp_username) || empty($klicktipp_password)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __( 'Fill in username and password, please.', 'wp2leads' ));
            echo json_encode($response);

            wp_die();
        }

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
                $response = array('success' => 1, 'error' => 0, 'auth' => 1, 'message' => __( 'You are on Klick Tipp Standard package with no API access! To connect please upgrade at least to Klick Tipp Premium.', 'wp2leads' ));
                echo json_encode($response);

                wp_die();
            }
        }

        delete_transient('wp2leads_upgrade_kt_package');
        echo json_encode(['error' => 0, 'success' => 1, 'auth' => (int)$logged_in]);

        wp_die();
    }

    public function klicktipp_getLastTransferInformation() {
        return unserialize(get_option('wp2l_klicktipp_transfer', serialize([])));
    }

    public function ajax_detach_from_tag() {
        $email = '';

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
        }

        $response = array(
            'error' => 1,
            'success' => 0,
            'message' => array()
        );

        if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($email)) {
            $email = trim($email);
            $tag_id = trim($_POST['tag_id']);
            $connector = new Wp2leads_KlicktippConnector();
            $logged_in = $connector->login($this->user_name, $this->user_password);

            if($logged_in) {
                $result = $connector->untag($email, $tag_id);

                if ($result) {
                    $response = array(
                        'error' => 0,
                        'success' => 1,
                        'message' => array()
                    );
                }
            }
        }

        echo json_encode($response);
        wp_die();
    }

    public function ajax_debug_fetch_query_for_map()
    {
        $map = $_POST['map'];
        $map['full_selects'] = $this->getSelectionsForQuery($map);
        echo json_encode(['sql' => MapsModel::generate_map_query($map)]);
        wp_die();
    }

    protected function klicktipp_isSubscriber($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'wp2l_klicktipp';

        $map_id = $data['map_id'] ? $data['map_id'] : null;
        $email = $data['email'] ? $data['email'] : null;

        if(is_null($map_id) || is_null($email)) {
            return false;
        }

        $result = $wpdb->query("SELECT * FROM `" . $table . "` WHERE `email` = '" . $email . "' AND `map_id` = '" . (int)$map_id . "'");

        return $result > 0 ? 1 : 0;
    }

    protected function klicktipp_addSubscriber($data) {
        global $wpdb;

        $table = $wpdb->prefix . 'wp2l_klicktipp';

        $map_id = $data['map_id'] ? $data['map_id'] : null;
        $email = $data['email'] ? $data['email'] : null;

        if(is_null($map_id) || is_null($email)) {
            return false;
        }

        $wpdb->query("INSERT IGNORE INTO `" . $table . "` (`email`, `map_id`, `time`) VALUES ('" . $email . "', '" . $map_id . "', '" . date('Y-m-d H:i:s') . "')");

        return true;
    }

    protected function getSelectionsForQuery($map) {
        $selects = array(
            'selects' => array(),
            'v_columns_counter' => 0
        );

        $isSelectsEmpty = false;

        if(count($map['selects'])) {
            foreach ($map['selects'] as $index => $select) {
                if ( !$select ) {
                    unset($map['selects'][$index]);

                    $isSelectsEmpty = true;
                } else {
                    $isSelectsEmpty = false;
                }
            }
        }

        if ($map['selects'] && !$isSelectsEmpty) {
            $v_columns = 0;
            foreach ($map['selects'] as $select) {
                list($table, $column) = explode('.', $select);

                if($table == 'v') {
                    $v_columns++;
                    continue;
                }

                if (!isset($selects['selects'][$table])) {
                    $selects['selects'][$table] = array();
                }

                $selects['selects'][$table][] = $column;
            }

            if (isset($map['joins']) && count($map['joins'])) {
                foreach ($map['joins'] as $join) {
                    if (!array_key_exists($join['joinTable'], $selects['selects'])) {
                        $selects['selects'][$join['joinTable']] = array();
                    }

                    $joining_table_columns = $this->fetch_columns_for_table($join['joinTable']);

                    foreach ($joining_table_columns as $column) {
                        if (!in_array($column, $selects['selects'][$join['joinTable']])) {
                            $selects['selects'][$join['joinTable']][] = $column;
                        }
                    }
                }
            }

            $selects['v_columns_counter'] = $v_columns;
        }

        return $selects;
    }

    protected function fetch_columns_for_table($table, $indexes = false)
    {
        global $wpdb;

        $table = $this->unindexedTableName($table);

        $columns = $wpdb->get_results('DESCRIBE ' . $wpdb->prefix . $table . ';');

        if ($indexes) {
            $columns = array_filter($columns, function($column) {
                if (
                    false !== strpos(strtoupper($column->Type), 'INT') ||
                    false !== strpos(strtoupper($column->Type), 'VARCHAR')
                ) {
                    return true;
                } else {
                    return false;
                }
            });
        }

        return array_values(array_map(function ($item) {
            return $item->Field;
        }, $columns));
    }

    protected function fetch_primary_key_for_table($table)
    {
        global $wpdb;

        $table = $this->unindexedTableName($table);

        $results = $wpdb->get_results('DESCRIBE ' . $wpdb->prefix . $table . ';');

        $onlyPriKey = array_filter($results, function ($item) {
            return $item->Key == "PRI";
        });

        if (count($onlyPriKey)) {
            return $onlyPriKey[0]->Field;
        } else {
            return null;
        }
    }

    protected function fetch_tables()
    {
        global $wpdb;

        return array_map(function ($item) use ($wpdb) {
            return str_replace($wpdb->prefix, '', array_values(get_object_vars($item))[0]);
        }, $wpdb->get_results('SHOW TABLES;'));
    }

    /**
     * @param $map
     * @param int $limit
     * @param int $offset
     *
     * @deprecated 1.2
     *
     * @return string
     */
    protected function generate_map_query($map, $limit = 100, $offset = 0) {
        $this->deprecated_function( __METHOD__, '1.2',
            'MapsModel::generate_map_query' );

        return MapsModel::generate_map_query($map, $limit, $offset);
    }

    /**
     * @param $map
     * @param int $limit
     * @param int $offset
     *
     * @deprecated 1.2
     *
     * @return array|bool|null|object
     */
    public function fetch_map_query_results($map, $limit = 100, $offset = 0) {
        $this->deprecated_function( __METHOD__, '1.2',
            'MapsModel::get_map_query_results' );

        return MapsModel::get_map_query_results($map, $limit, $offset);
    }

    /**
     * @param $function
     * @param $version
     * @param $replacement
     */
    public function deprecated_function( $function, $version, $replacement ) {
        $trigger_error = apply_filters( 'deprecated_function_trigger_error', true );

        if ( WP_DEBUG && $trigger_error ) {
            if ( function_exists( '__' ) ) {
                trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since wp2leads version %2$s! Use %3$s instead.', 'wp2leads' ), $function, $version, $replacement ) );
            } else {
                trigger_error( sprintf( '%1$s is <strong>deprecated</strong> since Contact Form 7 version %2$s! Use %3$s instead.', $function, $version, $replacement ) );
            }
        }
    }

    protected function getRowsCount($map) {
        global $wpdb;
        $map['count'] = true;
        $query = MapsModel::generate_map_query($map, NULL, NULL);

        if (empty($query)) {
            return 0;
        }

        $results = $wpdb->get_results($query);

        return $results;
    }

    protected function checkValue($checking_value) {
        $value = @unserialize($checking_value);

        if (empty($value)) {
            $value = $checking_value;
        } else {
            if (count($value) > 1) {
                $value = json_encode($value);
            } else {
                if (is_array($value)) {
                    foreach ($value as $key => $val) {
                        if ('boolean' === gettype($val)) {
                            $value = $key;
                        } else {
                            if (!is_array($val)) {
                                $value = $val;
                            } else {
                                $value = '';
                            }
                        }
                    }
                } else {
                    $value = '';
                }
            }
        }

        return $value;
    }

    protected function unindexedTableName($table)
    {
        $exploded = explode('-', $table);

        if (1 === count($exploded)) {
            return $table;
        }

        return $exploded[0];
    }

    protected function fetch_maps()
    {
        global $wpdb;
        $table = $this->getTable();

        return array_map(function ($item) {
            return array(
                'id' => $item->id,
                'name' => $item->name,
                'mapping' => $item->mapping,
                'api' => $item->api,
                'info'  => $item->info,
            );
        }, $wpdb->get_results("SELECT * FROM $table"));
    }

    /**
     * @param $map_id
     *
     * @deprecated 1.2
     *
     * @return array
     */
    public function fetch_map($map_id) {
        $this->deprecated_function( __METHOD__, '1.2',
            'MapsModel::get' );

        return MapsModel::get($map_id);
    }

    /**
     * Render Admin settings page
     */
    public function render_settings_page()
    {
        $system_info = SystemHelper::get_system_info();
        $wp2l_plugin_version_status = get_transient('wp2l_plugin_version_status');
        $wp2l_plugin_allowed_versions = get_transient('wp2l_plugin_allowed_versions');

        $current_user_id = get_current_user_id();
        $wp2l_is_dev = get_transient('wp2l_license_level');
        $wp2l_version = Wp2leads_License::get_license_level();
        $version = __('Free', 'wp2leads');

        if ('pro' === $wp2l_version || 'ktcc' === $wp2l_version) {
            $version = __('Professional', 'wp2leads');
        } else if ('essent' === $wp2l_version) {
            $version = __('Essential', 'wp2leads');
        }

        $wp2l_dev_version = Wp2leads_License::get_license_level(true);
        $dev_version = __('Free', 'wp2leads');

        if ('pro' === $wp2l_dev_version) {
            $dev_version = __('Professional', 'wp2leads');
        } else if ('essent' === $wp2l_dev_version) {
            $dev_version = __('Essential', 'wp2leads');
        }

        $transient_map_id = get_transient('wp2l_active_mapping_' . $current_user_id);

        if (empty($_GET[ 'tab' ]) && $transient_map_id) {
            $_GET[ 'active_mapping' ] = $transient_map_id;
        } elseif (empty($_GET[ 'active_mapping' ])) {
            delete_transient('wp2l_active_mapping_' . $current_user_id);
        } else {
            if ( $transient_map_id !== $_GET[ 'active_mapping' ] ) {
                set_transient('wp2l_active_mapping_' . $current_user_id, $_GET[ 'active_mapping' ]);
            }
        }

        $wp2l_current_version = $wp2l_dev_version ? $wp2l_dev_version : $wp2l_version;

        $tables = MapBuilderManager::fetch_tables();
        $columns = [];

        foreach($tables as $table) {
            $columns[$table] = $this->fetch_columns_for_table($table);
        }

        $maps = array_reverse($this->fetch_maps());

        $decodedMap = [];
        $mapForDuplicate = null;
        $activeMap = null;

        if ( isset( $_GET['active_mapping'] ) && empty( MapsModel::get( $_GET['active_mapping'] ) ) ) {
            unset($_GET['active_mapping']);
            delete_transient('wp2l_active_mapping_' . $current_user_id);

        } elseif (isset($_GET['active_mapping']) && $activeMap = MapsModel::get($_GET['active_mapping'])) {
            $decodedMap = unserialize($activeMap->mapping);
            $decodedInfo = unserialize($activeMap->info);

            if ( !empty($decodedMap) ) {
                set_transient('wp2leads_map_' . $_GET['active_mapping'], $activeMap->mapping);
            }

        } elseif ( isset( $_GET['duplicate_mapping'] ) && $mapForDuplicate = MapsModel::get( $_GET['duplicate_mapping'] ) ) {
            $decodedMap = unserialize($mapForDuplicate->mapping);
            $decodedInfo = !empty($activeMap->info) ? unserialize($activeMap->info) : '';
        }

        if (!empty($decodedMap)) {
            if (!empty($decodedMap['excludesFilters'])) {
                $excludesFilters = $decodedMap['excludesFilters'];

                $decodedMap_fetch_all_columns = MapsModel::fetch_all_columns_for_map(array('is_new_map' => true, 'new_map' => $decodedMap));
                $decodedMap_selects = !empty($decodedMap["selects"]) ? $decodedMap["selects"] : array();
                $decodedMap_selects = array_unique(array_merge($decodedMap_selects, $decodedMap_fetch_all_columns));

                $decodedMap_excludes = !empty($decodedMap["excludes"]) ? $decodedMap["excludes"] : array();

                if (!empty($decodedMap_excludes)) {
                    foreach ($decodedMap_excludes as $index => $excluded_column) {
                        foreach ($excludesFilters as $excludes_filter) {
                            if (false !== strpos($excluded_column, $excludes_filter)) {
                                unset($decodedMap_excludes[$index]);
                            }
                        }
                    }

                    $decodedMap["excludes"] = array_values($decodedMap_excludes);
                }

                $decodedMap_selects_only = !empty($decodedMap["selects_only"]) ? $decodedMap["selects_only"] : array();

                if (!empty($decodedMap_selects_only)) {
                    foreach ($decodedMap_selects as $index => $map_column) {
                        foreach ($excludesFilters as $excludes_filter) {
                            if (false !== strpos($map_column, $excludes_filter)) {
                                $decodedMap_selects_only[] = $map_column;
                            }
                        }
                    }

                    $decodedMap_selects_only = array_unique($decodedMap_selects_only);
                    $decodedMap["selects_only"] = array_values($decodedMap_selects_only);
                }
            }
        }

        include(dirname(__FILE__) . '/partials/wp2leads-admin-display.php');
    }
    public function render_support_submenu_page() {

        $lang = get_locale();
        if ( strlen( $lang ) > 0 ) {
            $lang = explode( '_', $lang )[0];
        }
        $support_link = ( $lang == 'en' ) ? 'https://wp2leads.tawk.help/' : 'https://wp2leads.tawk.help/' . $lang;
        include(dirname(__FILE__) . '/partials/wp2leads-admin-support.php');
        return;
    }
    public function render_changelog_submenu_page() {
        $changelog_link = get_admin_url() . 'plugin-install.php?tab=plugin-information&plugin=wp2leads&section=changelog';
        include(dirname(__FILE__) . '/partials/wp2leads-admin-changelog.php');
        return;
    }

    public function contains_valid_json_maps($json)
    {
        foreach($json as $map) {
            if(!property_exists($map, 'name') || !property_exists($map, 'mapping')) {
                return false;
            }
        }
        return true;
    }

    protected function ingest_uploaded_json_maps($json)
    {
        global $wpdb;
        foreach ($json as $map) {
            $wpdb->insert($this->getTable(), [
                'name' => $map->name,
                'mapping' => $map->mapping,
                'time' => $map->time ? $map->time : date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * @param $wpdb
     * @return string
     */
    protected function getTable()
    {
        global $wpdb;
        return $wpdb->prefix . 'wp2l_maps';
    }

    public function add_new_klick_tip_tag() {
        $response = array(
            'status' => 0,
            'message' => __( 'Something went wrong... Please, try again letter.', 'wp2leads' )
        );

        if (!isset($_POST['new_tag'])) {
            wp_send_json($response);
            wp_die();
        }

        $new_tag = trim($_POST['new_tag']);
        $klick_tip_connector = new Wp2leads_KlicktippConnector();
        $login_response = $klick_tip_connector->login();

        if ($login_response) {
            $result = $klick_tip_connector->tag_create($new_tag);

            if ($result) {
                $response['status'] = 1;
                $response['message'] = __( 'New tag with id ', 'wp2leads' ) . $result . __( ' successfully created', 'wp2leads' );
                $response['tag_id'] = $result;
            }
        } else {
            $response['message'] = $klick_tip_connector->get_last_error(false);
            wp_send_json($response);
            wp_die();
        }

        wp_send_json($response);
        wp_die();
    }

    public function remove_klick_tip_tag() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0, 'map_id' => null]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $response = array(
            'status' => 0,
            'message' => __( 'Something went wrong... Please, try again letter.', 'wp2leads' ),
            'deleted_tags' => []
        );

        if (!isset($_POST['tags_ids']) || empty($_POST['tags_ids'])) {
            wp_send_json($response);
            wp_die();
        }

        $tags_ids = $_POST['tags_ids'];

        $klick_tip_connector = new Wp2leads_KlicktippConnector();
        $login_response = $klick_tip_connector->login();

        if ($login_response) {
            foreach ($tags_ids as $tag_id) {
                $result = $klick_tip_connector->tag_delete($tag_id);

                if ($result) {
                    $response['deleted_tags'][] = $tag_id;
                }
            }

            if (!empty($response['deleted_tags'])) {
                $response['status'] = 1;
                $response['message'] = __( 'Deleted tags: ', 'wp2leads' ) . implode(' ', $response['deleted_tags']);
            }
        } else {
            $response['message'] = $klick_tip_connector->get_last_error(false);
            wp_send_json($response);
            wp_die();
        }

        wp_send_json($response);
        wp_die();
    }

    public static function get_site_domain() {
        $site_url = get_site_url();
        $nowww = preg_replace('/www\./i','',$site_url);

        $domain = parse_url($nowww);

        if(!empty($domain["host"])) {
            return $domain["host"];
        } else {
            return $domain["path"];
        }
    }

    // TODO - This is only for DEV purposes need to be removed
    public function set_fake_license_level() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            echo json_encode(['error' => 1, 'success' => 0]);
            wp_die();
        }

        check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
        $old_license_level = Wp2leads_License::get_license_level(true);

        if (isset($_POST['license_level'])) {
            if ('reset' === $_POST['license_level']) {
                delete_transient('wp2l_license_level');

                $new_license_level = 'pro';
            } else {
                set_transient( 'wp2l_license_level', $_POST['license_level'], 12 * HOUR_IN_SECONDS );

                $new_license_level = $_POST['license_level'];
            }

            KlickTippManager::license_changed($old_license_level, $new_license_level);
        }

        $license_level = Wp2leads_License::get_license_level();

        $version = __('Free', 'wp2leads');

        if ('pro' === $license_level) {
            $version = __('Professional', 'wp2leads');
        } else if ('essent' === $license_level) {
            $version = __('Essential', 'wp2leads');
        }

        $result = array(
            'status' => 'success',
            'license_level' => $version
        );

        echo json_encode($result);

        die();
    }

	public function change_crm_settings($status) {
		if ($status == 'activate') {
			$options = get_option('vxcf_leads_meta',array());
			$options['cookies'] = 'yes';
			$options['ip'] = 'yes';
			update_option('vxcf_leads_meta', $options);
		}
	}

    /**
     * Check if any BG process run for not existed map and terminate it
     */
	public function validate_maptoapi_bg_in_process() {
        $is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

        if ($is_ajax) {
            return;
        }

        BackgroundProcessManager::terminate_maptoapi_bg_in_process_for_not_existed_maps();
    }
}
