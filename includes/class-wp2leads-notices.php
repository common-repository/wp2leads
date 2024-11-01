<?php 
/* Class to work with notices, can add, show, remove notices */

class Wp2leads_Notices {
	/**
     * Table name in the database
     *
     * @var string
     */
    private static $table_name = 'wp2leads_notices';

    /**
     * Creating table schema on plugin activation
     */
	public static function createTableSchema() {
		global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
                    ID bigint(20) unsigned NOT NULL auto_increment,
                    type varchar(10) NOT NULL default 'notice',
                    text longtext,
					once bool default '0',
                    PRIMARY KEY (ID)
                    ) $charset_collate;";

        dbDelta($sql);
	}
	
	/**
     * Inserting new row to the table
     *
     * @param $text
	 * @param $type
	 * @param $once
     * @return mixed ID|false
     */
    private static function insert($text, $type = 'notice', $once = 0) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name;
		$text = htmlspecialchars($text, ENT_QUOTES);

		// check if notice exist
		$result = $wpdb->get_results("SELECT * FROM {$table_name} WHERE text = '{$text}'");
		
		if ($result) {
            return $wpdb->insert_id;
        }
		
        $result = $wpdb->insert(
            $table_name,
            array(
				'text' => $text,
				'type' => $type,
				'once' => $once
			)
        );

        if ($result) {
            return $wpdb->insert_id;
        }

        return false;
    }
	
	/**
     * Delete row to the table
     *
     * @param $id
     * @return mixed ID|false
     */
    public static function delete($id) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name;

        $result = $wpdb->delete(
            $table_name,
            array( 'ID' => $id ), 
			array( '%d' )
        );

        return $result;
    }
	
	/**
     * Returning all notices
     *
     * @return array|null|object
     */
    private static function getList() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        return $wpdb->get_results("SELECT * FROM {$table_name}");
    }
	
	/**
     * Add warning to the list
     * @param $text
	 * @param $once
     * @return mixed ID|false
     */
	 
	public static function add_warning($text, $once = 0) {
		$once = $once ? 1 : 0;
		return self::insert($text, 'warning', $once);
	}
	
	/**
     * Add notice to the list
     * @param $text
	 * @param $once
     * @return mixed ID|false
     */
	 
	public static function add_notice($text, $once = 0) {
		$once = $once ? 1 : 0;
		return self::insert($text, 'notice', $once);
	}
	
	/**
     * Add success to the list
     * @param $text
	 * @param $once
     * @return mixed ID|false
     */
	 
	public static function add_success($text, $once = 0) {
		$once = $once ? 1 : 0;
		return self::insert($text, 'success', $once);
	}
	
	/**
     * Add error to the list
     * @param $text
	 * @param $once
     * @return mixed ID|false
     */
	 
	public static function add_error($text, $once = 0) {
		$once = $once ? 1 : 0;
		return self::insert($text, 'error', $once);
	}
	
	/**
     * Show admin notices on admin 
     * @return none
     */
	 
	public static function show_notices() {
		$notices = self::getList();
		if ($notices) {
			$single = (count($notices) == 1);
			foreach ($notices as $notice) {
				if ($notice->once) {
					self::delete($notice->ID);
				} ?>
				<div class="wp2leads-global-notice notice notice-<?php echo $notice->type; ?> count-<?php echo (int)!$single; ?>">
					<p><strong>Wp2Leads:</strong> <?php echo htmlspecialchars_decode($notice->text); ?></p>
					<?php if (!$notice->once) { ?>
						<button class="wp2leads-hide-notice button deletemeta button-small" data-id="<?php echo $notice->ID; ?>"><?php _e('Dismiss', 'wp2leads'); ?></button>
					<?php } ?>
					<?php if (!$notice->once && !$single) { ?>
						<button class="wp2leads-hide-all-notices button deletemeta button-small"><?php _e('Dismiss All', 'wp2leads'); ?></button>
					<?php } ?>
				</div> <?php 
			}
		}
	}
	
	/**
     * Ajax handler to dismiss notice
     */
	 
	public static function dismiss_notice() {
		$response = array(
            'error' => 1,
            'success' => 0,
        );
		
		if (empty($_POST['id'])) {
			echo json_encode($response);
			wp_die();
		}
		
		$response = array(
            'error' => 0,
            'success' => 1,
        );
		
		self::delete($_POST['id']);
		
		echo json_encode($response);
		wp_die();
	}
	
	/**
     * Ajax handler to dismiss all notices
     */
	 
	public static function dismiss_all_notices() {
		
		$response = array(
            'error' => 0,
            'success' => 1,
        );
		
		$list = self::getList();
		if ($list) {
			foreach ($list as $item) {
				self::delete($item->ID);
			}
		}
		
		echo json_encode($response);
		wp_die();
	}
	
	public static function delete_message_by_text( $text ) {
		$list = self::getList();
		if ($list) {
			foreach ($list as $item) {
				if ( $item->text == $text ) self::delete($item->ID);
			}
		}
	}
	
	public static function get_id_by_text( $text ) {
		$list = self::getList();
		if ($list) {
			foreach ($list as $item) {
				if ( $item->text == $text ) {
					return $item->ID;
				}
			}
		}
	}
}


// Add notice for CRM plugin 

add_action('plugin_status_vxcf_form', 'vxcf_wp2leads_deactivate' );

function vxcf_wp2leads_deactivate( $action ) {
	
	$message = __( 'When Contact Forms Entries plugin is deactivated your forms will not sent any data to Klick-Tipp! <a href="#" id="activateCRM" data-message="Installing...">Activate it</a>', 'wp2leads' );
	
	if ( !$action ) {
		Wp2leads_Notices::add_warning( $message );
	} else {
		Wp2leads_Notices::delete_message_by_text( htmlspecialchars($message) );
	}
}
