<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 09.10.18
 * Time: 12:40
 */

defined( 'ABSPATH' ) || exit;

class Wp2leads_New_User_Request extends Wp2leads_Background_Process {

    use Wp2leads_Logger;

    /**
     * @var string
     */
    protected $action = 'wp2leads_new_user';

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
    protected function task( $email) {
        $rand = rand(0, 21);
        $rand_country = rand(0, 3);
        $request = wp_remote_get('https://randomuser.me/api/?nat=us,dk,fr,gb');
        $response = json_decode(wp_remote_retrieve_body( $request ), true);
        $fake_info = $response['results'][0];

        $old_email = explode('@', $fake_info['email']);

        // Prepare data to save in DB
        $fn = ucfirst($fake_info['name']['first']);
        $ln = ucfirst($fake_info['name']['last']);
        $email = $old_email[0] . $email;
        $login = str_replace('.', '_', $old_email[0]);
        $phone = '+' . $fake_info['phone'];
        $role = $this->user_roles[$rand];
        $password = wp_generate_password( 12, false );
        ob_start();
        var_dump($fake_info['location']['street']);
        $error_log = ob_get_clean();

        $this->log( $error_log );

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

        if( null == username_exists( $email ) ) {

            $user_id = wp_create_user( $login, $password, $email );

            if (!is_wp_error($user_id)) {

                $userdata = array(
                    'ID'              => $user_id,
                    'user_nicename'   => $login,
                    'user_url'        => $login . 'com.' . strtolower($country),
                    'display_name'    => 'Gerald' . ' ' . $fn . ' ' . $ln,
                    'nickname'        => $login,
                    'first_name'      => $fn,
                    'last_name'       => $ln,
                    'user_registered' => date('Y-m-d H:i:s'),   // registration date (Y-m-d H:i:s)
                    'role'            => $role,
                );

                $user_id = wp_update_user($userdata);

                update_user_meta( $user_id, 'first_name', $fn );
                update_user_meta( $user_id, 'last_name', $ln );
                update_user_meta( $user_id, 'nickname', $login );
                update_user_meta( $user_id, 'billing_address_1', $address );
                update_user_meta( $user_id, 'billing_city', $city );
                update_user_meta( $user_id, 'billing_country', $country );
                update_user_meta( $user_id, 'billing_company', $login . ' - ' . $fn . ' ' . $ln . ' - LTD' );
                update_user_meta( $user_id, 'billing_email', $email );
                update_user_meta( $user_id, 'billing_email', $email );
                update_user_meta( $user_id, 'billing_first_name', $fn );
                update_user_meta( $user_id, 'billing_last_name', $ln );
                update_user_meta( $user_id, 'billing_phone', $phone );
                update_user_meta( $user_id, 'billing_postcode', $postcode );
                update_user_meta( $user_id, 'billing_state', $state );
                update_user_meta( $user_id, 'shipping_first_name', $fn );
                update_user_meta( $user_id, 'shipping_last_name', $ln );
                update_user_meta( $user_id, 'shipping_company', $login . ' - ' . $fn . ' ' . $ln . ' - LTD' );
                update_user_meta( $user_id, 'shipping_address_1', $address );
                update_user_meta( $user_id, 'shipping_city', $city );
                update_user_meta( $user_id, 'shipping_postcode', $postcode );
                update_user_meta( $user_id, 'shipping_country', $country );
                update_user_meta( $user_id, 'shipping_state', $state );
            }
        }

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