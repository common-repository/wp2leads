<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wp2leads
 * @subpackage Wp2leads/includes
 */
class Wp2leads_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		add_filter( 'plugin_locale', 'Wp2leads_i18n::check_de_locale');

		load_plugin_textdomain(
			'wp2leads',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

		remove_filter( 'plugin_locale', 'Wp2leads_i18n::check_de_locale');
	}

	public static function check_de_locale($domain) {
		$site_lang = get_user_locale();
		$de_lang_list = array(
			'de_CH_informal',
			'de_DE_formal',
			'de_AT',
			'de_CH',
			'de_DE'
		);

		if (in_array($site_lang, $de_lang_list)) return 'de_DE';
		return $domain;
	}
	// add wp2leads_i18n_get() in js to translate the strings.
	public function load_js_translate_strings() {
		$translate_lines = array(
			'Map successfully saved!' => __('Map successfully saved!', 'wp2leads'),
			'Are you sure you want to completely start over?' => __('Are you sure you want to completely start over?', 'wp2leads'),
			'Are you sure you want to change the starter data? This will remove any existing relationships.' => __('Are you sure you want to change the starter data? This will remove any existing relationships.', 'wp2leads'),
			'Done' => __('Done', 'wp2leads'),
			'-- Select --' => __('-- Select --', 'wp2leads'),
			'Are you sure you want to change the starter data? This will remove any current settings.' => __('Are you sure you want to change the starter data? This will remove any current settings.', 'wp2leads'),
			'You must provide a name for the map' => __('You must provide a name for the map', 'wp2leads'),
			'Saved Successfully!' => __('Saved Successfully!', 'wp2leads'),
			'Something went wrong.' => __('Something went wrong.', 'wp2leads'),
			'Starter' => __('Starter', 'wp2leads'),
			'Relation' => __('Relation', 'wp2leads'),
			'Virtual Relation' => __('Virtual Relation', 'wp2leads'),
			'Update your map to begin showing results!' => __('Update your map to begin showing results!', 'wp2leads'),
			'Choose an option' => __('Choose an option', 'wp2leads'),
			'Map Builder' => __('Map Builder', 'wp2leads'),
			'Are you sure? Without correct license key you are not be able to activate your plugin again.' => __('Are you sure? Without correct license key you are not be able to activate your plugin again.', 'wp2leads'),
			'Authorization in KlickTipp was successful!' => __('Authorization in KlickTipp was successful!', 'wp2leads'),
			'The username or password you entered is incorrect!' => __('The username or password you entered is incorrect!', 'wp2leads'),
			'Select at least one map to export' => __('Select at least one map to export', 'wp2leads'),
			'Map successfully deleted!' => __('Map successfully deleted!', 'wp2leads'),
			'Hide map list' => __('Hide map list', 'wp2leads'),
			'Show map list' => __('Show map list', 'wp2leads'),
			'Are you sure you want to completely delete this maps?' => __('Are you sure you want to completely delete this maps?', 'wp2leads'),
			'Please, select one or more tags from the list.' => __('Please, select one or more tags from the list.', 'wp2leads'),
			'Be aware! You delete Tags in Klick-Tipp! Yes, i want to delete selected ' => __('Be aware! You delete Tags in Klick-Tipp! Yes, i want to delete selected ', 'wp2leads'),
			' Tags in Klick-Tipp.' => __(' Tags in Klick-Tipp.', 'wp2leads'),
			'Users started transfered in background' => __('Users started transfered in background', 'wp2leads'),
			'Please, choose one or more trigger from the list.' => __('Please, choose one or more trigger from the list.', 'wp2leads'),
			'Dismiss this notice.' => __('Dismiss this notice.', 'wp2leads'),
			'Are you sure you want to completely delete this map?' => __('Are you sure you want to completely delete this map?', 'wp2leads'),
			'Select form to import.' => __('Select form to import.', 'wp2leads'),
			'You should install and activate required plugins to use this map.' => __('You should install and activate required plugins to use this map.', 'wp2leads'),
			'Installing...' => __('Installing...', 'wp2leads'),
			'Plugins installed successfully, generate map once more after reload' => __('Plugins installed successfully, generate map once more after reload', 'wp2leads'),
			'Are you sure?' => __('Are you sure?', 'wp2leads'),
			'Edit Contact Form' => __('Edit Contact Form', 'wp2leads'),
			'Import KT campaign' => __('Import KT campaign', 'wp2leads'),
			'Edit map' => __('Edit map', 'wp2leads'),
			'Set prefix' => __('Set prefix', 'wp2leads'),
			'No value' => __('No value', 'wp2leads'),
		);

		?>
		<script>
			var wp2leads_i18n = {};
            var wp2leads_admin_url = "<?php echo get_admin_url() ?>?page=wp2l-admin"


			<?php foreach($translate_lines as $text => $translation) { ?>
				wp2leads_i18n["<?php echo $text; ?>"] = "<?php echo $translation; ?>";
			<?php } ?>

			function wp2leads_i18n_get(text) {
				if (wp2leads_i18n[text] == 'undefined') {
					console.log('Needs new translate:'+text);
					return text;
				}
				return wp2leads_i18n[text];
			}

		</script>
		<?php

	}

}
