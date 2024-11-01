<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 09.10.18
 * Time: 15:05
 */

class Wp2leads_Background {

    /**
     * @var WP_Example_Process
     */
    protected $new_user;
    protected $new_woo_order;

    protected $new_user_number = 100;

    protected $new_user_email = '@plugin-dev.ua';

    /**
     * Example_Background_Processing constructor.
     */
    public function __construct($email) {
        $this->new_user_email = $email;

        add_action( 'plugins_loaded', array( $this, 'init' ) );
        add_action( 'admin_bar_menu', array( $this, 'admin_bar' ), 100 );
        add_action( 'init', array( $this, 'process_handler' ) );
    }

    /**
     * Init
     */
    public function init() {
        require_once plugin_dir_path( __FILE__ ) . 'class-wp2leads-logger.php';
        require_once plugin_dir_path( __FILE__ ) . 'background/abstract-class-wp2leads-background.php';
        require_once plugin_dir_path( __FILE__ ) . 'background/class-wp2leads-background-new-user-request.php';
        require_once plugin_dir_path( __FILE__ ) . 'background/class-wp2leads-background-new-woo-order-request.php';

        if (class_exists('Wp2leads_New_User_Request')) {
            $this->new_user = new Wp2leads_New_User_Request();
        }

        if (class_exists('Wp2leads_New_Woo_Order_Request')) {
            $this->new_woo_order = new Wp2leads_New_Woo_Order_Request();
        }

    }

    /**
     * Admin bar
     *
     * @param WP_Admin_Bar $wp_admin_bar
     */
    public function admin_bar( $wp_admin_bar ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $wp_admin_bar->add_menu( array(
            'id'    => 'wp2leads-background',
            'title' => __( 'Background Processes', 'example-plugin' ),
            'href'  => '#',
        ) );

        $wp_admin_bar->add_menu( array(
            'parent' => 'wp2leads-background',
            'id'     => 'wp2leads-background-users',
            'title'  => __( 'New 10 Users', 'example-plugin' ),
            'href'   => wp_nonce_url( admin_url( '?process=newusers_10'), 'process' ),
        ) );

        $wp_admin_bar->add_menu( array(
            'parent' => 'wp2leads-background',
            'id'     => 'wp2leads-background-users-more',
            'title'  => __( 'New 100 Users', 'example-plugin' ),
            'href'   => wp_nonce_url( admin_url( '?process=newusers_100'), 'process' ),
        ) );

        $wp_admin_bar->add_menu( array(
            'parent' => 'wp2leads-background',
            'id'     => 'wp2leads-background-users-more-more',
            'title'  => __( 'New 1000 Users', 'example-plugin' ),
            'href'   => wp_nonce_url( admin_url( '?process=newusers_1000'), 'process' ),
        ) );

        $wp_admin_bar->add_menu( array(
            'parent' => 'wp2leads-background',
            'id'     => 'wp2leads-background-woo-orders',
            'title'  => __( 'New 10 Orders', 'example-plugin' ),
            'href'   => wp_nonce_url( admin_url( '?process=newwooorders_10'), 'process' ),
        ) );

        $wp_admin_bar->add_menu( array(
            'parent' => 'wp2leads-background',
            'id'     => 'wp2leads-background-woo-orders-more',
            'title'  => __( 'New 100 Orders', 'example-plugin' ),
            'href'   => wp_nonce_url( admin_url( '?process=newwooorders_100'), 'process' ),
        ) );

        $wp_admin_bar->add_menu( array(
            'parent' => 'wp2leads-background',
            'id'     => 'wp2leads-background-woo-orders-more-more',
            'title'  => __( 'New 1000 Orders', 'example-plugin' ),
            'href'   => wp_nonce_url( admin_url( '?process=newwooorders_1000'), 'process' ),
        ) );
    }

    /**
     * Process handler
     */
    public function process_handler() {
        if ( ! isset( $_GET['process'] ) || ! isset( $_GET['_wpnonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'process') ) {
            return;
        }

        if ( 'newusers_10' === $_GET['process'] ) {
            $this->handle_newuser(10);
        }

        if ( 'newusers_100' === $_GET['process'] ) {
            $this->handle_newuser(100);
        }

        if ( 'newusers_1000' === $_GET['process'] ) {
            $this->handle_newuser(1000);
        }

        if ( 'newwooorders_10' === $_GET['process'] ) {
            $this->handle_neworder(10);
        }

        if ( 'newwooorders_100' === $_GET['process'] ) {
            $this->handle_neworder(100);
        }

        if ( 'newwooorders_1000' === $_GET['process'] ) {
            $this->handle_neworder(1000);
        }
    }

    /**
     * Handle all
     */
    protected function handle_newuser($number) {
        if (!empty($this->new_user)) {
            for ($i = 1; $i <= $number; $i++) {

                $this->new_user->push_to_queue( $this->new_user_email );
            }

            $this->new_user->save()->dispatch();
        }
    }

    /**
     * Handle all
     */
    protected function handle_neworder($number) {
        if (!empty($this->new_woo_order)) {
            for ($i = 1; $i <= $number; $i++) {

                $this->new_woo_order->push_to_queue( $this->new_user_email );
            }

            $this->new_woo_order->save()->dispatch();
        }
    }
}

if (Wp2leads_License::is_dev_allowed()) {
    $current_site = Wp2leads_License::get_current_site();
    $current_site_encoded = base64_encode ($current_site);

    if ('ZHVtbXkuc2FudGVncmEtaW50ZXJuYXRpb25hbC5jb20=' === $current_site_encoded) {
        $email = '@plugin-test.de';
    } else if('d3AybGVhZHMyLmxvYw==' === $current_site_encoded) {
        $email = '@plugin2-dev.ua';
    } else if('cGx1Z2luLXRlc3QuZGU=' === $current_site_encoded) {
        $email = '@plugin2-test.de';
    } else if('d3AybGVhZHMubG9j' === $current_site_encoded) {
        $email = '@gmail.com';
    } else {
        $email = '@fleckens.hu';
    }

    new Wp2leads_Background($email);
}