<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           Wp2leads
 *
 * @wordpress-plugin
 * Plugin Name:       WP2LEADS
 * Plugin URI: https://wp2leads.com/
 * Description:       Transfer user data from nearly all WordPress Plugins to Klick-Tipp
 * Version:           3.3.3
 * Requires at least: 5.0
 * Tested up to: 6.6
 * WC requires at least: 4.0
 * WC tested up to: 9.2
 * Requires at least WooCommerce: 4.0
 * Tested up to WooCommerce: 9.2
 * Requires PHP: 7.0
 * Author: Saleswonder.biz Team
 * Author URI: https://wp2leads.com/
 * Text Domain:       wp2leads
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Freemius to give after the opt-in the best information for connection success
 */
if ( ! function_exists( 'wp2_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wp2_fs() {
        global $wp2_fs;

        if ( ! isset( $wp2_fs ) ) {
            // Activate multisite network integration.
            if ( ! defined( 'WP_FS__PRODUCT_13187_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_13187_MULTISITE', true );
            }

            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $wp2_fs = fs_dynamic_init( array(
                'id'                  					=> '13187',
                'slug'                				=> 'wp2leads',
                'type'               	 			=> 'plugin',
                'public_key'          			=> 'pk_0e8d214bc4493aff601fb01f0e9e6',
                'is_premium'          			=> false,
                'has_premium_version' 	=> false,
                'has_addons'          			=> false,
                'has_paid_plans'      		=> false,
                'menu'                => array(
                    'slug'           => 'wp2l-admin',
                    'first-path'     => 'admin.php?page=wp2l-admin&tab=catalog&welcome=1',
                    'account'        => false,
                    'contact'        => false,
                    'support'        => false,
                ),
            ) );
        }

        return $wp2_fs;
    }

    // Init Freemius.
    wp2_fs();
    // Signal that SDK was initiated.
    do_action( 'wp2_fs_loaded' );
}

    function wp2_fs_custom_connect_message_on_update(
        $message,
        $user_first_name,
        $plugin_title,
        $user_login,
        $site_link,
        $freemius_link
    ) {
        return sprintf(
            __( 'Hey %1$s' ) . ',<br>' .
            __( 'Please help us improve %2$s! If you opt-in, some data about your usage of %2$s will be sent to %5$s. If you skip this, that\'s okay! %2$s will still work just fine.</br></br>On click Skip you loose the free one time offer, with information about connecting WP plugins with KlickTipp the best. </b>My knowledge since 2014. Your usage data will be transferred to KlickTipp and mails are sent from there, you can opt-out anytime.<br><br>Best regards Tobias - To a good connection<br><br>So please klick <strong>Allow & Continue</strong> and we connect us in mails.', 'wp2leads' ),
            $user_first_name,
            '<b>' . $plugin_title . '</b>',
            '<b>' . $user_login . '</b>',
            $site_link,
            $freemius_link
        );
    }

    wp2_fs()->add_filter('connect_message_on_update', 'wp2_fs_custom_connect_message_on_update', 10, 6);

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WP2LEADS_VERSION', '3.3.3' );
define( 'WP2LEADS_BRANCH', 'filter-hooks-for-data-and-columns' );
define( 'WP2LEADS_DEBUG', false);

if ( ! defined( 'WP2LEADS_PLUGIN_FILE' ) ) {
    define( 'WP2LEADS_PLUGIN_FILE', __FILE__ );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp2leads-activator.php
 */
function activate_wp2leads() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp2leads-activator.php';
	Wp2leads_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp2leads-deactivator.php
 */
function deactivate_wp2leads() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp2leads-deactivator.php';
	Wp2leads_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp2leads' );
register_deactivation_hook( __FILE__, 'deactivate_wp2leads' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp2leads.php';

/**
 * Mark as HPOS compatible added 2023.08.08 by Tobias to test if working, need a compatible Connection
 */

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp2leads() {

	$plugin = new Wp2leads();
	$plugin->run();

}
run_wp2leads();


