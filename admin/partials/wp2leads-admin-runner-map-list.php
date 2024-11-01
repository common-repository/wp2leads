<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 22.09.18
 * Time: 11:37
 */

$tab = !empty($_GET['tab']) ? $_GET['tab'] : 'map_runner';
$cron_list_maps = Wp2LeadsCron::getScheduledMaps();
?>
<div class="meta-box-sortables available-maps__container">
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
            <?php
            $i = 0;

            if ($maps):
                $current_active_map_id = '0';

                if (!empty($activeMap)) {
                    $current_active_map_id = $activeMap->id;
                }


				// sort maps to have active first
				usort( $maps, function( $a, $b ){
					$current_active_map_id = empty ($_GET['active_mapping']) ? '0' : $_GET['active_mapping'];
					if ( $a['id'] == $current_active_map_id ) return -1;
					return 1;
				});

                foreach ($maps as $map):

                    $cron_list_status = '';
                    $cron_list_title =  __('Cron not set up', 'wp2leads');

                    if (!empty($cron_list_maps['map_' . $map['id']])) {
                        $current_cron_map = $cron_list_maps['map_' . $map['id']];

                        if (!empty($current_cron_map["status_to_change"])) {
                            if ('disable_cron_schedule' === $current_cron_map["status_to_change"]) {
                                $cron_list_status = ' disabled';
                                $cron_list_title = __('Cron disabled', 'wp2leads');
                            }
                        } elseif (!empty( $current_cron_map['status'] )) {
                            $cron_list_status = ' active';
                            $cron_list_title = __('Cron enabled', 'wp2leads');
                        } else {
                            $cron_list_status = ' disabled';
                            $cron_list_title = __('Cron disabled', 'wp2leads');
                        }
                    }

                    if ( !Wp2leads_License::is_map_transfer_allowed($map['id']) ) {
                        $cron_list_status = '';
                    }

                    $map_list_info = unserialize($map['info']);

                    ?>
                    <tr id="map-<?php echo $map['id'] ?>-row" class="<?php echo ($i % 2) ? ' alternate' : ''; echo ($map['id'] == $current_active_map_id) ? ' current ' : '' ?>">
                        <td style="text-align: center">
                            <input id="map_checkbox_<?php echo $map['id'] ?>" class="map_checkbox" type="checkbox" value="<?php echo $map['id'] ?>">
                        </td>
                        <td>
                            <p><?php echo MapBuilderManager::get_clock_icon_for_map($map['id']); ?></p>
                        </td>
                        <td>
                            <p>
                                <a href="?page=wp2l-admin&tab=<?php echo $tab ?>&active_mapping=<?php echo $map['id'] ?>">
                                    <?php echo stripslashes($map['name']); ?><?php echo !empty($map_list_info['serverId']) ? ' <strong>(id '. $map_list_info['serverId'] . ')</strong>' : '';  ?>
                                </a>
								<?php if ($map['id'] == $current_active_map_id) { ?>
									<br>
									<small><?php _e('Active Map', 'wp2leads'); ?></small>
								<?php } ?>
                            </p>
                        </td>

                        <td>
                            <form action="?page=wp2l-admin&tab=map_builder" method="post" style="display: inline">
                                <input type="hidden" name="map_id" value="<?php echo $map['id'] ?>">
                                <p>
									<span class="button-group">
									<a class="button button-small button-success" href="?page=wp2l-admin&tab=map_to_api&active_mapping=<?php echo $map['id'] ?>">
										<?php _e('Map Connect', 'wp2leads') ?>
									</a>
									<?php
										$map_object = MapsModel::get($map['id']);
										$mapping = unserialize($map_object->mapping);

										if ( isset($mapping['form_code']) ) {
											$form_code = explode('_', $mapping['form_code']);

											if ( !empty($mapping['form_code']) ) {
												$form_code = explode('_', $mapping['form_code']);

												if ( $form_code[0] == 'cf' ) { ?>
													<a class="wp2l-map-edit button button-small button-small" href="/wp-admin/admin.php?page=wpcf7&post=<?php echo $form_code[1]; ?>&action=edit">
														<?php _e('Edit Form', 'wp2leads') ?>
													</a>
													<?php
												}
											}
										}
										?>

										<span class="wp2l-map-delete button button-small button-delete button-danger">
											<?php _e('Delete', 'wp2leads') ?>
										</span>
									</span>
                                </p>
                            </form>
                        </td>
                    </tr>
                    <?php
                    $i++;
            endforeach;
            else:
                ?>
                <tr>
                    <td><?php _e('No stored maps', 'wp2leads') ?></td>
                </tr>
                <?php
            endif;
            ?>
            </tbody>
        </table>
    </div>
</div>
