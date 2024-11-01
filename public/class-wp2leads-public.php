<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/public
 */
class Wp2leads_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp2leads_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp2leads_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp2leads-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp2leads_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp2leads_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp2leads-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script($this->plugin_name . '-optin', plugin_dir_url(__FILE__) . 'js/wp2leads-optin.js?' . time(), array( 'jquery' ));

		wp_localize_script( $this->plugin_name . '-optin', 'wp2leadsOptinAjax',
			array(
				'url' => admin_url('admin-ajax.php')
			)
		);
	}

	public function process_kt_invite() {
        $kt_invites = [
            'kt_invite',
            // 'kt_invite_nocache',
        ];

        $is_invite = false;
        $kt_invite_selected = '';

        foreach ($kt_invites as $kt_invite) {
            if (!empty($_GET[$kt_invite])) {
                $is_invite = sanitize_text_field($_GET[$kt_invite]);
                $kt_invite_selected = $kt_invite;
                break;
            }
        }

        if (empty($is_invite)) return;
        $current_uri = home_url( add_query_arg( NULL, NULL ) );
        $parsed_url = parse_url($current_uri);

        $new_url = '';

        if (!empty($parsed_url["scheme"])) $new_url .= $parsed_url["scheme"] . '://';
        if (!empty($parsed_url["host"])) $new_url .= $parsed_url["host"];
        if (!empty($parsed_url["path"])) $new_url .= $parsed_url["path"];

        if (!empty($parsed_url["query"])) {
            parse_str($parsed_url["query"], $query_array);

            if (!empty($query_array[$kt_invite_selected])) {
                unset($query_array[$kt_invite_selected]);
            }

            if (!empty($query_array)) {
                $query_string = http_build_query($query_array);

                if ($kt_invite_selected === 'kt_invite_nocache') {
                    $new_url .= '?' . $query_string;
                } else {
                    $new_url .= '?' . $query_string . '&kt_invite_nocache=1';
                }
            } else {

                if ($kt_invite_selected === 'kt_invite_nocache') {
                    $new_url .= '';
                } else {
                    $new_url .= '?kt_invite_nocache=1';
                }
            }

            if ($kt_invite_selected !== 'kt_invite_nocache') {
                do_action('wp2leads_process_kt_invite', $is_invite);
                define( 'DONOTCACHEPAGE', true );
                KlickTippManager::autologin_kt_invite($is_invite);
            }
            wp_redirect($new_url);
            exit;
        }
    }

    public function replace_kt_invite_headers($headers) {
        if (empty($_GET['kt_invite_nocache'])) return;

        $headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
        $headers['Pragma'] = 'no-cache';
        $headers['Expires'] = '0';

        return $headers;
    }

}
