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

/**
 * @var $module_label
 * @var $module_description
 * @var $module_enabled
 * @var $activeMapId
 */
?>

<h3 class="accordion-subheader">
    <?php _e( 'Instant transfer settings:', 'wp2leads' ); ?>
</h3>

<div class="accordion-subbody">
    <div style="margin-bottom:10px;padding-left:11px">
        <?php _e( 'There is module available for this map, which will transfer user data instantly after some events.', 'wp2leads' ) ?>
    </div>

    <div class="settings-fieldset-holder">
        <div id="available-module-wrapper">
            <?php _e('Module title', 'wp2leads') ?>: <strong><?php echo $module_label; ?></strong>
        </div>

        <div class="module-description">
            <p style="margin-bottom:0;"><?php echo $module_description; ?></p>
        </div>
    </div>

    <div style="margin-bottom:10px;padding-left:11px">
        <input id="moduleStatus" type="checkbox" name="module-status"<?php echo $module_enabled; ?>>
        <label for="moduleStatus"><?php _e( 'Enable module', 'wp2leads' ) ?></label>
        <?php
        if ($cron_available) {
            ?>(<small><?php _e('Cron will be disabled', 'wp2leads') ?></small>)<?php
        }
        ?>
    </div>
    <div>
        <button id="saveModuleSettings" class="button button-primary" data-module-key="<?php echo $mapping["transferModule"]; ?>" data-map-id="<?php echo $activeMapId; ?>"><?php _e( 'Save module settings', 'wp2leads' ) ?></button>
    </div>
</div>
