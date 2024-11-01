<?php
/**
 * Notices - Limitation Counter
 *
 * @package Wp2Leads/Partials/Notices
 * @version 1.0.2.5
 * @since 1.0.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$kt_limitation = KlickTippManager::get_initial_kt_limitation();
$kt_counter = KlickTippManager::get_transfer_counter();

if (!$kt_limitation || !$kt_counter) {
    return;
}

$kt_limit_users = $kt_limitation['limit_users'];
$kt_limit_message = $kt_limitation['limit_message'];
$kt_limit_days = $kt_limitation['limit_days'];
$kt_limit_counter = $kt_counter['limit_counter'];
$kt_limit_counter_timeout = $kt_counter['limit_counter_timeout'];
$kt_limit_counter_timeout_left = $kt_limit_counter_timeout - time();

if ($kt_limit_counter_timeout_left > 86400) {
    $timeout_left = ceil($kt_limit_counter_timeout_left / 86400);
    $timeout_label = $timeout_left . __(' days', 'wp2leads');
} else {
    $timeout_left = ceil($kt_limit_counter_timeout_left / 3600);
    $timeout_label = $timeout_left . __(' hours', 'wp2leads');
}

$kt_limit_counter_left = (int) $kt_limit_users - (int) $kt_limit_counter;

if (0 > $kt_limit_counter_left || 0 === $kt_limit_counter_left) {
    ?>
    <div id="wp2lead-map-to-api-bg-notice" class="notice notice-info wp2lead-notice notice-can-disable api-processing-holder">
        <div style="padding-bottom:10px;padding-top:10px;">
            <strong><?php _e('WP2Leads', 'wp2leads') ?></strong>: <?php echo sprintf( __( 'You have exceeded your Pro Version limit for %s users per %s days.', 'wp2leads' ), $kt_limit_users, $kt_limit_days ); ?>
            <?php echo sprintf( __( 'Limit will be reset in %s.', 'wp2leads' ), $timeout_label ); ?> <a class="button button-primary button-small" href="https://wp2leads.com" target="_blank"><?php echo __('Please, buy a license on Wp2Leads.com') ?></a>
        </div>
    </div>
    <?php
} elseif ((int) $kt_limit_counter_left < (int) $kt_limit_message) {
    ?>
    <div id="wp2lead-map-to-api-bg-notice" class="notice notice-info wp2lead-notice notice-can-disable api-processing-holder">
        <div style="padding-bottom:10px;padding-top:10px;">
            <?php echo sprintf( __( 'You have %s users to transfer out of your Pro Version limit for %s users per %s days.', 'wp2leads' ), $kt_limit_counter_left, $kt_limit_users, $kt_limit_days ); ?>
            <?php echo sprintf( __( 'Limit will be reset in %s .', 'wp2leads' ), $timeout_label ); ?>
        </div>
    </div>
    <?php
}


