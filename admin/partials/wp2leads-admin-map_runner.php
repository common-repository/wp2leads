<?php
/**
 * Map Runner Page
 *
 * @package Wp2Leads/Partials
 * @version 1.0.1.7
 * @var $activeMap
 * @var $decodedMap
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ($activeMap) {
    if ( $decodedMap ) {
        $tablesToCheck = array();
        array_push($tablesToCheck, $decodedMap['from']);

        if ( !empty( $decodedMap['joins'] ) ) {
            foreach ( $decodedMap['joins'] as $join ) {
                array_push( $tablesToCheck, $join['joinTable'] );
            }
        }

        // compare existing tables against the map tables
        $mapValid = true;

        foreach ($tablesToCheck as $checkMe) {
            if ( !in_array( MapsModel::unindexed_table_name($checkMe), $tables ) ) {
                $mapValid = false;
                break;
            }
        }
    }
}
?>

<div style="display: inline; float: right; margin-top: 10px;">
    <?php _e( 'Limit results to:', 'wp2leads' ) ?>
    <input type="number" name="limit" id="map-sample-results-limit-mr" value="50" class="small-text">
    <button id="update-result-limit-mr" class="button button-secondary"><?php echo __( 'Run', 'wp2leads' ); ?></button>
</div>

<h2><?php _e( 'Map Runner', 'wp2leads' ) ?></h2>

<hr>

<div id="mapRunnerContainer" class="map-runner">
    <?php if ( ! $activeMap ): ?>
        <div id="map-runner__container">
            <div id="map-runner__body">
                <div id="map-runner__results" class="results-holder">
                    <?php require_once dirname( __FILE__ ) . '/wp2leads-admin-select-map.php'; ?>
                </div>

                <div id="map-runner__map-list" class="active">
                    <?php require_once dirname( __FILE__ ) . '/wp2leads-admin-runner-map-list.php'; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php
        $cron_maps = Wp2LeadsCron::getScheduledMaps();

        if ( empty( $cron_maps[ 'map_' . $activeMap->id ] ) || (
                !empty($cron_maps[ 'map_' . $activeMap->id ]["status_to_change"]) &&
                'remove_cron_schedule' === $cron_maps[ 'map_' . $activeMap->id ]["status_to_change"]
            ) ) {
            $cron_status = '';
            $cron_title  = __( 'Cron not set up', 'wp2leads' );
        } else {
            $cron_status = ' disabled';
            $cron_title  = __( 'Cron disabled', 'wp2leads' );

            if ( ! empty( $cron_maps[ 'map_' . $activeMap->id ]['status'] ) ) {
                $cron_status = ' active';
                $cron_title  = __( 'Cron enabled', 'wp2leads' );
            }

            if (!empty($cron_maps[ 'map_' . $activeMap->id ]["status_to_change"])) {
                if ('disable_cron_schedule' === $cron_maps[ 'map_' . $activeMap->id ]["status_to_change"]) {
                    $cron_status = ' disabled';
                    $cron_title = __('Cron disabled', 'wp2leads');
                }
            }
        }

        if ( ! Wp2leads_License::is_map_transfer_allowed( $activeMap->id ) ) {
            $cron_status = '';
        }
        ?>
        <div id="map-runner__container" data-map-id="<?php echo $activeMap->id ?>">
            <div id="map-runner__header">
                <h3 class="title">
                    <?php echo MapBuilderManager::get_clock_icon_for_map($activeMap->id); ?>
                    <?php echo stripslashes( $activeMap->name ) ?>
                </h3>

                <div class="buttons-holder">
                    <?php
                    if ($mapValid) {
                        ?>
                        <span style="display:inline-block;line-height:28px;margin:0 5px 0 0;">
                            <strong><?php _e( 'Next step', 'wp2leads' ) ?>:</strong>
                        </span>
                        <a href="?page=wp2l-admin&tab=map_to_api&active_mapping=<?php echo $activeMap->id ?>" class="button button-success">
                            <?php _e('Map to API', 'wp2leads') ?>
                        </a> <span style="display:inline-block;line-height:28px;margin:0 10px;"><?php _e( 'or', 'wp2leads' ) ?></span>
                        <?php
                    }
                    ?>

                    <a href="?page=wp2l-admin&tab=map_runner" class="button button-primary">
                        <?php _e( 'Exit Map', 'wp2leads' ) ?>
                    </a>
                </div>
            </div>

            <div id="map-runner__body">
                <div id="map-runner__map-list">
                    <?php require_once dirname( __FILE__ ) . '/wp2leads-admin-runner-map-list.php'; ?>
                </div>

                <?php
                if ($mapValid) {
                    ?>
                    <div id="map-runner__results" class="results-holder api-processing-holder" data-key-by="<?php echo $decodedMap['keyBy'] ?>">
                        <div id="wp2l-results-preview-wrap">
                            <div id="no-results">
                                <p>
                                    <?php _e( 'Result loading.', 'wp2leads' ) ?>
                                </p>
                            </div>
                        </div>

                        <div class="api-spinner-holder api-processing">
                            <div class="api-spinner"></div>
                        </div>
                    </div>
                    <?php
                } else {
                    ?>
                        <div class="notice notice-error inline" style="margin-top:0;margin-bottom:0;">
                            <p><?php _e('This saved map refers to a table no longer present in the database.', 'wp2leads') ?></p>
                        </div>
                    <?php
                }
                ?>
            </div>

        </div>
    <?php endif; ?>
</div>