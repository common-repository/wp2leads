<?php
/**
 * API Fields Section
 *
 * @package Wp2Leads/Partials/MapToAPI
 * @version 1.0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<h3 class="accordion-header strict<?php echo $is_initial_settings_done ? '' : ' disabled'; ?>"><?php _e( 'Fields', 'wp2leads' ) ?></h3>

<div class="accordion-body">
    <div id="inputFiledSearchKT">
        <input style="width: 100%; margin: 10px 0px;" type="text"
               placeholder="<?php _e( 'Enter Field name', 'wp2leads' ) ?>">
    </div>

    <div class="api-fields-wrapper">
        <div class="api_field_box">
            <div class="api_field_head">
                <p class="field_label"><?php _e( 'Email', 'wp2leads' ) ?></p>
                <p class="field_value"><?php _e( 'Choose an option', 'wp2leads' ) ?></p>
            </div>

            <div class="api_field_body">
                <fieldgroup id="api_email">
                    <?php
                    $api_options = ! empty( $api['fields']['api_email'] ) ? ! is_array( $api['fields']['api_email'] ) ? array( $api['fields']['api_email'] ) : $api['fields']['api_email'] : [];
                    ?>
                    <select class="api-field" name="api_email" multiple
                            data-api-option='<?php echo json_encode( $api_options, JSON_FORCE_OBJECT ); ?>'></select>
                </fieldgroup>
            </div>

            <div class="api-field__value"></div>
        </div>

        <?php
        foreach ( $fields as $fieldName => $fieldDescription ):
            ?>
            <div class="api_field_box">
                <div class="api_field_head">
                    <p class="field_label"><?php _e( $fieldDescription, 'wp2leads' ) ?></p>
                    <p class="field_value"><?php _e( 'Choose an option', 'wp2leads' ) ?></p>
                </div>

                <div class="api_field_body">
                    <fieldgroup id="api_<?php echo( $fieldName ) ?>">
                        <?php
                        $type         = ! empty( $api['fields'][ 'api_' . $fieldName ]['type'] ) ? $api['fields'][ 'api_' . $fieldName ]['type'] : 'text';
						$gmt          = ! empty( $api['fields'][ 'api_' . $fieldName ]['gmt'] ) ? $api['fields'][ 'api_' . $fieldName ]['gmt'] : false;
                        $gmt_to_local = ! empty( $api['fields'][ 'api_' . $fieldName ]['gmt_to_local'] ) ? $api['fields'][ 'api_' . $fieldName ]['gmt_to_local'] : false;
                        $api_options  = ! empty( $api['fields'][ 'api_' . $fieldName ] ) ? ! is_array( $api['fields'][ 'api_' . $fieldName ] ) ? array( $api['fields'][ 'api_' . $fieldName ] ) : $api['fields'][ 'api_' . $fieldName ] : [];
                        $table_columns = ! empty( $api_options['table_columns'] ) ? $api_options['table_columns'] : array();
                        $is_lead_value = $fieldName === 'fieldLeadValue';

                        if ($is_lead_value) {
                            $is_lead_value_checked = ! empty( $api['fields'][ 'api_' . $fieldName ]['add_to_lead'] );
                        }
                        ?>
                        <select
                                class="api-field"
                                name="api_<?php echo( $fieldName ) ?>"
                                multiple
                                data-api-option='<?php echo json_encode( $api_options, JSON_FORCE_OBJECT ); ?>'
                        ></select>
                        <select class="field-type">
                            <option value="">
                                <?php _e( '-- Select --', 'wp2leads' ) ?>
                            </option>
                            <option value="text" <?php echo 'text' === $type ? 'selected' : ''; ?>>
                                <?php _e( 'text', 'wp2leads' ) ?>
                            </option>
                            <option value="number" <?php echo 'number' === $type ? 'selected' : ''; ?>>
                                <?php _e( 'number', 'wp2leads' ) ?>
                            </option>
                            <option value="decimal" <?php echo 'decimal' === $type ? 'selected' : ''; ?>>
                                <?php _e( 'decimal', 'wp2leads' ) ?>
                            </option>
                            <option value="url" <?php echo 'url' === $type ? 'selected' : ''; ?>>
                                <?php _e( 'url', 'wp2leads' ) ?>
                            </option>
                            <option value="html" <?php echo 'html' === $type ? 'selected' : ''; ?>>
                                <?php _e( 'HTML', 'wp2leads' ) ?>
                            </option>
                            <option value="date" <?php echo 'date' === $type ? 'selected' : ''; ?>>
                                <?php _e( 'date', 'wp2leads' ) ?>
                            </option>
                            <option value="time" <?php echo 'time' === $type ? 'selected' : ''; ?>>
                                <?php _e( 'time', 'wp2leads' ) ?>
                            </option>
							<option value="datetime" <?php echo 'datetime' === $type ? 'selected' : ''; ?>>
                                <?php _e( 'date + time', 'wp2leads' ) ?>
                            </option>
                        </select>

                        <span style="display: <?php echo ('time' !== $type && 'datetime' !== $type && 'date' !== $type) ? 'none' : 'inline-block'; ?>; width: 20px;">
                            <span id="tippy_<?php echo( $fieldName ) ?>" data-template="tippy_content_<?php echo( $fieldName ) ?>" class="dashicons dashicons-editor-help tippy_button"></span>
                        </span>

						<label class="convert-to-label" style="<?php if('time' !== $type && 'datetime' !== $type && 'date' !== $type) echo 'display: none;'; ?>">
                            <span class="name"><?php _e('Convert your time', 'wp2leads'); ?>:</span>
						</label>
                        &nbsp;&nbsp;&nbsp;&nbsp;

                        <?php
                        $convert_to_arrow = '<span style="font-size: 12px;height: 12px;width: 16px;vertical-align: text-top;" class="dashicons dashicons-arrow-right-alt"></span>';
                        ?>

                        <label style="<?php if('time' !== $type && 'datetime' !== $type && 'date' !== $type) echo 'display: none;'; ?>">
                            <input type="checkbox" data-showtypes='["time","datetime","date"]' name="convert_to_local" class="convert-to-local" <?php if($gmt_to_local) echo 'checked="checked"'; ?>>
                            <span class="name">
                                <?php echo sprintf( __( 'gmt %s <strong>LOCAL</strong>', 'wp2leads' ), $convert_to_arrow ); ?>
                            </span>
                        </label>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<label style="<?php if('time' !== $type && 'datetime' !== $type && 'date' !== $type) echo 'display: none;'; ?>">
							<input type="checkbox" data-showtypes='["time","datetime","date"]' name="convert_to_gmt" class="convert-to-gmt" <?php if($gmt) echo 'checked="checked"'; ?>>
                            <span class="name">
                                <?php echo sprintf( __( 'local %s <strong>GMT</strong>', 'wp2leads' ), $convert_to_arrow ); ?>
                            </span>
						</label>

                        <div id="tippy_content_<?php echo( $fieldName ) ?>" style="display: none;">
                            <p>
                                <?php _e('You can convert date, time and datetime values from local to GMT and gmt to LOCAL time.', 'wp2leads'); ?>
                            </p>
                            <p>
                                <?php _e('You can add strotime like 734785638764 or english time like 2020.11.21....', 'wp2leads'); ?><br>
                                <?php _e('or german time like 21.11.2020 + 18:30:00 (From two seperate DB entries)', 'wp2leads'); ?><br>
                                <?php _e('or 21.11.2020 18:30 or 17.11.2020, 12:00 - 14:30 Uhr, Mo 17.11.2020, 12:00 - 14:30 Uhr', 'wp2leads'); ?><br>
                                <?php _e('and WP2LEADS will handle the translation into "KlickTipp" date & time.', 'wp2leads'); ?>
                            </p>
                            <p>
                                <?php _e('Best working with our "CF7 choose your date" example form. The example you can find inside the WP2LEADS catalog.', 'wp2leads'); ?>
                            </p>
                        </div>

                        <?php
                        if ($is_lead_value) {
                            ?>
                            <label>
                                <input type="checkbox" name="add_to_lead_value" class="add-to-lead-value" <?php if($is_lead_value_checked) echo 'checked="checked"'; ?>> <span class="name"><?php _e('Add field value to "LeadValue"', 'wp2leads'); ?></span>
                            </label>
                            <?php
                        }
                        ?>
                    </fieldgroup>
                </div>

                <div class="api-field__value"></div>
            </div>
        <?php endforeach; ?>
        <script>
            // With the above scripts loaded, you can call `tippy()` with a CSS
            // selector and a `content` prop:
            tippy('.tippy_button', {
                content(reference) {
                    var id = reference.getAttribute('data-template');
                    var template = document.getElementById(id);
                    return template.innerHTML;
                },
                allowHTML: true,
                theme: 'light-border',
                placement: 'bottom-end',
                interactive: true
            });
        </script>
    </div>
</div>