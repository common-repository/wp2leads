<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 27.10.18
 * Time: 20:03
 */

class MapBuilderManager {

    public static $excluded_maps = array(
        'wp2l_maps', 'wp2l_klicktipp', 'wp2leads_transfer_statistics', 'wptkt_log', 'wp2leads_transfer_failed'
    );


	public static function get_server() {
		if (defined( 'WP2LEADS_MAPS_SANDBOX' ) && WP2LEADS_MAPS_SANDBOX) {
			return 'aHR0cDovL21hcHMuc2FudGVncmEtaW50ZXJuYXRpb25hbC5jb20vc2VydmVyL21hcHMucGhw';
		} else {
			return 'aHR0cHM6Ly9tYXBzLndwMmxlYWRzLmNvbS9zZXJ2ZXIvbWFwcy5waHA=';
		}
	}
    public static function get_map_query_results_limit() {
        $system_info = SystemHelper::get_system_info();
        $php_memory_limit = $system_info['php_memory_limit'];

        if (268435456 <= $php_memory_limit) {
            return 7500;
        } elseif(234881024 <= $php_memory_limit) {
            return 6500;
        } elseif(201326592 <= $php_memory_limit) {
            return 5500;
        } elseif(167772160 <= $php_memory_limit) {
            return 4500;
        } elseif(134217728 <= $php_memory_limit) {
            return 3500;
        } elseif(100663296 <= $php_memory_limit) {
            return 2500;
        } elseif (67108864 <= $php_memory_limit) {
            return 1500;
        }

        return false;
    }

    /**
     * Get table results
     *
     * @param $table
     * @return mixed
     */
    public static function get_table_search_results($table, $column = false, $order = false) {
        global $wpdb;

        $columns = MapBuilderManager::fetch_columns_for_table($table);

        $temp_columns = $wpdb->get_results('DESCRIBE ' . $wpdb->prefix . MapBuilderManager::unindexedTableName($table) . ';');

        $columns_titles = array_map(function ($item) {
            return $item->Field . ' ('.strtoupper(str_replace(' unsigned', '', $item->Type)).')';
        }, $wpdb->get_results('DESCRIBE ' . $wpdb->prefix . MapBuilderManager::unindexedTableName($table) . ';'));

        $columns_titles = array_values($columns_titles);

        $table = $wpdb->prefix.$table;

        $orderColumn = !empty($column) ? $column : $columns[0];
        $order = !empty($order) ? strtoupper($order) : 'DESC';

        $query = $wpdb->prepare(
            "SELECT * FROM {$table} ORDER BY {$orderColumn} {$order} LIMIT %d",
            50
        );

        $results = $wpdb->get_results($query, ARRAY_A);

        return array('columns' => $columns, 'results' => $results, 'columnsTitles' => $columns_titles);
    }

    /**
     * Results for multisearch
     *
     * @param $string
     * @return mixed
     */
    public static function get_multisearch_results($string) {
        global $wpdb;

        $tables = MapBuilderManager::fetch_tables();

        $enriched_tables = array_map(function ($table) use ($wpdb) {
            return [
                'table' => $wpdb->prefix . $table,
                'columns' => MapBuilderManager::fetch_columns_for_table($table)
            ];
        }, $tables);

        $results = array();

        foreach ($enriched_tables as $enriched_table) {

            $table_title = str_replace($wpdb->prefix, '', $enriched_table['table']);
            $table_title_group = false;

            $total = 0;
            $table_title_array = explode('_', $table_title);
            if (count($table_title_array) > 1) {
                $table_title_group = $table_title_array[0];
            }

            foreach ($enriched_table['columns'] as $column) {
                $result_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM `{$enriched_table['table']}` WHERE `{$column}` LIKE '%s'",
                    '%' . $wpdb->esc_like($string) . '%'
                ));

                if ($result_count) {
                    $results[$table_title]['columns'][] = array(
                        'column' => $column,
                        'count' => $result_count
                    );

                    $total = $total + $result_count;

                    $results[$table_title]['total'] = $total;

                    if($table_title_group) {
                        $results[$table_title]['group'] = $table_title_group;
                    }
                }
            }
        }

        uasort($results, function($a,$b) {
            return ($a["total"] <= $b["total"]) ? 1 : -1;
        });

        return $results;
    }

    public static function contains_valid_json_maps($json)
    {
        foreach($json as $map) {
            if(!property_exists($map, 'name') || !property_exists($map, 'mapping')) {
                return false;
            }
        }
        return true;
    }

    public static function ingest_uploaded_json_maps($json)
    {
        global $wpdb;

        foreach ($json as $map) {
            $wpdb->insert(MapsModel::get_table(), [
                'name' => $map->name,
                'mapping' => $map->mapping,
                'time' => $map->time ? $map->time : date('Y-m-d H:i:s')
            ]);
        }
    }

    public static function get_multisearch_table($table, $column, $string) {
        global $wpdb;

        $columns = MapBuilderManager::fetch_columns_for_table($table);
        $table = $wpdb->prefix.$table;

        $query = $wpdb->prepare(
            "SELECT * FROM {$table} WHERE {$column} LIKE '%s'",
            '%' . $wpdb->esc_like($string) . '%'
        );

        $results = $wpdb->get_results($query, ARRAY_A);

        foreach ($results as $key => $result) {
            $column_value = $result[$column];
            $highlighted_value = str_replace($string, '<span class="highlighted">' . $string . '</span>', $column_value);
            $results[$key][$column] = $highlighted_value;
        }

        return array('columns' => $columns, 'results' => $results);
    }

    public static function get_single_table($table, $string) {
        global $wpdb;

        $columns = MapBuilderManager::fetch_columns_for_table($table);

        $columns_titles = array_map(function ($item) {
            return $item->Field . ' ('.strtoupper(str_replace(' unsigned', '', $item->Type)).')';
        }, $wpdb->get_results('DESCRIBE ' . $wpdb->prefix . MapBuilderManager::unindexedTableName($table) . ';'));

        $columns_titles = array_values($columns_titles);

        $table = $wpdb->prefix.$table;

        $query = "SELECT * FROM {$table} ";
        $i = 0;
        $placeholders = array();

        foreach ($columns as $column) {
            if ($i === 0) {
                $query .= " WHERE `{$column}` LIKE '%s'";

                $i++;
            } else {
                $query .= " OR `{$column}` LIKE '%s'";
            }

            $placeholders[] = '%' . $wpdb->esc_like($string) . '%';
        }

        $query .= ' LIMIT 1000';

        $query = $wpdb->prepare(
            $query,
            $placeholders
        );

        $results = $wpdb->get_results($query, ARRAY_A);

        return array('columns' => $columns_titles, 'results' => $results);
        // return array('columns' => $columns, 'results' => $results);
    }

    public static function fetch_tables() {
        global $wpdb;

        $tables = array_map(function ($item) use ($wpdb) {
            $tbl_name = array_values(get_object_vars($item))[0];
            $prefix = $wpdb->prefix;
            $is_prefixed = strpos($tbl_name, $prefix);
            if (0 !== $is_prefixed) return false;
            $tbl_name_no_pref = substr($tbl_name, strlen($prefix));
            return $tbl_name_no_pref;
        }, $wpdb->get_results('SHOW TABLES;'));

        foreach ($tables as $i => $table) {
            if (empty($table)) unset($tables[$i]);
        }

        $tables = array_values(array_diff($tables, apply_filters( 'wp2leads_executed_maps', MapBuilderManager::$excluded_maps )));

        return $tables;
    }

    public static function fetch_columns_for_table($table)
    {
        global $wpdb;

        $table = MapBuilderManager::unindexedTableName($table);

        return array_map(function ($item) {
            return $item->Field;
        }, $wpdb->get_results('DESCRIBE ' . $wpdb->prefix . $table . ';'));
    }

    public static function unindexedTableName($table)
    {
        @list($table, $index) = explode('-', $table);
        return $table;
    }

    public static function is_map_owner($map_info, $debug = false) {
        $site_url = Wp2leads_License::get_current_site();

        $map_owner_hash = MapBuilderManager::get_current_map_owner_hash();

        if (!empty($map_info['publicMapOwner'])) {
            if ($map_info['publicMapOwner'] === $map_owner_hash) {
                return true;
            }
        } elseif (!empty($map_info['domain']) && $map_info['domain'] === $site_url) {
            return true;
        }

        return false;
    }

    public static function get_current_map_owner_hash() {
        $is_user_free = Wp2leads_License::is_user_level('free');

        if ($is_user_free) {
            $current_user = wp_get_current_user();
            $license_email = $current_user->user_email;
            $license_key = '';

        } else {
            $license_info = Wp2leads_License::get_lecense_info();
            $license_email = $license_info['email'];
            $license_key = $license_info['key'];
        }

        $site_url = Wp2leads_License::get_current_site();

        $map_owner_hash = md5($site_url . $license_email . $license_key);

        return $map_owner_hash;
    }

    public static function is_map_on_server($map_info) {
        if (!empty($map_info['publicMapId']) && !empty($map_info['publicMapOwner']) && !empty($map_info['publicMapStatus'])) {
            return true;
        }

        return false;
    }

    public static function is_map_on_server_can_be_transfered($license_version, $map_kind) {
        $license_level = array(
            'pro'       => 3,
            'exclusive'       => 3,
            'essent'    => 2,
            'free'      => 1
        );

        return $license_level[$license_version] >= $license_level[$map_kind];
    }

    public static function is_map_on_server_outdated($map_id) {
        $map = MapsModel::get($map_id);
        $map_info = unserialize($map->info);

        if (MapBuilderManager::is_map_on_server($map_info)) {
            $server_map_content_hash = $map_info['publicMapContent'];
            $current_map_content_hash = MapBuilderManager::get_map_content_hash($map);

            if ($server_map_content_hash !== $current_map_content_hash) {
                return true;
            }
        }

        return false;
    }

    public static function get_map_content_hash($map) {
        $mapping = $map->mapping;
        $api = $map->api;

        return md5($mapping . $api);
    }

    public static function get_map_hash($mapping) {
        $map_from = !empty($mapping['from']) ? serialize($mapping['from']) : '';
        $map_joins = !empty($mapping['joins']) ? serialize($mapping['joins']) : '';
        $map_groupConcat = !empty($mapping['groupConcat']) ? serialize($mapping['groupConcat']) : '';
        $map_virtualRelationships = !empty($mapping['virtual_relationships']) ? serialize($mapping['virtual_relationships']) : '';
        $map_keyBy = !empty($mapping['keyBy']) ? serialize($mapping['keyBy']) : '';

        return md5($map_from . $map_joins . $map_groupConcat . $map_virtualRelationships . $map_keyBy);
    }

    public static function get_map_on_server_kind() {

    }

    public static function import_maps_from_server($maps) {

        global $wpdb;

        $table = $wpdb->prefix . 'wp2l_maps';
        $license_info = Wp2leads_License::get_lecense_info();
        $license_email = $license_info['email'];
        $license_key = $license_info['key'];
        $site_url = Wp2leads_License::get_current_site();

        $parameters = array (
            'license_email' => $license_email,
            'license_key' => $license_key,
            'site_url' => $site_url,
            'event' => 'import',
            'maps'  =>  serialize($maps)
        );

        $request = wp_remote_post(
            base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

        $response = json_decode(wp_remote_retrieve_body( $request ), true);

        if (200 === $response['code']) {
            $maps = $response['body']['maps'];
            $count = count($maps);

            if ($count === 0) {
                return false;
            }

            $imported_maps = array();

            foreach ($maps as $map) {
                $name = $map['name'];
                $mapping = $map['mapping'];
                $api = $map['api'];
                $info = $map['info'];

                $info = unserialize($info);
                $info['serverId'] = $map['id'];

                $info = serialize($info);

                $re = $wpdb->insert($table, [
                    'name' => $name,
                    'mapping' => $mapping,
                    'api' => $api,
                    'info' => $info,
                    'time' => date('Y-m-d H:i:s')
                ]);

                $lastid = $wpdb->insert_id;
                $imported_maps[] = $lastid;
            }

            return $imported_maps;
        }

        return false;

    }

	// if the name is exists - will add an index -1
	public static function import_map_by_public_id($id, $name = '') {

		global $wpdb;

        $table = $wpdb->prefix . 'wp2l_maps';
        $license_info = Wp2leads_License::get_lecense_info();
        $license_email = $license_info['email'];
        $license_key = $license_info['key'];
        $site_url = Wp2leads_License::get_current_site();

        $parameters = array (
            'license_email' => $license_email,
            'license_key' => $license_key,
            'site_url' => $site_url,
            'event' => 'import',
            'maps'  =>  serialize(array($id))
        );

        $request = wp_remote_post(
            base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

        $response = json_decode(wp_remote_retrieve_body( $request ), true);

        if (200 === $response['code']) {
            $maps = $response['body']['maps'];
            $count = count($maps);

            if ($count === 0) {
                return false;
            }

			$map = $maps[0];

			 if (!$name) $name = $map['name'];

			$title = $name;
			$i = 1;

			while (self::get_map_id_by_name($title)) {
				$i++;
				$title = $name . ' #' . $i;
			}

			$name = $title;


            $mapping = $map['mapping'];
            $api = $map['api'];
            $info = $map['info'];

            $info = unserialize($info);
            $info['serverId'] = $map['id'];

            $info = serialize($info);

            $re = $wpdb->insert($table, [
                'name' => $name,
                'mapping' => $mapping,
                'api' => $api,
                'info' => $info,
                'time' => date('Y-m-d H:i:s')
            ]);

			if($re) {
				return self::get_map_id_by_name($name);
			}
		}

		return false;
	}

    public static function import_pending_maps_from_server($maps) {
        global $wpdb;

        $table = $wpdb->prefix . 'wp2l_maps';
        $license_info = Wp2leads_License::get_lecense_info();
        $license_email = $license_info['email'];
        $license_key = $license_info['key'];
        $site_url = Wp2leads_License::get_current_site();

        $parameters = array (
            'license_email' => $license_email,
            'license_key' => $license_key,
            'site_url' => $site_url,
            'event' => 'import_pending',
            'maps'  =>  serialize($maps)
        );

        $request = wp_remote_post(
            base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

        $response = json_decode(wp_remote_retrieve_body( $request ), true);

        if (200 === $response['code']) {
            $maps = $response['body']['maps'];
            $count = count($maps);

            if ($count === 0) {
                return false;
            }

            $imported_maps = array();

            foreach ($maps as $map) {
                $name = $map['name'];
                $mapping = $map['mapping'];
                $api = $map['api'];
                $info = $map['info'];

                $wpdb->insert($table, [
                    'name' => $name,
                    'mapping' => $mapping,
                    'api' => $api,
                    'info' => $info,
                    'time' => date('Y-m-d H:i:s')
                ]);

                $lastid = $wpdb->insert_id;
                $imported_maps[] = $lastid;
            }

            return $imported_maps;
        }

        return false;

    }

    public static function export_maps_to_server($maps) {
        global $wpdb;
        $is_user_pro = Wp2leads_License::is_user_level('pro');

        $allowed_fields = ApiHelper::getDefaultApiFields();

        $table = $wpdb->prefix . 'wp2l_maps';
        $maps_to_export_count = count($maps);
        $maps_ids = array();
        $maps_version = array();
        $maps_exclusive = array();
        $maps_url = array();
		$maps_kt_url = array();
		$maps_names = array();
		$maps_descriptions = array();
		$maps_form_links = array();
		$export_form_data = array();

		// define arrays with data
        foreach ($maps as $map) {
            $maps_ids[] = $map['mapId'];
            $maps_version[$map['mapId']] = $map['mapVersion'];
            $maps_url[$map['mapId']] = $map['url'];
			$maps_kt_url[$map['mapId']] = $map['ktLinks'];
			$maps_names[$map['mapId']] = $map['mapName'];
			$maps_descriptions[$map['mapId']] = $map['mapDescription'];
            $maps_exclusive[$map['mapId']] = $map['isExclusive'] === 'true' ? true : false;
			$maps_examples[$map['mapId']] = $map['mapFormLink'];
        }

        $string_placeholders = array_fill(0, $maps_to_export_count, '%d');
        $placeholders_for_maps_id = implode(', ', $string_placeholders);

        $all_maps = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . $table . " WHERE id IN ($placeholders_for_maps_id)",
            $maps_ids
        ));

        foreach ($all_maps as $key => $map_obj) {
            $id = (int) $map_obj->id;

            $map_mapping = unserialize($map_obj->mapping);
            $map_api = unserialize($map_obj->api);
            $map_info = unserialize($map_obj->info);
			$map_meta = array();

            $map_version = !empty($maps_version[$id]) ? $maps_version[$id] : '';
            $map_url = !empty($maps_url[$id]) ? $maps_url[$id] : '';
			$map_kt_url = !empty($maps_kt_url[$id]) ? $maps_kt_url[$id] : '';
			$map_description = !empty($maps_descriptions[$id]) ? $maps_descriptions[$id] : '';
			$map_example_link = !empty($maps_examples[$id]) ? $maps_examples[$id] : '';

            $all_maps[$key]->map_kind = $map_version;
            $all_maps[$key]->map_url = $map_url;

            $map_exclusive = !empty($maps_exclusive[$id]) ? $maps_exclusive[$id] : false;
            $all_maps[$key]->map_exclusive = $is_user_pro ? $map_exclusive : false;

			$map_meta['kt_url'] = wp_strip_all_tags($map_kt_url);
			$map_meta['description'] = wp_strip_all_tags($map_description);
			$map_meta['example_link'] = wp_strip_all_tags($map_example_link);

			$map_export_id = time();

			while (isset($export_form_data['n_' . $map_export_id])) {
				$map_export_id++;
			}

            if (!empty($map_api['manually_selected_tags']['tag_ids'])) {
                $map_api['manually_selected_tags']['tag_ids'] = array();
            }

            if (!empty($map_api['detach_tags']['tag_ids'])) {
                $map_api['detach_tags']['tag_ids'] = array();
            }

            if (!empty($map_api['start_date_data'])) {
                unset($map_api['start_date_data']);
            }

            if (!empty($map_api['end_date_data'])) {
                unset($map_api['end_date_data']);
            }

            if (!empty($map_info['initial_settings'])) {
                unset($map_info['initial_settings']);
            }

			$map_info['map_export_id'] = $map_export_id;

			if (!empty($maps_names[$id])) $map_obj->name = $maps_names[$id];
            $map_obj->mapping = serialize($map_mapping);
            $map_obj->api = !empty($map_api) ? serialize($map_api) : '';
            $map_obj->info = serialize($map_info);
			$map_obj->meta = serialize($map_meta);

			// export form if exist
			if (!empty($map_mapping['form_code'])) {
				$code = explode('_', $map_mapping['form_code']);

				$export_form_data['n_' . $map_export_id] = array(
					'title' => $map_obj->name,
					'type' => $code[0],
					'form_id' => $code[1],
					'link' => $map_example_link,
					'map_id' => '',
					'map_title' => $map_obj->name,
					'server_id' => $map_info['serverId'],
					'kt_link' => $map_meta['kt_url']
				);
			}
        }

        $is_user_free = Wp2leads_License::is_user_level('free');

        if ($is_user_free) {
            $current_user = wp_get_current_user();
            $license_email = $current_user->user_email;
            $license_key = '';

        } else {
            $license_info = Wp2leads_License::get_lecense_info();
            $license_email = $license_info['email'];
            $license_key = $license_info['key'];
        }

        $site_url = Wp2leads_License::get_current_site();

        $parameters = array (
            'license_email' => $license_email,
            'license_key' => $license_key,
            'site_url' => $site_url,
            'event' => 'export',
            'maps'  =>  serialize($all_maps)
        );

        $request = wp_remote_post(
            base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

        $response = json_decode(wp_remote_retrieve_body( $request ), true);

        foreach ($response['body']['maps'] as $map_info) {
            $decoded_map_info = unserialize($map_info);

            foreach ($decoded_map_info as $map_id => $new_info) {
                $map_old = MapsModel::get($map_id);
                $map_old_info = unserialize($map_old->info);
                $map_old_initial = !empty($map_old_info['initial_settings']) ? $map_old_info['initial_settings'] : false;
                $new_info = unserialize($new_info);

                if ($map_old_initial) {
                    $new_info['initial_settings'] = true;
                }

                $wpdb->query("
                    UPDATE " . $table . " SET info = '".serialize($new_info)."'
                    WHERE id = ".$map_id);

				if (isset($new_info['map_export_id'])) {
					if (isset($export_form_data['n_' . $new_info['map_export_id']])) {
						$template_info = $export_form_data['n_' . $new_info['map_export_id']];

						Wp2leads_Catalog::export_form_template(
							$template_info['title'],
							$template_info['type'],
							$template_info['form_id'],
							$template_info['link'],
							$new_info['publicMapId'],
							$template_info['map_title'],
							'',
							$template_info['kt_link']
						);

						// add map like magic
						Wp2leads_MagicImport::add_new_magic_map($new_info['publicMapId'], '', $template_info['server_id']);
					}
				}
            }

        }
    }

    public static function update_maps_on_server($maps) {
        global $wpdb;
        $is_user_pro = Wp2leads_License::is_user_level('pro');

        $allowed_fields = ApiHelper::getDefaultApiFields();

        $table = $wpdb->prefix . 'wp2l_maps';
        $maps_to_export_count = count($maps);
        $maps_ids = array();
        $maps_version = array();
        $maps_exclusive = array();
        $maps_url = array();
		$maps_kt_url = array();
		$maps_names = array();
		$maps_descriptions = array();
		$maps_form_links = array();
		$export_form_data = array();

		// define arrays with data
        foreach ($maps as $map) {
            $maps_ids[] = $map['mapId'];
            $maps_version[$map['mapId']] = $map['mapVersion'];
            $maps_url[$map['mapId']] = $map['url'];
			$maps_kt_url[$map['mapId']] = $map['ktLinks'];
            $maps_exclusive[$map['mapId']] = $map['isExclusive'] === 'true' ? true : false;
			$maps_names[$map['mapId']] = $map['mapName'];
			$maps_descriptions[$map['mapId']] = $map['mapDescription'];
			$maps_examples[$map['mapId']] = $map['mapFormLink'];
        }

        $string_placeholders = array_fill(0, $maps_to_export_count, '%d');
        $placeholders_for_maps_id = implode(', ', $string_placeholders);

        $all_maps = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . $table . " WHERE id IN ($placeholders_for_maps_id)",
            $maps_ids
        ));

        foreach ($all_maps as $key => $map_obj) {
            $id = (int) $map_obj->id;

            $map_mapping = unserialize($map_obj->mapping);
            $map_api = unserialize($map_obj->api);
			$map_obj_info = unserialize($map_obj->info);
			$map_meta = array();

            $map_public_id = $map_obj_info['publicMapId'];
            $all_maps[$key]->public_map_id = $map_public_id;

            $map_version = !empty($maps_version[$id]) ? $maps_version[$id] : '';
            $map_url = !empty($maps_url[$id]) ? $maps_url[$id] : '';
			$map_kt_url = !empty($maps_kt_url[$id]) ? $maps_kt_url[$id] : '';
			$map_description = !empty($maps_descriptions[$id]) ? $maps_descriptions[$id] : '';
			$map_example_link = !empty($maps_examples[$id]) ? $maps_examples[$id] : '';

			$all_maps[$key]->map_kind = $map_version;
			$all_maps[$key]->map_url = $map_url;

            $map_exclusive = !empty($maps_exclusive[$id]) ? $maps_exclusive[$id] : false;
            $all_maps[$key]->map_exclusive = $is_user_pro ? $map_exclusive : false;

			$map_meta['kt_url'] = wp_strip_all_tags($map_kt_url);
			$map_meta['description'] = wp_strip_all_tags($map_description);
			$map_meta['example_link'] = wp_strip_all_tags($map_example_link);

			$map_export_id = time();

			while (isset($export_form_data['n_' . $map_export_id])) {
				$map_export_id++;
			}

            if (!empty($map_api['manually_selected_tags']['tag_ids'])) {
                $map_api['manually_selected_tags']['tag_ids'] = array();
            }

            if (!empty($map_api['detach_tags']['tag_ids'])) {
                $map_api['detach_tags']['tag_ids'] = array();
            }

            if (!empty($map_api['start_date_data'])) {
                unset($map_api['start_date_data']);
            }

            if (!empty($map_api['end_date_data'])) {
                unset($map_api['end_date_data']);
            }

            if (!empty($map_obj_info['initial_settings'])) {
                unset($map_obj_info['initial_settings']);
            }

			$map_obj_info['map_export_id'] = $map_export_id;

			if (!empty($maps_names[$id])) $map_obj->name = $maps_names[$id];
            $map_obj->mapping = serialize($map_mapping);
            $map_obj->api = !empty($map_api) ? serialize($map_api) : '';
            $map_obj->info = serialize($map_obj_info);
			$map_obj->meta = serialize($map_meta);

			// export form if exist
			if (!empty($map_mapping['form_code'])) {
				$code = explode('_', $map_mapping['form_code']);

				$export_form_data['n_' . $map_export_id] = array(
					'title' => $map_obj->name,
					'type' => $code[0],
					'form_id' => $code[1],
					'link' => $map_example_link,
					'map_id' => '',
					'map_title' => $map_obj->name,
					'server_id' => $map_obj_info['serverId'],
					'kt_link' => $map_meta['kt_url']
				);
			}
        }

        $is_user_free = Wp2leads_License::is_user_level('free');

        if ($is_user_free) {
            $current_user = wp_get_current_user();
            $license_email = $current_user->user_email;
            $license_key = '';

        } else {
            $license_info = Wp2leads_License::get_lecense_info();
            $license_email = $license_info['email'];
            $license_key = $license_info['key'];
        }

        $site_url = Wp2leads_License::get_current_site();

        $parameters = array (
            'license_email' => $license_email,
            'license_key' => $license_key,
            'site_url' => $site_url,
            'event' => 'update',
            'maps'  =>  serialize($all_maps)
        );

        $request = wp_remote_post(
            base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

        $response = json_decode(wp_remote_retrieve_body( $request ), true);

        foreach ($response['body']['maps'] as $map_info) {
            $decoded_map_info = unserialize($map_info);

            foreach ($decoded_map_info as $map_id => $new_info) {
                $map_old = MapsModel::get($map_id);
                $map_old_info = unserialize($map_old->info);
                $map_old_initial = !empty($map_old_info['initial_settings']) ? $map_old_info['initial_settings'] : false;
                $new_info = unserialize($new_info);

                if ($map_old_initial) {
                    $new_info['initial_settings'] = true;
                }

                $wpdb->query("
                    UPDATE " . $table . " SET info = '".serialize($new_info)."'
                    WHERE id = ".$map_id);

				if (isset($new_info['map_export_id'])) {
					if (isset($export_form_data['n_' . $new_info['map_export_id']])) {
						$template_info = $export_form_data['n_' . $new_info['map_export_id']];

						Wp2leads_Catalog::export_form_template(
							$template_info['title'],
							$template_info['type'],
							$template_info['form_id'],
							$template_info['link'],
							$new_info['publicMapId'],
							$template_info['map_title'],
							'',
							$template_info['kt_link']
						);

					}
				}
            }
        }
    }

    public static function check_map_status_on_server($map_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wp2l_maps';

        $map = MapsModel::get($map_id);
        $map_info = $map->info;
        $decoded_map_info = unserialize($map_info);
        $public_map_id = $decoded_map_info['publicMapId'];
        $license_info = Wp2leads_License::get_lecense_info();
        $license_email = $license_info['email'];
        $license_key = $license_info['key'];
        $site_url = Wp2leads_License::get_current_site();

        $parameters = array (
            'license_email' => $license_email,
            'license_key' => $license_key,
            'site_url' => $site_url,
            'event' => 'check_status',
            'map'  =>  $public_map_id
        );

        $request = wp_remote_post(
            base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

        $response = json_decode(wp_remote_retrieve_body( $request ), true);

        if (404 === $response['code']) {
            $decoded_map_info['publicMapId'] = '';
            $decoded_map_info['publicMapHash'] = '';
            $decoded_map_info['publicMapContent'] = '';
            $decoded_map_info['publicMapOwner'] = '';
            $decoded_map_info['publicMapKind'] = '';
            $decoded_map_info['publicMapStatus'] = '';

            $wpdb->query("
                UPDATE " . $table . " SET info = '".serialize($decoded_map_info)."'
                WHERE id = ".$map_id);

            return false;

        } elseif (200 === $response['code']) {
            $status = $response['body']['status'];
            $decoded_map_info['publicMapStatus'] = $status;

            $wpdb->query("
                UPDATE " . $table . " SET info = '".serialize($decoded_map_info)."'
                WHERE id = ".$map_id);

            return $status;
        }
    }

    public static function get_available_maps_from_server() {
        $license_info = Wp2leads_License::get_lecense_info();
        $license_email = $license_info['email'];
        $license_key = $license_info['key'];
        $site_url = Wp2leads_License::get_current_site();

        $parameters = array (
            'license_email' => $license_email,
            'license_key' => $license_key,
            'site_url' => $site_url,
            'event' => 'get_available'
        );

        $request = wp_remote_post(
			//base64_decode('aHR0cDovL3RvYmlhcy5tYXBzL3NlcnZlci9tYXBzLnBocA=='),
            base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

        $response = json_decode(wp_remote_retrieve_body( $request ), true);

        if (200 !== $response['code']) {
            return false;
        }

        $maps = $response['body']['maps'];

        if (count($maps) === 0) {
            return false;
        }

		// pin magic maps to the top
		$magic_maps = new Wp2leads_MagicImport();
		foreach ($maps as $key => $map) {
			$maps[$key]['magic'] = $magic_maps->is_have_magic($map['id']);

		}

		usort($maps, function($a, $b){
			if ($a['magic'] && $b['magic']) return 0;
			if (!$a['magic'] && !$b['magic']) return 0;

			if (!$a['magic'] && $b['magic']) return 1;
			if ($a['magic'] && !$b['magic']) return -1;
		});

        return $maps;
    }

	public static function get_maps_info_from_server($maps) {

        global $wpdb;

        $license_info = Wp2leads_License::get_lecense_info();
        $license_email = $license_info['email'];
        $license_key = $license_info['key'];
        $site_url = Wp2leads_License::get_current_site();

        $parameters = array (
            'license_email' => $license_email,
            'license_key' => $license_key,
            'site_url' => $site_url,
            'event' => 'import',
            'maps'  =>  serialize($maps)
        );

        $request = wp_remote_post(
            base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

        $response = json_decode(wp_remote_retrieve_body( $request ), true);

        if (200 === $response['code']) {
            $maps = $response['body']['maps'];
            $count = count($maps);

            if ($count === 0) {
                return false;
            }

            $imported_maps = array();

            foreach ($maps as $map) {
                $info = $map['info'];

                $info = unserialize($info);
                $imported_maps[$map['id']] = $info;
            }

            return $imported_maps;
        }

        return false;

    }

    public static function get_pending_maps_from_server() {
        $license_info = Wp2leads_License::get_lecense_info();
        $license_email = $license_info['email'];
        $license_key = $license_info['key'];
        $site_url = Wp2leads_License::get_current_site();

        $parameters = array (
            'license_email' => $license_email,
            'license_key' => $license_key,
            'site_url' => $site_url,
            'event' => 'get_pending'
        );

        $request = wp_remote_post(
            base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

        $response = json_decode(wp_remote_retrieve_body( $request ), true);

        if (200 !== $response['code']) {
            return false;
        }

        $maps = $response['body']['maps'];

        if (count($maps) === 0) {
            return false;
        }

        return $maps;
    }

    public static function upload_maps_to_server($maps) {
        $license_info = Wp2leads_License::get_lecense_info();

        $license_email = $license_info['email'];
        $license_key = $license_info['key'];
        $site_url = Wp2leads_License::get_current_site();

        $parameters = array(
            'license_email' => $license_email,
            'license_key' => $license_key,
            'site_url' => $site_url,
            'event' => 'export',
            'maps'  =>  serialize($maps)
        );

        $request = wp_remote_post(
            base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

        $response = json_decode(wp_remote_retrieve_body( $request ), true);
    }

	public static function get_map_id_by_name($name) {
		if (!$name) return false;

		global $wpdb;
		$table = $wpdb->prefix . 'wp2l_maps';
		$result = $wpdb->get_row( "SELECT * FROM $table WHERE  name = '" . $name . "'" );

		if ($result) return $result->id;

		return false;
	}

	public static function get_clock_icon_for_map($map_id) {
        $cron_status   = '';
        $cron_title    = '';

        $is_transfer_allowed = Wp2leads_License::is_map_transfer_allowed( $map_id );

        if (!$is_transfer_allowed) {
            $cron_status = '';
            $cron_title    = __( 'Transfer is not allowed', 'wp2leads' );
        } else {
            $module_active = false;

            $map = MapsModel::get($map_id);
            $mapping = unserialize( $map->mapping );
            $api = unserialize( $map->api );

            if (!empty($mapping["transferModule"])) {
                $transfer_modules = Wp2leads_Transfer_Modules::get_transfer_modules_class_names();

                if (!empty($transfer_modules[$mapping["transferModule"]])) {
                    $existed_modules_map = Wp2leads_Transfer_Modules::get_modules_map();

                    if (!empty($existed_modules_map[$mapping["transferModule"]][$map_id])) {
                        $module_active = true;
                    }
                }
            }

            if ($module_active) {
                $cron_status = ' module-active';
                $cron_title    = __( 'Transfer module is active', 'wp2leads' );
            } else {
                $cron_maps = Wp2LeadsCron::getScheduledMaps();

                if ( empty( $cron_maps[ 'map_' . $map_id ] ) || (!empty($cron_maps[ 'map_' . $map_id ]['status_to_change']) && 'remove_cron_schedule' === $cron_maps[ 'map_' . $map_id ]['status_to_change']) ) {
                    $cron_status   = '';
                } else {
                    $cron_status   = ' disabled';
                    $cron_title    = __( 'Cron disabled', 'wp2leads' );

                    if ( ! empty( $cron_maps[ 'map_' . $map_id ]['status'] ) && empty($cron_maps[ 'map_' . $map_id ]['status_to_change']) ) {
                        $cron_status  = ' active';
                        $cron_title   = __( 'Cron enabled', 'wp2leads' );
                    }
                }
            }
        }

        return '<span class="dashicons dashicons-clock'.$cron_status.'" title="'.$cron_title.'"></span>';
    }

	public static function update_map_meta_on_server($map_id, $key, $value) {
		//  map id  = id from maps_content!!
        $parameters = array(
            'event' => 'update_meta',
            'map_id'  =>  $map_id,
			'key'  =>  $key,
			'value'  =>  serialize($value),
        );

        $request = wp_remote_post(
            base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

        $response = json_decode(wp_remote_retrieve_body( $request ), true);
    }

	public static function get_map_meta_from_server($map_id, $key = false) {

		//  map id  = map_id from maps! will return active map meta
        $parameters = array(
            'event' => 'get_meta',
            'map_id'  =>  $map_id,
			'key'  =>  $key,
        );

        $request = wp_remote_post(
			base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

        $response = json_decode(wp_remote_retrieve_body( $request ), true);
		return $response['meta'];
    }

    public static function update_recomended_tags_prefixes($map_id, $tags_prefixes) {
        global $wpdb;

        $map = MapsModel::get( $map_id );
        $info = unserialize($map->info);
        $recomended_tags_prefixes = $tags_prefixes['recomended_tags_prefixes'];
        $standartTags = $tags_prefixes['recomended_tags'];

        if (!empty($info["possibleUsedTags"]["userInputTags"])) {
            foreach ($info["possibleUsedTags"]["userInputTags"] as $i => $userInputTag) {
                if (isset($recomended_tags_prefixes[$i])) {
                    $info["possibleUsedTags"]["userInputTags"][$i]['prefix'] = $recomended_tags_prefixes[$i];
                }
            }
        }

        $info["possibleUsedTags"]["standartTags"] = $standartTags;

        $wpdb->update(
            $wpdb->prefix . 'wp2l_maps',
            array('info' => serialize($info)),
            array('id' => $map_id)
        );
    }

    public static function get_recomended_tags($mapping, $prefix = '', $amount = NULL) {
        global $wpdb;

        $selects = !empty($mapping['selects']) ? array_unique($mapping['selects']) : false;
        $from = !empty($mapping['fromTable']) ? $mapping['fromTable'] : false;
        $comparisons = !empty($mapping['comparisons']) ? $mapping['comparisons'] : false;
        $joins = !empty($mapping['joins']) ? $mapping['joins'] : false;

        $full_selects = array(
            'selects' => []
        );

        if (!empty($selects)) {
            foreach ($selects as $select) {
                list($table, $column) = explode('.', $select);

                if (!isset($full_selects['selects'][$table])) {
                    $full_selects['selects'][$table] = array();
                }

                $full_selects['selects'][$table][] = $column;
            }

            if (!empty($joins)) {
                foreach ($joins as $join) {
                    if (!array_key_exists($join['joinTable'], $full_selects['selects'])) {
                        $full_selects['selects'][$join['joinTable']] = array();
                    }

                    $joining_table_columns = MapsModel::fetch_columns_for_table($join['joinTable']);

                    foreach ($joining_table_columns as $column) {
                        if (!in_array($column, $full_selects['selects'][$join['joinTable']])) {
                            $full_selects['selects'][$join['joinTable']][] = $column;
                        }
                    }
                }
            }
        }

        if ($mapping) {
            $map = array(
                'selects' => $selects,
                'from' => $from,
                'excludes' => false,
                'keyBy' => $mapping['groupBy'],
                'comparisons' => $comparisons,
                'groupConcat' => false,
                'joins' => $joins,
                'full_selects' => $full_selects
            );

            $count = array(
                'selects' => $selects,
                'from' => $from,
                'excludes' => false,
                'keyBy' => $mapping['groupBy'],
                'comparisons' => $comparisons,
                'groupConcat' => false,
                'joins' => $joins,
                'count' => true
            );

            $query = MapsModel::generate_map_query($map, $amount, NULL);
            $queryCount = MapsModel::generate_map_query($count, NULL, NULL);

            $result = $wpdb->get_results($query);
            $count = $wpdb->get_results($queryCount);

            $tags = array();

            if (count($result) > 0) {
                foreach ($result as $i => $item) {
                    $tag = $item->{$mapping['tagColumn']};
                    $tag = MapsModel::checkRecomendedTagsValue($tag);

                    if (is_array($tag)) {
                        foreach ($tag as $value) {
                            $filtered_tag = trim(ApiHelper::filterBeforeOutput(ApiHelper::filterForbidenKTSymbols(ApiHelper::remove_wp_emoji($value))));
                            $tags[] = $prefix . $filtered_tag;
                        }
                    } else {
                        $filtered_tag = trim(ApiHelper::filterBeforeOutput(ApiHelper::filterForbidenKTSymbols(ApiHelper::remove_wp_emoji($tag))));
                        $tags[] = $prefix . $filtered_tag;
                    }
                }
            }

            return [
                'count' => count($count),
                'result' => $result,
                'tags' => array_values(array_unique($tags))
            ];
        }

        return false;
    }
}