<?php
/**
 * Initial Settings Section
 *
 * @package Wp2Leads/Partials/MapToAPI
 * @version 1.0.2.5
 * @since 1.0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wp2l_is_cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
?>

<h3 class="accordion-subheader">
    <?php _e( 'Cron settings:', 'wp2leads' ); ?>
</h3>

<div class="accordion-subbody">
    <div id="cronSettingsWrapper">
        <?php
        if ( $wp2l_is_cron_disabled ) {
            ?>
            <p style="margin-top:0;margin-bottom:0;">
                <?php _e('You have WP Cron disabled on your site, background and cron transferring could not be run. Please, remove <strong>"DISABLE_WP_CRON"</strong> constant in your <strong>wp-config.php</strong> file, or set it to <strong>false</strong>', 'wp2leads') ?>
            </p>
            <?php
        } else {
            ?>
            <h4 style="margin-top:0;margin-bottom:10px">
                <span class="cron-status-icon dashicons dashicons-clock<?php echo $cron_status ?>" title="<?php echo $cron_title ?>"></span>
                <span class="cron-status-text"><?php echo $cron_title ?></span>
            </h4>

            <?php
            if ($module_available) {
                ?><p style="margin-top:0;"><small><?php _e( 'We recomend you to use instant module instead of cron', 'wp2leads' ) ?></small></p><?php
            }
            ?>


            <p style="margin-top:0;"><?php _e( 'Please, choose the transfer/update trigger date + time column', 'wp2leads' ) ?></p>

            <div id="cron-columns-options" class="settings-fieldset-holder">
                <?php
                $i = 1;
				$show_notice = true;
                foreach ( $decodedMap['dateTime'] as $date_time_field ) {
					if ( in_array($date_time_field, $cron_fields) ) $show_notice = false;
                    ?>
                    <fieldset>
                        <input id="activate_<?php echo $i ?>" class="cron-option" type="checkbox"
                               value="<?php echo $date_time_field ?>"
                            <?php echo in_array($date_time_field, $cron_fields) ? ' checked' : ''; ?>
                        >
                        <label for="activate_<?php echo $i ?>"><?php echo $date_time_field ?></label>
                    </fieldset>
                    <?php
                    $i++;
                }
                ?>
            </div>
			
            <div style="margin-bottom:10px;padding-left:11px">
                <input id="cronStatus" type="checkbox" name="cron-status" <?php echo ! empty( $cron_checked ) ? $cron_checked : '' ?>>
                <label for="cronStatus"><?php _e( 'Enable cron', 'wp2leads' ) ?></label>
                <?php
                if ($module_available) {
                    ?>(<small><?php _e('Instant transfer will be disabled', 'wp2leads') ?></small>)<?php
                }
                ?>
            </div>
            <div>
                <button id="saveCronSettings" class="button button-primary"><?php _e( 'Save cron settings', 'wp2leads' ) ?></button>
            </div>

            <div style="margin-top:15px;">
				<?php if ( $show_notice ) { ?>
					<div style="margin-bottom:10px;"><?php _e('To automatically transfer data you need to choose a "Date / Time columns" or one of the "transfer modules" or <a href="https://wp2leads-for-klick-tipp.com/web/let-us-create-a-map-for-you/" target="_blank">let us create the connections for you.</a>', 'wp2leads'); ?></div>
				<?php } ?>
                <?php _e( 'If you have low traffic, please, use <a href="https://uptimerobot.com/" target="_blank">Uptimerobot</a> or other resource to make requests on your site, because Wordpress Cron working only on hits.', 'wp2leads' ) ?>
            </div>
            <?php
        }
        ?>
    </div>
</div>
