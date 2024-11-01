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
class Wp2leads_Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        global $wpdb;
        $installed_version = get_option('wp2l_db_version');
        $current_version = WP2LEADS_VERSION;

        if ($installed_version != $current_version) {
            require_once plugin_dir_path( __FILE__ ) . 'library/StatisticsModel.php';
            require_once plugin_dir_path( __FILE__ ) . 'library/FailedTransferModel.php';
			require_once plugin_dir_path( __FILE__ ) . 'library/MetaModel.php';
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $table_name = $wpdb->prefix . 'wp2l_maps';
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    name tinytext NOT NULL,
                    mapping longtext NOT NULL,
                    api longtext NOT NULL,
                    info longtext NOT NULL,
                    PRIMARY KEY (id)
                    ) $charset_collate;";

            dbDelta($sql);

            StatisticsModel::createTableSchema();

            FailedTransferModel::createTableSchema();
			
			Wp2leads_Notices::createTableSchema();
			
			MetaModel::createTableSchema();

            update_option('wp2l_db_version', WP2LEADS_VERSION);
        }
    }

}
