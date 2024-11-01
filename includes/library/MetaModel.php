<?php
/**
 * Created by PhpStorm.
 * User: oleksii.khodakivskyi
 * Date: 06.09.18
 * Time: 23:31
 */

class MetaModel {
    /**
     * Table name in the database
     *
     * @var string
     */
    private static $table_name = 'wp2leads_maps_meta';

    /**
     * Creating table schema on plugin activation
     */
    public static function createTableSchema() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    map_id mediumint(9) NOT NULL,
                    meta_key text NOT NULL,
                    meta_value text NOT NULL,
                    PRIMARY KEY (id)
                    ) $charset_collate;";

        dbDelta($sql);
    }


    /**
     * Delete new row to the table
     *
     * @param $data
     * @return
     */
    private static function delete_all($map_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name;

        $result = $wpdb->delete( $table_name, array( 'map_id' => $map_id ) );

        return true;
    }
	
	public static function update_map_meta($map_id, $meta_key, $meta_value) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . self::$table_name;
		
		$meta = $wpdb->get_row(
			$wpdb->prepare("
				SELECT id, 
				FROM {$table_name}
				WHERE 	map_id = '%s'
				AND  meta_key = '%s'
				",
				$map_id,
				$meta_key
			)
		);
		
		if ($meta) {
			$wpdb->update( $table_name,
				array( 'meta_value' => serialize($meta_value) ),
				array( 'id' => $meta->id )
			);
			
			return true;
		} else {
			$wpdb->insert( $table_name,
				array( 'meta_value' => serialize($meta_value), 'map_id' => $map_id, 'meta_key' => $meta_key )
			);
			
			return true;
		}
	}
	
	public static function get_post_meta ($map_id, $key = false) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . self::$table_name;
		
		if ($key) {
			$meta = $wpdb->get_row(
				$wpdb->prepare("
					SELECT meta_value, 
					FROM {$table_name}
					WHERE 	map_id = '%s'
					AND  meta_key = '%s'
					",
					$map_id,
					$meta_key
				)
			);
			
			if ($meta) {
				return unserialize($meta->meta_value);
			} else {
				return '';
			}
		} else {
			$meta = $wpdb->get_results(
				$wpdb->prepare("
					SELECT meta_value, meta_key
					FROM {$table_name}
					WHERE 	map_id = '%s'
					",
					$map_id
				)
			);
			
			$result = array();
			
			if ($meta) {
				foreach ($meta as $m) {
					$result[$m->meta_key] = unserialize($m->meta_value);
				}
			}
			
			return $result;
		}
	
		return false;
	
	}

}