<?php
/* Class to work with catalog */

class Wp2leads_Catalog {
	/**
     * Table name in the database
     *
     * @var string
     */

	public static function get_server() {
        return MapBuilderManager::get_server();
	}

	public static function get_image_server() {
		if (defined( 'WP2LEADS_MAPS_SANDBOX' ) && WP2LEADS_MAPS_SANDBOX) {
			return 'aHR0cDovL21hcHMuc2FudGVncmEtaW50ZXJuYXRpb25hbC5jb20vaW1hZ2VzLw==';
		} else {
			return 'aHR0cHM6Ly9tYXBzLndwMmxlYWRzLmNvbS9pbWFnZXMv=';
		}
	}

	public static function add_meta_to_server($meta_key, $map_id = '', $meta_value, $map_title = 'Untitled', $server_id = '') {
		$parameters = array (
            'meta_key' => esc_sql($meta_key),
			'map_id' => esc_sql($map_id),
			'map_title' => esc_sql($map_title),
			'map_value' => esc_sql($meta_value),
			'server_id' => esc_sql($server_id),
            'event' => 'add_catalog_meta'
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

        return true;
	}

	public static function get_catalog_item_meta($meta_key, $map_id, $status = 'active') {
		$parameters = array (
            'meta_key' => esc_sql($meta_key),
			'map_id' => esc_sql($map_id),
			'status' => esc_sql($status),
            'event' => 'get_catalog_meta'
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

		if ($response['meta'] && isset($response['meta'][$meta_key])) {
			return $response['meta'][$meta_key];
		} else {
			return '';
		}
	}

	// deprecated function
	public static function ajax_export_form_template() {
		if (!is_user_logged_in() || !current_user_can('manage_options')) {
			echo json_encode(['error' => 1, 'success' => 0]);
			wp_die();
		}

		check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
		$response = array(
            'error' => 0,
            'success' => 1,
            'message' =>  __('Successfuly uploaded to server', 'wp2leads'),
        );

		switch ($_POST['form_type']) {
			case 'cf':
				$form_array = array();
				$form_array['form_title'] = $_POST['form_title'];
				$form_array['form_content'] = get_post_meta($_POST['form_id'], '_form', true);
				$form_array['form_type'] = 'cf';
				$form_array['example_link'] = $_POST['example_link'];

				if(!self::add_meta_to_server('map_contact_form', $_POST['map_id'], json_encode($form_array, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE), $_POST['map_title'])) {
					$response = array(
						'error' => 1,
						'success' => 0,
						'message' => __('Something went wrong', 'wp2leads')
					);
				}

				break;
		}


		echo json_encode($response);
        wp_die();
	}

	// use map_id OR server_id
	public static function export_form_template($title, $type, $form_id, $link, $map_id, $map_title, $server_id, $kt_link = '') {

		switch ($type) {
			case 'cf':
				$form_array = array();
				$form_array['form_title'] = $title;
				$form_array['form_content'] = get_post_meta($form_id, '_form', true);
				$form_array['form_type'] = 'cf';
				$form_array['example_link'] = $link;
				$form_array['kt_link'] = $kt_link;


				if(!self::add_meta_to_server('map_contact_form', $map_id, json_encode($form_array, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE), $map_title, $server_id)) {
					return false;
				}

				break;
		}

		return true;
	}

	public static function ajax_get_catalog_items() {
		if (!is_user_logged_in() || !current_user_can('manage_options')) {
			echo json_encode(['error' => 1, 'success' => 0]);
			wp_die();
		}

		check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
	    $test = '';
		$result = array(
            'error' => 0,
            'success' => 1,
            'message' =>  '',
			'html' => ''
        );


		$parameters = array (
            'per_page' => esc_sql($_POST['per_page']),
			'offset' => esc_sql($_POST['offset']),
            'event' => 'get_catalog_items'
        );

        $request = wp_remote_post(
            base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

		$response = json_decode(wp_remote_retrieve_body( $request ), true);

        if (200 !== $response['code']) {
            $response['success'] = 0;
			$response['error'] = 1;
        } else {
			if ($response['data']) {
				ob_start();
				global $wpdb;

				foreach ($response['data'] as $item) {

					if (!empty($item['image']) && stripos($item['image'], 'http') === 0 ) {
						$image = $item['image'];
					} else {
						$image = base64_decode(self::get_image_server());
						$image .= $item['image'] ? $item['image'] : 'no-image.png';
					}

					$tags = !empty($item['tags']) ? unserialize($item['tags']) : array();
					$tags_str = ' ';

					foreach ($tags as $tag) {
						$tags_str .= $tag . '|';
					}	?>

					<div class="catalog-item" data-id="<?php echo $item['map_id']; ?>" data-tags="<?php echo trim($tags_str); ?>" data-redirect_link="<?php echo $item['redirect_link']; ?>">
					<div class="d-image">
						<img src="<?php echo $image; ?>">
						<div class="d-console"><?php _e('Get template info, please wait', 'wp2leads'); ?></div>
					</div>
					<div class="d-title"><?php echo $item['title']; ?></div>
					<div class="d-description scrollbar-inner"><?php echo $item['short_description']; ?></div>
					<div class="d-buttons">
						<button class="start_magic_catalog button button-primary"><?php _e('Install Template', 'wp2leads'); ?></button>
						<?php if ($item['example_link']) { ?>
							<a href="<?php echo $item['example_link']; ?>" class="button" target="_blank"><?php _e('View Example', 'wp2leads'); ?></a>
						<?php } ?>
					</div>
				</div>
				<?php
				}

				$result['html'] = ob_get_clean();
			}
		}

		echo json_encode($result);
        wp_die();
	}

	public static function ajax_get_magic_steps_for_map() {
		if (!is_user_logged_in() || !current_user_can('manage_options')) {
			echo json_encode(['error' => 1, 'success' => 0]);
			wp_die();
		}

		check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );

		$map_id = $_POST['map_id'];
		$magic_import = new Wp2leads_MagicImport();

		// check if the mapis magic

		$server_id = true;
		if (!$server_id) {
			echo json_encode(array(
				'success' => 0,
				'error' => 1,
				'message' => __('This map has no import function', 'wp2leads')
			));
			wp_die();
		}

		// we sure that the map is magic
		$steps = array();

		// check required plugins for the map
		$required_plugins = new Wp2leads_RequiredPlugins();

		if ($rp = $required_plugins->check_map_plugins($map_id)) {
			foreach ($rp as $k => $p) {
				$steps[] = array(
					'start_message' => '<br>' . __('Installing ', 'wp2leads') . $p['label'],
					'act' => 'required_plugins',
					'map_id' => $map_id,
					'server_id' => $server_id,
					'plugin_slug' => $k
				);
			}
		}

		// check form
		$form_array = self::get_catalog_item_meta('map_contact_form', $map_id);

		if ($form_array) {
			$steps[] = array(
				'start_message' => '<br>' . __('Installing Form', 'wp2leads'),
				'act' => 'install_form',
				'map_id' => $map_id,
				'server_id' => $server_id,
				'form' => json_decode($form_array, true)
			);
		}

		// add map
		$steps[] = array(
			'start_message' => '<br>' . __('Installing Map', 'wp2leads'),
			'act' => 'install_map',
			'map_id' => $map_id,
			'server_id' => $server_id,
			'map_name' => $_POST['map_name']
		);

		// get KT campaign
		$kt_links = MapBuilderManager::get_map_meta_from_server($map_id, 'kt_url');

		if ($kt_links) {
			$steps[] = array(
				'start_message' => '<br>' . __('Get KT Links', 'wp2leads'),
				'act' => 'kt_links',
				'map_id' => $map_id,
				'server_id' => $server_id,
				'kt_links' => unserialize($kt_links['kt_url'])
			);
		}

		$redirect_link = $_POST['redirect_link'];

		if ($redirect_link) {

			if ( strripos($redirect_link, 'http') === 'false' ) {
				$redirect_link = get_admin_url() . $redirect_link;
			}

			$steps[] = array(
				'start_message' => '<br>' . __('Added redirect after install', 'wp2leads'),
				'act' => 'redirect_link',
				'map_id' => $map_id,
				'server_id' => $server_id,
				'redirect_link' => $redirect_link
			);
		}

		$description_en = MapBuilderManager::get_map_meta_from_server($map_id, 'description_en');

		if ( !$description_en ) {
			$description_en = 'Please add example data to view this map';
		} else {
			$description_en = unserialize($description_en['description_en']);
		}

		$description_de = MapBuilderManager::get_map_meta_from_server($map_id, 'description_de');
		if ( !$description_de ) {
			$description_de = 'Bitte fügen Sie Beispieldaten hinzu, um diese Karte anzuzeigen';
		} else {
			$description_de = unserialize($description_de['description_de']);
		}

		$steps[] = array(
			'description_en' => $description_en,
			'description_de' => $description_de,
			'act' => 'check_map_data',
			'map_id' => $map_id,
			'server_id' => $server_id,
		);

		echo json_encode(array(
			'message' => '<br>' . __('Installation started...', 'wp2leads'),
			'data' => $steps,
			'success' => 1,
			'error' => 0,
		));
        wp_die();

	}

	public static function ajax_make_magic_step() {
		if (!is_user_logged_in() || !current_user_can('manage_options')) {
			echo json_encode(['error' => 1, 'success' => 0]);
			wp_die();
		}
		check_ajax_referer( 'wp2leads_ajax_nonce', 'nonce' );
		$data = $_POST['data'];
		$process_info = isset($_POST['info']) ? $_POST['info'] : array();

		$response = array(
			'message' => ' Done',
			'data' => array(),
			'success' => 1,
			'error' => 0,
		);


		switch ($data['act']) {
			case 'required_plugins':
				// install required plugins for the map
				$required_plugins = new Wp2leads_RequiredPlugins();
				$i_and_a = $required_plugins->activate_and_install_plugin($data['map_id'], $data['plugin_slug']);

				if ($i_and_a['result']) {
					$response['message'] = ' ' . __('This plugin can not be installed automatically', 'wp2leads');
					$response['success'] = 0;
					$response['error'] = 1;
					$response['debug'] = $i_and_a['result'];
				}

				if (!empty($i_and_a['message'])) {

					if (!isset($response['data']['after_install_message'])) $response['data']['after_install_message'] = '';
					$response['data']['after_install_message'] .= '<p style="text-align: justify;">' . $i_and_a['message'] . '</p>';
				}

				$response['data']['one_more_ajax'] = 1;

				break;

			case 'install_form':
				$form = $data['form'];
				if ($form['form_type'] == 'cf') {
					// contact form 7 form

					$post_data = array(
						'post_title'    => wp_strip_all_tags( $form['form_title'] ),
						'post_content'  => $form['form_content'],
						'post_status'   => 'publish',
						'post_type' => 'wpcf7_contact_form'
					);

					$form_id = wp_insert_post( $post_data );

					if (!$form_id) {
						$response['message'] = ' ' . __('Can not install the form', 'wp2leads');
						$response['success'] = 0;
						$response['error'] = 1;
					} else {
						$response['data']['form_type'] = 'cf';
						$response['data']['form_id'] = $form_id;
						$response['data']['form_link'] = '/wp-admin/admin.php?page=wpcf7&post='.$form_id.'&action=edit';

						// create meta field
						update_post_meta($form_id, '_form', $form['form_content']);

						$dummy_form_data = array(
							'active' => true,
							'subject' => __('CF7 Form', 'wp2leads'),
							'sender' => 'wordpress@' . $_SERVER['SERVER_NAME'],
							'recipient' => get_option('admin_email'),
							'body' => __('Please fill this field', 'wp2leads'),
							'additional_headers' => '',
							'attachments' => '',
							'use_html' => false,
							'exclude_blank' => false,
						);

						update_post_meta($form_id, '_mail', $dummy_form_data);

						// generate fake data
						Wp2leads_MagicImport::create_fake_entry('cf_' . $form_id);
					}
				}

				break;

			case 'install_map':

				$new_map_id = MapBuilderManager::import_map_by_public_id($data['map_id']); // map id on the local db
				$response['data']['new_map_id'] = $new_map_id;

				if (isset($process_info['form_id'])) {
					$map = MapsModel::get($new_map_id);

					if($process_info['form_type'] == 'cf') {
						// cf7 form, attach new form to the map

						$mapping = unserialize($map->mapping);
						$info = unserialize($map->info);
						$api = unserialize($map->api);

						$api['tags_prefix'] = $map->name;

						// check losted tags (name of the map like a tag)

						if ( is_array($api['losted_name']) ) {
							$api['losted_name'][] = $map->name;
						} else {
							$api['losted_name'] = $map->name;
						}


						$api['losted_manually_selected_tags'][] = $map->name;

						MapsModel::updateMapCell($new_map_id, 'api', serialize($api));

						$form_fields = vxcf_form::get_form_fields($mapping['form_code']);

						$mapping['comparisons'] = array(
							array (
								'tableColumn' => 'vxcf_leads.form_id',
								'conditions' => array (
									array (
										'operator' => 'like',
										'string' => 'cf_' . $process_info['form_id'],
									),
								),
							),
						);

						$mapping['form_code'] = 'cf_' . $process_info['form_id'];
						MapsModel::updateMapCell($new_map_id, 'mapping', serialize($mapping));

						// check KT tags if exist
						$taggs = array();
						foreach($form_fields as $field) {
							if ($field['type'] == 'radio' || $field['type'] == 'checkbox' || $field['type'] == 'select') {

								foreach ($field['values'] as $val) {

									$preprefix = $map->name;

									if (!empty($mapping['replace_table']['v.vxcf_leads_detail-' . $field['name']])) {
										$preprefix = $mapping['replace_table']['v.vxcf_leads_detail-' . $field['name']];
									}

									$taggs[] = $api['tags_prefix'] . $preprefix . $val['value'];
								}
							}
						}

						if ($taggs) {
							$ajax = new Wp2leads_Admin_Ajax();
							$ajax->add_recommended_klick_tip_tags($taggs);
						}

					}
				}

				break;

			case 'kt_links':

				if ($data['kt_links']) {
					$response['data']['kt_links'] = $data['kt_links'];
				}

				break;

			case 'redirect_link':

				if ($data['redirect_link']) {
					$response['data']['redirect_link'] = $data['redirect_link'];
				}

				break;
			case 'check_map_data':
				$new_map = MapsModel::get_last_by_map_id($data['map_id']);
				if ( ! Wp2leads_MapsActivation::is_map_active($new_map->id) ) {
					Wp2leads_MapsActivation::add_map_to_list(array(
						'map_id' => $new_map->id,
						'title' => $new_map->name,
						'description_en' => $data['description_en'],
						'description_de' => $data['description_de'],
					));

					if ( strpos( get_user_locale(), 'de' ) === FALSE ) {
						$response['data']['non_active_map'] = '<li data-id="' . $new_map->id . '">' . $new_map->name . ' - ' . $data['description_en'] . '</li>';
					} else {
						$response['data']['non_active_map'] = '<li data-id="' . $new_map->id . '">' . $new_map->name . ' - ' . $data['description_de'] . '</li>';
					}


				}

				break;
		}

		echo "&&&";
		echo json_encode($response);
        wp_die();
	}

	// show welcome tet only once per user OR by the link from plugin panel
	public static function show_welcome_text_1() {

		$welcome = get_option('wp2leads_show_welcome1');
		if (empty($welcome) || ( $welcome !== WP2LEADS_VERSION ) ) update_option('wp2leads_show_welcome1', WP2LEADS_VERSION);

		if (empty($welcome) || ( $welcome !== WP2LEADS_VERSION ) || !empty($_GET['welcome'])) {
			$locale = get_user_locale();
			$locale_short = explode('_', $locale)[0];

			$filename = plugin_dir_path( WP2LEADS_PLUGIN_FILE ) . 'admin/partials/catalog_texts/welcome_top-'.$locale_short.'.php';

			if (!file_exists($filename)) {
				$filename = plugin_dir_path( WP2LEADS_PLUGIN_FILE ) . 'admin/partials/catalog_texts/welcome_top-en.php';
			}

			ob_start();
			require_once $filename;
			$html = ob_get_clean();

			echo apply_filters('the_content', $html);

		} else {
			$locale = get_user_locale();
			$locale_short = explode('_', $locale)[0];

			$filename = plugin_dir_path( WP2LEADS_PLUGIN_FILE ) . 'admin/partials/catalog_texts/regular_top-'.$locale_short.'.php';

			if (!file_exists($filename)) {
				$filename = plugin_dir_path( WP2LEADS_PLUGIN_FILE ) . 'admin/partials/catalog_texts/regular_top-en.php';
			}

			ob_start();
			require_once $filename;
			$html = ob_get_clean();

			echo apply_filters('the_content', $html);
		}

		return empty($welcome) || !empty($_GET['welcome']);
	}

	// show welcome tet only once per user OR by the link from plugin panel
	public static function show_welcome_text_2($w) {
		$welcome = get_option('wp2leads_show_welcome2');
		if (empty($welcome) || ( $welcome !== WP2LEADS_VERSION ) ) update_option('wp2leads_show_welcome2', 1);

		if (empty($welcome) || ( $welcome !== WP2LEADS_VERSION ) || !empty($_GET['welcome'])) {
			$locale = get_user_locale();
			$locale_short = explode('_', $locale)[0];

			$filename = plugin_dir_path( WP2LEADS_PLUGIN_FILE ) . 'admin/partials/catalog_texts/welcome_bottom-'.$locale_short.'.php';

			if (!file_exists($filename)) {
				$filename = plugin_dir_path( WP2LEADS_PLUGIN_FILE ) . 'admin/partials/catalog_texts/welcome_bottom-en.php';
			}

			ob_start();
			require_once $filename;
			$html = ob_get_clean();

			echo apply_filters('the_content', $html);
		} else {
			$locale = get_user_locale();
			$locale_short = explode('_', $locale)[0];

			$filename = plugin_dir_path( WP2LEADS_PLUGIN_FILE ) . 'admin/partials/catalog_texts/regular_bottom-'.$locale_short.'.php';

			if (!file_exists($filename)) {
				$filename = plugin_dir_path( WP2LEADS_PLUGIN_FILE ) . 'admin/partials/catalog_texts/regular_bottom-en.php';
			}

			ob_start();
			require_once $filename;
			$html = ob_get_clean();

			echo apply_filters('the_content', $html);
		}

	}

	public static function get_all_tags() {
		$parameters = array (
            'event' => 'get_all_tags'
        );

        $request = wp_remote_post(
            base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

		$response = json_decode(wp_remote_retrieve_body( $request ), true);

        if (200 !== $response['code']) {
            return array();
        }

        return isset($response['tags']) ? $response['tags'] : array();
	}

	public static function test_server_connection() {
        $parameters = array (
            'event' => 'get_all_tags'
        );

        $request = wp_remote_post(
            base64_decode(self::get_server()),
            array(
                'body'    => $parameters,
            )
        );

        $response = json_decode(wp_remote_retrieve_body( $request ), true);

        return !empty($response['code']) && 200 === $response['code'];
    }
}

