<?php

// class to check required plugins for the maps
class Wp2leads_RequiredPlugins {
	protected $maps_list = array();

	// return required plugins that are not installed and activated
	public function check_map_plugins($map_id) {
		$this->add_map_to_map_list($map_id);
		$needed_plugins = array();

		if (isset($this->maps_list['map_' . $map_id]['required_plugins'])) {
			foreach ($this->maps_list['map_' . $map_id]['required_plugins'] as $plugin_slug => $value) {
				if (!is_plugin_active($value['slug'])) {
					$needed_plugins[$plugin_slug] = $value;
				}
			}
		}

		return $needed_plugins;
	}

	// return recommend plugins that are not installed and activated
	public function check_map_recommends($map_id) {
		$this->add_map_to_map_list($map_id);
		$needed_plugins = array();

		if (isset($this->maps_list['map_' . $map_id]['recommend_plugins'])) {
			foreach ($this->maps_list['map_' . $map_id]['recommend_plugins'] as $plugin_slug => $value) {
				
				if (!is_plugin_active($value['slug'])) {
					$needed_plugins[$plugin_slug] = $value;
				}
			}
		}

		return $needed_plugins;
	}

	public function get_recommend_plugins($map_id) {
		if (!$map_id) return array();
		$this->add_map_to_map_list($map_id);
		return (isset($this->maps_list['map_' . $map_id]['recommend_plugins'])) ? $this->maps_list['map_' . $map_id]['recommend_plugins'] : array();
	}
	
	public function get_required_plugins($map_id) {
		$this->add_map_to_map_list($map_id);
		return (isset($this->maps_list['map_' . $map_id]['required_plugins'])) ? $this->maps_list['map_' . $map_id]['required_plugins'] : array();
	}
	
	public function get_active_plugins_list($map_id = 0) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		$rplugins = $this->get_required_plugins($map_id);
		$apl = get_option('active_plugins');
		$plugins = get_plugins();
		$activated_plugins = array();
		
		foreach ($apl as $p){
			$plugin_info['path'] = $p;
			$plugin_info['label'] = '';
			$slug = '';
			$plugin_info['status'] = '';
			
			foreach ($rplugins as $s => $rp) {
				if ($rp['slug'] == $p) {
					$slug = $s;
					$plugin_info['label'] = $rp['label'];
					$plugin_info['status'] = 'active';
				}
			}
			
			if (!$plugin_info['label']) {
				$slug = $p;
				$plugin_info['label'] = $plugins[$p]['Name'];
			}
			
			$activated_plugins[$slug] = $plugin_info;
		}

		return $activated_plugins;
	}

	public function is_plugin_installed( $plugin_slug ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();

		if ( !empty( $all_plugins[$plugin_slug] ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function install_plugin( $plugin_zip ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		wp_cache_flush();

		$upgrader = new Plugin_Upgrader();
		$installed = $upgrader->install( $plugin_zip );

		return $installed;
	}

	public function upgrade_plugin( $plugin_slug ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		wp_cache_flush();

		$upgrader = new Plugin_Upgrader();

		$upgraded = $upgrader->upgrade( $plugin_slug );

		return $upgraded;
	}

	// return message or false. False is good!
	public function activate_and_install_plugin( $map_id, $plugin_id) {
		$this->add_map_to_map_list($map_id);
		// get plugin for the map
		if (!isset($this->maps_list['map_' . $map_id])) return array('result' => 1, 'message' => '');
		if (!isset($this->maps_list['map_' . $map_id]['required_plugins'][$plugin_id]) && !isset($this->maps_list['map_' . $map_id]['recommend_plugins'][$plugin_id])) return array('result' => 2, 'message' => '');


		if (isset($this->maps_list['map_' . $map_id]['recommend_plugins'][$plugin_id])) {
			$slug = $this->maps_list['map_' . $map_id]['recommend_plugins'][$plugin_id]['slug'];
			$zip = $this->maps_list['map_' . $map_id]['recommend_plugins'][$plugin_id]['zip_path'] ? $this->maps_list['map_' . $map_id]['recommend_plugins'][$plugin_id]['zip_path'] : $this->maps_list['map_' . $map_id]['recommend_plugins'][$plugin_id]['link'];

		}

		if (isset($this->maps_list['map_' . $map_id]['required_plugins'][$plugin_id])) {
			$slug = $this->maps_list['map_' . $map_id]['required_plugins'][$plugin_id]['slug'];
			$zip = $this->maps_list['map_' . $map_id]['required_plugins'][$plugin_id]['zip_path'] ? $this->maps_list['map_' . $map_id]['required_plugins'][$plugin_id]['zip_path'] : $this->maps_list['map_' . $map_id]['required_plugins'][$plugin_id]['link'];
		}

		$message = '';
		if (isset($this->maps_list['map_' . $map_id]['recommend_plugins'][$plugin_id]['hello_message'])) $message = $this->maps_list['map_' . $map_id]['recommend_plugins'][$plugin_id]['hello_message'];
		if (isset($this->maps_list['map_' . $map_id]['required_plugins'][$plugin_id]['hello_message'])) $message = $this->maps_list['map_' . $map_id]['required_plugins'][$plugin_id]['hello_message'];

		$redirect = '';
        if (isset($this->maps_list['map_' . $map_id]['recommend_plugins'][$plugin_id]['redirect'])) $redirect = $this->maps_list['map_' . $map_id]['recommend_plugins'][$plugin_id]['redirect'];
        if (isset($this->maps_list['map_' . $map_id]['required_plugins'][$plugin_id]['redirect'])) $redirect = $this->maps_list['map_' . $map_id]['required_plugins'][$plugin_id]['redirect'];

		if (!$zip || !$slug) return array('result' => 3, 'message' => '');

		// check install
		if (!$this->is_plugin_installed($slug)) {
			$installed = $this->install_plugin($zip);
		} else {
			//$this->upgrade_plugin( $slug ); don't need update to not to brake old theme
			$installed = true;
		}

		// activation
		if ( !is_wp_error( $installed ) && $installed ) {
			$activate = activate_plugin( $slug );

			if ( is_null($activate) ) {
			  return array('result' => 0, 'message' => $message, 'redirect' => $redirect);
			} else {
				return array('result' => 5, 'message' => '');
			}
		} else {
			return array('result' => 4, 'message' => '');
		}
	}
	
	// before we will work with any form - we should add it to the maps_list 
	// $map_id - server OR public map_id
	// return true if exist something, false - if not
	private function add_map_to_map_list($map_id) {
		if (isset($this->maps_list['map_' . $map_id])) {
			return true;
		}
		
		$parameters = array (
			'event' => 'get_required_plugins',
			'map_id' => $map_id
		);
			
		$request = wp_remote_post(
			base64_decode(self::get_server()),
			array(
				'body'    => $parameters,
			)
		);

		$response = json_decode(wp_remote_retrieve_body( $request ), true);

		if (!empty($response['plugins']['required_plugins']) || !empty($response['plugins']['required_plugins'])) {
			$this->maps_list['map_' . $response['plugins']['map_id']] = $response['plugins'];
			$this->maps_list['map_' . $map_id] = $response['plugins'];
			
		} else {
			return false;
		}
	}
	
	public static function get_server() {
		if (defined( 'WP2LEADS_MAPS_SANDBOX' ) && WP2LEADS_MAPS_SANDBOX) {
			return 'aHR0cDovL21hcHMuc2FudGVncmEtaW50ZXJuYXRpb25hbC5jb20vc2VydmVyL21hcHMucGhw';
		} else {
			return 'aHR0cHM6Ly9tYXBzLndwMmxlYWRzLmNvbS9zZXJ2ZXIvbWFwcy5waHA='; 
		}
	}
	
	public function install_crm_plugin() {
		$plugins = $this->check_map_plugins(169);
		$message = __( 'When Contact Forms Entries plugin is deactivated your forms will not sent any data to Klick-Tipp! <a href="#" id="activateCRM" data-message="Installing...">Activate it</a>', 'wp2leads' );
		
		$response = array(
            'error' => 0,
            'success' => 1,
            'message' => __('CRM Plugin already activated', 'wp2leads'),
        );

		if ( !empty($plugins['crm']) ) {
			$result = $this->activate_and_install_plugin( 169, 'crm' );
			
			if ( $result['message'] ) {
				$response = array(
					'error' => 1,
					'success' => 0,
					'message' => $result['message']
				);
			} else {
				$response['message'] = __('CRM Plugin was installed and activated', 'wp2leads');
				Wp2leads_Notices::delete_message_by_text( htmlspecialchars($message) );
			}
		} else {
			Wp2leads_Notices::delete_message_by_text( htmlspecialchars($message) );
		}
		
		echo json_encode($response);
        wp_die();
	}
}
