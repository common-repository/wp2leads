<?php
/**
 * Available option template
 *
 * @var $map_id
 * @var $decodedMap
 * @var $paths
 * @var $rows_count
 * @var $next_page
 * @var $prev_page
 */

if (!$map_id || !$decodedMap) {
    exit;
}

$hide_empty_fields = get_transient('wp2lead_map_to_api_hide_empty_fields');
$start_time = time();
$fetch_all_columns = MapsModel::fetch_all_columns_for_map(array('is_new_map' => true, 'new_map' => $decodedMap));
$all_columns = array_merge($decodedMap['selects'], $fetch_all_columns);
$duration = time() - $start_time;
$excluded_columns = !empty($decodedMap['excludes']) ? $decodedMap['excludes'] : false;
$excluded_columns = apply_filters('wp2l_excluded_columns', $excluded_columns, $decodedMap, $all_columns);
if (!empty($decodedMap["excludesFilters"]) && $excluded_columns) {
    foreach ($excluded_columns as $index => $excluded_column) {
        foreach ($decodedMap["excludesFilters"] as $excludes_filter) {
            if (false !== strpos($excluded_column, $excludes_filter)) {
                unset($excluded_columns[$index]);
                $all_columns[] = $excluded_column;
            }
        }
    }
}

$all_columns = array_unique($all_columns);

if ($excluded_columns) {
    $map_columns = array_diff($all_columns, $excluded_columns);
} else {
    $map_columns = $all_columns;
}

if (isset($decodedMap['selects_only']) && is_array($decodedMap['selects_only'])) {
    foreach ($map_columns as $index => $column) {
        if (!empty($decodedMap["excludesFilters"]) && false !== strpos($column, $excludes_filter)) {
            continue;
        }
        if (
            !in_array($column, $decodedMap['selects_only'])
        ) {
            unset($map_columns[$index]);
        }
    }
}

$map_columns = apply_filters('wp2leads_available_options_map_columns', $map_columns, $decodedMap);

$available_options_path = $paths;
$replacements_table = !empty($decodedMap['replace_table']) ? $decodedMap['replace_table'] : array();
?>
<h3><?php _e( 'Total entries from Database', 'wp2leads' ) ?> (<span class="rows_count"><?php echo $rows_count ?></span>)</h3>
<div class="options-buttons-wrapper">
    <button class="next button" data-page="<?php echo $next_page ?>" data-direction="forward"><?php _e( 'Older', 'wp2leads' ) ?></button>
    <button class="prev button" disabled data-page="<?php echo $prev_page ?>" data-direction="back"><?php _e( 'Newer', 'wp2leads' ) ?></button>

    <button id="hide_empty_fields" class="button button-primary"<?php echo $hide_empty_fields ? ' style="display:none"' : '' ?>><?php _e( 'Hide empty fields', 'wp2leads' ) ?></button>
    <button id="show_empty_fields" class="button button-primary"<?php echo $hide_empty_fields ? '' : ' style="display:none"' ?>><?php _e( 'Show all fields', 'wp2leads' ) ?></button>
</div>
<div class="api-processing-holder">
    <div id="inputFiledSearchOption">
        <input style="width: 100%; margin: 10px 0px;" type="text" placeholder="<?php _e( 'Enter option name', 'wp2leads' ) ?>">
    </div>
    <div class="available_option_list<?php echo $hide_empty_fields ? ' hide-empty-options' : '' ?>">
        <?php

        foreach ($map_columns as $column) {
            $column_path = null;

            foreach ($available_options_path as $key => $value) {
                $value_array = explode(' (', $value);
                $table_column = $value_array[0];
                unset($value_array[0]);
                $path_option = implode(' (', $value_array);
                $table_column = str_replace('(concatenated)', '', $table_column);

                if ($table_column === $column && "" !== trim(str_replace(')', '', $path_option))) {
                    $column_path = $value;
                    unset($available_options_path[$key]);

                    continue;
                }
            }

            if ($column_path) {
                $value_array = explode(' (', $column_path);
                $table_column = $value_array[0];
                unset($value_array[0]);
                $option = implode(' (', $value_array);
                $option = substr($option, 0, -1);
                $option = str_replace('&#167;', '', $option);
                $option = str_replace('&#xa7;', '', $option);
                $option = str_replace('&sect;', '', $option);
                $option = str_replace('§', '', $option);
                $option = str_replace('&#38;', '+', $option);
                $option = str_replace('&amp;', '+', $option);
                $option = str_replace('&', '+', $option);

				if (isset($replacements_table[$table_column])) $option = str_replace(trim($replacements_table[$table_column]), '', $option);
				$option = trim($option);
				$option = sanitize_textarea_field($option);
                $option = str_replace('\t', ' ', $option);
                $option = str_replace('\n', ' ', $option);

                $text_option = (strlen($option) < 80) ? $option : substr($option, 0, 39) . '...' . substr($option, -39);

				if ($option) {
                ?>
                <div class="available_option">
                    <label data-table-column="<?php echo $table_column ?>" data-value='<?php echo $option ?>'><?php echo $table_column ?> (<?php echo $text_option ?>)</label>
                </div>
                <?php
				} else {
				?>
				<div class="available_option empty_option">
                    <label data-table-column="<?php echo $column ?>" data-value=''><?php echo $column ?> (<?php _e( 'No value for this user', 'wp2leads' ) ?>)</label>
                </div>
				<?php
				}
            } else {
                ?>
                <div class="available_option empty_option">
                    <label data-table-column="<?php echo $column ?>" data-value=''><?php echo $column ?> (<?php _e( 'No value for this user', 'wp2leads' ) ?>)</label>
                </div>
                <?php
            }
        }
        ?>
    </div>

    <div class="api-spinner-holder">
        <div class="api-spinner"></div>
    </div>
</div>
