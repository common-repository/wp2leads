<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 06.11.18
 * Time: 20:13
 */

$maps_prepare_in_progress = BackgroundProcessManager::get_prepare_bg_in_process();
$maps_load_in_progress = BackgroundProcessManager::get_load_bg_in_process();
$wp2l_map_to_api_in_progress = BackgroundProcessManager::get_transient( 'wp2leads_maptoapi_bg_in_process' );

if (!empty($wp2l_map_to_api_in_progress) || !empty($maps_load_in_progress) || !empty($maps_prepare_in_progress)) {
    ?>
    <div id="wp2lead-map-to-api-bg-notice" class="notice notice-info wp2lead-notice notice-can-disable api-processing-holder">
        <div id="map-to-api-bg-running-inner">
            <?php include plugin_dir_path(  WP2LEADS_PLUGIN_FILE ) . 'admin/partials/ajax/running-background-processes.php'; ?>
        </div>

        <div id="map-to-api-bg-running-buttons" style="padding-bottom: 10px">
            <button id="refresh-all-bg-map-to-api" class="button button-primary"><?php _e('Refresh', 'wp2leads') ?></button>
            <button id="terminate-all-bg-map-to-api" class="button button-danger"><?php _e('Stop all transfers', 'wp2leads') ?></button>
        </div>
    </div>
    <?php
}