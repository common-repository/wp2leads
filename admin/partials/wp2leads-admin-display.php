<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/admin/partials
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h1>
        <?php _e('WP2LEADS', 'wp2leads') ?> <?php echo $version ?>
        <?php
        if (Wp2leads_License::is_dev_allowed() && $wp2l_is_dev) {
            ?>(<?php _e('Dev mode', 'wp2leads') ?>: <?php echo $dev_version; ?>)<?php
        }
        ?>
    </h1>

    <?php settings_errors(); ?>

    <?php
    $default_tab = 'catalog';
    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : $default_tab;
    $active_mapping = isset( $_GET[ 'active_mapping' ] ) ? '&active_mapping=' . $_GET[ 'active_mapping' ] : '';
	
	if ( in_array( $active_tab, array( 'map_runner', 'map_port', 'map_builder', 'tools', 'settings' ) ) ) {
		$is_active_tab_in_advanced = true;
	} else {
		$is_active_tab_in_advanced = false;
	}
    ?>
	<div class="wp2l-nav-tabs wp2l-tabs-active-<?php echo $active_tab; ?>">
		<h2 class="nav-tab-wrapper <?php echo $is_active_tab_in_advanced ? 'wp2l-advanced-active' : 'wp2l-advanced-non-active'; ?>">
			<a
					href="?page=wp2l-admin&tab=catalog<?php echo $active_mapping?>"
					class="nav-tab <?php echo $active_tab == 'catalog' ? 'nav-tab-active' : ''; ?>"
			>
				<?php _e('Catalog', 'wp2leads'); ?>
			</a>
			<a
					href="?page=wp2l-admin&tab=map_to_api<?php echo $active_mapping?>"
					class="nav-tab <?php echo $active_tab == 'map_to_api' ? 'nav-tab-active' : ''; ?>"
			>
				<?php _e('Map to API', 'wp2leads') ?>
			</a>
			<a
					href="?page=wp2l-admin&tab=statistics<?php echo $active_mapping?>"
					class="nav-tab <?php echo $active_tab == 'statistics' ? 'nav-tab-active' : ''; ?>"
			>
				<?php _e('Statistics', 'wp2leads') ?>
			</a>
			<a
					href="?page=wp2l-admin&tab=map_runner<?php echo $active_mapping?>"
					class="nav-tab <?php echo $active_tab == 'map_runner' ? 'nav-tab-active' : ''; ?> wp2l-advanced-tab"
			>
				<?php _e('Map Runner', 'wp2leads') ?>
			</a>
			 <a
					href="?page=wp2l-admin&tab=map_port<?php echo $active_mapping?>"
					class="nav-tab <?php echo $active_tab == 'map_port' ? 'nav-tab-active' : ''; ?> wp2l-advanced-tab"
			>
				<?php _e('Maps', 'wp2leads') ?>
			</a>
			<a
					href="?page=wp2l-admin&tab=map_builder<?php echo $active_mapping?>"
					class="nav-tab <?php echo $active_tab == 'map_builder' ? 'nav-tab-active' : ''; ?> wp2l-advanced-tab"
			>
				<?php _e('Map Builder', 'wp2leads') ?>
			</a>
			<a
					href="?page=wp2l-admin&tab=tools<?php echo $active_mapping?>"
					class="nav-tab <?php echo $active_tab == 'tools' ? 'nav-tab-active' : ''; ?> wp2l-advanced-tab"
			>
				<?php _e('Tools', 'wp2leads') ?>
			</a>
			<a
					href="?page=wp2l-admin&tab=settings<?php echo $active_mapping?>"
					class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?> wp2l-advanced-tab"
			>
				<?php _e('Settings', 'wp2leads') ?>
			</a>
			<a
					href="#"
					class="nav-tab  wp2l-advanced-tab-switcher"
			>
				<?php _e('Advanced', 'wp2leads') ?>
			</a>
			
			<button id="btnShowGlobalMapList" class="button button-primary"
					data-open-text="<?php echo __( 'Show map list', 'wp2leads' ); ?>"
					data-close-text="<?php echo __( 'Hide map list', 'wp2leads' ); ?>"
			>
			   <?php echo __( 'Show map list', 'wp2leads' ); ?>
			</button>
			
		</h2>
		<div id="globalMapsList">
			<?php require_once dirname( __FILE__ ) . '/wp2leads-admin-runner-map-list.php'; ?>
		</div>
	</div>
	
    <?php
    switch($active_tab) {
        case "map_builder":
            if ( !empty( $_GET['active_mapping'] ) && !empty($decodedMap) ) {
                require_once dirname(__FILE__) . '/wp2leads-admin-map_builder-edit.php';
                require_once dirname(__FILE__) . '/wp2leads-admin-handlebars-templates-mapbuilder.php';
                break;
            }

            if ( !empty( $_GET['duplicate_mapping'] ) ) {
                require_once dirname(__FILE__) . '/wp2leads-admin-map_builder-duplicate.php';
                require_once dirname(__FILE__) . '/wp2leads-admin-handlebars-templates-mapbuilder.php';
                break;
            }

            require_once dirname(__FILE__) . '/wp2leads-admin-map_builder-new.php';
            require_once dirname(__FILE__) . '/wp2leads-admin-handlebars-templates-mapbuilder.php';

            break;

        case "map_to_api":
            require_once dirname(__FILE__) . '/wp2leads-admin-map_to_api.php';
            require_once dirname(__FILE__) . '/wp2leads-admin-handlebars-templates-map_to_api.php';
            break;

        case "tools":
            require_once dirname(__FILE__) . '/wp2leads-admin-tools.php';
            break;

        case "statistics":
            require_once dirname(__FILE__) . '/wp2leads-admin-statistics.php';
            break;

        case "map_port":
            require_once dirname(__FILE__) . '/wp2leads-admin-map_port.php';
            break;

        case "settings":
            require_once dirname(__FILE__) . '/wp2leads-admin-settings.php';
            break;

        case "test":
            require_once dirname(__FILE__) . '/wp2leads-admin-test.php';
            break;
			
		case "catalog":
            require_once dirname(__FILE__) . '/wp2leads-admin-catalog.php';
            break;

        case "map_runner":
        default:
            require_once dirname(__FILE__) . '/wp2leads-admin-map_runner.php';
            break;
    }
    ?>
</div>
<?php
require_once dirname(__FILE__) .'/wp2leads-admin-handlebars-templates.php';
?>