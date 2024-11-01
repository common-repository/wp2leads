<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 01.11.18
 * Time: 17:02
 */
class Wp2leads_Rest_Api_Events extends WP_REST_Controller {
    protected $namespace;

    private $version = '1';

    private $events_allowed = array(
        'on_payment',
        'on_payment_missed',
        'on_refund',
        'on_chargeback',
        'on_rebill_cancelled',
        'on_rebill_resumed',
        'last_paid_day',
        'connection_test',
        'on_delete',
        'update_license'
    );

    public function __construct() {
        $this->namespace = 'wp2leads/v' . $this->version;
        $this->rest_base = 'events';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/events/(?P<event>[\w]+)' . '/key/(?P<key>[\w]+)', array(
            array (
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'handle_request' ),
                'permission_callback' => array( $this, 'permissions_check' ),
            )
        ) );
    }

    public function permissions_check($request) {
        // check if action allowed
        $event = $request['event'];

        if (!in_array( $event, $this->events_allowed )) {
            return false;
        }

        // Check if key is valid
        $key = $request['key'];

        $license_info = Wp2leads_License::get_lecense_info();

        if ($key !== $license_info['key'] && $key !== md5($license_info['key'])) {
            return false;
        }

        return true;
    }

    public function handle_request( $request ) {
        $event = $request['event'];

        $response = $this->{'handle_' . $event . '_event'}();

        return $response;
    }

    protected function handle_on_payment_missed_event() {
        $result = Wp2leads_License::get_lecense_info();

        $body = array(
            'code' => 'payment_missed',
            'message' => __('Success', 'wp2leads'),
            'data' => array(
                'status' => 200,
                'body' => $result,
            ),
        );

        return new WP_REST_Response( $body, 404 );
    }

    /**
     * Response for test connection
     *
     * @return WP_REST_Response
     */
    protected function handle_connection_test_event() {
        $result = Wp2leads_License::update_license();

        $body = array(
            'code' => 'success_connection',
            'message' => __('Success', 'wp2leads'),
            'data' => array(
                'status' => 200,
                'result' => $result
            ),
        );

        return new WP_REST_Response( $body, 200 );
    }

    /**
     * Response for test connection
     *
     * @return WP_REST_Response
     */
    protected function handle_update_license_event() {
        $result = Wp2leads_License::update_license();

        $body = array(
            'code' => 'success_connection',
            'message' => __('Success', 'wp2leads'),
            'data' => array(
                'status' => 200,
                'result' => $result
            ),
        );

        return new WP_REST_Response( $body, 200 );
    }

    /**
     * @param $event
     *
     * @return bool
     */
    protected function is_event_allowed( $event ) {
        return in_array( $event, $this->events_allowed );
    }
}