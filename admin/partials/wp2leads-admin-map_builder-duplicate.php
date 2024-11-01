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
 * @var $decodedMap
 * @var $mapForDuplicate
 * @var $tables
 * @var $columns
 */

$is_create_own_map_allowed = Wp2leads_License::is_action_allowed('create_own_map');
$is_use_own_map_allowed = Wp2leads_License::is_action_allowed('use_own_map');
$change_owner = !empty($_GET['change_owner']) ? sanitize_text_field($_GET['change_owner']) : false;

$mapValid = false;

if ( $decodedMap ) {
    // ensure that all tables that this map refers to are present.

    // gather all tables in the map
    $tablesToCheck = array();
    array_push($tablesToCheck, $decodedMap['from']);

    if ( !empty( $decodedMap['joins'] ) ) {
        foreach ( $decodedMap['joins'] as $join ) {
            array_push( $tablesToCheck, $join['joinTable'] );
        }
    }

    // compare existing tables against the map tables
    $mapValid = true;

    foreach ($tablesToCheck as $checkMe) {
        if ( !in_array( MapsModel::unindexed_table_name($checkMe), $tables ) ) {
            $mapValid = false;
            break;
        }
    }

    if ($mapValid) {
        $duplicatedInfo = unserialize($mapForDuplicate->info);
    }
}
?>

<?php
if (!$mapValid) {
    ?>
    <h3 class="title"><?php echo $mapForDuplicate->name ?></h3>

    <div class="notice notice-error inline">
        <p>
            <?php _e('This saved map refers to a table no longer present in the database.', 'wp2leads') ?>

            <a href="?page=wp2l-admin&tab=map_runner" class="button button-primary">
                <?php _e( 'Exit Map', 'wp2leads' ) ?>
            </a>
        </p>
    </div>
    <?php
} else {
    ?>
    <?php if (!$is_use_own_map_allowed): ?>
        <div class="notice notice-warning inline">
            <h4><?php _e('Full Map adjustment only in paid versions', 'wp2leads') ?></h4>

            <p><?php _e('Please upgrade to Professional Version to transfer user via OWN created maps.', 'wp2leads') ?> <a href="https://wp2leads.com" target="_blank"><?php _e('Click here!', 'wp2leads') ?></a></p>
        </div>
    <?php endif; ?>

    <?php include dirname(__FILE__) . '/wp2leads-admin-map_builder-headstart.php'; ?>

    <h2 id="wp2l-create-map-header">
        <?php _e('Duplicate Map', 'wp2leads') ?>
    </h2>

    <hr>

    <section id="create-map-section">
        <form id="map-generator" action="" method="post">
            <input type="hidden" class="mapping" value='<?php echo json_encode( $decodedMap ); ?>'>

            <table class="form-table">
                <tr class="alternate">
                    <td><?php _e('Duplicated Map', 'wp2leads') ?></td>

                    <td><strong><?php echo $mapForDuplicate->name ?></strong></td>
                </tr>

                <tr>
                    <td><?php _e('Utilities', 'wp2leads') ?></td>

                    <td>
                        <?php
                        if ( $is_use_own_map_allowed ) {
                            ?>
                            <span id="reset-map-builder-form" class="button button-small">
                                <?php _e('Start Over', 'wp2leads') ?>
                            </span>
                            <?php
                        }
                        ?>
                    </td>
                </tr>

                <tr class="alternate">
                    <td>
                        <label for="from-table"><?php _e('Starter Data', 'wp2leads') ?></label>
                    </td>

                    <td class="api-processing-holder">
                        <?php
                        $selected = false;
                        $selected_starter = '';
                        $disabled_params = $is_use_own_map_allowed ? '' : ' style="display:none" disabled';
                        ?>
                        <div style="width:100%;max-width:400px;">
                            <select data-previousValue="" name="from-table" id="from-table" class="wp2l_starter_data form-control"<?php echo $disabled_params ?>>
                                <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>

                                <?php
                                foreach ($tables as $table):
                                    $selected = $table == $decodedMap['from'];
                                    ?>
                                    <option value="<?php echo $table; ?>"<?php echo $selected ? ' selected' : ''; ?>>
                                        <?php echo $table; ?>
                                    </option>
                                    <?php
                                    if ( $selected ) $selected_value = $table;
                                endforeach;
                                ?>
                            </select>
                        </div>
                        <?php if (!$is_use_own_map_allowed) echo '<strong style="display:inline-block;margin-left:15px">' .$selected_value .'</strong>'; ?>

                        <div class="api-spinner-holder api-processing">
                            <div class="api-spinner"></div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td><?php _e('Group Results by', 'wp2leads') ?></td>

                    <td class="api-processing-holder">
                        <div style="width:100%;max-width:400px;">
                            <select data-current_value="<?php echo !empty( $decodedMap['keyBy'] ) ? $decodedMap['keyBy'] : '' ?>"
                                    name="group-map-by-key"
                                    id="wp2l-group-map-results-by"<?php echo $is_use_own_map_allowed ? ' class="form-control"' : ' class="disabled form-control" disabled' ?>
                            ></select>

                            <input type="checkbox"
                                   id="disable-grouping"
                                   name="disable-grouping"
                                   style="display: none"
                                <?php echo !empty( $decodedMap['disableGrouping'] ) ? $decodedMap['disableGrouping'] : '' ?>
                            >

                            <label for="disable-grouping"  style="display: none"><?php _e('Disable', 'wp2leads') ?></label>
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
                        <div id="relationship-map-holder"<?php echo $is_use_own_map_allowed ? '' : ' class="disabled"' ?>
                             data-current_values='<?php echo ( !empty( $decodedMap['joins'] ) ) ? json_encode($decodedMap['joins'] ) : '' ?>'>
                        </div>

                        <?php
                        if ( $is_use_own_map_allowed ) {
                            ?>
                            <p><a href="#" class="button" id="add-new-relationship-map">+ <?php _e('Add', 'wp2leads') ?></a></p>
                            <?php
                        }
                        ?>
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
                            <div class="virtual-relationship-list">
                                <?php if(isset($decodedMap['virtual_relationships']) && count($decodedMap['virtual_relationships']) > 0): ?>
                                    <?php foreach($decodedMap['virtual_relationships'] as $vr): ?>
                                        <div class="virtual-relationship">
                                            <div class="relationship">
                                                <?php _e('Map', 'wp2leads') ?>
                                                <select data-current_value="<?php echo $vr['table_from'] ?>" name="table_from" class="virtual-table_from" title=""<?php echo $is_use_own_map_allowed ? '' : ' disabled' ?>>
                                                    <?php foreach($tables as $table): ?>
                                                        <option value="<?php echo $table ?>" <?php echo ($vr['table_from'] == $table ? 'selected' : ''); ?>><?php echo $table; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                . <select data-current_value="<?php echo $vr['column_from'] ?>" name="column_from" class="virtual-column_from" title=""<?php echo $is_use_own_map_allowed ? '' : ' disabled' ?>>
                                                    <?php foreach($columns[$vr['table_from']] as $column): ?>
                                                        <option value="<?php echo $column ?>" <?php echo ($vr['column_from'] == $column ? 'selected' : ''); ?>><?php echo $column; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php _e('To', 'wp2leads') ?>
                                                <select data-current_value="<?php echo $vr['table_to'] ?>" name="table_to" class="virtual-table_to" title=""<?php echo $is_use_own_map_allowed ? '' : ' disabled' ?>>
                                                    <?php foreach($tables as $table): ?>
                                                        <option value="<?php echo $table ?>" <?php echo ($vr['table_to'] == $table ? 'selected' : ''); ?>><?php echo $table; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                . <select data-current_value="<?php echo $vr['column_to'] ?>" name="column_to" class="virtual-column_to" title=""<?php echo $is_use_own_map_allowed ? '' : ' disabled' ?>>
                                                    <?php foreach($columns[$vr['table_to']] as $column): ?>
                                                        <option value="<?php echo $column ?>" <?php echo ($vr['column_to'] == $column ? 'selected' : ''); ?>><?php echo $column; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="column">
                                                <?php _e('Column is', 'wp2leads') ?>:

                                                <?php
                                                $selected = false;
                                                $selected_column = '';
                                                $disabled_params = $is_use_own_map_allowed ? '' : ' disabled';
                                                ?>

                                                <select data-current_value="" name="column_key" class="virtual-column_key" title=""<?php echo $disabled_params; ?>>
                                                    <?php
                                                    foreach($columns[$vr['table_to']] as $column):
                                                        $selected = $vr['column_key'] == $vr['table_to'] . '.' . $column;
                                                        ?>
                                                        <option value="<?php echo $vr['table_to'] . '.' . $column ?>"<?php echo $selected ? ' selected' : ''; ?>>
                                                            <?php echo $vr['table_to'] . '.' . $column; ?>
                                                        </option>
                                                        <?php
                                                        if ( $selected ) $selected_value = $vr['table_to'] . '.' . $column;
                                                    endforeach;
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="values">
                                                <?php _e('Value is', 'wp2leads') ?>:

                                                <?php
                                                $selected = false;
                                                $selected_value = '';
                                                $disabled_params = $is_use_own_map_allowed ? '' : ' disabled'
                                                ?>

                                                <select data-current_value="" name="column_value" class="virtual-column_value" title=""<?php echo $disabled_params ?>>
                                                    <?php
                                                    foreach($columns[$vr['table_to']] as $column):
                                                        $selected = $vr['column_value'] == $vr['table_to'] . '.' . $column;
                                                        ?>
                                                        <option value="<?php echo $vr['table_to'] . '.' . $column ?>" <?php echo ($selected ? 'selected' : ''); ?>>
                                                            <?php echo $vr['table_to'] . '.' . $column; ?>
                                                        </option>
                                                        <?php
                                                        if ( $selected ) $selected_value = $vr['table_from'] . '.' . $column;
                                                    endforeach;
                                                    ?>
                                                </select>
                                            </div>

                                            <?php
                                            if ($is_use_own_map_allowed) {
                                                ?>
                                                <div class="submenu">
                                                    <span data-action="remove-virtual-relationship" class="button remove-virtual-relationship"><?php _e('Remove', 'wp2leads') ?></span>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <?php
                            if ($is_use_own_map_allowed) {
                                ?>
                                <div class="virtual-relationship-bottom-menu">
                                    <a href="#" id="virtual-relationship-button-add" class="button">+ <?php _e('Add', 'wp2leads') ?></a>
                                </div>
                                <?php
                            }
                            ?>
                        </div>

                        <div class="api-spinner-holder api-processing">
                            <div class="api-spinner"></div>
                        </div>
                    </td>
                </tr>

                <tr class="alternate">
                    <td><?php _e('Add Comparison', 'wp2leads') ?></td>

                    <td class="api-processing-holder">
                        <div id="column-comparison-holder"
                             data-current_values='<?php echo !empty( $decodedMap['comparisons'] ) ? json_encode( $decodedMap['comparisons'] ) : '' ?>'
                        ></div>

                        <p><a href="#" class="button" id="add-new-comparison-map">+ <?php _e('Add', 'wp2leads') ?></a></p>

                        <div class="api-spinner-holder api-processing">
                            <div class="api-spinner"></div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td><?php _e('Concat results for', 'wp2leads') ?></td>

                    <td class="api-processing-holder">
                        <select style="width: 100%; max-width: 400px; height: 200px" name="group-concat" id="wp2l-group-concat-for"
                                multiple
                                data-current_value='<?php echo !empty( $decodedMap['groupConcat'] ) ? json_encode( $decodedMap['groupConcat'], JSON_FORCE_OBJECT ) : '' ?>'
                            <?php echo $is_use_own_map_allowed ? '' : ' disabled' ?>
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
                                value="<?php echo !empty( $decodedMap['groupConcatSeparator'] ) ? $decodedMap['groupConcatSeparator'] : ','; ?>"
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
                        <select style="width: 100%; max-width: 400px; height: 200px" name="date-time-columns" id="wp2l-date-time-columns"
                                multiple
                                data-current_value='<?php echo !empty( $decodedMap['dateTime'] ) ? json_encode( $decodedMap['dateTime'], JSON_FORCE_OBJECT ) : '' ?>'
                            <?php echo $is_use_own_map_allowed ? '' : ' disabled' ?>
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
                        <div id="excludedColumnsFilter_container"
                             data-current_value='<?php echo !empty( $decodedMap["excludesFilters"] ) ? json_encode( $decodedMap["excludesFilters"], JSON_FORCE_OBJECT ) : '' ?>'
                        >
                            <?php
                            if (!empty($decodedMap["excludesFilters"])) {
                                foreach ($decodedMap["excludesFilters"] as $filter) {
                                    ?>
                                    <div class="created-filter">
                                        <span class="filter-name"><?php echo $filter ?></span>
                                        <span class="filter-close-btn"></span>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>

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
                        <select style="width: 100%; max-width: 400px; height: 200px" name="column-options" id="wp2l-column-options"
                                multiple
                                data-current_values='<?php echo !empty( $decodedMap['selects'] ) ? json_encode( $decodedMap['selects'] ) : '' ?>'
                        ></select>

                        <div class="api-spinner-holder api-processing">
                            <div class="api-spinner"></div>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
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
                            <?php
                            if ($change_owner) {
                                $new_title = $mapForDuplicate->name;
                            } else {
                                $new_title = $mapForDuplicate->name . __( ' (Copy)', 'wp2leads' );
                            }
                            ?>
                            <input class="regular-text" type="text" name="mapname" id="title" value="<?php echo $new_title; ?>" required>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td></td>

                <td>
                    <input type="hidden" id="existing_map_id" name="map_id" value="">

                    <div id="create-map-buttons-holder">
                        <p>
                            <button id="btnCreateNewMap" type="button" class="button button-primary" data-action="noexit">
                                <?php echo __('Save Map', 'wp2leads'); ?>
                            </button>

                            <button id="btnCreateExitNewMap" type="button" class="button button-primary" data-action="exit">
                                <?php echo __('Save and Exit', 'wp2leads'); ?>
                            </button>

                            <a href="?page=wp2l-admin&tab=map_builder" class="button button-primary">
                                <?php _e( 'Exit Map', 'wp2leads' ) ?>
                            </a>
                        </p>

                        <?php
                        if ($change_owner) {
                            ?>
                            <input type="hidden" id="deleteOriginalMap" name="deleteOriginalMap" value="<?php echo $mapForDuplicate->id ?>">
                            <?php
                        } else {
                            ?>
                            <input type="hidden" id="originalMap" name="originalMap" value="<?php echo $mapForDuplicate->id ?>">
                            <?php
                        }
                        ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </section>

    <?php include dirname(__FILE__) . '/wp2leads-admin-map_builder-results.php'; ?>
    <?php
}
