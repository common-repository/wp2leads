<?php
/**
 * Template for displaying Map Builder Results
 *
 * @since      1.0.0
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/admin/partials
 */
?>

<div class="buttons_holder">
    <span class="button" id="btnRunSampleMap"><?php _e('Run Sample Map', 'wp2leads') ?></span>

    <?php if( WP2LEADS_DEBUG ): ?>
        <span class="button" id="fetchGeneratedSql"><?php _e('Fetch SQL', 'wp2leads') ?></span>
    <?php endif; ?>

    <span id="wp2l-results-filtering" style="float: right;">
        <span data-active_text="<?php _e( 'Apply Selections', 'wp2leads' ) ?>"
              data-active_class="button-primary"
              data-inactive_text="<?php _e( 'Toggle Direct Selection', 'wp2leads' ) ?>"
              data-inactive_class="button-secondary"
              id="wp2l-toggle-direct-results-selection"
              class="button button-secondary button-small"
              style="margin-top: 3px; margin-right: 10px;"
        >
            <?php _e('Toggle Direct Selection', 'wp2leads') ?>
        </span>

        <span><?php _e( 'Limit results to:', 'wp2leads' ) ?>
            <input type="number" name="map-sample-results-limit" id="map-sample-results-limit" value="50" class="small-text">
        </span>

        <span class="button" id="btnRunSampleMap"><?php _e('Run', 'wp2leads') ?></span>
    </span>
</div>

<h2 id="wp2l-results-header"><?php _e('Results', 'wp2leads') ?></h2>

<hr>

<div id="wp2l-results-preview-wrap">
    <div id="wp2l-results-preview-wrap-inner" style="overflow: auto; position: relative;">
        <table id="wp2l-results-preview" class="widefat">
            <tbody>
                <tr>
                    <td><?php _e('Update your map to begin showing results!', 'wp2leads') ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>