<?php
/**
 * Created by PhpStorm.
 * User: oleksii.khodakivskyi
 * Date: 30.07.18
 * Time: 22:29
 */
$active_mapping = null;

if (!empty($_GET['active_mapping'])) {
    $active_mapping = $_GET['active_mapping'];
} elseif (!empty($_POST['active_mapping'])) {
    $active_mapping = $_POST['active_mapping'];
}

if (!$active_mapping || !$decodedMap) {
    exit;
}

$hide_empty_fields = get_transient('wp2lead_map_to_api_hide_empty_fields');
$start_time = time();
$all_columns = $decodedMap['selects'];
$duration = time() - $start_time;
$excluded_columns = !empty($decodedMap['excludes']) ? $decodedMap['excludes'] : false;
$excluded_columns = apply_filters('wp2l_excluded_columns', $excluded_columns, $decodedMap, $all_columns);

if ($excluded_columns) {
    $map_columns = array_diff($all_columns, $excluded_columns);
} else {
    $map_columns = $all_columns;
}

$available_options_path = $paths;
?>
<h3><?php _e( 'Total entries from Database', 'wp2leads' ) ?> (<span class="rows_count"><?php echo $rows_count ?></span>)</h3>
<div class="options-buttons-wrapper">
    <button class="next button" data-page="<?php echo $next_page ?>" data-direction="forward"><?php _e( 'Older', 'wp2leads' ) ?></button>
    <button class="prev button" disabled data-page="<?php echo $prev_page ?>" data-direction="back"><?php _e( 'Newer', 'wp2leads' ) ?></button>

    <button id="hide_empty_fields" class="button button-primary"<?php echo $hide_empty_fields ? ' style="display:none"' : '' ?>><?php _e( 'Hide empty fields', 'wp2leads' ) ?></button>
    <button id="show_empty_fields" class="button button-primary"<?php echo $hide_empty_fields ? '' : ' style="display:none"' ?>><?php _e( 'Show all fields', 'wp2leads' ) ?></button>
</div>
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
            $option = sanitize_textarea_field($option);
            $option = str_replace('\t', ' ', $option);
            $option = str_replace('\n', ' ', $option);
            $text_option = (strlen($option) < 80) ? $option : substr($option, 0, 39) . '...' . substr($option, -39);
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
    }
    ?>
</div>
