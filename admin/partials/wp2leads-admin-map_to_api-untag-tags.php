<?php
/**
 * Untag Manual Tags Section
 *
 * @package Wp2Leads/Partials/MapToAPI
 * @version 1.0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<h3 class="accordion-header strict<?php echo $is_initial_settings_done ? '' : ' disabled'; ?>"><?php _e( 'Untag Tags', 'wp2leads' ) ?></h3>

<div class="accordion-body">
    <div class="detach-tags-wrapper">
        <h3 class="accordion-subheader"><?php _e( 'Tags to detach:', 'wp2leads' ) ?></h3>

        <div class="accordion-subbody">
            <input type="text" value="" placeholder="<?php _e( 'Filter tags...', 'wp2leads' ) ?>"
                   class="tag-text">
            <button id="select-all-for-detach"
                    class="button button-primary"><?php echo _e( 'Select all', 'wp2leads' ); ?></button>
            <button id="deselect-all-for-detach"
                    class="button button-primary"><?php echo _e( 'Deselect all', 'wp2leads' ); ?></button>

            <div class="detach-tags-wrapper-selection">
                <?php
                $detach_tags = isset( $api['detach_tags']['tag_ids'] ) ? $api['detach_tags']['tag_ids'] : array();

                foreach ( $tags as $tag_code => $tag_name ) { ?>
                    <fieldset>
                        <input <?php echo in_array( $tag_code, $detach_tags ) ? 'checked' : ''; ?>
                                id="detach_<?php echo $tag_code; ?>"
                                type="checkbox"
                                value="<?php echo $tag_code; ?>"
                                data-name="<?php echo $tag_name; ?>"
                        >
                        <label for="detach_<?php echo $tag_code; ?>"><?php echo $tag_name; ?></label>
                    </fieldset>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="tags-detach-conditions-wrapper">
        <h3 class="accordion-subheader"><?php _e( 'Conditions:', 'wp2leads' ) ?></h3>

        <div class="accordion-subbody">
            <div id="tags-detach-conditions">
                <div class="conditions-list"
                     data-saved-value='<?php echo isset( $api['conditions']['detach_tags'] ) ? json_encode( $api['conditions']['detach_tags'], JSON_FORCE_OBJECT ) : ''; ?>'></div>
                <a href="javascript:void(0);"
                   class="button add_new_detach_condition">&plus; <?php _e( 'Add Condition', 'wp2leads' ) ?></a>
            </div>
        </div>
    </div>
</div>
