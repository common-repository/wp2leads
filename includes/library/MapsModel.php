<?php
/**
 * Created by PhpStorm.
 * User: oleksii.khodakivskyi
 * Date: 06.09.18
 * Time: 23:31
 */

class MapsModel {
    /**
     * Table name in the database
     *
     * @var string
     */
    public static $table_name = 'wp2l_maps';

    public static function get_table() {
        global $wpdb;

        return $wpdb->prefix . self::$table_name;
    }

    public static function get( $map_id ) {
        global $wpdb;

        if (!$map_id) {
            return null;
        }

        $table = self::get_table();
        $map = $wpdb->get_row("SELECT * FROM $table WHERE id='$map_id'");

        if (!$map) return $map;

        return apply_filters('wp2leads_get_map_by_id', $map, $map_id);
    }

	public static function get_last_by_map_id( $map_id ) {
        global $wpdb;

        if (!$map_id) {
            return null;
        }

        $table = self::get_table();

        return $wpdb->get_row("SELECT * FROM $table WHERE info LIKE '%$map_id%' ORDER BY id DESC LIMIT 1;");
    }

    /**
     * Create New Map
     */
    public static function create( $data ) {
        global $wpdb;

        $info_array = array();
        $domain = Wp2leads_Admin::get_site_domain();
        $info_array['domain'] = $domain;
        $info_array['search'] = stripslashes($data['search']);
        $info_array['searchTable'] = stripslashes($data['searchTable']);
        $info_array['possibleUsedTags'] = json_decode(stripslashes($data['possibleUsedTags']), true);
        $info_array['publicMapId'] = '';
        $info_array['publicMapHash'] = '';
        $info_array['publicMapContent'] = '';
        $info_array['publicMapOwner'] = '';
        $info_array['publicMapKind'] = '';
        $info_array['publicMapStatus'] = '';
        $info_array['publicMapVersion'] = '';
        $info_array['map_kind'] = isset($data['map_kind']) ? $data['map_kind'] : '';
        $info_array['initial_settings'] = isset($data['initial_settings']) ? $data['initial_settings'] : '';

        if (!empty( $data['map'] ) && !empty( $data['api'] )) {

            $mapping = stripslashes($data['map']);
            $api = stripslashes($data['api']);
            $api_decoded = unserialize($api);

			// clear notice if exist
			if (isset($api_decoded['remove_notice'])) {
				Wp2leads_Notices::delete($api_decoded['remove_notice']);
				unset($api_decoded['remove_notice']);
			}

			if (empty($data['initial_settings'])) {
                if (isset($api_decoded['tags_prefix'])) {
                    unset($api_decoded['tags_prefix']);
                }

                if (isset($api_decoded['start_date_data'])) {
                    unset($api_decoded['start_date_data']);
                }

                if (isset($api_decoded['end_date_data'])) {
                    unset($api_decoded['end_date_data']);
                }
            }

            $result = $wpdb->insert(
                self::get_table(),
                [
                    'time' => date('Y-m-d H:i:s'),
                    'name' => sanitize_text_field($data['name']),
                    'mapping' => serialize(json_decode($mapping, true)),
                    'api' => serialize($api_decoded),
                    'info'  => serialize($info_array),
                ],
                ['%s', '%s', '%s', '%s', '%s']
            );

        } else if (!empty($data['map'])) {
            $mapping = stripslashes($data['map']);
            // create
            $result = $wpdb->insert(
                self::get_table(),
                [
                    'time' => date('Y-m-d H:i:s'),
                    'name' => sanitize_text_field($data['name']),
                    'mapping' => serialize(json_decode($mapping, true)),
                    'info'  => serialize($info_array),
                ],
                ['%s', '%s', '%s']
            );
        }

        if (!$result) {
            return false;
        }

        return array(
            'mapping' => $mapping, 'map_id' => $wpdb->insert_id, 'map_owner' => $domain
        );
    }

    /**
     * Update Map
     */
    public static function update( $data ) {

        global $wpdb;

        $map_id = $data['map_id'];

		// add replace_table and form_code if need
		if (!empty($data['map'])) {
			// get old map
			$old_map = MapsModel::get( $map_id );

			if ($old_map) {
				$old_mapping = unserialize($old_map->mapping);
				$new_mapping = json_decode(stripslashes($data['map']), true);

				// check form
				if (!empty($new_mapping['comparisons'])) {
					foreach ($new_mapping['comparisons'] as $comparison) {
						if ($comparison['tableColumn'] == 'vxcf_leads.form_id') {
							foreach ($comparison['conditions'] as $condition) {
								$new_mapping['form_code'] = $condition['string'];
							}
						}
					}
				}

				// check replace table
				if (!empty($old_mapping['replace_table']) && !isset($new_mapping['replace_table'])) {
					$new_mapping['replace_table'] = $old_mapping['replace_table'];
				}

				$data['map'] = json_encode($new_mapping);
			}
		}

        if (!empty( $data['map'] ) && !empty( $data['api'] )) {
            $mapping = stripslashes($data['map']);

            $api = stripslashes($data['api']);
            $api_decoded = unserialize($api);

			// clear notice if exist
			if (isset($api_decoded['remove_notice'])) {
				Wp2leads_Notices::delete($api_decoded['remove_notice']);
				unset($api_decoded['remove_notice']);
			}

            if (isset($api_decoded['tags_prefix'])) {
                unset($api_decoded['tags_prefix']);
            }

            if (isset($api_decoded['start_date_data'])) {
                unset($api_decoded['start_date_data']);
            }

            if (isset($api_decoded['end_date_data'])) {
                unset($api_decoded['end_date_data']);
            }

            $api = serialize($api_decoded);

            $info_array['domain'] = $data['domain'];
            $info_array['search'] = stripslashes($data['search']);
            $info_array['searchTable'] = stripslashes($data['searchTable']);
            $info_array['possibleUsedTags'] = json_decode(stripslashes($data['possibleUsedTags']), true);
            $info_array['serverId'] = stripslashes($data['serverId']);
            $info_array['publicMapId'] = stripslashes($data['publicMapId']);
            $info_array['publicMapHash'] = stripslashes($data['publicMapHash']);
            $info_array['publicMapContent'] = stripslashes($data['publicMapContent']);
            $info_array['publicMapKind'] = stripslashes($data['publicMapKind']);
            $info_array['publicMapOwner'] = stripslashes($data['publicMapOwner']);
            $info_array['publicMapStatus'] = stripslashes($data['publicMapStatus']);
            $info_array['publicMapVersion'] = stripslashes($data['publicMapVersion']);
            // update
            $result = $wpdb->update(
                self::get_table(),
                [
                    'time' => date('Y-m-d H:i:s'),
                    'name' => sanitize_text_field($data['name']),
                    'mapping' => serialize(json_decode($mapping, true)),
                    'api' => $api,
                    'info'  => serialize($info_array)
                ],
                [
                    'id' => $map_id
                ],
                ['%s', '%s', '%s', '%s', '%s'],
                ['%d']
            );
        } else if ( !empty( $data['map'] ) ) {
            $mapping = stripslashes($data['map']);

            $info_array['domain'] = $data['domain'];
            $info_array['search'] = !empty($data['search']) ? stripslashes($data['search']) : '';
            $info_array['searchTable'] = !empty($data['searchTable']) ? stripslashes($data['searchTable']) : '';
            $info_array['possibleUsedTags'] = json_decode(stripslashes($data['possibleUsedTags']), true);
            $info_array['serverId'] = stripslashes($data['serverId']);
            $info_array['publicMapId'] = stripslashes($data['publicMapId']);
            $info_array['publicMapHash'] = stripslashes($data['publicMapHash']);
            $info_array['publicMapContent'] = stripslashes($data['publicMapContent']);
            $info_array['publicMapKind'] = stripslashes($data['publicMapKind']);
            $info_array['publicMapOwner'] = stripslashes($data['publicMapOwner']);
            $info_array['publicMapStatus'] = stripslashes($data['publicMapStatus']);
            $info_array['publicMapVersion'] = stripslashes($data['publicMapVersion']);

            if ($data['initialSettings'] === '1') {
                $info_array['initial_settings'] = true;
            }

            if ($data['isExclusive'] === '1') {
                $info_array['isExclusive'] = true;
            } else {
                $info_array['isExclusive'] = false;
            }

            // update
            $result = $wpdb->update(
                self::get_table(),
                [
                    'time' => date('Y-m-d H:i:s'),
                    'name' => sanitize_text_field($data['name']),
                    'mapping' => serialize(json_decode($mapping, true)),
                    'info'  => serialize($info_array),
                ],
                [
                    'id' => $map_id
                ],
                ['%s', '%s', '%s'],
                ['%d']
            );
        } else if ( !empty( $data['api'] ) ) {
            $mapping = stripslashes($data['api']); // Map to API

            $api_to_save = json_decode($mapping, true);

			if (isset($api_to_save['remove_notice'])) {
				Wp2leads_Notices::delete($api_to_save['remove_notice']);
				unset($api_to_save['remove_notice']);
			}

            $api_to_save = serialize($api_to_save);

            $result = $wpdb->update(
                self::get_table(),
                [
                    'time' => date('Y-m-d H:i:s'),
                    'api' => $api_to_save
                ],
                [
                    'id' => $map_id
                ],
                ['%s','%s'],
                ['%d']
            );
        }

        if (false === $result) {
            return false;
        }

        return array( 'mapping' => $mapping, 'map_id' => $map_id );
    }

    public static function delete( $map_id ) {
        global $wpdb;

        return false !== $wpdb->delete(self::get_table(), ['id' => $map_id], ['%d']);
    }

    /**
     * @param $map
     * @param int $limit
     * @param int $offset
     * @param bool $get_empty
     * @param null $map_id
     *
     * @return array|bool|object|null
     */
    public static function get_map_query_results($map, $limit = 100, $offset = 0, $get_empty = true, $map_id = null) {
        global $wpdb;

        // $memory = (memory_get_usage()/1048576);

        $fetch_all_columns = self::fetch_all_columns_for_map(array('is_new_map' => true, 'new_map' => $map));
        $all_columns = $map['selects'];
        $all_columns = array_merge($all_columns, $fetch_all_columns);
        $excluded_columns = !empty($map['excludes']) ? $map['excludes'] : false;

        if (!empty($map["excludesFilters"]) && $excluded_columns) {
            foreach ($excluded_columns as $index => $excluded_column) {
                if (strpos($excluded_column, 'v.') === 0) {
                    foreach ($map["excludesFilters"] as $excludes_filter) {
                        if (false !== strpos($excluded_column, $excludes_filter)) {
                            unset($excluded_columns[$index]);
                            $all_columns[] = $excluded_column;
                        }
                    }
                }
            }

            $map['excludes'] = $excluded_columns;
        }

        $all_columns = array_unique($all_columns);

        if ($excluded_columns && is_array($excluded_columns)) {
            $map_columns = array_diff($all_columns, $excluded_columns);
        } else {
            $map_columns = $all_columns;
        }

        $selects_only = $map["selects_only"];
        $old_selects_only_count = count($selects_only);

        if (!empty($map["excludesFilters"]) && !empty($map_columns)) {
            foreach ($map_columns as $index => $map_column) {
                if (strpos($map_column, 'v.') === 0) {
                    foreach ($map["excludesFilters"] as $excludes_filter) {
                        if (false !== strpos($map_column, $excludes_filter)) {
                            $selects_only[] = $map_column;
                        }
                    }
                }
            }
        }

        $selects_only = array_unique($selects_only);
        $new_selects_only_count = count($selects_only);

        $map["selects_only"] = apply_filters('wp2leads_map_selects_only', $selects_only, $map);
        $map["selects"] = apply_filters('wp2leads_map_selects_only', $map["selects"], $map);

        if (($old_selects_only_count < $new_selects_only_count) && !empty($map_id)) {
            $update_mapping = self::updateMapCell($map_id, 'mapping', serialize($map));
            $new_fields_notice = sprintf (__('You have new fields in your map ID %s, please check them on <a href="?page=wp2l-admin&tab=map_to_api&active_mapping=%s">Map To API tab</a>.', 'wp2leads'), $map_id, $map_id );
            $new_notice = Wp2leads_Notices::add_notice($new_fields_notice);
        } elseif (($old_selects_only_count > $new_selects_only_count) && !empty($map_id)) {
            $update_mapping = self::updateMapCell($map_id, 'mapping', serialize($map));
        }

        $map['full_selects'] = self::get_selections_for_query($map);
        $query = self::generate_map_query($map, $limit, $offset);

        if (empty($query)) {
            return false;
        }

        $results = $wpdb->get_results($query);
        $v_names = array();

        // $memory = (memory_get_usage()/1048576);

        if (!empty($map["selects_only"])) {
            foreach ($map["selects_only"] as $map_column_name) {
                if(strpos($map_column_name, 'v.') === 0) {
                    if (false === strpos($map_column_name, '_oembed_')) {
                        $v_names[] = $map_column_name;
                    }
                }
            }
        } else {
            foreach ($map_columns as $map_column_name) {
                if(strpos($map_column_name, 'v.') === 0) {
                    if (false === strpos($map_column_name, '_oembed_')) {
                        $v_names[] = $map_column_name;
                    }
                }
            }
        }

        if(isset($map['virtual_relationships']) && count($map['virtual_relationships']) > 0) {
            foreach($results as $i => $result) {
                foreach ($map['virtual_relationships'] as $vr) {
                    $table_from_key = explode('.', $vr['column_key'])[0];
                    $table_from_value = explode('.', $vr['column_value'])[0];
                    $column_key_name = explode('.', $vr['column_key'])[1];
                    $column_key_value = explode('.', $vr['column_value'])[1];

                    $from = ' FROM ';

                    if ($table_from_key === $table_from_value) {
                        $from .= $wpdb->prefix . $table_from_key;
                    } else {
                        $from .= $wpdb->prefix . $table_from_key . ', ' . $wpdb->prefix . $table_from_value;
                    }

                    $field_value = isset($result->{$vr['table_from'] . '.' . $vr['column_from']}) ? $result->{$vr['table_from'] . '.' . $vr['column_from']} : NULL;
                    $compare = $wpdb->prefix . $vr['table_to'] . '.' . $vr['column_to'];

                    if (is_null($field_value)) {
                        $result_sub = false;
                    } else {
                        $where = 'WHERE ';
                        if (!is_null($field_value)) {
                            $where .= $compare . '=' . $field_value;

                            $sql = "SELECT {$wpdb->prefix}{$vr['column_key']}, {$wpdb->prefix}{$vr['column_value']} {$from} {$where};";
                        } else {
                            $where .= '1=1';

                            $sql = "SELECT DISTINCT {$wpdb->prefix}{$vr['column_key']} {$from} {$where};";
                        }

                        $result_sub = $wpdb->get_results($sql);
                    }

                    if (!empty($result_sub)) {
                        foreach ($result_sub as $item) {
                            if (false !== strpos($item->{$column_key_name}, '_oembed_')) {
                                continue;
                            }
                            $column_post_name = $item->{$column_key_name};
                            $column_post_name = str_replace($wpdb->prefix, '', $column_post_name);
                            $column_post_name = str_replace('.', '-', $column_post_name);
                            $v_column_name = 'v.' . $table_from_key . '-' . $column_post_name;

                            if (isset($map['excludes']) && is_array($map['excludes'])) {
                                if(in_array($v_column_name, $map['excludes'])) {
                                    continue;
                                }
                            }

                            if (isset($map['selects_only']) && is_array($map['selects_only'])) {
                                if(!in_array($v_column_name, $map['selects_only'])) {
                                    $exclude = true;
                                    if (!empty($map["excludesFilters"])) {
                                        foreach ($map["excludesFilters"] as $excludes_filter) {
                                            if (false !== strpos($excludes_filter, $v_column_name)) {
                                                $exclude = false;
                                            }
                                        }
                                    }

                                    if ($exclude) {
                                        continue;
                                    }
                                }
                            }

                            if (property_exists($item, $column_key_value)) {
                                $value = self::checkValue( $item->{$column_key_value} );
                            } else {
                                $value = '';
                            }

                            $v_names[] = $v_column_name;

                            if (isset($results[$i]->{$v_column_name}) && !empty($value)) {
                                if (strpos($results[$i]->{$v_column_name}, $value) === false) {
                                    $results[$i]->{$v_column_name} .= $value .' ';
                                }
                            } else {
                                $results[$i]->{$v_column_name} = $value .' ';
                            }
                        }

                        $v_names = array_unique($v_names);
                    }

                    unset($result_sub);
                }

                unset($result);
            }
        }

        $count = count($results);

        apply_filters('wp2leads_map_query_results_before_comparison', $results, $map);

        for($i = 0; $i < $count; $i++) {
            if (isset($map['comparisons']) && count($map['comparisons'])) {
                foreach ($map['comparisons'] as $comparisons) {
                    if(!property_exists($results[$i], $comparisons['tableColumn'])) {
                        unset($results[$i]);
                        continue 2;
                    } else {
                        if (isset($comparisons['conditions'])) {
                            $value = trim(self::checkValue($results[$i]->{$comparisons['tableColumn']}));
                            $passed = false;

                            foreach ($comparisons['conditions'] as $condition) {
                                switch ($condition['operator']) {
                                    case 'like':
                                        if (trim($value) === $condition['string']) {
                                            $passed = true;
                                        }
                                        break;
                                    case 'not-like':
                                        if (trim($value) !== $condition['string']) {
                                            $passed = true;
                                        }
                                        break;
                                    case 'contains':
                                        if (strpos(strtolower($value), strtolower($condition['string'])) !== false) {
                                            $passed = true;
                                        }
                                        break;
                                    case 'not contains':
                                        if (strpos(strtolower($value), strtolower($condition['string'])) === false) {
                                            $passed = true;
                                        }
                                        break;
                                }
                            }

                            if (!$passed) {
                                unset($results[$i]);
                                continue 2;
                            }
                        }
                    }
                }
            }

            if ($get_empty) {
                foreach($v_names as $v_name) {
                    if(!property_exists($results[$i], $v_name)) {
                        $results[$i]->{$v_name} = '';
                    }
                }
            }

            $tmp = json_decode(json_encode($results[$i]), true);
            ksort($tmp);
            $results[$i] = (object)$tmp;

            unset($tmp);
        }

        $results = array_values($results);

		$filtered_results = array();

		if(!isset($map['replace_table']) || !$map['replace_table']) {
            apply_filters('wp2leads_map_query_results', $results, $map);
            return $results;
        } // no replacement table

		foreach ($results as $result) {
			foreach ($map['replace_table'] as $key => $val) {
				if (isset($result->$key) && $result->$key) {
					$result->$key = $val . $result->$key;
				}
			}

			$filtered_results[] = $result;
		}

        apply_filters('wp2leads_map_query_results', $filtered_results, $map);

        return $filtered_results;
    }

    public static function get_map_query_rows_count($map) {
        global $wpdb;

        $map['count'] = true;
        $query = MapsModel::generate_map_query($map, NULL, NULL);

        if (empty($query)) {
            return 0;
        }

        $results = $wpdb->get_results($query);

        return $results;
    }

    public static function generate_map_query($map, $limit = 100, $offset = 0) {
        global $wpdb;
        $values = array();
        $concat_columns = !empty($map['groupConcat']) ? is_array($map['groupConcat']) ? $map['groupConcat']: array($map['groupConcat']) : null;
        $concat_separator = !empty($map['groupConcatSeparator']) ? trim($map['groupConcatSeparator']) : ',';
        $concat_separator_replace = ';' === $concat_separator ? ',' : ';';
        $concat_separator_string = $concat_separator;
        if ($concat_separator_string === ',') $concat_separator_string = ', ';
        $concat = '';

        if (!empty($map['full_selects']['selects'])) {
            if (isset($map['excludes']) && !empty($map['excludes'])) {
                foreach ($map['excludes'] as $exclude) {
                    list($table, $column) = explode('.', $exclude);

                    if (isset($map['full_selects']['selects'][$table])) {
                        $key = array_search ($column, $map['full_selects']['selects'][$table]);

                        if ($key !== false) {
                            unset($map['full_selects']['selects'][$table][$key]);
                        }
                    }
                }
            }

            foreach ($map['full_selects']['selects'] as $table => $columns) {
                foreach ($columns as $column) {
                    $tableColumn = $table . '.' . $column;
                    if (!empty($concat_columns) && in_array($tableColumn, $concat_columns)) {
                        $tableColumnPrefixed = $wpdb->prefix . $table . '.' . $column;
                        $concat .= "GROUP_CONCAT(DISTINCT REPLACE({$tableColumnPrefixed}, '{$concat_separator}' , '{$concat_separator_replace}') ORDER BY {$tableColumnPrefixed} ASC SEPARATOR '{$concat_separator_string}') AS '{$tableColumn}(concatenated)', ";
                    } else {
                        // mysql specifier
                        array_push($values, $wpdb->prefix . $table);
                        array_push($values, $column);
                        // label the data
                        array_push($values, $table);
                        array_push($values, $column);
                    }
                }
            }

            $selectPlaceholders = implode(',', array_fill(0, count($values)/4, "`%s`.`%s` as '%s.%s'"));
        } elseif(isset($map['count']) && $map['count'] == true) {
            $selectPlaceholders = 'COUNT(*)';
        } else {
            // if, for some reason, all columns have been excluded...
            $selectPlaceholders = '*';
        }

        if (!isset($map['from'])) {
            return '';
        }

        $fromPlaceholder = '`%s`';
        array_push($values, $wpdb->prefix . $map['from']);

        $sql = "SELECT {$concat} {$selectPlaceholders} FROM {$fromPlaceholder} ";

        if (!empty($map['joins']) && count($map['joins'])) {
            foreach ($map['joins'] as $join) {
                if($join['joinTable'] !== $unindexedTable = self::unindexed_table_name($join['joinTable'])) {
                    // table has nickname...
                    $sql .= "LEFT JOIN `%s` as `%s` ON `%s`.`%s` = `%s`.`%s` ";
                    // there are 6 placeholders with nickname...
                    array_push($values, $wpdb->prefix . $unindexedTable);
                    array_push($values, $wpdb->prefix . $join['joinTable']);
                    array_push($values, $wpdb->prefix . $join['joinTable']);
                    array_push($values, $join['joinColumn']);
                    array_push($values, $wpdb->prefix . $join['referenceTable']);
                    array_push($values, $join['referenceColumn']);
                } else {
                    // table has no nickname...
                    $sql .= "LEFT JOIN `%s` ON `%s`.`%s` = `%s`.`%s` ";
                    // for every join statement, there are 5 placeholders
                    array_push($values, $wpdb->prefix . $join['joinTable']);
                    array_push($values, $wpdb->prefix . $join['joinTable']);
                    array_push($values, $join['joinColumn']);
                    array_push($values, $wpdb->prefix . $join['referenceTable']);
                    array_push($values, $join['referenceColumn']);
                }
            }
        }

        $comparisons = "WHERE 1=1 ";

        if (isset($map['comparisons']) && count($map['comparisons'])) {

            foreach ($map['comparisons'] as $comparison) {
                if (strpos($comparison['tableColumn'], 'v.') === false) {
                    list($table, $column) = explode('.', $comparison['tableColumn']);

                    if (isset($comparison['conditions'])) {
                        $connect_string = 'AND';
                        foreach ($comparison['conditions'] as $condition) {
                            $operator = '';
                            $value = trim($condition['string']);
                            switch ($condition['operator']) {
                                case 'like':
                                    $operator = '=';
                                    break;
                                case 'not-like':
                                    $operator = '!=';
                                    break;
                                case 'contains':
                                    $operator = 'LIKE';
                                    $value = '%' . trim($condition['string']) . '%';
                                    break;
                                case 'not contains':
                                    $operator = 'NOT LIKE';
                                    $value = '%' . trim($condition['string']) . '%';
                                    break;
                            }

                            $table_column = "`{$wpdb->prefix}{$table}`.`{$column}`";
                            $comparisons .= " {$connect_string} %s %s '%s'";

                            array_push($values, $table_column);
                            array_push($values, $operator);
                            array_push($values, $value);

                            $connect_string = 'OR';
                        }
                    }
                }
            }
        }

        $sql .= $comparisons;

        if (!empty($map['keyBy'])) {
            $sql .= ' GROUP BY %s';
            array_push($values, $wpdb->prefix . $map['keyBy']);
        }

        $sql .= ' ORDER BY `%s`.`%s` DESC';
        array_push($values, $wpdb->prefix . $map['from']);
        array_push($values, self::fetch_primary_key_for_table($map['from']));

        if (!is_null($limit)) {
            $sql .= " LIMIT %d";
            array_push($values, $limit);
        }

        if (!is_null($offset)) {
            $sql .= " OFFSET %d";
            array_push($values, $offset);
        }

        return vsprintf($sql, $values);
    }

    public static function get_selections_for_query( $map ) {
        $selects = array(
            'selects' => array(),
            'v_columns_counter' => 0
        );

        if ($map['selects']) {
            $v_columns = 0;
            foreach ($map['selects'] as $select) {
                list($table, $column) = explode('.', $select);

                if($table == 'v') {
                    $v_columns++;
                    continue;
                }

                if (!isset($selects['selects'][$table])) {
                    $selects['selects'][$table] = array();
                }

                $selects['selects'][$table][] = $column;
            }

            if (isset($map['joins']) && count($map['joins'])) {
                foreach ($map['joins'] as $join) {
                    if (!array_key_exists($join['joinTable'], $selects['selects'])) {
                        $selects['selects'][$join['joinTable']] = array();
                    }

                    $joining_table_columns = self::fetch_columns_for_table($join['joinTable']);

                    foreach ($joining_table_columns as $column) {
                        if (!in_array($column, $selects['selects'][$join['joinTable']])) {
                            $selects['selects'][$join['joinTable']][] = $column;
                        }
                    }
                }
            }

            $selects['v_columns_counter'] = $v_columns;
        }

        return $selects;
    }

    public static function fetch_columns_for_table( $table ) {
        global $wpdb;

        $table = self::unindexed_table_name( $table );

        return array_map(function ($item) {
            return $item->Field;
        }, $wpdb->get_results('DESCRIBE ' . $wpdb->prefix . $table . ';'));
    }

    public static function fetch_all_columns_for_map($options) {
        global $wpdb;

        if (empty($options['is_new_map'])) {
            $map = unserialize(MapsModel::get($options['map_id'])->mapping);
        } else {
            $map = $options['new_map'];
        }

        $map['full_selects'] = MapsModel::get_selections_for_query($map);
        $v_names = array();

        foreach ($map['full_selects']['selects'] as $table => $columns) {
            foreach ($columns as $column) {
                $b_columns[] = $table . '.' . $column;
            }
        }

        $main_table_columns = MapsModel::fetch_columns_for_table($map['from']);

        foreach ($main_table_columns as $column) {
            if (!in_array($column, $b_columns)) {
                $b_columns[] = $map['from'] . '.' . $column;
            }
        }

        if(isset($map['virtual_relationships']) || !empty($map['virtual_relationships'])) {
            foreach ($map['virtual_relationships'] as $vr) {
                $table_from_key = explode('.', $vr['column_key'])[0];
                $table_from_value = explode('.', $vr['column_value'])[0];
                $column_key_name = explode('.', $vr['column_key'])[1];
                $column_key_value = explode('.', $vr['column_value'])[1];

                $from = ' FROM ';
                $from .= $wpdb->prefix . $table_from_key;
                $where = 'WHERE ';
                $where .= '1=1';

                $sql = <<<EOL
                        SELECT 
                            DISTINCT {$wpdb->prefix}{$vr['column_key']}
                        {$from}
                        {$where};
EOL;

                $result_sub = $wpdb->get_results($sql);

                if (!empty($result_sub)) {
                    foreach ($result_sub as $item) {
                        if (false !== strpos($item->{$column_key_name}, '_oembed_')) {
                            continue;
                        }
                        $column_post_name = $item->{$column_key_name};
                        $column_post_name = str_replace($wpdb->prefix, '', $column_post_name);
                        $column_post_name = str_replace('.', '-', $column_post_name);
                        $v_column_name = 'v.' . $table_from_key . '-' . $column_post_name;
                        $v_names[] = $v_column_name;
                    }
                }
            }
            //}
        }

        $columns = array_values(array_unique(array_merge($b_columns, $v_names)));

        return $columns;
    }

    /**
     * @param $table
     *
     * @return mixed
     */
    public static function unindexed_table_name($table) {
        $exploded = explode('-', $table);

        if (1 === count($exploded)) {
            return $table;
        }

        return $exploded[0];
    }

    public static function fetch_primary_key_for_table($table)
    {
        global $wpdb;

        $table = self::unindexed_table_name($table);

        $results = $wpdb->get_results('DESCRIBE ' . $wpdb->prefix . $table . ';');

        $onlyPriKey = array_filter($results, function ($item) {
            return $item->Key == "PRI";
        });

        if (count($onlyPriKey)) {
            return $onlyPriKey[0]->Field;
        } else {
            return null;
        }
    }

    public static function checkValue($checking_value) {
        if ( !is_serialized( $checking_value ) ) {
            return $checking_value;
        }

        $value = @unserialize($checking_value);

        if (empty($value)) {
            $value = $checking_value;
        } else {
            if (is_array($value)) {
                $value = ApiHelper::arrayToString($value);

                $value = implode(',', $value);
            } else {
                $value = '';
            }
        }

        return $value;
    }

    public static function checkRecomendedTagsValue($checking_value) {
        if ( !is_serialized( $checking_value ) ) {
            return $checking_value;
        }

        $value = @unserialize($checking_value);

        if (empty($value)) {
            $value = $checking_value;
        } else {
            if (is_array($value)) {
                $value = ApiHelper::arrayToString($value);
            } else {
                $value = '';
            }
        }

        return $value;
    }

	// $map_id int
	// $column string like "mapping", db column
	// $value - string
	// return bool - result
	public static function updateMapCell ($map_id, $column, $value) {
		global $wpdb;
		// clear notice if exist
		if ('api' == $column && isset($value['remove_notice'])) {
			Wp2leads_Notices::delete($value['remove_notice']);
			unset($value['remove_notice']);
		}

		$result = $wpdb->update(
            self::get_table(),
            [ $column => $value ],
            [ 'id' => $map_id ]
        );


		return $result;
	}
}
