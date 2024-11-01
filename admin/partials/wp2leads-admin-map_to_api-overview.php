<?php
/**
 * Overview Section
 *
 * @package Wp2Leads/Partials/MapToAPI
 * @version 1.0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @var $activeMapId
 * @var $is_initial_settings_done
 * @var $is_transfer_allowed
 * @var $module_label
 * @var $module_description
 * @var $module_enabled
 * @var $cron_available
 * @var $cron_active
 * @var $module_active
 * @var $module_available
 */
$wp2l_is_cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
$totally_transfered = StatisticsManager::getTotallyTransferedData($activeMapId);
$last_transfered = '';
$last_transfered_cron = '';

if ($totally_transfered['time']) {
    $last_transfered = StatisticsManager::convertTimeToLocal($totally_transfered['time']);
}

if ($totally_transfered['crontime']) {
    $last_transfered_cron = StatisticsManager::convertTimeToLocal($totally_transfered['crontime']);
}
?>

<h3 class="accordion-header<?php echo $is_initial_settings_done ? ' active' : ' disabled'; ?>"><?php _e( 'Overview', 'wp2leads' ) ?></h3>

<div class="accordion-body <?php echo $is_initial_settings_done ? 'accordion-body-visible' : ''; ?> api-processing-holder">
    <h3 class="accordion-subheader"><?php _e( 'Active Opt-In Process:', 'wp2leads' ) ?></h3>

    <div id="active-optin-holder"
         class="active-optin accordion-subbody">
        <div class="active-optin-wrapper"></div>
    </div>

    <h3 class="accordion-subheader">
        <?php _e( 'Already exiting Tags for this E-Mail Address (received from Klick-Tipp):', 'wp2leads' ) ?>
    </h3>

    <div id="existed-tags-holder" class="accordion-subbody">
        <div class="existed-tags-wrapper"></div>
        <hr>
        <div class="existed-tags-info">
            <p style="margin-top:0;margin-bottom:0;">
                <small><?php _e( 'Click on x near the tag, to untag the tag for this user instantly in Klick-Tipp', 'wp2leads' ) ?></small>
            </p>
        </div>
    </div>

    <h3 class="accordion-subheader" style="display: none">
        <?php _e( 'Possible Tags from current set of data:', 'wp2leads' ) ?>
    </h3>

    <div id="selected-tags-holder" class="accordion-subbody" style="display: none">
        <div class="selected-tags-wrapper"></div>
    </div>

    <h3 class="accordion-subheader">
        <?php _e( 'Possible Tags from current set of data:', 'wp2leads' ) ?>
    </h3>

    <div id="selected-tags-holder" class="accordion-subbody">
        <div id="selectedTagsCloudHolder" class="selected-tags-cloud-wrapper"></div>
        <hr>
        <div class="selected-tags-cloud-legend">
            <h4 style="margin-top:0;margin-bottom:10px;"><?php _e( 'Legend:', 'wp2leads' ) ?></h4>
            <p>
                <span class="selected-tag-kt-legend"><?php _e( 'Tag on KT', 'wp2leads' ) ?></span>
                - <?php _e( 'Tag already on Klick Tipp', 'wp2leads' ) ?>
            </p>

            <p>
                <span class="selected-tag-kt-manual"><?php _e( 'Map Tag on KT', 'wp2leads' ) ?></span>
                - <?php _e( 'Tag already on Klick Tipp and in current map settings', 'wp2leads' ) ?>
            </p>

            <p>
                <span class="selected-tag-kt-added"><?php _e( 'Not Attached Tag', 'wp2leads' ) ?></span>
                - <?php _e( 'Tag on Klick Tipp, but not attached to current user and will be added after transfering data', 'wp2leads' ) ?>
            </p>

            <p>
                <span class="selected-tag-kt-new"><?php _e( 'New Tag', 'wp2leads' ) ?></span>
                - <?php _e( 'Tag not on Klick Tipp, but will be added after transfering data', 'wp2leads' ) ?>
            </p>

            <p>
                <span class="selected-tag-kt-detach"><?php _e( 'Tag to Detach', 'wp2leads' ) ?></span>
                - <?php _e( 'Tag already on Klick Tipp but will be detached after transfering data', 'wp2leads' ) ?>
            </p>
        </div>
    </div>

    <?php
    if (!empty($decodedMap['dateTime']) && is_array($decodedMap['dateTime'])) {
        ?>
        <h3 class="accordion-subheader">
            <?php _e( 'Initial transfer date range:', 'wp2leads' ) ?>
        </h3>

        <div class="accordion-subbody">
            <div id="tagPrefixesContainer">
                <div id="globalTagPrefixContainer">
                    <p class="globalTagPrefix__holder" style="margin-top:0;">
                        <label>
                            <?php echo __( 'Start Date', 'wp2leads' ); ?>
                            <?php
                            if ($start_date_data) {
                                ?><span class="dashicons dashicons-edit settings-change"
                                        data-change="startDateData"></span><?php
                            }
                            ?>
                        </label>

                    <div class="">
                        <input id="startDateData" type="text"
                               value="<?php echo $start_date_data ? $start_date_data : '' ?>"
                            <?php echo $start_date_data ? ' disabled' : '' ?>
                               class="wp2lead-datepicker form-control form-control-medium<?php echo $start_date_data ? ' disabled' : '' ?>">
                    </div>
                    </p>
                </div>

                <div id="mapTagPrefixContainer">
                    <p id="mapTagPrefix__holder" style="margin-top:0;">
                        <label>
                            <?php echo __( 'End Date', 'wp2leads' ); ?>
                            <?php
                            if ($end_date_data) {
                                ?><span class="dashicons dashicons-edit settings-change" data-change="endDateData"></span><?php
                            }
                            ?>
                        </label>
                    <div class="">
                        <input id="endDateData" type="text"
                               value="<?php echo $end_date_data ? $end_date_data : '' ?>"
                            <?php echo $end_date_data ? ' disabled' : '' ?>
                               class="wp2lead-datepicker form-control form-control-medium<?php echo $end_date_data ? ' disabled' : '' ?>">

                    </div>
                    </p>
                </div>
            </div>
			<p style="margin-top:0;margin-bottom:0;display:none;" id="transferDataRangeButtons">
				<button id="btnTransferDataCurrentWithRange" class="button button-primary button-green">
                    <?php echo __( 'Apply date range', 'wp2leads' ); ?>
                </button>
				<button id="btnTransferDataCurrentWithoutRange" class="button">
                    <?php echo __( 'Skip filter', 'wp2leads' ); ?>
                </button>
			</p>
            <p style="margin-top:0;margin-bottom:0;">
                <span><small><i><?php echo __( 'Both fields are optional', 'wp2leads' ); ?></i></small></span>
            </p>
        </div>
        <?php
    }

    if ($cron_available) {
        if (!$cron_active && !$module_active) {
            include 'wp2leads-admin-map_to_api-auto-cron-settings.php';
        }
    }

    if ($module_available) {
        if (!$module_active && !$cron_active) {
            include 'wp2leads-admin-map_to_api-auto-module-settings.php';
        }

    }
    ?>

    <div id="transfer-btn-holder" class="accordion-subbody">
        <?php
        $is_map_transfer_in_bg = KlickTippManager::is_map_transfer_in_bg($activeMapId);

        if (!$is_transfer_allowed) {
            $kt_limitation = KlickTippManager::get_initial_kt_limitation();

            if (!$kt_limitation) {
                ?>
                <button id="btnTransferDataCurrent" class="button disabled" disabled="disabled">
                    <?php echo __( 'Transfer current', 'wp2leads' ); ?>
                </button>

                <button id="btnTransferDataImmediately" class="button disabled" disabled="disabled">
                    <?php echo __( 'Transfer all immediately', 'wp2leads' ); ?>
                </button>

                <button id="btnTransferDataCalculate" class="button disabled" disabled="disabled">
                    <?php echo __( 'Calculate and Transfer all', 'wp2leads' ); ?>
                </button>
                <?php
            } else {
                $kt_limit_users = $kt_limitation['limit_users'];
                $kt_limit_message = $kt_limitation['limit_message'];
                $kt_limit_days = $kt_limitation['limit_days'];
                $kt_counter = KlickTippManager::get_transfer_counter();

                if (!$kt_counter) {
                    ?>
                    <div id="kt_limit_notice_holder">
                        <div class="notice notice-warning inline">
                            <p style="margin-top:0;margin-bottom:0;">
                                <?php echo sprintf( __( 'You have <strong>%s</strong> users to transfer out of your <strong>Pro Version</strong> limit for <strong>%s</strong> users per <strong>%s</strong> days.', 'wp2leads' ), $kt_limit_users, $kt_limit_users, $kt_limit_days ); ?>
                            </p>
                        </div>
                    </div>
                    <button id="btnTransferDataCurrent" class="button button-primary">
                        <?php echo __( 'Transfer current', 'wp2leads' ); ?>
                    </button>

                    <button id="btnTransferDataImmediately" class="button <?php echo $is_map_transfer_in_bg || $wp2l_is_cron_disabled ? 'disabled' : 'button-primary' ?>"<?php echo $is_map_transfer_in_bg || $wp2l_is_cron_disabled ? ' disabled="disabled"' : '' ?>>
                        <?php echo __( 'Transfer all immediately', 'wp2leads' ); ?>
                    </button>
                    <?php

                    if ($is_map_transfer_in_bg) {
                        ?>
                        <p class="warning-text">
                            <?php echo __( 'Background transfer not available right now for this map as far as another process running.', 'wp2leads' ); ?>
                        </p>
                        <?php

                    }
                } else {
                    $kt_limit_counter = $kt_counter['limit_counter'];
                    $kt_limit_counter_timeout = $kt_counter['limit_counter_timeout'];
                    $kt_limit_counter_timeout_left = $kt_limit_counter_timeout - time();

                    $kt_limit_counter_left = (int) $kt_limit_users - (int) $kt_limit_counter;

                    if (0 < $kt_limit_counter_left) {
                        ?>
                        <div id="kt_limit_notice_holder">
                            <div class="notice notice-warning inline">
                                <p style="margin-top:0;margin-bottom:0;">
                                    <?php echo sprintf( __( 'You can transfer <strong>%s</strong> users out of your <strong>Pro Version</strong> limit for <strong>%s</strong> users per <strong>%s</strong> days.', 'wp2leads' ), $kt_limit_counter_left, $kt_limit_users, $kt_limit_days ); ?>
                                    <br>
                                    <small><i><?php echo __( 'This value can be less because of transfer running in background and transfer scheduled by cron.', 'wp2leads' ); ?></i></small>
                                </p>
                            </div>
                        </div>
                        <button id="btnTransferDataCurrent" class="button button-primary">
                            <?php echo __( 'Transfer current', 'wp2leads' ); ?>
                        </button>

                        <button id="btnTransferDataImmediately" class="button <?php echo $is_map_transfer_in_bg || $wp2l_is_cron_disabled ? 'disabled' : 'button-primary' ?>"<?php echo $is_map_transfer_in_bg || $wp2l_is_cron_disabled ? ' disabled="disabled"' : '' ?>>
                            <?php echo __( 'Transfer all immediately', 'wp2leads' ); ?>
                        </button>
                        <?php

                        if ($is_map_transfer_in_bg) {
                            ?>
                            <p class="warning-text">
                                <?php echo __( 'Background transfer not available right now for this map as far as another process running.', 'wp2leads' ); ?>
                            </p>
                            <?php

                        }
                    } else {
                        ?>
                        <div id="kt_limit_notice_holder">
                            <div class="notice notice-warning inline">
                                <p style="margin-top:0;margin-bottom:0;">
                                    <?php echo sprintf( __( 'You have exceeded your <strong>Pro Version</strong> limit for <strong>%s</strong> users per <strong>%s</strong> days.', 'wp2leads' ), $kt_limit_users, $kt_limit_days ); ?> <a class="button button-primary button-small" href="https://wp2leads.com" target="_blank"><?php echo __('Please, buy a license on Wp2Leads.com') ?></a>
                                </p>
                            </div>
                        </div>
                        <button id="btnTransferDataCurrent" class="button disabled" disabled="disabled">
                            <?php echo __( 'Transfer current', 'wp2leads' ); ?>
                        </button>

                        <button id="btnTransferDataImmediately" class="button disabled" disabled="disabled">
                            <?php echo __( 'Transfer all immediately', 'wp2leads' ); ?>
                        </button>
                        <?php
                    }
                }
            }
            ?>
            <?php
        } else {
            ?>
            <button id="btnTransferDataCurrent" class="button button-primary">
                <?php echo __( 'Transfer current', 'wp2leads' ); ?>
            </button>

            <button id="btnTransferDataImmediately" class="button <?php echo $is_map_transfer_in_bg || $wp2l_is_cron_disabled ? 'disabled' : 'button-primary' ?>"<?php echo $is_map_transfer_in_bg || $wp2l_is_cron_disabled ? ' disabled="disabled"' : '' ?>>
                <?php echo __( 'Transfer all immediately', 'wp2leads' ); ?>
            </button>
            <?php

            if ($is_map_transfer_in_bg) {
                ?>
                <p class="warning-text">
                    <?php echo __( 'Background transfer not available right now for this map as far as another process running.', 'wp2leads' ); ?>
                </p>
                <?php

            }
        }

        if ($wp2l_is_cron_disabled) {
            ?>
            <p class="warning-text" style="margin-bottom:0;">
                <?php _e('You have WP Cron disabled on your site, background and cron transferring could not be run. Please, remove <strong>"DISABLE_WP_CRON"</strong> constant in your <strong>wp-config.php</strong> file, or set it to <strong>false</strong>', 'wp2leads') ?>
            </p>
            <?php
        }
        ?>
    </div>

    <?php
    if ($is_initial_settings_done) {
        ?>
        <h3 class="accordion-subheader">
            <?php _e( 'Statistics:', 'wp2leads' ) ?>
        </h3>

        <div id="map-statistic-holder" class="accordion-subbody">
            <div class="two-col__holder">
                <div class="col__holder">
                    <h4 style="margin-top:0;margin-bottom:8px;"><?php _e( 'Transfered users:', 'wp2leads' ) ?></h4>

                    <div id="totalTransferInfo">
                        <p class="all-transferred" style="margin-top:0;margin-bottom:0;">
                            <?php _e( 'Total amount', 'wp2leads' ) ?>:
                            <strong class="total">
                                <?php echo !empty($totally_transfered['all']) ? $totally_transfered['all'] : 0; ?>
                            </strong>
                        </p>
                        <p class="unique-transferred" style="margin-top:0;margin-bottom:0;">
                            <?php _e( 'Unique', 'wp2leads' ) ?>:
                            <strong class="total">
                                <?php echo !empty($totally_transfered['unique']) ? $totally_transfered['unique'] : 0; ?>
                            </strong>
                        </p>

                        <p class="failed-transferred" style="margin-top:0;margin-bottom:0;">
                            <?php
                            $href = '?page=wp2l-admin&tab=statistics&failed_items_list=show';
                            $href .= '&active_mapping=' . $activeMapId;
                            ?>
                            <?php _e( 'Failed', 'wp2leads' ) ?>:
                            <strong class="total">
                                <?php echo !empty($totally_transfered['failed']) ? $totally_transfered['failed'] : 0; ?>
                            </strong> (<a href="<?php echo $href ?>" target="_blank"><?php _e('why failed?', 'wp2leads') ?></a>)
                        </p>
                    </div>
                </div>

                <div class="col__holder">
                    <a href="?page=wp2l-admin&tab=statistics&active_mapping=<?php echo $activeMapId; ?>" target="_blank" class="button button-primary">
                        <?php _e( 'See detailed statistics', 'wp2leads' ) ?>
                    </a>

                    <div id="lastTransferInfo" style="display: none;">
                        <p class="manual-transfer-date" style="margin-top:0;margin-bottom:0;"><?php _e( 'Manually', 'wp2leads' ) ?>:
                            <strong class="total">
                                <?php echo !empty($totally_transfered['time']) ? $totally_transfered['time'] : __( 'No manual transfer', 'wp2leads' ); ?>
                            </strong>
                        </p>

                        <p class="cron-transfer-date" style="margin-top:0;margin-bottom:0;"><?php _e( 'With cron', 'wp2leads' ) ?>:
                            <strong class="total">
                                <?php echo !empty($totally_transfered['crontime']) ? $totally_transfered['crontime'] : __( 'No cron transfer', 'wp2leads' ); ?>
                            </strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>

    <div class="api-spinner-holder">
        <div class="api-spinner"></div>
    </div>
</div>
