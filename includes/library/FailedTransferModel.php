<?php
/**
 * Failed transfer items.
 *
 * Used to save, manage and delete failed transfer users.
 *
 * @version    1.0.2.1
 * @since      1.0.2.1
 * @package    Wp2leads
 * @subpackage Wp2leads/includes
 */
class FailedTransferModel {
    /**
     * Table name in the database
     *
     * @var string
     */
    private static $table_name = 'wp2leads_transfer_failed';

    /**
     * Creating table schema on plugin activation
     */
    public static function createTableSchema() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
                    ID bigint(20) unsigned NOT NULL auto_increment,
                    map_id mediumint(9) NOT NULL,
                    user_email varchar(100) NOT NULL default '',
                    user_data longtext,
                    user_status varchar(20) NOT NULL default '',
                    time datetime NOT NULL default '0000-00-00 00:00:00',
                    PRIMARY KEY  (ID),
                    KEY map_id (map_id),
                    KEY user_status (user_status),
                    KEY user_email (user_email)
                    ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * Inserting new row to the table
     *
     * @param $data
     * @return mixed ID|false
     */
    public static function insert($data) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name;

        $result = $wpdb->insert(
            $table_name,
            $data,
            array('%d', '%s','%s', '%s','%s')
        );

        if ($result) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Returning all statistics information ordering by time DESC
     *
     * @return array|null|object
     */
    public static function getList() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        return $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY time DESC", ARRAY_A);
    }

    /**
     * Returning all statistics information ordering by time DESC
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