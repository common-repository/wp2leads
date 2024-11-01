<?php
/**
 * Template for displaying Import/Export tab content
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?>
<h2><?php _e('Maps on Server', 'wp2leads') ?></h2>
<?php

$wp2l_no_map_server_response = get_transient('wp2l_no_map_server_response');

if ($wp2l_no_map_server_response) {
    ?>
    <div class="notice notice-error inline">
        <h3><?php _e('No response from map server', 'wp2leads') ?></h3>

        <p>
            <strong><?php _e('Possible reason', 'wp2leads') ?>:</strong> <?php _e('Your web hosting is blocking the connection to our server so we can not load catalog items. Please write your web hosting and let them add the full URL https://maps.wp2leads.com/server/maps.php to the server whitelist. Sorry for the trouble.', 'wp2leads') ?>
        </p>
    </div>
    <?php
    return;
}

$rplugins = new Wp2leads_RequiredPlugins();
$is_export_allowed = Wp2leads_License::is_export_allowed();
$maps_from_server = MapBuilderManager::get_available_maps_from_server();
$maps_pending_from_server = Wp2leads_License::is_dev_allowed() ? MapBuilderManager::get_pending_maps_from_server() : false;
$current_owner_hash = MapBuilderManager::get_current_map_owner_hash();
$is_user_pro = Wp2leads_License::is_user_level('pro');

$maps_from_server_info = array();

foreach ($maps_from_server as $map_from_server) {
	$maps_from_server_info[] = $map_from_server['map_id'];
	$maps_meta[$map_from_server['map_id']] = MapBuilderManager::get_map_meta_from_server($map_from_server['map_id']);
}

$maps_from_server_info = MapBuilderManager::get_maps_info_from_server($maps_from_server_info);
?>

<hr>
<?php

if ($maps_pending_from_server) {
    ?>
    <div class="import-pending-maps-from-server__section">
        <h3><?php _e( 'Pending Maps', 'wp2leads' ) ?></h3>

        <div class="import-pending-maps-from-server__holder" style="width: 100%;max-width: 800px">
            <form id="import-pending-maps-from-server-form">
                <div class="import-maps-from-server-table-wrap">
                    <table id="import-maps-from-server-table" class="thead-responsive-table import-maps-from-server-table" style="width: 100%;min-width: 700px">
                        <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th><?php _e('Map', 'wp2leads') ?></th>
                            <th><?php _e('Map Version', 'wp2leads') ?></th>
                            <th><?php _e('Required Plugins', 'wp2leads'); ?></th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php

                        $show_notice = false;
                        foreach ($maps_pending_from_server as $map_from_server) {
                            $required_plugins = $rplugins->check_map_plugins($map_from_server['id']);
                            $recommend_plugins = $rplugins->check_map_recommends($map_from_server['id']);

                            if ($required_plugins) $show_notice = true;
                            ?>
                            <tr>
                                <td class="map-owner_column" style="width: 24px;text-align: center">
                                    <?php
                                    if ($current_owner_hash === $map_from_server['owner_hash']) {
                                        ?>
                                        <span class="dashicons dashicons-admin-users wp2lead-tip" data-tip="<?php _e( 'Ovid', 'wp2leads' ) ?>"></span>
                                        <?php
                                    }
                                    ?>
                                </td>
                                <td style="width: 30px;text-align: center">
                                    <input
                                        name="mapids[]"
                                        class="map-public-ids"
                                        type="radio"
                                        id="mappublicid_<?php echo $map_from_server['map_id']; ?>"
                                        value="<?php echo $map_from_server['map_id']; ?>"
                                        data-map_hash="<?php echo $map_from_server['map_hash']; ?>"
                                        data-owner_hash="<?php echo $map_from_server['owner_hash']; ?>"
                                        <?php if ($required_plugins || $recommend_plugins) echo 'readonly="readonly"'; ?>
                                        data-map_id="<?php echo $map_from_server['id'] ?>"
                                    />
                                </td>

                                <td>
                                    <strong><?php echo $map_from_server['name']; ?></strong>
                                    <?php
                                    if (!empty($map_from_server['map_version'])) {
                                        ?>
                                        <small>v. <?php echo $map_from_server['map_version']; ?></small>
                                        <?php
                                    }

                                    ?>
                                </td>

                                <td style="text-align:center;">
                                    <?php
                                    $map_from_server_kind = $map_from_server['map_kind'];

                                    if ($map_from_server_kind == "pro") {
                                        _e('Pro', 'wp2leads');
                                    } elseif ($map_from_server_kind == "essent") {
                                        _e('Essential', 'wp2leads');
                                    } elseif ($map_from_server_kind == "free") {
                                        _e('Free', 'wp2leads');
                                    }
                                    ?>
                                </td>

                                <td class="required-plugins" style="text-align:center;">
                                    <?php
                                    if ($required_plugins) {
                                        foreach ($required_plugins as $p) {
                                            ?>
                                            <div style="white-space: nowrap; text-align: center;">
                                                <a href="<?php echo $p['link']; ?>" target="_blank"><?php echo $p['label']; ?></a>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>

                <p class="submit">
                    <button id="import_from_remote" type="submit" class="button button-primary">
                        <?php _e('Import from Map Server', 'wp2leads') ?>
                    </button>
                </p>
            </form>
        </div>
    </div>
    <?php
}


if (!$maps_from_server) {
    ?>
    <div class="wp2leads-notice wp2leads-notice-warning">
        <h4><?php _e('No available maps on server', 'wp2leads') ?></h4>
    </div>
    <?php
} else {
    ?>
    <div class="import-maps-from-server__holder" style="width: 100%;max-width: 900px">
        <form id="import-maps-from-server-form">
            <div class="import-maps-from-server-table-wrap api-processing-holder">
                <table id="import-maps-from-server-table" class="thead-responsive-table import-maps-from-server-table" style="width: 100%;min-width: 700px">
                    <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="wp2l_select_all_import_maps" style="display:none;" />
                        </th>
                        <th><?php _e('Map', 'wp2leads') ?></th>
                        <th colspan="3"><?php _e('Map Version', 'wp2leads') ?></th>
						<th><?php _e('Required Plugins', 'wp2leads'); ?></th>
						<th><?php _e('KT import campaign links', 'wp2leads'); ?></th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php
					$magic_maps = new Wp2leads_MagicImport();
					$show_notice = false;

                    foreach ($maps_from_server as $map_from_server) {
                        $map_from_server_kind = $map_from_server['map_kind'];
                        $is_exclusive = !empty($map_from_server['is_exclusive']) ? $map_from_server['is_exclusive'] : false;
                        $sale_link = false;

						$required_plugins = $rplugins->check_map_plugins($map_from_server['id']);
						$recommend_plugins = $rplugins->check_map_recommends($map_from_server['id']);

						if ($required_plugins) $show_notice = true;

						$magic_map = $magic_maps->is_have_magic($map_from_server['id']);


                        if ($is_exclusive && '1' === $is_exclusive) {
                            $is_exclusive = true;
                        }

                        if (!empty($map_from_server['sale_link'])) {
                            $sale_link = $map_from_server['sale_link'];
                        }

                        $is_map_from_server_can_be_transfered = MapBuilderManager::is_map_on_server_can_be_transfered($wp2l_current_version, $map_from_server_kind);

						$kt_link = '';

						if (!empty($maps_meta[$map_from_server['map_id']]['kt_url'])) {
							$links = unserialize($maps_meta[$map_from_server['map_id']]['kt_url']);
							if (is_array($links)) {
								foreach ($links as $link) {
									$kt_link .= '<a href="'.$link.'" style="display:block;" target="_blank">'.$link.'</a>';
								}
							} else {
								$kt_link .= '<a href="'.$links.'" style="display:block;" target="_blank">'.$links.'</a>';
							}
						}

                        ?>
                        <tr>
                            <td style="text-align: center">
                                <?php
                                if (!$is_exclusive && !$magic_map) {
                                    ?>
                                    <input
                                            name="mapids[]"
                                            class="map-public-ids"
                                            type="radio"
                                            id="mappublicid_<?php echo $map_from_server['map_id'] ?>"
                                            value="<?php echo $map_from_server['map_id'] ?>"
                                            data-map_hash="<?php echo $map_from_server['map_hash'] ?>"
                                            data-owner_hash="<?php echo $map_from_server['owner_hash'] ?>"
											<?php if ($required_plugins || $recommend_plugins) echo 'readonly="readonly"'; ?>
											data-map_id="<?php echo $map_from_server['id'] ?>"
                                    />
                                    <?php
                                }

								if ($magic_map) {
									?>
									<button class="open-magic button" data-id="<?php echo $map_from_server['id'] ?>"><?php _e('Generate Map', 'wp2leads'); ?></button>
									<?php
								}
                                ?>
                            </td>

                            <td>
                                <strong><?php echo $map_from_server['name']; ?> (id <?php echo $map_from_server['id'] ?>)</strong>
                                <?php
                                if (!empty($map_from_server['map_version'])) {
                                    ?>
                                    <small>v. <?php echo $map_from_server['map_version']; ?></small>
                                    <?php

                                    if (!empty($map_from_server['sale_link'])) {
                                        ?><a href="<?php echo $sale_link; ?>" target="_blank"><?php _e('Tutorial and support', 'wp2leads') ?></a><?php
                                    }

                                    if ($is_exclusive) {
                                        ?>
                                        (<?php _e('Exclusive', 'wp2leads') ?>)
                                        <?php
                                        if ($sale_link) {
                                            ?><a href="<?php echo $sale_link; ?>" target="_blank"><?php _e('Buy this map', 'wp2leads') ?></a><?php
                                        }
                                    }
                                }

								if (!empty($maps_meta[$map_from_server['map_id']]['description'])) { ?>
								<p><?php echo unserialize($maps_meta[$map_from_server['map_id']]['description']); ?></p>
								<?php } ?>
                            </td>

                            <td class="map-owner_column" style="text-align: center">
                                <?php
                                if ($current_owner_hash === $map_from_server['owner_hash']) {
                                    ?>
                                    <span class="dashicons dashicons-admin-users wp2lead-tip" data-mode="above" data-tip="<?php _e('Own map', 'wp2leads') ?>"></span>
                                    <?php
                                }
                                ?>
                            </td>

                            <td class="map-transfer-allowed_column" style="text-align: center">
                                <?php
                                if ( $is_map_from_server_can_be_transfered ) {
                                    ?>
                                    <span class="dashicons dashicons-unlock wp2lead-tip" data-mode="above" data-tip="<?php _e('Transfer allowed', 'wp2leads') ?>"></span>
                                    <?php
                                } else {
                                    ?>
                                    <span class="dashicons dashicons-lock wp2lead-tip" data-mode="above" data-tip="<?php _e('Transfer not allowed', 'wp2leads') ?>"></span>
                                    <?php
                                }
                                ?>
                            </td>

                            <td style="text-align:center;">
                                <?php
                                if ($map_from_server_kind == "pro") {
                                    _e('Pro', 'wp2leads');
                                } elseif ($map_from_server_kind == "essent") {
                                    _e('Essential', 'wp2leads');
                                } elseif ($map_from_server_kind == "free") {
                                    _e('Free', 'wp2leads');
                                }
                                ?>
                            </td>

							<td class="required-plugins">
								<?php
									if ($required_plugins) {
										foreach ($required_plugins as $p) {
											?>
											<div style="white-space: nowrap; text-align: center;">
												<a href="<?php echo $p['link']; ?>" target="_blank"><?php echo $p['label']; ?></a>
											</div>
											<?php
										}
									}
								?>
							</td>
							<td>
									<?php
									echo $kt_link;
									?>
								</td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
				<div class="api-spinner-holder ">
					<div class="api-spinner"></div>
				</div>
            </div>
			<?php if ($show_notice) { ?>
				<div class="wp2leads-notice wp2leads-notice-warning">
					<h4><?php _e('Install and activate required plugins to unblock maps', 'wp2leads') ?></h4>
				</div>
			<?php } ?>
            <p class="submit">
                <button id="import_from_remote" type="submit" class="button button-primary">
                    <?php _e('Import Map', 'wp2leads') ?>
                </button>
            </p>
        </form>
    </div>
    <?php
}

?>

<div class="magic-maps magic-169" style="display: none;">
	<h2><?php _e('Contact Forms Maps', 'wp2leads') ?></h2>

	<hr>
	<div class="magic-info"></div>
	<div class="magic-content">
		<?php echo Wp2leads_MagicImport::get_169_html(); ?>
	</div>
</div>


<h2><?php _e('Local Maps', 'wp2leads') ?></h2>

<hr>

<h4><?php _e('Select the maps you would like to export:', 'wp2leads') ?></h4>

<div class="export-maps-form__holder" style="width: 100%;max-width:950px">
    <form id="export-maps-form" method="post">
        <input type="hidden" name="download_map_data" id="download_map_data">

        <div class="export-maps-to-server-table-wrap" style="margin-bottom: 15px">
            <table id="export-maps_table" class="thead-responsive-table" style="width: 100%;min-width: 900px">
                <thead>
                <tr>

                    <th>
                        <?php _e('Export', 'wp2leads') ?><br>
                        <input type="checkbox" id="wp2l_select_all_mapuploadids"<?php echo $is_export_allowed ? '' : ' disabled'; ?> />
                    </th>

                    <th>
                        <?php _e('Update', 'wp2leads') ?><br>
                        <input type="checkbox" id="wp2l_select_all_mapupdateids"<?php echo $is_export_allowed ? '' : ' disabled'; ?> />
                    </th>

                    <th><?php _e('Map', 'wp2leads') ?></th>

                    <th colspan="4"><?php _e('Version (status)', 'wp2leads') ?></th>
                    <?php
                    if (false && $is_user_pro) {
                        ?>
                        <th><?php _e('Make exclusive', 'wp2leads') ?></th>
                        <?php
                    }
                    ?>
					<th><?php _e('Links', 'wp2leads') ?></th>
                </tr>
                </thead>

                <tbody>
                <?php
					$i=0;
					$forms = array();
					foreach($maps as $map): ?>
                    <?php
					$map_meta = MetaModel::get_post_meta($map['id']);

                    $map_status = false;
                    $decoded_map_port_info = unserialize($map['info']);
                    $is_map_port_owner = Wp2leads_License::is_dev_allowed() || MapBuilderManager::is_map_owner($decoded_map_port_info, true);
                    $is_map_port_on_server = MapBuilderManager::is_map_on_server($decoded_map_port_info);
                    $is_map_on_server_outdated = MapBuilderManager::is_map_on_server_outdated($map['id']);
                    $map_port_kind = '';
					$mapping = unserialize($map['mapping']);

					$kt_urls = isset($decoded_map_port_info['kt_url']) ? $decoded_map_port_info['kt_url'] : array();
					$kt_value = '';
					foreach ($kt_urls as $url) {
						$kt_value .= $url . PHP_EOL;
					}

                    if ($is_map_port_on_server) {
                        $map_port_kind = !empty($decoded_map_port_info['publicMapKind']) ? $decoded_map_port_info['publicMapKind'] : false;
                    }

                    if ($is_map_port_owner && $is_map_port_on_server) {
                        $map_status = MapBuilderManager::check_map_status_on_server($map['id']);
                    }
                    ?>

                    <tr>
                        <td style="width: 45px;max-width: 20%;text-align: center">
                            <?php
                            if ($is_map_port_owner && !$is_map_port_on_server) {
                                ?>
                                <input
                                        name="mapuploadids[<?php echo $map['id']?>]"
                                        class="map-upload"
                                        type="checkbox"
                                        id="mapkind_<?php echo $i ?>"
                                        value="<?php echo $map['id']?>"<?php echo $is_export_allowed ? '' : ' disabled'; ?>
                                />
                                <?php
                            }
                            ?>
                        </td>

                        <td style="width: 45px;max-width: 20%;text-align: center">
                            <?php
                            if ( $is_map_port_on_server && $is_map_port_owner && (  Wp2leads_License::is_dev_allowed() || 'pending' !== $map_status ) ) {
                                ?>
                                <input
                                        name="mapupdateids[<?php echo $map['id']?>]"
                                        class="map-update"
                                        type="checkbox"
                                        id="mapkind_<?php echo $i ?>"
                                        value="<?php echo $map['id']?>"<?php echo $is_export_allowed ? '' : ' disabled'; ?>
                                />
                                <?php
                            }
                            ?>
                        </td>

                        <td>
							<label  class="hidden-editable">
								<textarea name="map_names[<?php echo $map['id']?>]" id="map_name_<?php echo $map['id']?>"><?php echo $map['name']; ?></textarea><span class="dashicons dashicons-edit"></span>
							</label>
							<?php
                            if ($is_map_port_on_server && !empty($decoded_map_port_info['publicMapVersion'])) {
                                echo ' <small>v. '. $decoded_map_port_info['publicMapVersion'] . '</small>';
                            }
                            ?>
							<label  class="hidden-editable">
								<textarea name="map_description[<?php echo $map['id']?>]" placeholder="<?php _e('Description', 'wp2leads'); ?>" id="map_description_<?php echo $map['id']?>"><?php if (isset($map_meta['description'])) echo $map_meta['description']; ?></textarea><span class="dashicons dashicons-edit"></span>
							</label>
                        </td>

                        <td class="map-owner_column" style="width: 18px;text-align: center">
                            <?php
                            if ($is_map_port_owner) {
                                ?>
                                <span class="dashicons dashicons-admin-users wp2lead-tip" data-mode="above" data-tip="<?php _e('Own map', 'wp2leads') ?>"></span>
                                <?php
                            }
                            ?>
                        </td>

                        <td class="map-on-server_column" style="width: 18px;text-align: center">
                            <?php
                            if ($is_map_port_on_server) {
                                ?>
                                <span class="dashicons dashicons-admin-site wp2lead-tip" data-mode="above" data-tip="<?php _e('Published', 'wp2leads') ?>"></span>
                                <?php
                            }
                            ?>
                        </td>

                        <td class="map-transfer-allowed_column" style="text-align: center">
                            <?php
                            if ( Wp2leads_License::is_map_transfer_allowed($map['id']) ) {
                                ?>
                                <span class="dashicons dashicons-unlock wp2lead-tip" data-mode="above" data-tip="<?php _e('Transfer allowed', 'wp2leads') ?>"></span>
                                <?php
                            } else {
                                ?>
                                <span class="dashicons dashicons-lock wp2lead-tip" data-mode="above" data-tip="<?php _e('Transfer not allowed', 'wp2leads') ?>"></span>
                                <?php
                            }
                            ?>
                        </td>

                        <td style="width: 80px;max-width: 25%;text-align: center">
                            <?php
                            if ($is_map_port_owner && !$is_map_port_on_server) {
                                ?>
                                <select name="mapkindversion[<?php echo $map['id']?>]" class="map-kind-version" id="mapkindversion_<?php echo $map['id']?>"<?php echo $is_export_allowed ? '' : ' disabled'; ?>>
                                    <option value="pro" <?php echo ($map_port_kind == "pro" ? 'selected' : ''); ?>><?php _e('Pro', 'wp2leads') ?></option>
                                    <?php
                                    if ('pro' === $wp2l_current_version) {
                                    }
                                    ?>

                                    <option value="essent" <?php echo ($map_port_kind == "essent" ? 'selected' : ''); ?>><?php _e('Essential', 'wp2leads') ?></option>
                                    <option value="free" <?php echo ($map_port_kind == "free" ? 'selected' : ''); ?>><?php _e('Free', 'wp2leads') ?></option>
                                </select>
                                <?php
                            } else {
                                ?>
                                <input type="hidden" name="mapkindversion[<?php echo $map['id']?>]" class="map-kind-version" value="<?php echo $map_port_kind ?>" id="mapkindversion_<?php echo $map['id']?>" />
                                <?php
                                if ($map_port_kind == "pro") {
                                    _e('Professional', 'wp2leads');
                                } elseif ($map_port_kind == "essent") {
                                    _e('Essential', 'wp2leads');
                                } elseif ($map_port_kind == "free") {
                                    _e('Free', 'wp2leads');
                                }

                                if ($map_status) {
                                    echo ' <br>('. $map_status . ')';
                                }
                            }
                            ?>
                        </td>

                        <?php
                        if (false && $is_user_pro) {
                            ?>
                            <td style="text-align:center;">
                                <?php
                                if ($is_map_port_owner) {
                                    ?>
                                    <input
                                            name="mapexclusive[<?php echo $map['id']?>]"
                                            class="map-exclusive"
                                            type="checkbox"
                                            id="mapexclusive_<?php echo $i ?>"
                                        <?php echo !empty($decoded_map_port_info['isExclusive']) ? ' checked' : '' ?>
                                            value="<?php echo $map['id']?>"<?php echo $is_map_port_on_server ? ' disabled' : ''; ?>
                                    />
                                    <?php
                                }
                                ?>
                            </td>
                            <?php
                        }
                        ?>

                        <td class="map-support-url_column">
							<table class="map-urls">
							<?php
							// support url link
								if ($is_map_port_owner) {
									$url = false;
									foreach ($maps_from_server as $map_from_server) {
										if ($decoded_map_port_info['publicMapId'] === $map_from_server['map_id']) {
											$url = !empty($map_from_server['sale_link']) ? $map_from_server['sale_link'] : false;
										}
									}
                                ?>
								<tr>
									<td class="map-url-title"><?php _e('Support URL', 'wp2leads'); ?></td>
									<td class="map-url-input">
										<label  class="hidden-editable">
											<input type="text"
												   name="mapurl[<?php echo $map['id']?>]"
												   id="mapurl_<?php echo $map['id']?>"
												   placeholder="http://yourdomain.com"
												   value="<?php echo $url ? $url : '' ?>"
											>
											<span class="dashicons dashicons-edit"></span>
										</label>
									</td>
									<td class="map-tooltip"></td>
								</tr>
                                <?php
                            } else {
                                $url = false;
                                foreach ($maps_from_server as $map_from_server) {
                                    if ($decoded_map_port_info['publicMapId'] === $map_from_server['map_id']) {
                                        $url = !empty($map_from_server['sale_link']) ? $map_from_server['sale_link'] : false;
                                    }
                                }

                                if ($url) {
                                    ?>
									<tr>
										<td class="map-url-title"><?php _e('Support URL', 'wp2leads'); ?></td>
										<td class="map-url-input"><a href="<?php echo $url ?>" target="_blank"><?php _e('Tutorial and support', 'wp2leads') ?></a></td>
										<td class="map-tooltip"></td>
									</tr>
									<?php
                                }
                            }

							// cf7 link
							if (isset($mapping['form_code'])) {
								$code = explode('_', $mapping['form_code']);

								if ('cf' == $code[0]) {
									// we have cf7 form with id = $code[1]

									$post = get_post($code[1]);

									if ($post) { ?>
										<tr>
											<td class="map-url-title"><?php _e('CF7 form example link', 'wp2leads'); ?></td>
											<td class="map-url-input">
												<label  class="hidden-editable">
													<input type="text"
													   name="mapformlink[<?php echo $map['id']?>]"
													   id="mapformlink_<?php echo $map['id']?>"
													   placeholder="http://yourdomain.com/something"
													>
													<span class="dashicons dashicons-edit"></span>
												</label>
											</td>
											<td class="map-tooltip"><span class="dashicons dashicons-lightbulb" title="<?php _e('Add a link to the page with the example of this form', 'wp2leads'); ?>"></span></td>
										</tr>
									<?php
									}
								}
							}
							// KT campaign link
							if ($is_map_port_owner) { ?>
								<tr>
									<td class="map-url-title"><?php _e('KT import campaign links', 'wp2leads') ?></td>
									<td class="map-url-input">
										<label  class="hidden-editable">
											<input type="text"
												name="kturl[<?php echo $map['id']?>]"
												id="kturl_<?php echo $map['id']?>"
												placeholder="https://www.klick-tipp.com/template/fsdfsdfd"
												value="<?php echo $kt_value; ?>"
											>
											<span class="dashicons dashicons-edit"></span>
										</label>
									</td>
									<td class="map-tooltip"><span class="dashicons dashicons-lightbulb" title="<?php _e('Add a link with which somebody can import KT campaign', 'wp2leads') ?>"></span></td>
								</tr><?php
                            }
								$map_plugins = $rplugins->get_active_plugins_list(empty($decoded_map_port_info['serverId']) ? '' : $decoded_map_port_info['serverId']);
							?><tr>
									<td class="map-url-title"><?php _e('Required Plugins', 'wp2leads') ?></td>
									<td class="map-url-input">
										<select
												name="ktplugins[<?php echo $map['id']?>]"
												id="ktplugins_<?php echo $map['id']?>"
												multiple
												>
											<?php foreach ($map_plugins as $plugin) { ?>
												<option
													value="<?php echo $plugin['label']; ?>"
													<?php selected((bool)$plugin['status']); ?>
												>
													<?php echo $plugin['label']; ?>
												</option>
											<?php } ?>
										</select>
									</td>
									<td class="map-tooltip"><span class="dashicons dashicons-lightbulb" title="<?php _e('Select plugins that are required for this map', 'wp2leads') ?>"></span></td>
								</tr>


							</table>
                        </td>

                    </tr>
                    <?php $i++; endforeach; ?>

                </tbody>
            </table>
        </div>

        <?php
        if ($is_export_allowed) :
            ?>
            <button id="exportMaps" type="submit" class="button button-primary"><?php _e('Export Maps', 'wp2leads') ?></button>
        <?php
        else :
            ?>
            <button id="exportMaps" type="submit" class="button" disabled="disabled"><?php _e('Export Maps', 'wp2leads') ?></button>
        <?php
        endif;
        ?>
    </form>

    <?php
    if (Wp2leads_License::is_user_level('free')) {
        $current_user_info = wp_get_current_user();
        $current_user_email_info = $current_user_info->user_email;
        ?>
        <p style="margin-top: 10px; margin-bottom:5px">
            <input type="checkbox" id="privacyPolicyConfirm" name="privacy-policy-confirm"<?php echo $is_export_allowed ? ' checked' : ''; ?>>
            <?php _e('With uploading map to map server you agree with our data <a href="https://wp2leads-for-klick-tipp.com/datenschutz/" target="_blank">privacy policy</a> and the points below.', 'wp2leads'); ?>
        </p>

        <p style="margin-top:5px;margin-bottom:10px;padding-left:25px">
            <?php
            echo sprintf (
                __('We will contact you on issues with the map. We save email address only inside map server and NOT show it with the map to public. We will use your email address (%s) to inform you about best map practice via our email marketing service. You can opt out on every email sent.', 'wp2leads'),
                $current_user_email_info
            );
            ?>
        </p>
        <?php
    }
    ?>
	<?php if (isset($_GET['generate_map'])) { ?>
		<input class="start_magic" value="<?php echo $_GET['generate_map']; ?>" type="hidden"><?php
		// check required plugins for this map
		$pl = new Wp2leads_RequiredPlugins();
		$rqp = $pl->check_map_plugins($_GET['generate_map']);

		if ( ! $rqp ) { ?>
			<input type="hidden" class="skip_check"><?php
		}
	}

	if (isset($_GET['form_preselect'])) { ?>
		<input class="form_preselect" value="<?php echo $_GET['form_preselect']; ?>" type="hidden">
	<?php } ?>
</div>