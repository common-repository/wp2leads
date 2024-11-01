<?php
/**
 * Template for displaying Map Builder Tab
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/admin/partials
 * @var $activeMap
 * @var $mapForDuplicate
 * @var $decodedMap
 * @var $tables
 */

$is_create_own_map_allowed = Wp2leads_License::is_action_allowed('create_own_map');
$is_use_own_map_allowed = Wp2leads_License::is_action_allowed('use_own_map');
?>

<!-- Section for creating new map -->

<?php if (!$is_create_own_map_allowed): ?>
    <div class="notice notice-warning inline">
        <h4><?php _e('Creating own Map only in paid versions', 'wp2leads') ?></h4>

        <p><?php _e('Please upgrade to Professional Version to transfer user via OWN created maps.', 'wp2leads') ?> <a href="https://wp2leads.com" target="_blank"><?php _e('Click here!', 'wp2leads') ?></a></p>
    </div>
<?php endif; ?>

<?php include dirname(__FILE__) . '/wp2leads-admin-map_builder-headstart.php'; ?>

<h2 id="wp2l-create-map-header">
    <?php _e('Create Map', 'wp2leads') ?>
</h2>

<hr>

<section id="create-map-section">
    <?php
    if ( $is_create_own_map_allowed ) {
        ?>
        <form id="map-generator" action="" method="post">
            <input type="hidden" class="mapping" value='<?php echo ( $activeMap || $mapForDuplicate ) ? json_encode($decodedMap) : ''; ?>'>

            <table class="form-table">
                <tr>
                    <td><?php _e('Utilities', 'wp2leads') ?></td>

                    <td>
                        <span id="reset-map-builder-form" class="button button-small">
                            <?php _e('Start Over', 'wp2leads') ?>
                        </span>
                    </td>
                </tr>

                <tr class="alternate">
                    <td>
                        <label for="from-table"><?php _e('Starter Data', 'wp2leads') ?></label>
                    </td>

                    <td class="api-processing-holder">
                        <div style="width:100%;max-width:400px;">
                            <select data-previousValue="" name="from-table" id="from-table" class="wp2l_starter_data form-control">
                                <option value="" selected><?php _e( '-- Select --', 'wp2leads' ) ?></option>

                                <?php foreach ($tables as $table): ?>
                                    <option value="<?php echo $table; ?>">
                                        <?php echo $table; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="api-spinner-holder api-processing">
                            <div class="api-spinner"></div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td><?php _e('Group Results by', 'wp2leads') ?></td>

                    <td class="api-processing-holder">
                        <div style="width:100%;max-width:400px;">
                            <select data-current_value="" name="group-map-by-key" id="wp2l-group-map-results-by" class="form-control"></select>

                            <input type="checkbox" id="disable-grouping" name="disable-grouping" style="display: none">

                            <label for="disable-grouping" style="display: none"><?php _e('Disable', 'wp2leads') ?></label>
                        </div>

                        <div class="api-spinner-holder api-processing">
                            <div class="api-spinner"></div>
                        </div>
                    </td>
                </tr>

                <tr class="alternate">
                    <td>
                        <?php _e('Relationship Data', 'wp2leads') ?>
                    </td>

                    <td class="api-processing-holder">
                        <div id="relationship-map-holder" data-current_values=""></div>

                        <p>
                            <a href="#" class="button" id="add-new-relationship-map">+ <?php _e('Add', 'wp2leads') ?></a>
                        </p>
                        <p class="warning-text" style="line-height: 1">
                            <small>
                                <i>
                                    <br>
                                    <?php _e('Please, avoid using columns with type like: TEXT, LONGTEXT in Joined tables (the last one dropdown). It could cause crashing of MySQL server.', 'wp2leads') ?>
                                    <br>
                                    <?php _e('You can check column types in search result in Map Headstart Section.', 'wp2leads') ?>
                                </i>
                            </small>
                        </p>

                        <div class="api-spinner-holder api-processing">
                            <div class="api-spinner"></div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php _e('Virtual relationship', 'wp2leads') ?>
                    </td>

                    <td class="api-processing-holder">
                        <div id="virtual-relationships">
                            <div class="virtual-relationship-list"></div>

                            <div class="virtual-relationship-bottom-menu">
                                <a href="#" id="virtual-relationship-button-add" class="button">+ <?php _e('Add', 'wp2leads') ?></a>
                            </div>
                        </div>

                        <div class="api-spinner-holder api-processing">
                            <div class="api-spinner"></div>
                        </div>
                    </td>
                </tr>

                <tr class="alternate">
                    <td><?php _e('Add Comparison', 'wp2leads') ?></td>

                    <td class="api-processing-holder">
                        <div id="column-comparison-holder" data-current_values=""></div>

                        <p><a href="#" class="button" id="add-new-comparison-map">&plus; <?php _e('Add', 'wp2leads') ?></a></p>

                        <div class="api-spinner-holder api-processing">
                            <div class="api-spinner"></div>
                        </div>
                    </td>

                </tr>

                <tr>
                    <td><?php _e('Concat results for', 'wp2leads') ?></td>

                    <td class="api-processing-holder">
                        <select style="width: 100%; max-width: 400px; height: 200px"
                                name="group-concat" id="wp2l-group-concat-for"
                                data-current_value="" multiple
                        ></select>

                        <p style="margin-top: 15px;">
                            <?php _e('Separator for concatenated items', 'wp2leads') ?>
                        </p>

                        <input
                                type="text"
                                class="form-control"
                                style="width: 100%; max-width: 400px;"
                                name="group-concat-separator"
                                id="wp2l-group-concat-separator"
                                value="||"
                        >

                        <div class="api-spinner-holder api-processing">
                            <div class="api-spinner"></div>
                        </div>
                    </td>
                </tr>

                <tr class="alternate">
                    <td>
                        <?php _e('Date / Time columns', 'wp2leads') ?>
                        <p style="line-height: 1">
                            <small>
                                <i><?php _e('For Cron(automatic transfer) please, choose the transfer/update trigger "date + time" column: e.g. 123761278 or 2018/09/09 14:14:14 like in "posts.post_modified"', 'wp2leads') ?></i>
                            </small>
                        </p>
                    </td>

                    <td class="api-processing-holder">
                        <select style="width: 100%; max-width: 400px; height: 200px"
                                name="date-time-columns" id="wp2l-date-time-columns"
                                data-current_value="" multiple
                        ></select>

                        <div class="api-spinner-holder api-processing">
                            <div class="api-spinner"></div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <p><?php _e('Exclude Columns Filter', 'wp2leads') ?></p>

                        <p style="line-height: 1">
                            <small>
                                <?php _e('Use this section for filtering columns that should not be excluded if column title contains some specific value. Useful for custom forms fields, etc.', 'wp2leads') ?>
                                <?php _e('Works only with virtual columns.', 'wp2leads') ?>
                                <br>
                                <i>
                                    <?php _e('f.e. <strong>_wc_form_field</strong>, <strong>_wpbd_form_field[</strong>', 'wp2leads') ?>
                                </i>
                            </small>
                        </p>
                    </td>

                    <td class="api-processing-holder">
                        <div
                                id="excludedColumnsFilter_container"
                                data-saved-value=''
                        ></div>

                        <div id="excludedColumnsFilter_control">
                            <div class="input-group_holder">
                                <div class="tag-input_holder input_holder">
                                    <input id="excludedColumnsFilterInput" placeholder="<?php _e( 'Input Filter Value', 'wp2leads' ) ?>" class="form-control" type="text">
                                </div>

                                <div class="tag-create-btn_holder btn_holder">
                                    <button id="createExcludedFilter" type="button" class="button"><?php _e('Add', 'wp2leads') ?></button>
                                </div>
                            </div>
                        </div>

                        <div class="api-spinner-holder api-processing">
                            <div class="api-spinner"></div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <p><?php _e('Exclude Columns', 'wp2leads') ?></p>
                        <p><span id="select-all-column-options" class="button button-small"><?php _e('Select All', 'wp2leads') ?></span></p>
                        <p><span id="invert-selected-column-options" class="button button-small"><?php _e('Invert Selection', 'wp2leads') ?></span></p>
                        <p><span id="deselect-all-column-options" class="button button-small"><?php _e('De-Select All', 'wp2leads') ?></span></p>
                    </td>

                    <td class="api-processing-holder">
                        <select style="width: 100%; max-width: 400px; height: 200px"
                                name="column-options" id="wp2l-column-options"
                                data-current_values="" multiple
                        ></select>

                        <div class="api-spinner-holder api-processing">
                            <div class="api-spinner"></div>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
        <?php
    }
    ?>
</section>

<?php include dirname(__FILE__) . '/wp2leads-admin-map_builder-additional.php'; ?>

<h2 id="wp2l-save-map-header">
    <?php _e('Save Map', 'wp2leads') ?>
</h2>

<hr>

<section id="save-the-map-section">
    <table class="form-table">
        <tbody>
            <tr>
                <td><?php _e('Map Name', 'wp2leads') ?></td>

                <td>
                    <div id="titlediv">
                        <div id="titlewrap">
                            <input class="regular-text" type="text" name="mapname" id="title" value="" required>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td></td>

                <td>
                    <input type="hidden" id="existing_map_id" name="map_id" value="">

                    <div id="create-map-buttons-holder">
                        <button id="btnCreateNewMap" type="button" class="button button-primary" data-action="noexit">
                            <?php echo __('Save Map', 'wp2leads'); ?>
                        </button>

                        <button id="btnCreateExitNewMap" type="button" class="button button-primary" data-action="exit">
                            <?php echo __('Save and Exit', 'wp2leads'); ?>
                        </button>

                        <a href="?page=wp2l-admin&tab=map_builder" class="button button-primary">
                            <?php _e( 'Exit Map', 'wp2leads' ) ?>
                        </a>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</section>

<?php include dirname(__FILE__) . '/wp2leads-admin-map_builder-results.php'; ?>