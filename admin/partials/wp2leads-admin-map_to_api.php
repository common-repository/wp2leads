<?php
/**
 * Map To API Page
 *
 * @package Wp2Leads/Partials
 * @version 1.0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( $activeMap ) {

	$api         = unserialize( $activeMap->api );
    $mapping         = unserialize( $activeMap->mapping );
    $info        = unserialize( $activeMap->info );

	$is_map_have_results = Wp2leads_MapsActivation::is_map_active($_GET['active_mapping']);
	$is_plugins_ok = true;

	if ( $info['serverId'] ) {
		$plugins = new Wp2leads_RequiredPlugins();
		if ( count( $plugins->check_map_plugins($info['serverId']) ) ) {

			$is_plugins_ok = false;

		}
	}

	if ( !empty($_GET['start_step']) ) {
		echo '<input type="hidden" id="start_step" value="' . $_GET['start_step'] . '">';
	}
} ?>

<h2><?php _e( 'Map to API', 'wp2leads' ) ?></h2>

<hr>

<?php
if ( !$activeMap ) {
    ?>
    <div id="mapToApiPage" class="map-to-api">
        <?php include_once 'wp2leads-admin-map_to_api-no-active-map.php'; ?>
    </div>
    <?php
} else if ( !$is_plugins_ok || !$is_map_have_results ) {
	?>
    <div id="mapToApiPage" class="map-to-api">
		<?php
			if ( !$is_map_have_results ) {
				_e('This map is not active:', 'wp2leads');
				echo ' ' . Wp2leads_MapsActivation::get_map_error_message($_GET['active_mapping']);
			}

			if ( !$is_plugins_ok ) {

				echo '<br>';
				_e( 'The next plugins are required for this map: ', 'wp2leads' );
				echo '<ul>';
				foreach ( $plugins->check_map_plugins($info['serverId']) as $plugin ) {
					echo '<li> - ' . $plugin['label'] . '</li>';
				}
				echo '</ul>';
				echo '<button id="installPluginsMapToAPI" class="button button-green" data-id="' . $info['serverId'] . '">' . __('Install Plugin(s)', 'wp2leads') . '</button>';

				echo '<div class="map-to-api-install-plugins" style="display: none;">' . __('Installing...', 'wp2leads') . '</div>';
			}  ?>
    </div>
    <?php
} else {
    $activeMapId = '';
    $is_load_data = false;

    if ( $_GET['tab'] == 'map_to_api' && isset( $_GET['active_mapping'] ) ) {
        global $wpdb;
        $table       = $wpdb->get_row( sprintf( "SELECT * FROM %s WHERE id=%d", $wpdb->prefix . "wp2l_maps", (int) $_GET['active_mapping'] ) );

        $activeMapId = $_GET['active_mapping'];

        $tablesToCheck = array();
        array_push($tablesToCheck, $mapping['from']);

        if ( !empty( $mapping['joins'] ) ) {
            foreach ( $mapping['joins'] as $join ) {
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
    }

    $connector = new Wp2leads_KlicktippConnector();
    $logged_in = $connector->login();
    $logged_in_error = $connector->get_last_connector_error();

    if ( $logged_in ) {

		// check losted tags
		if (!empty($api['losted_manually_selected_tags'])) {

			foreach ($api['losted_manually_selected_tags'] as $t) {
				$result = $connector->tag_create(trim($t));
				if ($result) {
					$code = $result;
				} else {
					$code = array_search(trim($t), $connector->tag_index());
				}

				if ($code) {
					$api['manually_selected_tags']['tag_ids'][] = $code;
				}
			}

			unset($api['losted_manually_selected_tags']);
			MapsModel::updateMapCell($activeMap->id, 'api', serialize($api));
		}

		if (!empty($api['losted_tags'])) {
			$admin_ajax = new Wp2leads_Admin_Ajax();
			$admin_ajax->add_recommended_klick_tip_tags($api['losted_tags']);
			unset($api['losted_tags']);
			MapsModel::updateMapCell($activeMap->id, 'api', serialize($api));
		}

        $fields = $connector->field_index();
        $tags   = (array) $connector->tag_index();
        asort( $tags, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL );
        $optins = $connector->subscription_process_index();
    }

    $is_transfer_allowed = Wp2leads_License::is_map_transfer_allowed( (int) $_GET['active_mapping'] );
    $is_initial_settings_done = !empty($info['initial_settings']);
    // $is_initial_settings_done = true;

    $is_load_data = $is_initial_settings_done && $mapValid && $logged_in;

    if ( !$is_transfer_allowed && $mapValid ) {
        $kt_limitation = KlickTippManager::get_initial_kt_limitation();

        if (!$kt_limitation) {
            ?>
            <div class="wp2leads-notice wp2leads-notice-warning">
                <h4><?php _e( 'Transfer Map only in paid versions', 'wp2leads' ) ?></h4>

                <p><?php _e( 'Please upgrade to Professional Version to transfer user via OWN created maps. <a href="https://wp2leads.com" target="_blank">Buy it</a>, or <button class="button button-small" id="license-modal-open">Enter a license</button>', 'wp2leads' ) ?></p>
            </div>
            <?php
        }
    }

    ?>

	<?php if (isset($api['remove_notice'])) { ?>
		<input type="hidden" id="remove_notice" value="<?php echo $api['remove_notice']; ?>">
	<?php } ?>

    <div id="map-to-api-message-in-progress__holder"></div>

    <div id="mapToApiPage" class="map-to-api">
        <?php
        $cron_status   = '';
        $cron_title    = __( 'Cron not set up', 'wp2leads' );
        $cron_checked = '';
        $cron_selected = '';

        $cron_available = false;

        $cron_active = false;

        if (!empty($decodedMap['dateTime']) && is_array($decodedMap['dateTime'])) {
            $cron_maps = Wp2LeadsCron::getScheduledMaps();
            $cron_fields = array();

            $cron_available = true;

            if ( empty( $cron_maps[ 'map_' . $activeMapId ] ) || (!empty($cron_maps[ 'map_' . $activeMapId ]['status_to_change']) && 'remove_cron_schedule' === $cron_maps[ 'map_' . $activeMapId ]['status_to_change']) ) {
                $cron_status   = '';
                $cron_title    = __( 'Cron not set up', 'wp2leads' );
                $cron_checked  = '';
                $cron_selected = '';
            } else {
                $cron_status   = ' disabled';
                $cron_title    = __( 'Cron disabled', 'wp2leads' );
                $cron_checked  = '';
                $cron_selected = '';

                if ( ! empty( $cron_maps[ 'map_' . $activeMapId ]['status'] ) && empty($cron_maps[ 'map_' . $activeMapId ]['status_to_change']) ) {
                    $cron_status  = ' active';
                    $cron_title   = __( 'Cron enabled', 'wp2leads' );
                    $cron_checked = 'checked';

                    $cron_active = true;
                }

                if ( ! empty( $cron_maps[ 'map_' . $activeMapId ]['date_base'] ) ) {
                    if ( ! is_array( $cron_maps[ 'map_' . $activeMapId ]['date_base'] ) ) {
                        $cron_selected = htmlspecialchars( json_encode( array( $cron_maps[ 'map_' . $activeMapId ]['date_base'] ) ) );
                        $cron_fields = array( $cron_maps[ 'map_' . $activeMapId ]['date_base'] );
                    } else {
                        $cron_selected = htmlspecialchars( json_encode( $cron_maps[ 'map_' . $activeMapId ]['date_base'], JSON_HEX_QUOT ) );
                        $cron_fields = $cron_maps[ 'map_' . $activeMapId ]['date_base'];
                    }
                }
            }

            if ( ! $is_transfer_allowed ) {
                $cron_status = '';
            }
        }

        $module_available = false;
        $module_active = false;

        if (!empty($mapping["transferModule"])) {
            $transfer_modules = Wp2leads_Transfer_Modules::get_transfer_modules_class_names();

            if (!empty($transfer_modules[$mapping["transferModule"]])) {
                $transfer_module = $transfer_modules[$mapping["transferModule"]];
                $class_name = $transfer_module;
                $module_label = $class_name::get_label();
                $module_description = $class_name::get_description();
                $existed_modules_map = Wp2leads_Transfer_Modules::get_modules_map();
                $module_enabled = !empty($existed_modules_map[$mapping["transferModule"]][$activeMapId]) ? ' checked' : '';

                $module_available = true;

                if (!empty($existed_modules_map[$mapping["transferModule"]][$activeMapId])) {
                    $module_active = true;
                }
            }
        }

        $all_columns      = $decodedMap['selects'];
        $excluded_columns = ! empty( $decodedMap['excludes'] ) ? $decodedMap['excludes'] : false;

        if ( $excluded_columns ) {
            $map_columns = array_diff( $all_columns, $excluded_columns );
        } else {
            $map_columns = $all_columns;
        }

		$losted_name =  !empty( $api['losted_name'] ) ? $api['losted_name'] : '';
		if ( !empty( $api['losted_name'] ) && is_array($api['losted_name']) ) $losted_name = $api['losted_name'][0];

        ?>

        <input type="hidden" class="mapping"
               value='<?php echo ! empty( $api ) ? json_encode( $api ) : ''; ?>'
               data-prev_value=''
               data-new_value=''
        >
		<input type="hidden" class="losted_name"
			value='<?php echo $losted_name; ?>'
		>

        <div id="map-to-api__container">
            <div id="map-to-api__header">
                <h3 class="title">
                    <?php echo MapBuilderManager::get_clock_icon_for_map($activeMapId); ?>
                    <?php echo stripslashes( $activeMap->name ) ?>
                </h3>

                <div id="mapToApiControl" class="buttons-holder">
                    <?php

                    if ($is_initial_settings_done && $mapValid) {
                        ?>
                        <button id="btnSaveMapToApi" class="button button-primary">
                            <?php echo __( 'Save map', 'wp2leads' ); ?>
                        </button>

                        <button id="btnUpdateMapToApi" class="button button-primary">
                            <?php echo __( 'Save and Exit', 'wp2leads' ); ?>
                        </button>
                        <?php
                    }

                    ?>
                    <a id="exitMap" href="?page=wp2l-admin&tab=map_to_api" class="button button-primary">
                        <?php _e( 'Exit Map', 'wp2leads' ) ?>
                    </a>

                    <?php
                    $class = 'secondary';
                    $args  = array( 'disabled' => 'disabled' );

                    if ( $is_transfer_allowed ) {
                        $class = 'primary';
                        $args  = array(
                            'data-active-map'    => $activeMapId,
                            'data-cron-checked'  => $cron_checked,
                            'data-cron-selected' => $cron_selected,
                        );
                    }
                    ?>
                </div>
            </div>

            <div id="map-to-api__body" class="fixed-panel">
                <div id="map-to-api__map-list">
                    <?php require_once dirname( __FILE__ ) . '/wp2leads-admin-runner-map-list.php'; ?>
                </div>

                <?php
                if (!$mapValid) {
                    ?>
                    <div id="map-to-api__full-column">
                        <div class="notice notice-error inline" style="margin-top:0;margin-bottom:0;">
                            <p><?php _e('This saved map refers to a table no longer present in the database.', 'wp2leads') ?></p>
                        </div>
                    </div>
                    <?php
                } else {
                    ?>
                    <div id="map-to-api__left-column" class="panel-hidden">
						<div class="map-to-api-toggle-panel">
							<span class="dashicons dashicons-admin-collapse"></span>
							<span class="tp-button"><?php _e('DB Entries'); ?></span>
						</div>
                        <div class="map2api_body">
                            <?php
                            if (!$logged_in) {
                                ?>
                                <div class="no_available_options">
                                    <p><?php _e('You must be logged in to KT account to continue working with this map.', 'wp2leads') ?></p>
                                </div>
                                <?php
                            } elseif (!$is_initial_settings_done) {
                                ?>
                                <div class="no_available_options">
                                    <p><?php _e('Please make initial settings to continue working with this map.', 'wp2leads') ?></p>
                                </div>
                                <?php
                            } else {
                                ?>
                                <div class="available_options">
                                    <?php
                                    include_once 'ajax/available_options_page_empty.php';
                                    ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>

                    <div id="map-to-api__right-column" class="api-processing-holder">
                        <div class="map2api_side">
                            <div class="api_fields_container<?php echo $is_load_data ? '' : ' no-data-load' ?>">
                                <?php
                                if ( $logged_in ) {
                                    ?>
                                    <!-- Initial Settings Section - Start -->
                                    <div class="accordion-group">
                                        <?php include_once 'wp2leads-admin-map_to_api-initial-settings.php'; ?>
                                    </div>
                                    <!-- Initial Settings Section - End -->

                                    <!-- Overview Section - Start -->
                                    <div class="accordion-group">
                                        <?php include_once 'wp2leads-admin-map_to_api-overview.php'; ?>
                                    </div>
                                    <!-- Overview Section - End -->

                                    <div class="accordion-group">
                                        <!-- Optins Section - Start -->
                                        <?php include_once 'wp2leads-admin-map_to_api-optins.php'; ?>
                                        <!-- Optins Section - End -->

                                        <!-- API Fields Section - Start -->
                                        <?php include_once 'wp2leads-admin-map_to_api-fields.php'; ?>
                                        <!-- API Fields Section - End -->

                                        <hr>

                                        <h3 style="margin-bottom: 10px"><?php _e( 'Tags', 'wp2leads' ) ?></h3>

                                        <!-- Automatic Tags Section - Start -->
                                        <?php include_once 'wp2leads-admin-map_to_api-auto-tags.php'; ?>
                                        <!-- Automatic Tags Section - End -->

                                        <!-- Add Tags Section - Start -->
                                        <?php include_once 'wp2leads-admin-map_to_api-add-tags.php'; ?>
                                        <!-- Add Tags Section - End -->

                                        <!-- Untag Tags Section - Start -->
                                        <?php include_once 'wp2leads-admin-map_to_api-untag-tags.php'; ?>
                                        <!-- Untag Tags Section - End -->
                                    </div>
                                    <?php
                                } else { // KT Logged In false
                                    if ($logged_in_error && ('no_login_data' === $logged_in_error['error'] || 'login_failed' === $logged_in_error['error'])) {
                                        ?>
                                        <div class="accordion-group">
                                            <?php include_once 'wp2leads-admin-map_to_api-no-login-data.php'; ?>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>

                        <?php
                        $api_processing = '';

                        if ($is_initial_settings_done && $logged_in) {
                            $api_processing = ' api-processing';
                        }
                        ?>
                        <div class="api-spinner-holder<?php echo $api_processing; ?>">
                            <div class="api-spinner"></div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>

            <?php
            if ($is_initial_settings_done && $mapValid) {
                ?>
                <div id="mapToIpiControlSticky">
                    <button id="btnSaveMapToApiSticky" class="button button-primary icon-btn">
                        <svg fill="none" height="30" viewBox="0 0 20 20" width="30" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 5C3 3.89543 3.89543 3 5 3H13.3787C13.9091 3 14.4178 3.21071 14.7929 3.58579L16.4142 5.20711C16.7893 5.58218 17 6.09089 17 6.62132V15C17 16.1046 16.1046 17 15 17H5C3.89543 17 3 16.1046 3 15V5ZM5 4C4.44772 4 4 4.44772 4 5V15C4 15.5523 4.44772 16 5 16L5 11.5C5 10.6716 5.67157 10 6.5 10H13.5C14.3284 10 15 10.6716 15 11.5V16C15.5523 16 16 15.5523 16 15V6.62132C16 6.3561 15.8946 6.10175 15.7071 5.91421L14.0858 4.29289C13.8983 4.10536 13.6439 4 13.3787 4L13 4V6.5C13 7.32843 12.3284 8 11.5 8L7.5 8C6.67157 8 6 7.32843 6 6.5L6 4H5ZM7 4L7 6.5C7 6.77614 7.22386 7 7.5 7L11.5 7C11.7761 7 12 6.77614 12 6.5V4L7 4ZM14 16V11.5C14 11.2239 13.7761 11 13.5 11H6.5C6.22386 11 6 11.2239 6 11.5V16H14Z" fill="#fff"/>
                        </svg>
                    </button>

                    <button id="btnUpdateMapToApiSticky" class="button button-primary icon-btn">
                        <svg fill="none" height="30" viewBox="0 0 20 20" width="30" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 5C3 3.89543 3.89543 3 5 3H13.3787C13.9091 3 14.4178 3.21071 14.7929 3.58579L16.4142 5.20711C16.7893 5.58218 17 6.09089 17 6.62132V9.59971C16.6832 9.43777 16.3486 9.30564 16 9.20703V6.62132C16 6.3561 15.8946 6.10175 15.7071 5.91421L14.0858 4.29289C13.8983 4.10536 13.6439 4 13.3787 4L13 4V6.5C13 7.32843 12.3284 8 11.5 8L7.5 8C6.67157 8 6 7.32843 6 6.5L6 4H5C4.44772 4 4 4.44772 4 5V15C4 15.5523 4.44772 16 5 16L5 11.5C5 10.6716 5.67157 10 6.5 10H11.3369C10.9338 10.2839 10.5705 10.6206 10.2572 11H6.5C6.22386 11 6 11.2239 6 11.5V16H9.20703C9.30564 16.3486 9.43777 16.6832 9.59971 17H5C3.89543 17 3 16.1046 3 15V5ZM7 4L7 6.5C7 6.77614 7.22386 7 7.5 7L11.5 7C11.7761 7 12 6.77614 12 6.5V4L7 4ZM19 14.5C19 16.9853 16.9853 19 14.5 19C12.0147 19 10 16.9853 10 14.5C10 12.0147 12.0147 10 14.5 10C16.9853 10 19 12.0147 19 14.5ZM16.8532 14.854L16.8557 14.8514C16.9026 14.804 16.938 14.7495 16.9621 14.6914C16.9861 14.6333 16.9996 14.5697 17 14.503L17 14.5L17 14.497C16.9996 14.4303 16.9861 14.3667 16.9621 14.3086C16.9377 14.2496 16.9015 14.1944 16.8536 14.1464L14.8536 12.1464C14.6583 11.9512 14.3417 11.9512 14.1464 12.1464C13.9512 12.3417 13.9512 12.6583 14.1464 12.8536L15.2929 14H12.5C12.2239 14 12 14.2239 12 14.5C12 14.7761 12.2239 15 12.5 15H15.2929L14.1464 16.1464C13.9512 16.3417 13.9512 16.6583 14.1464 16.8536C14.3417 17.0488 14.6583 17.0488 14.8536 16.8536L16.8532 14.854Z" fill="#fff"/>
                        </svg>
                    </button>

                    <a id="exitMapSticky" href="?page=wp2l-admin&tab=map_to_api" class="button button-primary icon-btn">
                        <svg fill="none" height="30" viewBox="0 0 20 20" width="30" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 2C4.89543 2 4 2.89543 4 4V16C4 17.1046 4.89543 18 6 18H10.2572C10.0035 17.6929 9.78261 17.3578 9.59971 17H6C5.44772 17 5 16.5523 5 16V4C5 3.44772 5.44772 3 6 3H14C14.5523 3 15 3.44772 15 4V9.02242C15.3434 9.05337 15.6777 9.11588 16 9.20703V4C16 2.89543 15.1046 2 14 2H6ZM8 10C8 10.5523 7.55228 11 7 11C6.44772 11 6 10.5523 6 10C6 9.44771 6.44772 9 7 9C7.55228 9 8 9.44771 8 10ZM14.5 19C16.9853 19 19 16.9853 19 14.5C19 12.0147 16.9853 10 14.5 10C12.0147 10 10 12.0147 10 14.5C10 16.9853 12.0147 19 14.5 19ZM14.8536 16.8536C14.6583 17.0488 14.3417 17.0488 14.1464 16.8536C13.9512 16.6583 13.9512 16.3417 14.1464 16.1464L15.2929 15H12.5C12.2239 15 12 14.7761 12 14.5C12 14.2239 12.2239 14 12.5 14H15.2929L14.1464 12.8536C13.9512 12.6583 13.9512 12.3417 14.1464 12.1464C14.3417 11.9512 14.6583 11.9512 14.8536 12.1464L16.8536 14.1464C16.9015 14.1944 16.9377 14.2496 16.9621 14.3086C16.9861 14.3667 16.9996 14.4303 17 14.497L17 14.5L17 14.503C16.9996 14.5697 16.9861 14.6333 16.9621 14.6914C16.938 14.7495 16.9026 14.804 16.8557 14.8514L16.8532 14.854L14.8536 16.8536Z" fill="#fff"/>
                        </svg>
                    </a>
                </div>
                <div id="tagsHolderControl">
                    <button  class="button button-primary icon-btn">
                        <svg fill="none" height="30" viewBox="0 0 20 20" width="30" xmlns="http://www.w3.org/2000/svg"><path d="M13.5 6.5C14.0523 6.5 14.5 6.05228 14.5 5.5C14.5 4.94772 14.0523 4.5 13.5 4.5C12.9477 4.5 12.5 4.94772 12.5 5.5C12.5 6.05228 12.9477 6.5 13.5 6.5ZM9.20711 2.58579C9.58218 2.21071 10.0909 2 10.6213 2H15.0732C16.1778 2 17.0732 2.89543 17.0732 4V8.37426C17.0732 8.90818 16.8598 9.41993 16.4803 9.79556L10.6624 15.5553C9.88023 16.3297 8.61938 16.3265 7.8411 15.5482L3.45711 11.1642C2.67606 10.3832 2.67606 9.11684 3.45711 8.33579L9.20711 2.58579ZM10.6213 3C10.3561 3 10.1017 3.10536 9.91421 3.29289L4.16421 9.04289C3.77369 9.43342 3.77369 10.0666 4.16421 10.4571L8.54821 14.8411C8.93735 15.2302 9.56777 15.2318 9.95886 14.8446L15.7768 9.08491C15.9665 8.89709 16.0732 8.64122 16.0732 8.37426V4C16.0732 3.44772 15.6255 3 15.0732 3H10.6213ZM2.99709 11.8001C2.73198 12.5111 2.88524 13.3427 3.45688 13.9143L6.43375 16.8912C7.9903 18.4478 10.512 18.4541 12.0764 16.9054L16.4801 12.5457C16.8595 12.1701 17.073 11.6583 17.073 11.1244V10.5522L12.0764 15.5053C12.0281 15.5531 11.9789 15.5994 11.9289 15.6442L11.3728 16.1947C10.1995 17.3563 8.30827 17.3515 7.14086 16.1841L6.64236 15.6856C6.5711 15.6237 6.50151 15.5589 6.43375 15.4911L3.45688 12.5143C3.24747 12.3049 3.09421 12.0606 2.99709 11.8001Z" fill="#fff"/></svg>
                        <span id="tagsCounter">
                            <span id="counterHolder">0</span>
                            <div id="tagsLoader">
                                <span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>
                            </div>
                        </span>
                    </button>
                </div>

                <div id="stickyTagsHolder">
                    <h3 class="accordion-subheader">
                        <?php _e( 'Possible Tags from current set of data:', 'wp2leads' ) ?>
                    </h3>

                    <div id="stickyTagsHolderInner"></div>
                    <div id="tagsHolderLoader">
                        <span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>
                    </div>
                    <div id="closeStickyTagsHolder">
                        ×
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}
