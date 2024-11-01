<?php
/**
 * Initial Settings Section
 *
 * @package Wp2Leads/Partials/MapToAPI
 * @version 1.0.2.5
 * @since 1.0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$global_tag_prefixe = !empty(get_option( 'wp2l_klicktipp_tag_prefix' )) ? get_option( 'wp2l_klicktipp_tag_prefix' ) : false;
$map_tag_prefix = !empty( $api['tags_prefix'] ) ? $api['tags_prefix'] : false;
$start_date_data = !empty( $api['start_date_data'] ) ? $api['start_date_data'] : false;
$end_date_data = !empty( $api['end_date_data'] ) ? $api['end_date_data'] : false;
$warning_prefix_class = '';

if (empty( $global_tag_prefixe ) && !$map_tag_prefix) {
    $warning_prefix_class = ' warning-text';
}

$all_tags_prefix = '';

if ($global_tag_prefixe) {
    $all_tags_prefix = $global_tag_prefixe . ' ';
}

if ($map_tag_prefix) {
    $all_tags_prefix = $map_tag_prefix . ' ';
}

if ( !empty($_GET['come_from']) ) {
	echo '<input type="hidden" id="come_from" value="'.$_GET['come_from'].'">';
} ?>

<h3 class="accordion-header<?php echo $is_initial_settings_done ? '' : ' active'; ?>">
    <?php
    if (!$is_initial_settings_done) {
        _e( 'Initial Settings', 'wp2leads' );
    } else {
        _e( 'Settings', 'wp2leads' );
    }
    ?>
</h3>

<div class="accordion-body <?php echo $is_initial_settings_done ? '' : 'accordion-body-visible'; ?> api-processing-holder">

    <?php
	$first_help_text = __( 'Map Tag Prefix', 'wp2leads' );

    if (!$is_initial_settings_done) {
		$first_help_text = __('Start settings here', 'wp2leads');
        ?>
		<input type="hidden" id="noInitSettings">
        <p style="margin-top:5px;margin-bottom:15px">
            <?php _e('Before starting working with current map, we recommend you to make some initial settings.', 'wp2leads'); ?>
        </p>
		<button id="skipStep" class="button" style="display:none;"><?php _e('Skip Step'); ?> <span class="dashicons dashicons-redo"></span></button>
		<button id="disableWizard" class="button" style="display:none;"><?php _e('Disable Wizard'); ?></button>
        <?php
    }
    ?>

    <h3 class="accordion-subheader">
        <?php _e( 'Tags Prefixes:', 'wp2leads' ) ?>
    </h3>

    <div class="accordion-subbody">
        <div id="tagPrefixesContainer">
            <div id="globalTagPrefixContainer">
                <p class="globalTagPrefix__holder" style="margin-top:0;">
                    <label>
                        <?php echo __( 'Global Tag Prefix', 'wp2leads' ); ?>
                        <?php
                        if ($global_tag_prefixe) {
                            ?><span class="dashicons dashicons-edit settings-change" data-change="globalTagPrefix"></span><?php
                        }
                        ?>
                    </label>

                    <input id="globalTagPrefix"
                           type="text"
                           value="<?php echo $global_tag_prefixe ? $global_tag_prefixe : '' ?>"
                           data-selected-value="<?php echo $global_tag_prefixe ? $global_tag_prefixe : '' ?>"
                           <?php echo $global_tag_prefixe ? ' disabled' : '' ?>
                           class="form-control form-control-medium<?php echo $global_tag_prefixe ? ' disabled' : '' ?>">
                    <span><small><i><?php echo __( 'Example:', 'wp2leads' ); ?></i> <strong><?php _e( 'web1', 'wp2leads' ) ?></strong></small></span>
                </p>
            </div>

            <div id="mapTagPrefixContainer">
                <p id="mapTagPrefix__holder" style="margin-top:0;">
                    <label>
                        <?php echo __( 'Map Tag Prefix', 'wp2leads' ); ?>
                        <?php
                        if ($map_tag_prefix) {
                            ?><span class="dashicons dashicons-edit settings-change" data-change="mapTagPrefix"></span><?php
                        }
                        ?>
                    </label>

                    <input id="mapTagPrefix"
                           type="text"
                           value="<?php echo $map_tag_prefix ? $map_tag_prefix : '' ?>"
                           data-selected-value="<?php echo $map_tag_prefix ? $map_tag_prefix : '' ?>"
                           <?php echo $map_tag_prefix ? ' disabled' : '' ?>
                           class="form-control form-control-medium<?php echo $map_tag_prefix ? ' disabled' : '' ?>"
						   data-id="<?php echo isset($_GET['active_mapping']) ? $_GET['active_mapping'] : ''; ?>"
						   title="<?php echo $first_help_text; ?>"
						   >
                    <span><small><i><?php echo __( 'Example:', 'wp2leads' ); ?></i> <strong><?php _e( 'web1_form1', 'wp2leads' ) ?></strong></small></span>
                </p>
            </div>
        </div>

        <p id="prefixInfo" style="margin-top: 0">
            <span><small><i><?php echo __( 'Map Tag Prefix has higher priority', 'wp2leads' ); ?></i></small></span>
        </p>

        <p class="tagPrefixWarning__noprefix<?php echo $warning_prefix_class; ?>">
            <span>
                <i>
                    <?php _e( 'If you ever want to connect more than one website with same, similar tags please choose a prefix (f.e. <strong>web1</strong>). So the tags are clean separated.', 'wp2leads' ); ?>
                </i>
            </span>
        </p>
    </div>

    <?php
    $recomendedManualTags    = !empty( $info['possibleUsedTags']['standartTags'] ) ? $info['possibleUsedTags']['standartTags'] : array();
    $recomendedUserInputTags = !empty( $info['possibleUsedTags']['userInputTags'] ) ? $info['possibleUsedTags']['userInputTags'] : false;

    if (!$is_initial_settings_done) {
        if (!empty($api['conditions']['tags']) && is_array($api['conditions']['tags'])) {
            foreach ($api['conditions']['tags'] as $condition) {
                if (!empty($condition['connectToName'])) {
                    $prefix = !empty($condition["prefix"]) ? trim($condition["prefix"]) . '||' : '';
                    $recomendedManualTags[] = $prefix . $condition['connectToName'];
                }
            }
        }

        if (!empty($api['conditions']['detach_tags']) && is_array($api['conditions']['detach_tags'])) {
            foreach ($api['conditions']['detach_tags'] as $condition) {
                if (!empty($condition['connectToName'])) {
                    $prefix = !empty($condition["prefix"]) ? trim($condition["prefix"]) . '||' : '';
                    $recomendedManualTags[] = $prefix . $condition['connectToName'];
                }
            }
        }
    }

    if (empty($recomendedManualTags)) {
        $recomendedManualTags = false;
    } else {
        $recomendedManualTags = array_values(array_unique($recomendedManualTags));
    }

	if ( !empty($mapping['form_code']) ) {
		$recomendedManualTags = false;
	}

    if ( $recomendedManualTags || $recomendedUserInputTags ) {
        ?>
        <h3 class="accordion-subheader">
            <?php _e( 'Recommended tags:', 'wp2leads' ) ?>
        </h3>

        <div class="accordion-subbody">
            <?php
            if ( $recomendedManualTags ) {
                ?>
                <h4 style="margin-top:0;margin-bottom:10px"><?php _e( 'Manually created tags', 'wp2leads' ) ?></h4>
                <?php
                if (!$is_initial_settings_done) {
                    ?>
                    <p style="margin-top:5px;margin-bottom:15px">
                        <?php _e('We highly recommend you to create all tags from this list after you set map prefix. Otherwise you will need to make some settings in tag add / untag conditions later.', 'wp2leads'); ?>
                    </p>
                    <?php
                }

                $possibleUsedTags_userInputTags_saved = !empty($info['possibleUsedTags']['userInputTags']) ? $info['possibleUsedTags']['userInputTags'] : array();

				$recomendedManualTags2 = array();
				foreach ($recomendedManualTags as $t) {
					$recomendedManualTags2[] = str_replace(
						array('&', '<', '>', '"', "'"),
						array('&amp;','&lt;','&gt;','&quot;','&#039;'),
						$t
					);
				}
                ?>

                <div id="recommendedTagsCloud"
                     data-saved-value-standart='<?php echo json_encode( $recomendedManualTags2, JSON_FORCE_OBJECT ); ?>'
                     data-saved-value-userinput='<?php echo json_encode( $possibleUsedTags_userInputTags_saved, JSON_FORCE_OBJECT ); ?>'
                >
                    <p style="margin-top:0;margin-bottom:0">
                        <?php _e( 'Getting your manual tags.', 'wp2leads' ) ?>
                    </p>
                </div>

                <div id="recommendedTagsCloudBtn"<?php echo $recomendedUserInputTags ? ' style="margin-bottom:10px;"' : '' ?>>
                    <button id="selectRecommendedTags" class="button button-small"><?php _e( 'Select All', 'wp2leads' ) ?></button>
                    <button id="deselectRecommendedTags" class="button button-small"><?php _e( 'Deselect All', 'wp2leads' ) ?></button>
                    <button id="createRecommendedTags" class="button button-primary button-small"><?php _e( 'Create Tags', 'wp2leads' ) ?></button>
					<button id="skipRecommendedTags" class="button button-small button-small skip-btn" style="display:none;"><?php _e( 'Skip', 'wp2leads' ) ?></button>
                </div>
                <?php
            }

            if ( $recomendedUserInputTags ) {
                global $wpdb;
                foreach ( $recomendedUserInputTags as $container_index => $recomendedUserInputTag ) {
                    $unique  = md5( $activeMap->id . sha1($container_index) );
                    $is_running_in_bg = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $unique . "%';" );
                    $tags_prefix = !empty($recomendedUserInputTag['prefix']) ? $recomendedUserInputTag['prefix'] : '';
                    $tags_prefix = trim($tags_prefix);
                    ?>
                    <h4 style="margin-top:0;margin-bottom:10px"><?php _e( 'Tags from:', 'wp2leads' ) ?> <?php echo $recomendedUserInputTag['title'] ?></h4>

                    <div class="recommended_user_input_tags_cloud-container" data-container="<?php echo $container_index; ?>">
                        <?php
                        if (!empty($is_running_in_bg)) {
                            ?>
                            <div class="recommended_user_input_tags_cloud">
                                <p style="margin-top:0;margin-bottom:0">
                                    <?php _e( 'Background process for this tags set is running.', 'wp2leads' ) ?>
                                </p>
                            </div>

                            <?php
                        } else {
                            ?>
                            <div class="recommended_user_input_tags_prefix">
                                <div class="wptl-row">
                                    <div class="wptl-col-xs-12 wptl-col-sm-6 wptl-col-md-2 wptl-col-lg-1">
                                        <p style="margin: 3px 0;"><?php _e( 'Prefix', 'wp2leads' ) ?>:</p>
                                    </div>
                                    <div class="wptl-col-xs-12 wptl-col-sm-6 wptl-col-md-4 wptl-col-lg-3">
                                        <input data-value="<?php echo $tags_prefix; ?>" type="text" name="recomended-tags-prefix" class="recomended-tags-prefix form-control" value="<?php echo $tags_prefix; ?>" style="margin-bottom: 5px;">
                                    </div>
                                </div>
                            </div>

                            <div class="recommended_user_input_tags_cloud">
                                <p style="margin-top:0;margin-bottom:0">
                                    <?php _e( 'To get tags list, click Get tags button', 'wp2leads' ) ?>
                                </p>
                            </div>

                            <div class="recommended_user_input_tags_message" style="display:none">
                                <p>
                                    <?php _e( 'In order to prevent performance issues we are displaying you first <strong class="limit-tags"></strong> tags out of <strong class="all-tags"></strong>.', 'wp2leads' ); ?>
                                    <?php _e( 'You can select displayed tags and create them on Klick-Tipp and after that you can get next tags set and create them.', 'wp2leads' ); ?>
                                    <?php _e( 'Otherwise you can click "Create all" button and all tags will be created in background.', 'wp2leads' ); ?>
                                </p>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="recommended_user_input_tags_filter">

                        </div>
                        <button class="get-user-input-tags-results button button-small"
                                data-value='<?php echo json_encode( $recomendedUserInputTag, JSON_FORCE_OBJECT ) ?>'<?php echo !empty($is_running_in_bg) ? ' style="display:none"' : ''; ?>><?php _e( 'Get Tags', 'wp2leads' ) ?></button>

                        <button class="select-user-input-tags button button-small" style="display:none"><?php _e( 'Select All', 'wp2leads' ) ?></button>

                        <button class="deselect-user-input-tags button button-small" style="display:none"><?php _e( 'Deselect All', 'wp2leads' ) ?></button>

                        <button class="create-user-input-tags button button-primary button-small"
                                style="display:none"><?php _e( 'Create Selected', 'wp2leads' ) ?></button>

                        <button class="create-all-user-input-tags button button-primary button-small"
                                style="display:none" data-set-id="<?php echo sha1($container_index); ?>" data-map-id="<?php echo $activeMap->id; ?>" data-tags-all><?php _e( 'Create All', 'wp2leads' ) ?></button>

                        <button class="reload-kt-tags button button-primary button-small"<?php echo empty($is_running_in_bg) ? ' style="display:none"' : ''; ?>><?php _e( 'Reload Tags from KT', 'wp2leads' ) ?></button>
						<button class="button button-small button-small skip-btn" style="display:none;"><?php _e( 'Skip', 'wp2leads' ) ?></button>

                        <?php
                        if ($is_initial_settings_done) {
                            $recomendedUserInputTagFilter = $recomendedUserInputTag;

                            if (isset($recomendedUserInputTagFilter['prefix'])) {
                                unset($recomendedUserInputTagFilter['prefix']);
                            }
                            ?>
                            <button class="get-user-input-tags-filter button button-small" data-value='<?php echo json_encode( $recomendedUserInputTagFilter, JSON_FORCE_OBJECT ) ?>'>
                                <?php _e( 'Load filter to clean the tags', 'wp2leads' ) ?>
                            </button>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <?php
    }
	// check if the form have some replacements
	if ( isset($mapping['form_code']) && $mapping['form_code'] && class_exists('vxcf_form') ) {
		$form_code = explode('_', $mapping['form_code']);
		$form_fields = vxcf_form::get_form_fields($mapping['form_code']);
		$need_button = false;

		foreach($form_fields as $field) {
			if ($field['type'] == 'radio' || $field['type'] == 'checkbox' || $field['type'] == 'select') {
				$need_button = true;
			}
		}

		if ( $need_button ) { ?>
			<h3 class="accordion-subheader"><?php _e( 'Tags to replace:', 'wp2leads' ) ?></h3>

			<div class="accordion-subbody">
				<?php $show_popup = (empty($api['show_magic_popup'])) ? 0 : 1;	?>
				<button class="change-magic-replacements button button-small" data-id="<?php echo $activeMap->id; ?>" data-show="<?php echo $show_popup; ?>"><?php _e('Change replacements'); ?></button>
			</div>   <?php
		}
	}

    if ($is_initial_settings_done) {
        if ($cron_available) {
            if ($module_active || $cron_active) {
                include 'wp2leads-admin-map_to_api-auto-cron-settings.php';
            }
        }

        if ($module_available) {
            if ($module_active || $cron_active) {
                include 'wp2leads-admin-map_to_api-auto-module-settings.php';
            }
        }
    }

    if (empty($fields) || !is_array($fields)) {
        $fields = array();
    }

    if (!$is_initial_settings_done) {
        $default_api_fields = ApiHelper::getDefaultApiFields();

        $not_existed_fields = array();
        $existed_fields = array();

        if (!empty($api['fields'])) {
            foreach ($api['fields'] as $existed_field_slug => $existed_field_value) {
                $existed_api_field_slug = ltrim($existed_field_slug, 'api_');

                if (!in_array($existed_field_slug, $default_api_fields) && empty($fields[$existed_api_field_slug]) && !empty($existed_field_value['table_columns'])) {
                    $not_existed_fields[$existed_field_slug] = $existed_field_value;
                    $existed_fields[$existed_field_slug] = $existed_field_value;
                } elseif (!in_array($existed_field_slug, $default_api_fields) && !empty($existed_field_value['table_columns'])) {
                    $existed_fields[$existed_field_slug] = $existed_field_value;
                }
            }
        }

		// KT link
		$kt_url = MapBuilderManager::get_map_meta_from_server($info['publicMapId'], 'kt_url');

		if (isset($kt_url['kt_url'])) {
			$kt_url = unserialize($kt_url['kt_url']);
		}

		$imported_campaings = get_option('wp2leads_campaign_list', array());

		if ( !in_array($activeMap->id, $imported_campaings) && !empty($info['publicMapId']) && $kt_url ) {  ?>

			<h3 class="accordion-subheader">
                <?php _e( 'Klick Tipp campaign Import:', 'wp2leads' ) ?>
            </h3>
			<div class="accordion-subbody">
                <div style="margin-top:5px;margin-bottom:10px;">
					<h4>
						<?php
							if ( $recomendedManualTags || $recomendedUserInputTags ) {
								_e( 'All Tags generated?', 'wp2leads' );

								$alert = "alert('" . __( 'Please generated Tags so they can be used in the Klick Tipp Campaign', 'wp2leads' ) . "');return false;";
							} else {
								_e( 'Start Klick-Tipp campaign import?', 'wp2leads' );
								$alert = "jQuery('#skipStep').click();return false;";
							}
						?>
					</h4>
					<a href="<?php echo $kt_url; ?>" class="button button-primary map-to-api-kt-link" target="_blank"><?php _e( 'Yes', 'wp2leads' ) ?></a>
					<a href="#" class="button" onclick="<?php echo $alert; ?>"><?php _e( 'No', 'wp2leads' ) ?></a>
				</div>
				<div style="margin-top:5px;margin-bottom:10px;display:none;">
					<h4><?php _e( 'Was Campaign imported?', 'wp2leads' ) ?></h4>
					<a href="#" class="button button-primary map-to-api-kt-imported-yes" data-id="<?php echo $activeMap->id; ?>"><?php _e( 'Yes', 'wp2leads' ) ?></a>
					<a href="#" class="button map-to-api-kt-imported-no" data-id="<?php echo $activeMap->id; ?>"><?php _e( 'Skip (press only if you understand what you do)', 'wp2leads' ) ?></a>
					<a href="<?php echo $kt_url; ?>" style="vertical-align: middle;margin-left: 10px;" target="_blank"><?php _e( 'Open Campaign Again', 'wp2leads' ) ?></a>
				</div>
			</div> <?php
		} elseif (!empty($info['publicMapId']) && $kt_url) {
			?>

			<h3 class="accordion-subheader">
                <?php _e( 'Klick Tipp campaign Import:', 'wp2leads' ) ?>
            </h3>
			<div class="accordion-subbody">
				<div style="margin-top:5px;margin-bottom:10px;">
					<a href="<?php echo $kt_url; ?>" class="button" target="_blank"><?php _e( 'Open Campaign', 'wp2leads' ) ?></a>
				</div>
			</div> <?php
		}

        $count_not_existed_fields = count($not_existed_fields);

        if (!empty($count_not_existed_fields) || !empty($existed_fields)) {
            ?>
            <h3 class="accordion-subheader">
                <?php _e( 'API Fields Types:', 'wp2leads' ) ?>
            </h3>

            <div class="accordion-subbody">
                <?php
                if (!empty($count_not_existed_fields)) {
                    ?>
                    <p style="margin-top:5px;margin-bottom:10px;">
                        <?php
                        echo sprintf (
                            __('With this map we have %d extra fields which are not connected to your fields in Klick Tipp please connect them. You may have to manually create new fields. Please be aware of the right field type. For the date and time fields you should check Time Zone after export and change it in the Fields if need.', 'wp2leads'),
                            $count_not_existed_fields
                        );
                        ?>
                    </p>
                    <?php
                }
                ?>

                <div id="apiFieldsInitialSettings__container" data-select-placeholder="<?php _e('Type something to start...', 'wp2leads'); ?>">
                    <?php
					$results = MapsModel::get_map_query_results($mapping);
					if ( $results ) {
						$results = (array)$results[0];
					}

                    if (!empty($existed_fields)) {
                        foreach ($existed_fields as $existed_field_slug => $existed_field_value) {
                            if (!in_array($existed_field_slug, $default_api_fields) && !empty($existed_field_value['table_columns'])) {
                                $existed_fields[$existed_field_slug] = $existed_field_value;
                                ?>
                                <div class="select_api_field_item" data-old-slug="<?php echo $existed_field_slug; ?>">
                                    <h3 style="margin-top:0;margin-bottom:10px;font-weight:normal;">
                                        <?php _e( 'Field name:', 'wp2leads' ) ?> <strong class="select_api_field_name"><?php echo $existed_field_value['name']; ?></strong> -
                                        <?php _e( 'Columns:', 'wp2leads' ) ?> <strong class="select_api_field_columns" data-col="<?php echo implode(', ', $existed_field_value['table_columns']); ?>">
                                            <?php echo implode(', ', $existed_field_value['table_columns']); ?>
                                            <?php
                                            $values = '';

                                            foreach ( $existed_field_value['table_columns'] as $col ) {
                                                if ( !empty($results[$col]) ) {
                                                    if ( $values ) $values .= ', ';

                                                    if ( 'html' === $existed_field_value['type'] ) {
                                                        $values .= '&lt;html&gt;';
                                                    } else {
                                                        $values .= $results[$col];
                                                    }
                                                }
                                            }

                                            if ( $values ) {
                                                echo '(' . $values . ')';
                                            }
                                            ?>
                                        </strong>
                                    </h3>

                                    <div class="columns-two">
                                        <div class="column-1">
                                            <?php _e( 'Connect to field in your KT account:', 'wp2leads' ) ?>

                                            <div class="select_api_field_to__holder">
                                                <select class="select_api_field_to" multiple>
                                                    <option value=""><?php _e( '-- Select field --', 'wp2leads' ) ?></option>

                                                    <?php $i = 0;
                                                    foreach ($fields as $api_field_slug => $api_field_name) {
                                                        if (!in_array('api_' . $api_field_slug, $default_api_fields)) {
                                                            ?>
                                                            <option <?php selected($api_field_name, $existed_field_value['name']); ?> value="<?php echo 'api_' . $api_field_slug ?>">
                                                                <?php echo $api_field_name; ?>
                                                            </option>
                                                            <?php $i++;
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="column-2">
                                            <?php _e( 'Field type:', 'wp2leads' ) ?>
                                            <div class="select_api_field_type__holder">
                                                <select class="form-control select_api_field_type">
                                                    <option value="text"<?php echo 'text' === $existed_field_value['type'] ? ' selected' : ''; ?>>
                                                        <?php _e( 'text', 'wp2leads' ) ?>
                                                    </option>
                                                    <option value="number"<?php echo 'number' === $existed_field_value['type'] ? ' selected' : ''; ?>>
                                                        <?php _e( 'number', 'wp2leads' ) ?>
                                                    </option>
                                                    <option value="decimal"<?php echo 'decimal' === $existed_field_value['type'] ? ' selected' : ''; ?>>
                                                        <?php _e( 'decimal', 'wp2leads' ) ?>
                                                    </option>
                                                    <option value="html"<?php echo 'html' === $existed_field_value['type'] ? ' selected' : ''; ?>>
                                                        <?php _e( 'HTML', 'wp2leads' ) ?>
                                                    </option>
                                                    <option value="url"<?php echo 'url' === $existed_field_value['type'] ? ' selected' : ''; ?>>
                                                        <?php _e( 'url', 'wp2leads' ) ?>
                                                    </option>
                                                    <option value="date"<?php echo 'date' === $existed_field_value['type'] ? ' selected' : ''; ?>>
                                                        <?php _e( 'date', 'wp2leads' ) ?>
                                                    </option>
                                                    <option value="time"<?php echo 'time' === $existed_field_value['type'] ? ' selected' : ''; ?>>
                                                        <?php _e( 'time', 'wp2leads' ) ?>
                                                    </option>
                                                    <option value="datetime"<?php echo 'datetime' === $existed_field_value['type'] ? ' selected' : ''; ?>>
                                                        <?php _e( 'date + time', 'wp2leads' ) ?>
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <?php
                            }
                        }
                    }
                    ?>
                </div>

                <button id="updateApiFieldsOptions" class="button button-primary">
                    <?php _e('Update KT fields', 'wp2leads'); ?>
                </button>
            </div>
            <?php
        }

		if (!$is_initial_settings_done) { ?>
			<h3 class="accordion-subheader">
                <?php _e( 'Default Opt-In Process:', 'wp2leads' ); ?>
            </h3>
			<div class="accordion-subbody">
				<div class="api-optins-wrapper">
					<select class="optins-list" class="form-control">
						<?php
							$default_optin_id = !empty($api['default_optin']) ? $api['default_optin'] : '';
							$first_opt = array();
							$i = 0;
							foreach ( $optins as $key => $optin ) {
								$sp = $connector->subscription_process_get($key);
								if (!$i) {
									$i++;
									if ($sp->pendingurl) $first_opt['confirm'] = $sp->pendingurl;
									if ($sp->thankyouurl) $first_opt['thankyou'] = $sp->thankyouurl;
								}

								if ($key == $default_optin_id) {
									$first_opt = array();
									if ($sp->pendingurl) $first_opt['confirm'] = $sp->pendingurl;
									if ($sp->thankyouurl) $first_opt['thankyou'] = $sp->thankyouurl;
								}
							?>
								<option value="<?php echo $key ?>" <?php
									echo $key == $default_optin_id ? 'selected ' : '';
									echo 'data-confirm="'.$sp->pendingurl.'" ';
									echo 'data-thankyou="'.$sp->thankyouurl.'" ';
									echo 'data-default="'.__('Klick Tipp standard link', 'wp2leads').'" ';
								?>><?php echo $optin ? $optin : __( 'Default Opt-In Process', 'wp2leads' ); ?></option>
							<?php } ?>
					</select>
					<div class="optin-urls">
						<div class="confirm-url">
							<?php _e('Confirmation redirect URL: ', 'wp2leads'); ?>
							<span> <?php echo empty($first_opt['confirm']) ? __('Klick Tipp standard link', 'wp2leads') : '<a href="'.$first_opt['confirm'].'" target="_blank">'.$first_opt['confirm'].'</a>'; ?></span>
						</div>
						<div class="thankyou-url">
							<?php _e('Thank You redirect URL: ', 'wp2leads'); ?>
							<span> <?php echo empty($first_opt['thankyou']) ? __('Klick Tipp standard link', 'wp2leads') : '<a href="'.$first_opt['thankyou'].'" target="_blank">'.$first_opt['thankyou'].'</a>'; ?></span>
						</div>
						<?php _e('You can change links in double opt in settings in Klick Tipp.', 'wp2leads'); ?><br>
						<?php if (!empty($mapping['form_code']) && (explode('_', $mapping['form_code'])[0] == 'cf')) { ?>
						<strong><?php _e('Contact Form 7 will redirecting after submitting form', 'wp2leads'); ?></strong>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php
		}
    }

    if (!$is_initial_settings_done) {
        ?>
		<div class="wizard-complete-text" style="display: none;">
			<?php _e('Press Save Setting when all settings will be filled. Skip this if you understand what will happens.', 'wp2leads'); ?>
			<br><br>
		</div>
        <button id="skipInitialSettings" class="button" data-action="skip">
            <?php _e( 'Skip Settings', 'wp2leads' ) ?>
        </button>

        <button id="saveInitialSettings" class="button button-primary" data-action="save">
            <?php _e( 'Save Settings', 'wp2leads' ) ?>
        </button>
        <?php
    }
    ?>

    <div class="api-spinner-holder<?php echo $is_initial_settings_done ? ' api-processing' : '' ?>">
        <div class="api-spinner"></div>
    </div>
</div>
