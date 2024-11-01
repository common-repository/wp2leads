<?php
/**
 * Created by PhpStorm.
 * User: oleksii.khodakivskyi
 * Date: 30.07.18
 * Time: 22:29
 */

if (!empty($_GET['active_mapping'])) {
    $active_mapping = $_GET['active_mapping'];
} elseif (!empty($_POST['active_mapping'])) {
    $active_mapping = $_POST['active_mapping'];
}

if (!$active_mapping || !$decodedMap) {
    exit;
}

$hide_empty_fields = get_transient('wp2lead_map_to_api_hide_empty_fields');

?>
<h3><?php _e( 'Total entries from Database', 'wp2leads' ) ?> (<span class="rows_count"><?php _e( 'Counting...', 'wp2leads' ) ?></span>)</h3>
<div class="options-buttons-wrapper">
    <button class="next button" data-direction="forward" disabled="disabled"><?php _e( 'Older', 'wp2leads' ) ?></button>
    <button class="prev button" data-direction="back" disabled="disabled"><?php _e( 'Newer', 'wp2leads' ) ?></button>

    <button id="hide_empty_fields" class="button button-primary"<?php echo $hide_empty_fields ? ' style="display:none"' : '' ?> disabled="disabled"><?php _e( 'Hide empty fields', 'wp2leads' ) ?></button>
    <button id="show_empty_fields" class="button button-primary"<?php echo $hide_empty_fields ? '' : ' style="display:none"' ?> disabled="disabled"><?php _e( 'Show all fields', 'wp2leads' ) ?></button>
</div>
<div class="api-processing-holder">
    <div id="inputFiledSearchOption">
        <input style="width: 100%; margin: 10px 0px;" type="text" placeholder="<?php _e( 'Enter option name', 'wp2leads' ) ?>" disabled="disabled">
    </div>
    <div class="available_option_list<?php echo $hide_empty_fields ? ' hide-empty-options' : '' ?>">
        <?php

        foreach ($map_columns as $column) {
            ?>
            <div class="available_option">
                <label ><?php echo $column ?> (<?php _e( 'Counting...', 'wp2leads' ) ?>)</label>
            </div>
            <?php
        }
        ?>
    </div>

    <div class="api-spinner-holder api-processing">
        <div class="api-spinner"></div>
    </div>
</div>
