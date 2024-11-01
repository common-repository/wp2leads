<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 01.11.18
 * Time: 17:02
 */
class Wp2leads_Cron_Rest_Api_Events extends WP_REST_Controller {
    protected $namespace;

    private $version = '1';

    private $actions_allowed = array(
        'prepare_for_transfer',
        'transfer'
    );

    public function __construct() {
        $this->namespace = 'wp2leads/v' . $this->version;
        $this->rest_base = 'cron';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/cron/(?P<action>[\w]+)' . '/map_id/(?P<map_id>[\w]+)', array(
            array (
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'handle_request' ),
                'permission_callback' => array( $this, 'permissions_check' ),
            )
        ) );
    }

    public function permissions_check($request) {
        // check if action allowed
        $action = $request['action'];

        if (!in_array( $action, $this->actions_allowed )) {
            return false;
        }

        // Check if key is valid

        return true;
    }

    public function handle_request( $request ) {
        $action = $request['action'];
        $map_id = $request['map_id'];

        $response = $this->{'handle_' . $action . '_action'}($map_id);

        return $response;
    }

    protected function handle_prepare_for_transfer_action($map_id) {
        global $wpdb;
        $need_transfer = false;
        $map = MapsModel::get($map_id);
        $mapping = unserialize($map->mapping);
        $api = unserialize($map->api);

        $date_time = $mapping['dateTime'];

        foreach ($date_time as $item) {
            if (0 === strpos($item, 'v.')) {
                $name = ltrim($item, 'v.');
                $name = explode('-', $name);
                $table = $wpdb->prefix . $name[0];
                $field = $name[1];
            } else {
                $name = explode('.', $item);
                $table = $wpdb->prefix . $name[0];
                $field = $name[1];
            }

            $now = time();

            $now_date = date('Y-m-d H:i:s');

            $sql = "SELECT COUNT(*) FROM {$table}";
            $sql .= " WHERE {$field} BETWEEN '2019-04-30 14:15:55' AND '{$now_date}';";

            $outdated = (int) $wpdb->get_var( $sql );

            if ($outdated > 0) {
                $need_transfer = true;
                break;
            }
        }

        $body = array(
            'code' => 'preparation_started',
            'message' => __('Success', 'wp2leads'),
            'data' => array(
                'status' => 200,
                'body' => $api,
            ),
        );

        return new WP_REST_Response( $body, 200 );
    }

    /**
     * Response for test connection
     *
     * @return WP_REST_Response
     */
    protected function handle_transfer_action($map_id) {
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