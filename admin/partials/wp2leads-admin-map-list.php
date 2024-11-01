<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 22.09.18
 * Time: 11:37
 */

$tab = !empty($_GET['tab']) ? $_GET['tab'] : 'map_runner';
$cron_maps = Wp2LeadsCron::getScheduledMaps();
?>
<div class="meta-box-sortables available-maps__container">
    <div class="postbox">
        <h3><span><?php _e('Available Maps', 'wp2leads') ?></span></h3>

        <div class="action_panel" style="padding: 5px 10px">
            <?php _e('Action', 'wp2leads') ?>:
            <select id="available-maps_actions">
                <option value=""><?php _e('-- Select --', 'wp2leads') ?></option>
                <option value="delete_selected"><?php _e('Delete Selected', 'wp2leads') ?></option>
                <option value="delete_all"><?php _e('Delete All', 'wp2leads') ?></option>
            </select>
            <button id="available-maps_actions-run" type="button" class="button disabled"><?php _e('Run', 'wp2leads') ?></button>
        </div>

        <div class="inside">
            <div class="available-maps_table-wrap">
                <table class="widefat available-maps__table">
                    <thead>
                    <tr>
                        <th>
                            <input id="map_checkbox_all" type="checkbox" style="display: none">
                        </th>
                        <th></th>
                        <th style="text-align: left;"><?php _e('Map', 'wp2leads') ?></th>
                        <th><?php _e('Actions', 'wp2leads') ?></th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php $i = 0; ?>
                    <?php if ($maps): ?>
                        <?php foreach ($maps as $map): ?>
                            <?php
                            $cron_status = '';
                            $cron_title =  __('Cron not set up', 'wp2leads');

                            if (!empty($cron_maps['map_' . $map['id']])) {
                                $current_cron_map = $cron_maps['map_' . $map['id']];

                                if (!empty( $current_cron_map['status'] )) {
                                    $cron_status = ' active';
                                    $cron_title = __('Cron enabled', 'wp2leads');
                                } else {
                                    $cron_status = ' disabled';
                                    $cron_title = __('Cron disabled', 'wp2leads');
                                }
                            }

                            if ( !Wp2leads_License::is_map_transfer_allowed($map['id']) ) {
                                $cron_status = '';
                            }
                            ?>
                            <tr id="map-<?php echo $map['id'] ?>-row" <?php echo ($i % 2) ? ' class="alternate"' : '' ?>>
                                <td style="text-align: center">
                                    <input id="map_checkbox_<?php echo $map['id'] ?>" class="map_checkbox" type="checkbox" value="<?php echo $map['id'] ?>">
                                </td>
                                <td>
                                    <p><span class="dashicons dashicons-clock<?php echo $cron_status ?>" title="<?php echo $cron_title ?>"></span></p>
                                </td>
                                <td>
                                    <p>
                                        <a href="?page=wp2l-admin&tab=<?php echo $tab ?>&active_mapping=<?php echo $map['id'] ?>">
                                            <?php echo stripslashes($map['name']); ?>
                                        </a>
                                    </p>
                                </td>

                                <td>
                                    <form action="?page=wp2l-admin&tab=map_builder" method="post" style="display: inline">
                                        <input type="hidden" name="map_id" value="<?php echo $map['id'] ?>">
                                        <p>
                                        <span class="button-group">
                                            <a class="wp2l-map-edit button button-small" href="?page=wp2l-admin&tab=map_builder&active_mapping=<?php echo $map['id'] ?>">
                                                <?php _e('Edit', 'wp2leads') ?>
                                            </a>

                                            <span class="wp2l-map-delete button button-small button-delete button-danger">
                                                <?php _e('Delete', 'wp2leads') ?>
                                            </span>
                                        </span>
                                        </p>
                                    </form>
                                </td>
                            </tr>
                            <?php $i++; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td><?php _e('No stored maps', 'wp2leads') ?></td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
