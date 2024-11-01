<?php
class Wp2leads_MapsActivation {
	protected $deactivated_maps_list = array();

	public function __construct() {
		$this->deactivated_maps_list = get_option('wp2leads_deactivated_maps', array());
		$this->delete_active_maps();

		// hooks
		add_action( 'admin_bar_menu', array( $this, 'add_panel_alert' ) );
		add_action( 'admin_footer', array( $this, 'admin_popup' ) );

	}

	// add alert on the top admin panel
	public function add_panel_alert( $wp_admin_bar ) {

		if ( !count( $this->deactivated_maps_list ) || !is_admin() ) return;

		if ( $this->deactivated_maps_list ) {
			$meta = array(
				'class' => 'active',
			);
		} else {
			$meta = array();
		}

		$wp_admin_bar->add_node(
			array(
				'id'     => 'wp2leads_not_active_maps_trigger',
				'parent' => 'top-secondary',
				'title'  => '<span class="dashicons dashicons-warning"></span> Wp2Leads (<span class="wp2leads-nam-count">' . count( $this->deactivated_maps_list ) . '</span>)',
				'href'   => '#',
				'meta'   => $meta
			)
		);
	}

	/** add map to deactivated list
	 *  Args = array( map_id, title, description_en, description_de ) map_id from current wordpress installation
	 */
	public static function add_map_to_list( $args ) {
		$maps = get_option('wp2leads_deactivated_maps', array());

		$maps['map_'.$args['map_id']] = array(
			'title' => $args['title'],
			'description_en' => $args['description_en'],
			'description_de' => $args['description_de'],
		);

		return update_option('wp2leads_deactivated_maps', $maps);
	}

	// check map if it still activated
	public static function is_map_active ( $map_id ) {
		$map = MapsModel::get($map_id);
		if ( !$map ) return true;
        $mapping = unserialize($map->mapping);
        $result = MapsModel::get_map_query_results($mapping, 100);
        return count($result);
	}

	// show html of the popup with notices for the maps
	public function admin_popup() { ?>
		<div id="wp2leads-non-active-maps-popup" style="display: none;">
			<h2><?php _e('You have maps with no any data for it. To activate and use it you should do the next:', 'wp2leads'); ?></h2>
			<ul> <?php
				foreach ($this->deactivated_maps_list as $map_id => $map_info) {
					if ( strpos( get_user_locale(), 'de' ) === FALSE ) {
						echo '<li data-id="' . $map_id . '">' . $map_info['title'] . ' - ' . stripslashes($map_info['description_en']) . '</li>';
					} else {
						echo '<li data-id="' . $map_id . '">' . $map_info['title'] . ' - ' . stripslashes($map_info['description_de']) . '</li>';

					}
				} ?>
			</ul>
			<a class="close" href="#">&times;</a>
		</div> <?php
	}

	// check maps and delete from list that are already active
	public function delete_active_maps() {

		$maps = $this->deactivated_maps_list;

		foreach ($maps as $map_id => $map_info) {
			if ( self::is_map_active( preg_replace("/[^0-9]/", '', $map_id) ) ) unset($this->deactivated_maps_list[$map_id]);
		}

		update_option('wp2leads_deactivated_maps', $this->deactivated_maps_list);
	}

	public static function get_map_error_message($map_id) {
		$maps = get_option('wp2leads_deactivated_maps', array());

		if ( isset($maps['map_' . $map_id])) {
			if ( strpos( get_user_locale(), 'de' ) === FALSE ) {

				if ( $maps['map_' . $map_id]['description_en'] ) {
					return $maps['map_' . $map_id]['description_en'];
				} else {
					return 'Because no user data found, please add user data by submit form or add dummy data.';
				}


			} else {
				if ( $maps['map_' . $map_id]['description_de'] ) {
					return $maps['map_' . $map_id]['description_de'];
				} else {
					return 'Da keine Benutzer-Daten gefunden wurden, bitte sende das Formular einmal ab oder füge dummy Daten hinzu.';
				}
			}
		}
	}
}

new Wp2leads_MapsActivation();