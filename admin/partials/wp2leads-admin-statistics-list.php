<?php
/**
 * Statistic Page List Template
 *
 * @package Wp2Leads/Partials/Statistics
 * @version 1.0.1.7
 * @var $pagination_params
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$pagination = StatisticsManager::get_pagination($pagination_params);

// here we should add all transtales of the Failed subscribers
$failed_strings = array(
	'Failed subscribers',
	'Nicht übertragene',
);

?>

<div class="wp2leads-list-action wp2leads-pagination">
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

        <button
                id="delete_selected_statistics"
                type="button" class="button button-danger"
                data-warningmsg="<?php _e('Are you sure you want to delete selected statistics', 'wp2leads-wtsr'); ?>"
                data-notselectedmsg="<?php _e('Please, select at least one statistic to delete', 'wp2leads-wtsr'); ?>"
        >
            <?php _e('Delete selected', 'wp2leads') ?>
        </button>
    </span>
    <?php echo $pagination; ?>
</div>

<table class="wp-list-table widefat fixed striped pages">
    <thead>
        <tr>
            <td class="manage-column column-cb check-column">
                <label class="screen-reader-text" for="cb-select-all-1"><?php echo __( 'Select All', 'wp2leads' ) ?></label>
                <input id="cb-select-all-1" type="checkbox"/>
            </td>
            <th class="column-primary"><?php _e('Map', 'wp2leads'); ?></th>
            <th><?php _e('Last transferred', 'wp2leads') ?></th>
            <th><?php _e('Transfer type', 'wp2leads') ?></th>
            <th><?php _e('Statistics of the transfer', 'wp2leads') ?></th>
        </tr>
    </thead>

    <tbody id="the-list">
    <?php
    if (is_array($statistics_list)) {
        foreach ($statistics_list as $row) {
            $statistic = json_decode($row['statistics'], true);
            $local_date = StatisticsManager::convertTimeToLocal($row['time']);
            ?>
            <tr>
                <th class="check-column">
                    <input id="cb-select-<?php echo $row["id"]; ?>" type="checkbox" value="<?php echo $row["id"]; ?>">
                </th>

                <td class="column-primary has-row-actions">
                    <?php
                    if (!empty($row['name'])) {
                        ?>
                        <a href="?page=wp2l-admin&tab=map_to_api&active_mapping=<?php echo $row['map_id']?>">
                            <?php echo $row['map_id']?> - <?php echo $row['name']?>
                        </a>
                        <?php
                    } else {
                        ?>
                        <?php _e('This saved statistics refers to a map no longer present in the database.', 'wp2leads') ?>
                        <?php
                    }
                    ?>

                    <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                </td>

                <td data-colname="<?php _e('Last transferred', 'wp2leads') ?>">
                    <?php echo $local_date; ?>
                </td>

                <td data-colname="<?php _e('Transfer type', 'wp2leads') ?>">
                    <?php _e( $row['transfer_type'], 'wp2leads'); ?>
                </td>

                <td data-colname="<?php _e('Statistics of the transfer', 'wp2leads') ?>">
                    <?php
                    if (!empty($row['name'])) {
                        foreach ($statistic as $key => $value) {
                            if (!in_array($key,$failed_strings)) {
                                ?>
                                <div>
                                    <span class="name"><?php _e($key, 'wp2leads'); ?></span> - <span class="value"><?php echo $value ?></span>
                                </div>
                                <?php
                            } else {
                                if (is_array($value)) {
                                    $count = count($value);
                                    $href = '?page=wp2l-admin&tab=statistics&failed_items_list=show&failed_item=' . $row['id'];
                                    $href .= '&active_mapping=' . $row['map_id'];
                                    ?>
                                    <div>
                                        <span class="name"><?php _e($key, 'wp2leads'); ?></span> - <span class="value"><?php echo $count ?></span>
                                        <a href="<?php echo $href ?>"><?php _e('why failed?', 'wp2leads') ?></a>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            <?php
                        }
                    } else {
                        ?>
                        <button class="button button-danger button-small delete-statistic-item" data-statistic-id="<?php echo $row['id'] ?>"><?php _e('Delete item', 'wp2leads') ?></button>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
    }
    ?>

    </tbody>

    <tfoot>
        <tr>
            <td class="manage-column column-cb check-column">
                <label class="screen-reader-text" for="cb-select-all-1"><?php echo __( 'Select All', 'wp2leads' ) ?></label>
                <input id="cb-select-all-1" type="checkbox" />
            </td>
            <th class="column-primary"><?php _e('Map', 'wp2leads'); ?></th>
            <th><?php _e('Last transferred', 'wp2leads') ?></th>
            <th><?php _e('Transfer type', 'wp2leads') ?></th>
            <th><?php _e('Statistics of the transfer', 'wp2leads') ?></th>
        </tr>
    </tfoot>
</table>

<div class="wp2leads-pagination wp2leads-list-action"> <?php echo $pagination; ?></div>
