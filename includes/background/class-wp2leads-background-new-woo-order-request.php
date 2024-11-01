<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 09.10.18
 * Time: 12:40
 */

defined( 'ABSPATH' ) || exit;

function updpout($true) {
    return true;
}

class Wp2leads_New_Woo_Order_Request extends Wp2leads_Background_Process {

    use Wp2leads_Logger;

    /**
     * @var string
     */
    protected $action = 'wp2leads_new_wooorder';

    protected $user_roles = array(
        'editor',
        'author',
        'contributor',
        'subscriber',
        'customer',
        'shop_manager',
        'subscriber',
        'customer',
        'subscriber',
        'customer',
        'subscriber',
        'customer',
        'subscriber',
        'customer',
        'subscriber',
        'customer',
        'subscriber',
        'customer',
        'subscriber',
        'customer',
        'subscriber',
        'customer'
    );

    protected $user_countries = array(
        'us',
        'dk',
        'fr',
        'gb',
    );

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param mixed $item Queue item to iterate over
     *
     * @return mixed
     */
    protected function task( $email_domain) {

        $request = wp_remote_get('https://randomuser.me/api/?nat=us,gb,nz');
        $response = json_decode(wp_remote_retrieve_body( $request ), true);
        $fake_info = $response['results'][0];
        $old_email = explode('@', $fake_info['email']);
        $email_check = $old_email[0] . '-woo' . $email_domain;

        $email_valid = filter_var($email_check, FILTER_VALIDATE_EMAIL);

        if (!$email_valid) {
            $this->log( 'email: ' . $email_check );
            return false;
        }

        $email = $email_valid;

        $fn = ucfirst($fake_info['name']['first']);
        $ln = ucfirst($fake_info['name']['last']);
        $login = str_replace('.', '_', $old_email[0]);
        $phone = '+' . $fake_info['phone'];

        if (is_array($fake_info['location']['street'])) {
            $address = '';
            if (!empty($fake_info['location']['street']['number'])) {
                $address .= $fake_info['location']['street']['number'].', ';
            }
            if (!empty($fake_info['location']['street']['name'])) {
                $address .= $fake_info['location']['street']['name'];
            } else {
                $address .= 'Fake Address';
            }
        } else {
            $address = ucwords($fake_info['location']['street']);
        }

        $city = ucwords($fake_info['location']['city']);
        $state = ucwords($fake_info['location']['state']);
        $postcode = $fake_info['location']['postcode'];
        $country = strtoupper($fake_info['nat']);

        $this->log( 'first_name: ' . $fn );
        $this->log( 'last_name: ' . $ln );
        $this->log( 'company: ' . $login . ' - ' . $fn . ' ' . $ln . ' - LTD' );
        $this->log( 'email: ' . $email );
        $this->log( 'phone: ' . $phone );
        $this->log( 'address_1: ' . $address );
        $this->log( 'city: ' . $city );
        $this->log( 'state: ' . $state );
        $this->log( 'postcode: ' . $postcode );
        $this->log( 'country: ' . $country );
        $this->log( '========================================' );

        $payment_gateways = WC()->payment_gateways->payment_gateways();

        $payment_statuses = array(
            'bacs',
            'cheque',
            'cod',
            'paypal',
        );

        $order_statuses = array(
            'pending',
            'on-hold',
            'processing',
            'completed',
            'refunded',
            'cancelled',
            'completed',
            'processing',
            'completed',
            'processing',
            'completed',
            'completed',
            'processing',
            'completed',
            'processing',
            'completed',
            'completed',
            'processing',
            'completed',
        );

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1
        );

        $posts_array = get_posts( $args );

        $products_array = array();

        foreach ($posts_array as $post_item) {
            $product_object = wc_get_product($post_item->ID);

            //if ('simple' === $product_object->get_type() || 'variable' === $product_object->get_type()) {
            if ('simple' === $product_object->get_type()) {
                $products_array[] = $product_object;
            }
        }

        $order_max = count($products_array) - 1;

        $order_rand = rand(1, $order_max);
//        echo "<pre>";
//        echo 'Order Items: ' . $order_rand . PHP_EOL;
//        echo "===============================" . PHP_EOL;

        $order_address = array(
            'first_name' => $fn,
            'last_name'  => $ln,
            'company'    => $login . ' - ' . $fn . ' ' . $ln . ' - LTD',
            'email'      => $email,
            'phone'      => $phone,
            'address_1'  => $address,
            'address_2'  => '',
            'city'       => $city,
            'state'      => $state,
            'postcode'   => $postcode,
            'country'    => $country
        );

        add_filter("update_post_metadata_cache", "updpout");
        $order = wc_create_order();
        remove_filter("update_post_metadata_cache", "updpout");

        for ($i = 0; $i < $order_rand; $i++) {
            $products_array = array_values($products_array);
            $products_count = count($products_array);

            $rand = rand(0, $products_count - 1);

            $_product = $products_array[$rand];

            unset($products_array[$rand]);

            $order->add_product( wc_get_product( $_product->get_id() ), rand(1, 10) );
        }
//        echo "</pre>";
        $status_rand = rand(0, 15);
        $payment_rand = rand(0, 3);

        $order->set_address( $order_address, 'billing' );
        $order->set_address( $order_address, 'shipping' );
        $order->set_payment_method( $payment_gateways[$payment_statuses[$payment_rand]] );
        $order->calculate_totals();
        $order->update_status($order_statuses[$status_rand], 'Lorem ipsum dolor sit amet, est at principes intellegat');

        wp_reset_postdata();

        return false;
    }

    /**
     * Limit each task ran per batch to 1 for image regen.
     *
     * @return bool
     */
    protected function batch_limit_exceeded() {
        return true;
    }

    /**
     * Complete
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete() {
        parent::complete();

        // Show notice to user or perform some other arbitrary task...
    }
}