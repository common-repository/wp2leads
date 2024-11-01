<?php

/**
 * Hooks and functions for opt-in processes
 *
 * @since      1.0.0
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/includes
 */


class Wp2leads_OptIn {

	public function __construct() {
		add_action( 'wp_ajax_wp2leads_cf7_redirects', array( $this, 'wp2leads_cf7_redirects' ) );
        add_action( 'wp_ajax_nopriv_wp2leads_cf7_redirects', array( $this, 'wp2leads_cf7_redirects' ) );
	}

	public function wp2leads_cf7_redirects() {
		$form_id = empty($_POST['contactFormId']) ? 0 : (int)$_POST['contactFormId']; // int for sql injections
		$inputs = empty($_POST['inputs']) ? array() : $_POST['inputs'];

		if (!$form_id || !$inputs || !class_exists('vxcf_form') ) wp_die(); // wrong query

		$fields = vxcf_form::get_form_fields('cf_' . $form_id);
		if (!$fields ) wp_die();

		$user_email = '';

		foreach ($fields as $field) {
			if ($field['type'] == 'email') {
				foreach ($inputs as $input) {
					if ($input['name'] == $field['name']) {
						$user_email = $input['value'];
					}
				}
			}
		}

		foreach ($fields as $field) {
		    $fname = $field['name'];
		    $input_exists = false;

		    foreach ($inputs as $input) {
		        $iname = str_replace('[]', '', $input['name']);
                if ($iname === $fname) {
                    $input_exists = true;
                }
            }

		    if (!$input_exists) {
		        $input_to_add = array(
		            'name' => $fname,
                    'value' => ''
                );

                array_push($inputs,$input_to_add);
            }
        }

		if (!$user_email) wp_die(); // no email in the fields

		// find map
		global $wpdb;
		$table = MapsModel::get_table();
		$maps = $wpdb->get_results("SELECT * FROM $table WHERE mapping LIKE '%cf_$form_id%'");

		if (!$maps) wp_die(); // no map for this form

		$map = $maps[0];

		// check opt-in
		$api = unserialize($map->api);
		if (empty($api['default_optin'])) wp_die(); // no opt in processe

		$optin = $api['default_optin'];

		// check opt in conditions
		if (!empty($api['conditions']) && !empty($api['conditions']['donot_optins'])) {
			$do_not_optin = false;

			foreach ($api['conditions']['donot_optins'] as $condition) {

				if (!isset($condition['option']) || !isset($condition['string']) || !isset($condition['operator'])) {
                    continue;
                }

				$value = false;

				foreach ($fields as $field) {
					if (strpos( $condition['option'], $field['name']) !== false) {
						foreach ($inputs as $input) {
                            $iname = str_replace('[]', '', $input['name']);
							if ($iname == $field['name']) {
								$value = $input['value'];
							}
						}
					}
				}

				if (false === $value) continue;

				$condition_result = false;
				switch ($condition['operator']) {
					case 'is like':
						if ($value === $condition['string']) {
							$condition_result = true;
						}
						break;
					case 'like':
						if ($value === $condition['string']) {
							$condition_result = true;
						}
						break;
					case 'not-like':
						if ($value !== $condition['string']) {
							$condition_result = true;
						}
						break;
					case 'contains':
						if (strpos($value, $condition['string']) !== false) {
							$condition_result = true;
						}
						break;
					case 'not contains':
						if (strpos($value, $condition['string']) === false) {
							$condition_result = true;
						}
						break;
					case 'bigger as':
						if ((float) $value > (float)$condition['string']) {
							$condition_result = true;
						}
						break;
					case 'smaller as':
						if ((float)$value < (float)$condition['string']) {
							$condition_result = true;
						}
						break;
				}

				if ($condition_result) {
                    $do_not_optin = true;
                }

			}

			if ($do_not_optin) {
                wp_die(); // we have a field that is in the do not condition
            }
		}

		// check other opt-ins
		if (!empty($api['conditions']) && !empty($api['conditions']['optins'])) {

			foreach ($api['conditions']['optins'] as $condition) {

				if (!isset($condition['option']) || !isset($condition['string']) || !isset($condition['operator']) || !isset($condition['connectTo'])) {
                    continue;
                }

				$value = false;

				foreach ($fields as $field) {
					if (strpos( $condition['option'], $field['name']) !== false) {
						foreach ($inputs as $input) {
							if ($input['name'] == $field['name']) {
								$value = $input['value'];
							}
						}
					}
				}

				if (!$value) continue;

				$condition_result = false;
				switch ($condition['operator']) {
					case 'is like':
						if ($value === $condition['string']) {
							$condition_result = true;
						}
						break;
					case 'like':
						if ($value === $condition['string']) {
							$condition_result = true;
						}
						break;
					case 'not-like':
						if ($value !== $condition['string']) {
							$condition_result = true;
						}
						break;
					case 'contains':
						if (strpos($value, $condition['string']) !== false) {
							$condition_result = true;
						}
						break;
					case 'not contains':
						if (strpos($value, $condition['string']) === false) {
							$condition_result = true;
						}
						break;
					case 'bigger as':
						if ((float) $value > (float)$condition['string']) {
							$condition_result = true;
						}
						break;
					case 'smaller as':
						if ((float)$value < (float)$condition['string']) {
							$condition_result = true;
						}
						break;
				}

				if ($condition_result) {
                    $optin = $condition['connectTo'];
                }

			}
		}

		$connector = new Wp2leads_KlicktippConnector();
        $logged_in = $connector->login(get_option('wp2l_klicktipp_username'), get_option('wp2l_klicktipp_password'));

        if(!$logged_in) wp_die(); // no KT connection

		$link = $connector->subscription_process_redirect($optin, $user_email);

		// for new user we should add it at first
		if (!$link) {
			$user = $connector->subscribe($user_email, $optin);
			// try once more
			$link = $connector->subscription_process_redirect($optin, $user_email);
		}

		echo $link;
		wp_die();
	}

}

new Wp2leads_OptIn();
