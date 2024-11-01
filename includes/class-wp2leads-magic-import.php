<?php
class Wp2leads_MagicImport {
	protected $maps_list = array();
	private static $table_name = 'wp2l_maps';

	public function __construct() {
		$last_update = get_option('wp2leads_update_magic', 0);

		//3600 - min update time from the server!
		if ( (time() - 3600) > $last_update) {
			// update cache
			$parameters = array (
				'event' => 'get_magic_maps',
			);

			$request = wp_remote_post(
				base64_decode(self::get_server()),
				array(
					'body'    => $parameters,
				)
			);

			$response = json_decode(wp_remote_retrieve_body( $request ), true);

			$maps_list = array();
			if (!empty($response['data'])) {
				foreach ($response['data'] as $data) {
					if (isset($maps_list['map_' . $data['parent_server_id']])) {
						$maps_list['map_' . $data['parent_server_id']]['clones'][] = $data['map_id'];
					} else {
						$maps_list['map_' . $data['parent_server_id']] = array(
							'id' => $data['parent_server_id'],
							'map_id' => $data['parent_map_id'],
							'clones' => array($data['map_id'])
						);
					}
				}
			}

			$this->maps_list = $maps_list;

			update_option('wp2leads_magic_cache', $this->maps_list);
			update_option('wp2leads_update_magic', time());
		} else {
			$this->maps_list = get_option('wp2leads_magic_cache', array());
		}

	}

	public static function get_server() {
		if (defined( 'WP2LEADS_MAPS_SANDBOX' ) && WP2LEADS_MAPS_SANDBOX) {
			return 'aHR0cDovL21hcHMuc2FudGVncmEtaW50ZXJuYXRpb25hbC5jb20vc2VydmVyL21hcHMucGhw';
		} else {
			return 'aHR0cHM6Ly9tYXBzLndwMmxlYWRzLmNvbS9zZXJ2ZXIvbWFwcy5waHA=';
		}
	}

	public function is_have_magic($map_id) {
		foreach($this->maps_list as $map) {
			if ($map['id'] == $map_id || $map['map_id'] == $map_id || in_array($map_id, $map['clones'])) return $map['id'];
		}

		return false;
	}

	public function update_maps_after_form_update($form) {
		global $wpdb;
		global $post;

		if ($form instanceof WPCF7_ContactForm) {
			// update cf7 maps
			// find maps
			$table = MapsModel::get_table();
			$id = $form->id();

			$maps = $wpdb->get_results("SELECT id FROM $table WHERE mapping LIKE '%cf_$id%'");


			if ($maps ) {
				foreach ($maps as $m) {
					$mid = $m->id;

					$map = MapsModel::get($mid);
					$mapping = unserialize($map->mapping);

					if ( class_exists('vxcf_form') ) {
						$form_fields = vxcf_form::get_form_fields('cf_' . $id);

						foreach($form_fields as $field) {
							if (!in_array('v.vxcf_leads_detail-' .$field['name'], $mapping['selects'])) {
								$mapping['selects_only'][] = 'v.vxcf_leads_detail-' .$field['name'];
								$mapping['selects'][] = 'v.vxcf_leads_detail-' .$field['name'];
							}
						}

						MapsModel::updateMapCell($mid, 'mapping', serialize($mapping));
						self::create_fake_entry('cf_' . $id); // add fake entry to any saving of the map
					}
					// show popup for the magic map
					if (!empty($mapping['form_code'])) {
						$api = unserialize($map->api);
						$api['show_magic_popup'] = 1;
						$api['remove_notice'] = Wp2leads_Notices::add_warning(sprintf(__('Looks like you changed the form. Check Fields setting of the map <a href="/wp-admin/admin.php?page=wp2l-admin&tab=map_to_api&active_mapping=%s&come_from=wp2l_notice">%s</a>', 'wp2leads'), $mid, $map->name));
						MapsModel::updateMapCell($mid, 'api', serialize($api));
					}
				}


			} elseif ($form->prop('additional_settings') != 'demo_mode: on') { // Live Preview for Contact Form 7 fix
				Wp2leads_Notices::add_notice(
				sprintf(__('Looks like you have a new form. You can generate a new map for this form <a href="/wp-admin/admin.php?page=wp2l-admin&tab=map_port&generate_map=169&form_preselect=%s">on this page</a>', 'wp2leads'), $id), 1 );
			}
		}
	}

	public static function add_wp7_skins_callback($data) {
		$data[] = 'wp2leads_save_cf7_fields';
		return $data;
	}

	public static function get_169_html() {
		ob_start();
		?>
		<?php if (is_plugin_active('contact-form-entries/contact-form-entries.php')) { ?>
		<?php

			$forms = vxcf_form::get_forms();

			$vxcf_form = new vxcf_form();
			$vxcf_form_meta = $vxcf_form->get_meta();
			$vxcf_data = vxcf_form::get_data_object();
			$disabled_notice_list = array();
			if ($forms ) {
				foreach ($forms as $key => $form_group) {
					if ('WooCommerce' !== $form_group['label']) {
					?>
					<div class="magic-import-row">
						<h4><?php echo $form_group['label']; ?></h4>
						<select name="magic<?php echo $key; ?>">
							<option value=""><?php _e( '-- Select --', 'wp2leads' ); ?></option>
							<?php foreach ($form_group['forms'] as $code => $form) { ?>
								<option value="<?php echo $code; ?>"
									<?php
										if (isset($_GET['form_preselect']) ) {
											selected($_GET['form_preselect'], $code);
										}
									?>
								><?php echo $form; ?></option>
							<?php } ?>
						</select>
						<button type="submit" class="button button-primary magic-import" data-type="<?php echo $key; ?>" data-map_id="a18656a8f236469ade0450975e7886e8">
							<?php _e('Connect', 'wp2leads') ?>
						</button>
					</div>
					<?php
					}
				}
			}
			?>
			<br>
			<div class="wp2leads-notice wp2leads-notice-warning">
				<h4><?php _e('If some forms are not in the list turn them on in the <a href="/wp-admin/admin.php?page=vxcf_leads&tab=settings" target="_blank">CRM Entries Settings</a>', 'wp2leads') ?></h4>
			</div>
			<div class="wp2leads-notice wp2leads-notice-warning">
				<h4><?php _e('Recommended plugins', 'wp2leads') ?></h4>
				<p>
					<a href="https://wordpress.org/plugins/contact-form-7/" target="_blank">Contact Form 7</a>,
					<a href="https://wordpress.org/plugins/jetpack/" target="_blank">JetPack Contact Form</a>,
					<a href="https://wordpress.org/plugins/ninja-forms/" target="_blank">Ninja Forms</a>,
					<a href="https://wordpress.org/plugins/formidable/" target="_blank">Formidable Forms</a>,
					<a href="http://codecanyon.net/item/quform-wordpress-form-builder/706149" target="_blank">Quform</a>,
					<a href="https://wordpress.org/plugins/cforms2/" target="_blank">cformsII</a>,
					<a href="https://wordpress.org/plugins/contact-form-plugin/" target="_blank">Contact Form by BestWebSoft</a>,
					<a href="https://wordpress.org/plugins/ultimate-form-builder-lite/" target="_blank">Ultimate Form Builder</a>,
					<a href="https://wordpress.org/plugins/caldera-forms/" target="_blank">Caldera Forms</a>,
					<a href="https://wordpress.org/plugins/wpforms-lite/" target="_blank">WP Forms</a>,
					<a href="https://www.gravityforms.com/" target="_blank">Gravity Forms</a>.
					<br><br>
					<a class="button" href="/wp-admin/plugin-install.php" target="_blank"><?php _e('Install plugins', 'wp2leads'); ?></a> <a class="button" href="/wp-admin/plugins.php" target="_blank"><?php _e('View your plugins', 'wp2leads'); ?></a>
				</p>
			</div>
			<?php
		} else { ?>
			<div class="wp2leads-notice wp2leads-notice-warning">
				<h4><?php _e('Install "CRM Entries" plugin to use this feature', 'wp2leads'); ?></h4>
			</div>
		<?php } ?>

		<?php

		$html = ob_get_clean();
		return $html;
	}



	// for CRM plugin
	public function filter_contact_form_label($label, $type, $form_id, $name) {

		if ('cf' == $type) {
			// wpcf7_contact_form
			// get form text
			$form = get_post($form_id);

			if ($form && $form->post_content) {
				$form_array = explode($name, strip_tags ($form->post_content, '<strong><b><i>'));

				if (count($form_array) > 1) {
					$form_array = explode ('[', $form_array[0]);
					array_pop($form_array); // delete "select" from string
					$form_array = explode(']', array_pop($form_array));
					$form_array = explode(PHP_EOL, trim(array_pop($form_array)));

					return trim(str_replace(array(',', ';', ':'), array('', '', ''), substr(array_pop($form_array), -100)));
				}
			}
		}
		return $label;
	}

	public static function create_fake_entry($form_id) {
		$fields = vxcf_form::get_form_fields($form_id);
		$fake_data = array();

		$data = array(
			'form_id' => $form_id,
			'ip' => '0.0.0.0',
			'user_id' => 0
		);

		foreach ($fields as $key => $field) {

			switch ($field['type']) {
				case 'select':
				case 'checkbox':
				case 'radio':
				case 'quiz':
					if (!empty($field['values']))	$fake_data[$key] = $field['values'][0]['value'];
					break;

				case 'email':
					$fake_data[$key] = 'dsv@ds.ds';
					break;

				case 'acceptance':
					$fake_data[$key] = '1';
					break;

				case 'text':

				default:
					$fake_data[$key] = 'test';
			}
		}

		$d = new vxcf_form_data();
		$d->create_lead($fake_data, $data);
	}

	public static function add_new_magic_map($map_id, $parent_map_id, $parent_server_id) {
		$parameters = array (
			'event' => 'add_magic_map',
			'map_id' => $map_id,
			'parent_map_id' => $parent_map_id,
			'parent_server_id' => $parent_server_id
		);

		$request = wp_remote_post(
			base64_decode(self::get_server()),
			array(
				'body'    => $parameters,
			)
		);

			$response = json_decode(wp_remote_retrieve_body( $request ), true);
	}
}