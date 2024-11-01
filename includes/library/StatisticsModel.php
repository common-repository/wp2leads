<?php
/**
 * Created by PhpStorm.
 * User: oleksii.khodakivskyi
 * Date: 06.09.18
 * Time: 23:31
 */

class StatisticsModel {
    /**
     * Table name in the database
     *
     * @var string
     */
    private static $table_name = 'wp2leads_transfer_statistics';

    public static $limit = 25;

    /**
     * Creating table schema on plugin activation
     */
    public static function createTableSchema() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    map_id mediumint(9) NOT NULL,
                    statistics text NOT NULL,
                    transfer_type text NOT NULL,
                    PRIMARY KEY (id)
                    ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * Inserting new row to the table
     *
     * @param $data
     */
    public static function insert($data) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name;

        $wpdb->insert(
            $table_name,
            $data
        );
    }

    /**
     * Inserting new row to the table
     *
     * @param $data
     * @return
     */
    public static function delete($id) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name;

        $result = $wpdb->delete( $table_name, array( 'id' => $id ) );

        return $result;
    }

    /**
     * Returning all statistics information ordering by time DESC
     *
     * @param array $params
     *
     * @return array|object|null
     */
    public static function getList($params = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;
        $map_table_name = $wpdb->prefix . MapsModel::$table_name;

        if (!is_array($params) || empty($params)) {
            return $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY time DESC", ARRAY_A);
        } else {
            $sql = "SELECT s.id, s.time, s.map_id, s.statistics, s.transfer_type, m.name FROM {$table_name} AS s";
            $sql .= " LEFT JOIN {$map_table_name} AS m ON s.map_id = m.id WHERE 1=1";

            if (!empty($params['map_id'])) {
                $sql .= " AND s.map_id = {$params['map_id']}";
            } elseif (!empty($params['no_map'])) {
                $sql .= " AND (m.name IS NULL OR m.name = '')";
            }

            $sql .= " ORDER BY s.time DESC";

            if (!empty($params['limit'])) {
                $limit = (int)$params['limit'] < self::$limit ? self::$limit : (int)$params['limit'];
                $sql .= " LIMIT " . $limit;

                if (!empty($params['page']) && 0 < ($page = $params['page'] - 1)) {
                    $offset = $page * $limit;
                    $sql .= " OFFSET " . $offset;
                }
            }

            $result = $wpdb->get_results($sql, ARRAY_A);

            return $result;
        }
    }

    public static function count($params = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        $sql = "SELECT COUNT(*) FROM {$table_name} WHERE 1=1";

        return $wpdb->get_var($sql);
    }

    /**
     * Returning statistics information by map ID ordering by time DESC
     *
     * @return array|null|object
     */
    public static function getListByMapId($map_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        return $wpdb->get_results("SELECT * FROM {$table_name} WHERE map_id = {$map_id} ORDER BY time DESC", ARRAY_A);
    }

    public static function get($filter, $select) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        $sql = 'SELECT ';

        if (!empty($select)) {
            $sql .= implode(', ', $select);
        } else {
            $sql .= '*';
        }

        $sql .= ' FROM ' . $table_name . ' WHERE 1=1';

        if ( !empty( $filter ) ) {
            foreach ($filter as $key => $value) {
                $sql .= ' AND ' . $key . "='" . $value . "' ";
            }
        }

        $sql .= ' ORDER BY time DESC';

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Returning data filtered by conditions in $filter array and selecting only columns from $select or all
     *
     * @param $filter
     * @param $select
     * @return array|null|object
     */
    public static function getBy($filter, $select) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        $sql = 'SELECT ';

        if (!empty($select)) {
            $sql .= implode(', ', $select);
        } else {
            $sql .= '*';
        }

        $sql .= ' FROM ' . $table_name;

        if (!empty($filter)) {
            $sql .= ' WHERE 1=1 ';

            foreach ($filter as $code => $value) {
                $sql .= 'AND ' . $code . "='" . $value . "' ";
            }
        }

        $sql .= ' ORDER BY time DESC';

        return $wpdb->get_row($sql, ARRAY_A);
    }
}