<?php
trait Wp2leads_Logger {

    /**
     * Really long running process
     *
     * @return int
     */
    public function really_long_running_task() {
        return sleep( 1 );
    }

    /**
     * Log
     *
     * @param string $message
     */
    public function log( $message ) {
        $wp2l_is_dev_env = defined( 'WP2LEADS_DEV_ENV' ) && WP2LEADS_DEV_ENV;
        if( $wp2l_is_dev_env ) {
            error_log($message);
        }
    }
    
    /**
     * Get lorem
     *
     * @param string $name
     *
     * @return string
     */
    protected function get_message( $name ) {
        //$response = wp_remote_get( esc_url_raw( 'http://loripsum.net/api/1/short/plaintext' ) );
        //$body     = trim( wp_remote_retrieve_body( $response ) );
        $body     = false;

        if ( empty( $body ) ) {
            $body = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        }

        return $name . ': ' . $body;
    }

}