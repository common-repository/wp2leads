<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp2leads
 * @subpackage Wp2leads/includes
 */
class Wp2leads {

    public static $wp2l_license_check = false;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wp2leads_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WP2LEADS_VERSION' ) ) {
			$this->version = WP2LEADS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wp2leads';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->init_transfer_modules();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wp2leads_Loader. Orchestrates the hooks of the plugin.
	 * - Wp2leads_i18n. Defines internationalization functionality.
	 * - Wp2leads_Admin. Defines all hooks for the admin area.
	 * - Wp2leads_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-license.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-rest-api.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-cron-rest-api.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-klicktipp-connector.php';

        /**
         * Library for setting up Background processes
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-background.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-background-maptoapi-load.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-background-maptoapi-prepare.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-background-maptoapi.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-background-cron-load.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-background-cron-prepare.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-background-cron-transfer.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-background-module-transfer.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-background-tags-create.php';

        /**
         * Library for transfer modules
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-transfer-modules.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/transfer-modules/class-wp2leads-transfer-wp-user-update.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/transfer-modules/class-wp2leads-transfer-woo-order-status-changed.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/transfer-modules/class-wp2leads-transfer-cfentries-insert-entry.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/transfer-modules/class-wp2leads-transfer-ea-new-edit.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/transfer-modules/class-wp2leads-transfer-wp-business-directory.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-i18n.php';

		/**
		 * The class responsible for checking required plugins for the map
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-required-plugins.php';

		/**
		 * The class responsible for opt in processes handlers
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-opt-in-processes.php';

		/**
		 * The class responsible for checking required plugins for the map
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-magic-import.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp2leads-admin-ajax.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp2leads-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp2leads-public.php';

        /**
         * The class responsible for defining all actions for cron.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-admin-cron.php';

		 /**
         * The class to work with wp notices
         */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-notices.php';

		/**
         * The class to work with catalog page
         */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-catalog.php';

		$this->loader = new Wp2leads_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp2leads_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wp2leads_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_i18n, 'load_js_translate_strings' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_i18n, 'load_js_translate_strings' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Wp2leads_Admin($this->get_plugin_name(), $this->get_version());
		$cron_manager = new Wp2LeadsCron;
		$magic_import = new Wp2leads_MagicImport;
		$ajax_handler = new Wp2leads_Admin_Ajax;

        $cron_manager->setScheduleHook();

		$this->loader->add_action('plugins_loaded', $plugin_admin, 'update_db_check');
		$this->loader->add_action('admin_init', $plugin_admin, 'set_license');
		$this->loader->add_action('admin_init', $plugin_admin, 'check_map_server_connection');
        $this->loader->add_action( 'init', $plugin_admin, 'validate_maptoapi_bg_in_process' );
        $this->loader->add_action( 'init', $plugin_admin, 'check_updated_features' );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_global_scripts' );

		if(isset($_GET['page']) && $_GET['page'] == 'wp2l-admin') {
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts', 200 );
        }
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_menu');

		// Add admin bar menu for displaying BG Processes
        //$this->loader->add_action('admin_bar_menu', $plugin_admin, 'admin_bar_menu', 100);
		$this->loader->add_action('admin_notices', $plugin_admin, 'add_admin_notices');
		$this->loader->add_action('admin_notices', $plugin_admin, 'add_admin_map_to_api_bg_notices');

        $this->loader->add_filter('plugin_action_links_' . plugin_basename(dirname(dirname(__FILE__))) . '/wp2leads.php', $plugin_admin, 'add_plugin_screen_link');
        $this->loader->add_filter('upload_mimes', $plugin_admin, 'allowed_mimes');

        // TODO - Old prepare data script
        $this->loader->add_action('wp_ajax_wp2l_prepare_data_for_klicktipp', $ajax_handler, 'ajax_prepare_data_for_klicktipp');
        // TODO: Test Prepare data for KT
        $this->loader->add_action('wp_ajax_wp2l_prepare_data_for_klicktipp_bg', $ajax_handler, 'prepare_data_for_klicktipp_bg');
        $this->loader->add_action('wp_ajax_wp2l_save_map_before_transfer', $ajax_handler, 'save_map_before_transfer');

        $this->loader->add_action('wp_ajax_wp2l_get_transfer_modal_data_info', $ajax_handler, 'get_transfer_modal_data_info');
        $this->loader->add_action('wp_ajax_wp2l_delete_statistic_item', $ajax_handler, 'delete_statistic_item');

        // MAP TO API
        $this->loader->add_action('wp_ajax_wp2l_settings_klick_tip_credentials', $ajax_handler, 'settings_klick_tip_credentials');
        $this->loader->add_action('wp_ajax_wp2l_save_map_to_api_initial_settings', $ajax_handler, 'save_map_to_api_initial_settings');

        $this->loader->add_action('wp_ajax_wp2l_save_cron_settings', $ajax_handler, 'save_cron_status');
        $this->loader->add_action('wp_ajax_wp2l_save_module_settings', $ajax_handler, 'save_module_status');
        $this->loader->add_action('wp_ajax_wp2l_update_api_fields', $ajax_handler, 'update_api_fields');
        $this->loader->add_action('wp_ajax_wp2l_check_limit', $ajax_handler, 'check_limit');
        $this->loader->add_action('wp_ajax_wp2l_change_transfer_module', $ajax_handler, 'change_transfer_module');
        $this->loader->add_action('wp_ajax_wp2l_transfer_data_immediately', $ajax_handler, 'transfer_data_immediately');
        $this->loader->add_action('wp_ajax_wp2l_is_map_transfer_in_bg', $ajax_handler, 'is_map_transfer_in_bg');

        // TODO - New Transfer data to KT
        $this->loader->add_action('wp_ajax_wp2l_transfer_all_data_to_klicktip_bg', $ajax_handler, 'transfer_all_data_to_klicktip_bg');

        // TODO - New Transfer current user data to KT
        $this->loader->add_action('wp_ajax_wp2l_transfer_current_data_to_klicktip_bg', $ajax_handler, 'transfer_current_to_klicktipp');
        $this->loader->add_action('wp_ajax_wp2l_update_existed_tag_fieldset_list', $ajax_handler, 'update_existed_tag_fieldset_list');
        $this->loader->add_action('wp_ajax_wp2l_terminate_map_to_api', $ajax_handler, 'terminate_map_to_api');
        $this->loader->add_action('wp_ajax_wp2l_refresh_all_map_to_api', $ajax_handler, 'refresh_all_map_to_api');
        $this->loader->add_action('wp_ajax_wp2l_get_map_to_api_statistics', $ajax_handler, 'get_map_to_api_statistics');
        $this->loader->add_action('wp_ajax_wp2l_terminate_all_map_to_api', $ajax_handler, 'terminate_all_map_to_api');

        // TODO - Old Transfer data to KT
        $this->loader->add_action('wp_ajax_wp2l_transfer_to_klicktipp', $ajax_handler, 'ajax_transfer_to_klicktipp');

        $this->loader->add_action('wp_ajax_get_another_page_data', $ajax_handler, 'ajax_get_another_page_data');
        $this->loader->add_action('wp_ajax_wp2l_get_available_options_data', $ajax_handler, 'get_available_options_data');
        $this->loader->add_action('wp_ajax_wp2l_get_subscriber_tags_from_klicktipp', $ajax_handler, 'ajax_get_subscriber_tags_from_klicktipp');
        $this->loader->add_action('wp_ajax_wp2l_load_possible_tags_cloud', $ajax_handler, 'load_possible_tags_cloud');
        $this->loader->add_action('wp_ajax_wp2l_save_new_map', $ajax_handler, 'ajax_save_new_map');
        $this->loader->add_action('wp_ajax_wp2l_map_delete', $ajax_handler, 'ajax_map_delete');
        $this->loader->add_action('wp_ajax_wp2l_fetch_map_query_results', $ajax_handler, 'ajax_fetch_map_query_results');
        $this->loader->add_action('wp_ajax_wp2l_get_map_query_results_by_map_id', $ajax_handler, 'get_map_query_results_by_map_id');
        $this->loader->add_action('wp_ajax_wp2l_get_map_query_results_by_map_id_limited', $ajax_handler, 'get_map_query_results_by_map_id_limited');
        $this->loader->add_action('wp_ajax_wp2l_get_mapping', $ajax_handler, 'ajax_get_mapping');
        $this->loader->add_action('wp_ajax_wp2l_maps_actions_run', $ajax_handler, 'maps_actions_run');
        $this->loader->add_action('wp_ajax_wp2l_maps_import_from_remote', $ajax_handler, 'import_from_remote');
        $this->loader->add_action('wp_ajax_wp2l_get_map_rows_count', $ajax_handler, 'get_map_rows_count');
        $this->loader->add_action('wp_ajax_wp2l_get_map_query_results_limit', $ajax_handler, 'get_map_query_results_limit');

        $this->loader->add_action('wp_ajax_wp2l_save_policy_confirmed', $ajax_handler, 'save_policy_confirmed');
        $this->loader->add_action('wp_ajax_wp2l_export_maps', $ajax_handler, 'export_maps');
        $this->loader->add_action('wp_ajax_wp2l_import_maps', $ajax_handler, 'import_maps');
		$this->loader->add_action('wp_ajax_wp2l_magic_import', $ajax_handler, 'magic_import');
		$this->loader->add_action('wp_ajax_wp2l_magic_import_step2', $ajax_handler, 'magic_import_step2');
        $this->loader->add_action('wp_ajax_wp2l_import_pending_maps', $ajax_handler, 'import_pending_maps');
		$this->loader->add_action('wp_ajax_wp2l_get_map_plugins', $ajax_handler, 'get_map_plugins');
		$this->loader->add_action('wp_ajax_wp2l_check_plugin_by_slug', $ajax_handler, 'check_plugin_by_slug');
		$this->loader->add_action('wp_ajax_wp2l_install_map_to_api_plugins', $ajax_handler, 'install_map_to_api_plugins');
		$this->loader->add_action('wp_ajax_wp2l_update_magic_content', $ajax_handler, 'update_magic_content');
		$this->loader->add_action('wp_ajax_wp2l_get_edit_replacements_popup', $ajax_handler, 'get_edit_replacements_popup');
		$this->loader->add_action('wp_ajax_wp2l_delete_selected_statistics', $ajax_handler, 'delete_selected_statistics');


        $this->loader->add_action('wp_ajax_wp2l_license_activation', $ajax_handler, 'ajax_license_activation');
        $this->loader->add_action('wp_ajax_wp2l_license_updation', $ajax_handler, 'ajax_license_updation');
        $this->loader->add_action('wp_ajax_wp2l_license_login', $ajax_handler, 'ajax_license_login');
        $this->loader->add_action('wp_ajax_wp2l_license_get_key', $ajax_handler, 'ajax_license_get_key');
        $this->loader->add_action('wp_ajax_wp2l_modal_license_activation', $ajax_handler, 'ajax_modal_license_activation');
        $this->loader->add_action('wp_ajax_wp2l_license_deactivation', $ajax_handler, 'ajax_license_deactivation');
        $this->loader->add_action('wp_ajax_wp2l_license_validate-ktcc', $ajax_handler, 'ajax_license_validate_ktcc');
        $this->loader->add_action('wp_ajax_wp2l_license_removing', $ajax_handler, 'ajax_license_removing');
        $this->loader->add_action('wp_ajax_wp2l_complete_activation', $ajax_handler, 'ajax_complete_activation');

        $this->loader->add_action('wp_ajax_wp2l_set_transient', $ajax_handler, 'set_transient');
        $this->loader->add_action('wp_ajax_wp2l_get_transient', $ajax_handler, 'get_transient');
        $this->loader->add_action('wp_ajax_wp2l_delete_transient', $ajax_handler, 'delete_transient');

        $this->loader->add_action('wp_ajax_wp2l_save_results_to_transient', $ajax_handler, 'save_results_to_transient');

        // Multisearch
        $this->loader->add_action('wp_ajax_wp2l_global_multisearch_results', $ajax_handler, 'ajax_get_global_multisearch_results');
        $this->loader->add_action('wp_ajax_wp2l_global_table_search_results', $ajax_handler, 'ajax_get_global_table_search_results');
        $this->loader->add_action('wp_ajax_wp2l_single_multisearch_table', $ajax_handler, 'ajax_get_single_multisearch_table');

        $this->loader->add_action('wp_ajax_wp2l_fetch_tables', $plugin_admin, 'ajax_fetch_tables');
        $this->loader->add_action('wp_ajax_wp2l_fetch_all_columns_for_map', $plugin_admin, 'ajax_fetch_all_columns_for_map');
        $this->loader->add_action('wp_ajax_wp2l_get_all_columns_for_recomended_tags', $ajax_handler, 'get_all_columns_for_recomended_tags');
        $this->loader->add_action('wp_ajax_wp2l_get_recomended_tags_result', $ajax_handler, 'get_recomended_tags_result');
        $this->loader->add_action('wp_ajax_wp2l_get_recomended_tags_filter', $ajax_handler, 'get_recomended_tags_result');
        $this->loader->add_action('wp_ajax_wp2l_fetch_column_options', $plugin_admin, 'ajax_fetch_column_options');
        $this->loader->add_action('wp_ajax_wp2l_debug_fetch_query_for_map', $plugin_admin, 'ajax_debug_fetch_query_for_map');

        $this->loader->add_action('wp_ajax_wp2l_detach_from_tag', $plugin_admin, 'ajax_detach_from_tag');
        $this->loader->add_action('wp_ajax_wp2l_klicktipp_get_speed', $plugin_admin, 'klicktipp_getSpeed');
        $this->loader->add_action('wp_ajax_wp2l_settings_klicktipp', $plugin_admin, 'ajax_settigs_klicktipp');
        $this->loader->add_action('wp_ajax_add_new_klick_tip_tag', $plugin_admin, 'add_new_klick_tip_tag');
        $this->loader->add_action('wp_ajax_wp2l_add_recommended_klick_tip_tags', $ajax_handler, 'add_recommended_klick_tip_tags');
        $this->loader->add_action('wp_ajax_wp2l_add_all_recommended_klick_tip_tags', $ajax_handler, 'add_all_recommended_klick_tip_tags');
        $this->loader->add_action('wp_ajax_remove_klick_tip_tag', $plugin_admin, 'remove_klick_tip_tag');

        $this->loader->add_action('wp2lead_after_license_server_response', $plugin_admin, 'delete_server_status_messages', 20, 2);
        $this->loader->add_action('wp2lead_after_license_server_response', $plugin_admin, 'set_server_status_messages', 20, 2);

        // Extend rest api
        $this->loader->add_action('rest_api_init', $plugin_admin, 'rest_api_init');

        // TODO - This is only for DEV purposes need to be removed
        $this->loader->add_action('wp_ajax_wp2l_set_fake_license_level', $plugin_admin, 'set_fake_license_level');

		// change default options to make allow gdpr
		$this->loader->add_action('plugin_status_vxcf_form', $plugin_admin, 'change_crm_settings');

		// update magic forms
		$this->loader->add_action('wpcf7_after_save', $magic_import, 'update_maps_after_form_update');
		$this->loader->add_filter('wp2leads_cf_label_filter', $magic_import, 'filter_contact_form_label', 10, 4);
		$this->loader->add_filter('cf7s_visual_update_js_callbacks', $magic_import, 'add_wp7_skins_callback');
		$this->loader->add_action('wp_ajax_wp2l_export_form_template', 'Wp2leads_Catalog', 'ajax_export_form_template');
		$this->loader->add_action('wp_ajax_wp2l_get_catalog_items', 'Wp2leads_Catalog', 'ajax_get_catalog_items');
		$this->loader->add_action('wp_ajax_wp2l_get_magic_steps_for_map', 'Wp2leads_Catalog', 'ajax_get_magic_steps_for_map');
		$this->loader->add_action('wp_ajax_wp2l_make_magic_step', 'Wp2leads_Catalog', 'ajax_make_magic_step');

		// notices
		$this->loader->add_action('admin_notices', 'Wp2leads_Notices', 'show_notices');
		$this->loader->add_action('wp_ajax_wp2l_dismiss_notice', 'Wp2leads_Notices', 'dismiss_notice');
		$this->loader->add_action('wp_ajax_wp2l_dismiss_all_notices', 'Wp2leads_Notices', 'dismiss_all_notices');

		// other
		$this->loader->add_action('wp_ajax_wp2l_update_imported_campaings', $ajax_handler, 'update_imported_campaings');

		// filter DB Entries results
		$this->loader->add_filter('wp2l_excluded_columns', 'ApiHelper', 'exclude_cf7_wrong_table_columns', 10, 3);

		// install CRM function
		$this->loader->add_action('wp_ajax_wp2l_activate_crm', new Wp2leads_RequiredPlugins(), 'install_crm_plugin', 10, 3);
//        add_shortcode('wp2l-klicktipp-speed', ['Wp2leads_Admin', 'klicktipp_getSpeed']);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wp2leads_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $this, 'init_transfer_modules' );
		$this->loader->add_action( 'init', $plugin_public, 'process_kt_invite' );
		$this->loader->add_action( 'wp_headers', $plugin_public, 'replace_kt_invite_headers' );
	}

	public function init_transfer_modules() {
        $transfer_modules = Wp2leads_Transfer_Modules::get_transfer_modules_class_names();

        foreach ($transfer_modules as $slug => $transfer_module) {
            $class_name = $transfer_module;

            $class_name::transfer_init();
        }
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wp2leads_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
