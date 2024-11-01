<?php

/**
 * Class SystemHelper
 */
class SystemHelper {
    public static function get_system_info() {
        global $wpdb;

        $php_memory_limit = self::php_ini_val_to_num(WP_MEMORY_LIMIT);

        if ( function_exists( 'memory_get_usage' ) ) {
            $php_memory_limit = max( $php_memory_limit, self::php_ini_val_to_num( @ini_get( 'memory_limit' ) ) );
        }

        return array(
            'wp_version' => get_bloginfo( 'version' ),
            'wp2lead_version' => WP2LEADS_VERSION,
            'php_version' => phpversion(),
            'mysql_version' => $wpdb->db_version(),
            'php_memory_limit' => $php_memory_limit,
        );
    }

    public static function php_ini_val_to_num( $size ) {
        $l    = substr( $size, -1 );
        $ret  = substr( $size, 0, -1 );
        $byte = 1024;

        switch ( strtoupper( $l ) ) {
            case 'P':
                $ret *= $byte;
            case 'T':
                $ret *= $byte;
            case 'G':
                $ret *= $byte;
            case 'M':
                $ret *= $byte;
            case 'K':
                $ret *= $byte;
        }
        return $ret;
    }
}