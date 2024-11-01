<?php
/**
 * Auto Tags Section
 *
 * @package Wp2Leads/Partials/MapToAPI
 * @version 1.0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<h3 class="accordion-header strict<?php echo $is_initial_settings_done ? '' : ' disabled'; ?>"><?php _e( 'Auto Tags', 'wp2leads' ) ?></h3>

<div class="accordion-body">
    <?php
    if ($is_initial_settings_done) {
        ?>
        <h3 class="accordion-subheader"><?php echo _e( 'Automatic create tags and attach to Klick-Tipp user:', 'wp2leads' ); ?></h3>

        <div class="accordion-subbody">
            <div class="connected-options-wrapper">

                <div id="autotags-add-conditions" style="margin-bottom: 5px">
                    <?php
                    $conditions_autotags = !empty($api['conditions']['autotags']) ? $api['conditions']['autotags'] : array();
                    ?>
                    <div class="conditions-list"
                         data-saved-value='<?php echo json_encode( $conditions_autotags, JSON_FORCE_OBJECT ); ?>'
                    ></div>

                    <button id="addConditionForAutotags" class="button"
                            data-type="add">
                        + <?php _e( 'Add Condition', 'wp2leads' ) ?>
                    </button>

                    <p style="margin-top:10px;margin-bottom:5px">
                        <?php _e( 'You can set up conditions to create new automatic created tags. Otherwise tags always will be created and added.', 'wp2leads' ); ?>
                        <br>
                        <?php _e( '<small><i>f.e. <strong>if value in </strong> v.usermeta-capabilities <strong>is like</strong> customer </i></small>', 'wp2leads' ); ?>
                    </p>
                </div>

                <?php
                $api_options        = ! empty( $api['connected_for_tags']['tags'] ) ? ! is_array( $api['connected_for_tags']['tags'] ) ? array( $api['connected_for_tags']['tags'] ) : $api['connected_for_tags']['tags'] : array();
                $api_options_concat = ! empty( $api['connected_for_tags']['tags_concat'] ) ? ! is_array( $api['connected_for_tags']['tags_concat'] ) ? array( $api['connected_for_tags']['tags_concat'] ) : $api['connected_for_tags']['tags_concat'] : array();
                $unique_paths       = $map_columns;
                $api_options_saved = ! empty( $api_options ) ? $api_options : array();
                $api_options_concat_saved = ! empty( $api_options_concat ) ? $api_options_concat : array();
                ?>
                <div class="wptl-row simple-auto-tags">
                    <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-6">
                        <h3 style="margin-top:15px;margin-bottom:5px;font-size:15px;"><?php echo _e( 'For single values:', 'wp2leads' ); ?></h3>
                        <p style="margin-top:0;margin-bottom:5px;line-height:1;">
                            <?php _e( '<small><i>f.e. <strong>posts.post_type</strong> or <strong>v.usermeta-first_name</strong></i></small>', 'wp2leads' ); ?>
                        </p>
                        <select class="options-list" multiple
                                data-saved-value='<?php echo json_encode( $api_options_saved, JSON_FORCE_OBJECT ); ?>'>
                        </select>
                    </div>

                    <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-6">
                        <h3 style="margin-top:15px;margin-bottom:5px;font-size:15px;"><?php echo _e( 'For concatenated values:', 'wp2leads' ); ?></h3>

                        <p style="margin-top:0;margin-bottom:5px;line-height:1;">
                            <?php _e( '<small><i>f.e. <strong>usermeta.meta_value(concatenated)</strong> or <strong>terms.name(concatenated)</strong></i></small>', 'wp2leads' ); ?>
                        </p>

                        <select class="options-concat-list" multiple
                                data-saved-value='<?php echo json_encode( $api_options_concat_saved, JSON_FORCE_OBJECT ); ?>'>
                        </select>
                    </div>
                </div>

                <h3 style="margin-top:10px;margin-bottom:5px;font-size:15px;"><?php echo _e( 'For custom separated values:', 'wp2leads' ); ?></h3>

                <div class="separators-list"
                     data-saved-value='<?php echo isset( $api['connected_for_tags']['separators'] ) ? json_encode( $api['connected_for_tags']['separators'], JSON_FORCE_OBJECT ) : ''; ?>'
                ></div>

                <a href="javascript:void(0);" class="button add_new_separator">+
                    <?php _e( 'Add Separator', 'wp2leads' ) ?></a>
            </div>
        </div>

        <h3 class="accordion-subheader"><?php echo _e( 'Detach automatic created tags from Klick-Tipp user:', 'wp2leads' ); ?></h3>

        <div class="accordion-subbody">
            <div id="autotags-detach-conditions">
                <?php
                $conditions_detach_autotags_saved = !empty($api['conditions']['detach_autotags']) ? $api['conditions']['detach_autotags'] : array();
                ?>
                <div class="conditions-list"
                     data-saved-value='<?php echo json_encode( $conditions_detach_autotags_saved, JSON_FORCE_OBJECT ); ?>'
                ></div>

                <button id="addConditionForAutotags" class="button"
                        data-type="detach">
                    + <?php _e( 'Add Condition', 'wp2leads' ) ?>
                </button>

                <p style="margin-top: 10px;margin-bottom: 5px">
                    <?php _e( 'You can set up conditions to prevent create new or detach tags from automatic created.', 'wp2leads' ); ?>
                    <br>
                    <?php _e( '<span><small><i>f.e. <strong>if value in </strong> v.postmeta-status <strong>is like</strong> wc-completed </i></small></span>', 'wp2leads' ); ?>
                </p>
            </div>
        </div>

        <h3 class="accordion-subheader"><?php echo _e( 'Automatic create tags and attach to Klick-Tipp user:', 'wp2leads' ); ?></h3>

        <div class="accordion-subbody">
            <div id="multiple-autotags-add-conditions">
                <?php
                $multiple_autotags_autotag_items_saved = !empty($api['multiple_autotags']['autotag_items']) ? $api['multiple_autotags']['autotag_items'] : array();
                ?>
                <div class="multiple-autotag-list"
                     data-saved-value='<?php echo json_encode( $multiple_autotags_autotag_items_saved, JSON_FORCE_OBJECT ); ?>'
                ></div>

                <button id="addConditionForSingleAutotags"
                        class="button add-multiple-autotag-item" data-type="add"
                        data-value-type="autotag-single">
                    + <?php _e( 'Single', 'wp2leads' ) ?>
                </button>

                <button id="addConditionForConcatAutotags"
                        class="button add-multiple-autotag-item" data-type="add"
                        data-value-type="autotag-concat">
                    + <?php _e( 'Concatenated', 'wp2leads' ) ?>
                </button>

                <button id="addConditionForSeparatorAutotags"
                        class="button add-multiple-autotag-item" data-type="add"
                        data-value-type="autotag-separator">
                    + <?php _e( 'Separator', 'wp2leads' ) ?>
                </button>
            </div>
        </div>
        <?php
    }
    ?>
</div>
