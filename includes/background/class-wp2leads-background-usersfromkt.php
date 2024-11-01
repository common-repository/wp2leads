<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 09.10.18
 * Time: 12:40
 */

defined( 'ABSPATH' ) || exit;

class Wp2leads_Background_UsersFromKt extends Wp2leads_Background_Process {

    use Wp2leads_Logger;

    /**
     * @var string
     */
    protected $action = 'wp2leads_get_users_from_kt';

    /**
     * Initiate new background process.
     */
    public function __construct() {
        parent::__construct();
    }

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
    protected function task( $subscriber ) {
        $user_name = get_option('wp2l_klicktipp_username');
        $user_password = get_option('wp2l_klicktipp_password');

        $connector = new Wp2leads_KlicktippConnector();
        $logged_in = $connector->login($user_name, $user_password);

        if ( $logged_in ) {
            $subscriber = $connector->subscriber_get($subscriber);
            $message = json_encode($subscriber);
        } else {
            $message = 'Something went wrong';
        }

        $this->log( $message );

        return false;
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