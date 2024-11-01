<?php
/**
 * Add Manual Tags Section
 *
 * @package Wp2Leads/Partials/MapToAPI
 * @version 1.0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<h3 class="accordion-header strict<?php echo $is_initial_settings_done ? '' : ' disabled'; ?>"><?php _e( 'Add Tags', 'wp2leads' ) ?></h3>

<div class="accordion-body">
    <h3 class="accordion-subheader"><?php _e( 'Tags to add when the lead is transferred:', 'wp2leads' ) ?></h3>

    <div class="accordion-subbody">
        <div class="create-tag-wrapper">
            <input type="text" value=""
                   placeholder="<?php _e( 'Filter and create Tags', 'wp2leads' ) ?>"
                   class="tag-text">
            <button id="create-tag"
                    class="button button-primary"><?php echo _e( 'Create tag', 'wp2leads' ); ?></button>
        </div>

        <div class="tags-wrapper">
            <div id="tags-cloud-options" class="tags-cloud"
                 data-tags-cloud='<?php echo json_encode( $tags, JSON_FORCE_OBJECT ) ?>'>
                <?php
                $manually_selected_tags = isset( $api['manually_selected_tags']['tag_ids'] ) ? $api['manually_selected_tags']['tag_ids'] : array();

                foreach ( $tags as $tag_code => $tag_name ) {
                    ?>
                    <fieldset>
                        <input
                            <?php echo in_array( $tag_code, $manually_selected_tags ) ? 'checked' : ''; ?>
                                id="<?php echo $tag_code; ?>"
                                type="checkbox"
                                value="<?php echo $tag_name; ?>"
                                data-name="<?php echo $tag_name; ?>"
                        >
                        <label for="<?php echo $tag_code; ?>"><?php echo $tag_name; ?></label>
                    </fieldset>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

    <h3 class="accordion-subheader"><?php _e( 'Conditions', 'wp2leads' ) ?>:</h3>

    <div class="accordion-subbody">
        <div class="tags-conditions-wrapper">
            <div id="tags-add-conditions">
                <div
                        class="conditions-list"
                        data-saved-value='<?php echo isset( $api['conditions']['tags'] ) ? json_encode( $api['conditions']['tags'], JSON_FORCE_OBJECT ) : ''; ?>'
                ></div>

                <a href="javascript:void(0);" class="button add_new_condition"
                   data-type="tags">+ <?php _e( 'Add Condition', 'wp2leads' ) ?></a>
            </div>
        </div>
    </div>
</div>
