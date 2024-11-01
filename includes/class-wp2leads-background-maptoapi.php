<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 06.11.18
 * Time: 17:35
 */

class Wp2leads_Background_MapToApi {
    /**
     * Background process to regenerate all images
     *
     * @var WC_Regenerate_Images_Request
     */
    protected static $maptoapi_bg;

    /**
     * Background process to regenerate all images
     *
     * @var WC_Regenerate_Images_Request
     */
    protected static $map_id;

    /**
     * Init function
     */
    public static function init() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp2leads-logger.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/abstract-class-wp2leads-background.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/class-wp2leads-background-maptoapi-request.php';

        self::$maptoapi_bg = new Wp2leads_Background_MapToApi_Request();
    }

    public static function maptoapi_bg($map_id, $data_for_transfer) {
        $map = MapsModel::get($map_id);

        if (empty($map)) {
            return 0;
        }

        self::$maptoapi_bg->set_map_id($map_id);

        $i = 1;
        $count = count($data_for_transfer);

        self::$maptoapi_bg->set_total($count);

        foreach ( $data_for_transfer as $email => $data ) {
            $single_data_for_transfer = array( $email => $data );

            if ($i <= $count) {
                self::$maptoapi_bg->push_to_queue( $single_data_for_transfer );
            }

            $i++;
        }

        self::$maptoapi_bg->save()->dispatch();

        return $count;
    }
}

add_action( 'init', array( 'Wp2leads_Background_MapToApi', 'init' ) );