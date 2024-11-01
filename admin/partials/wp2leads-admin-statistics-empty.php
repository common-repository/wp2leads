<?php
/**
 * Statistic Page Empty Template
 *
 * @package Wp2Leads/Partials/Statistics
 * @version 1.0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wp2leads-pagination wp2leads-list-action">
    <?php
    if (!empty($maps)) {
        ?>
        <form method="get" action="" id="filter_maps_statistics">
            <input type="hidden" name="page" value="wp2l-admin">
            <input type="hidden" name="tab" value="statistics">
            <select name="active_mapping" id="active_mapping" class="form-control">
                <option><?php _e('-- Select Map --', 'wp2leads') ?></option>
                <?php
                foreach ($maps as $statistic_map_id_select => $statistic_map_select) {
                    ?>
                    <option value="<?php echo $statistic_map_id_select ?>"<?php echo (!empty($statistic_map_id) && $statistic_map_id == $statistic_map_id_select) ? ' selected' : ''; ?>>
                        <?php echo $statistic_map_id_select . ' - ' . $statistic_map_select['name'] ?>
                    </option>
                    <?php
                }
                ?>
            </select>
        </form>
        <?php
    }
    ?>
    <span class="buttons-holder">
    <?php
    if ($statistic_map_id || !empty($_GET['no_map'])) {
        ?>
        <a class="button button-primary" href="?page=wp2l-admin&tab=statistics">
            <?php _e('Statistics for all maps', 'wp2leads') ?>
        </a>
        <?php
    }

    if (empty($_GET['no_map'])) {
        $deleted_maps_statistics = StatisticsModel::getList(array('no_map' => 1));

        if (!empty(count($deleted_maps_statistics))) {
            ?>
            <a href="?page=wp2l-admin&tab=statistics&no_map=1" class="button button-primary">
                <?php _e('Statistics for deleted maps', 'wp2leads') ?> (<?php echo count($deleted_maps_statistics); ?>)
            </a>
            <?php
        }
    }
    ?>
    <a href="?page=wp2l-admin&tab=map_to_api<?php echo $activeMap ? '&active_mapping=' . $activeMap->id : '' ?>" class="button button-primary">
        <?php _e('Start transfer now', 'wp2leads') ?>
    </a>
    </span>

</div>

<table class="wp-list-table widefat fixed striped pages">
    <thead>
    <tr>
        <td class="manage-column column-cb check-column">
            <label class="screen-reader-text" for="cb-select-all-1"><?php echo __( 'Select All', 'wp2leads' ) ?></label>
            <input id="cb-select-all-1" type="checkbox" style="display: none" />
        </td>
        <th class="column-primary"><?php _e('Map', 'wp2leads'); ?></th>
        <th><?php _e('Last transferred', 'wp2leads') ?></th>
        <th><?php _e('Transfer type', 'wp2leads') ?></th>
        <th><?php _e('Statistics of the transfer', 'wp2leads') ?></th>
    </tr>
    </thead>

    <tbody id="the-list">
        <tr>
            <td colspan="5"><?php _e('You do not have any transfering data.', 'wp2leads') ?></td>
        </tr>
    </tbody>

    <tfoot>
    <tr>
        <td class="manage-column column-cb check-column">
            <label class="screen-reader-text" for="cb-select-all-1"><?php echo __( 'Select All', 'wp2leads' ) ?></label>
            <input id="cb-select-all-1" type="checkbox" style="display: none" />
        </td>
        <th class="column-primary"><?php _e('Map', 'wp2leads'); ?></th>
        <th><?php _e('Last transferred', 'wp2leads') ?></th>
        <th><?php _e('Transfer type', 'wp2leads') ?></th>
        <th><?php _e('Statistics of the transfer', 'wp2leads') ?></th>
    </tr>
    </tfoot>
</table>
