<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 29.05.19
 * Time: 18:20
 */

class Wp2leads_Background_Cron_Load_Request extends Wp2leads_Background_Process {
    use Wp2leads_Logger;

    protected $map_id = null;

    protected $from_time = null;

    protected $till_time = null;

    /**
     * Initiate new background process.
     */
    public function __construct() {
        $this->action = 'wp2leads_cron_load';

        // This is needed to prevent timeouts due to threading. See https://core.trac.wordpress.org/ticket/36534.
        if (function_exists('putenv')) {
            @putenv( 'MAGICK_THREAD_LIMIT=1' );  // @codingStandardsIgnoreLine.
        }

        parent::__construct();
    }

    public function set_map_id( $map_id ) {
        $this->map_id = $map_id;
    }

    public function set_from_time( $from_time ) {
        $this->from_time = $from_time;
    }

    public function set_till_time( $till_time ) {
        $this->till_time = $till_time;
    }

    /**
     * Handle.
     */
    protected function handle() {
        $this->lock_process();

        do {
            $batch = $this->get_batch();
            $count = count($batch->data);

            foreach ( $batch->data as $key => $value ) {
                $load_data = $this->load_data($value);

                $task = $this->task( $value );

                if ( false !== $task ) {
                    $batch->data[ $key ] = $task;
                } else {
                    unset( $batch->data[ $key ] );
                }

                if ( $this->batch_limit_exceeded() ) {
                    // Batch limits reached.
                    break;
                }
            }

            // Update or delete current batch.
            if ( ! empty( $batch->data ) ) {
                $this->update( $batch->key, $batch->data );
            } else {
                $this->delete( $batch->key );
            }
        } while ( ! $this->batch_limit_exceeded() && ! $this->is_queue_empty() );

        $this->unlock_process();

        // Start next batch or complete process.
        if ( ! $this->is_queue_empty() ) {
            $this->dispatch();
        } else {
            $this->complete();
        }
    }

    protected function load_data($value) {
        global $wpdb;

        $start = $value[2];
        $end = $value[2] + $value[1] - 1;

        $map = MapsModel::get($value[0]);
        $mapping = unserialize($map->mapping);
        $result = MapsModel::get_map_query_results($mapping, $value[1], $value[2], false, $value[0]);
        $ser_result = serialize($result);


        $option_key = 'wp2lead_cron_map_to_api_results__' . $value[0] . '__' . $value[3] . '__' . $value[4] . '__' . $start . '__' . $end;
        $saved_results_transient = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $option_key . "%';" );

        if (0 === $saved_results_transient) {
            $wpdb->insert( $wpdb->options, array(
                'option_name' => '_transient_' . $option_key,
                'option_value' => $ser_result,
                'autoload' => 'no'
            ));
        }

        return true;
    }

    /**
     * Task
     */
    protected function task( $item ) {
        return false;
    }

    /**
     * Limit each task ran per batch to 1 for image regen.
     */
    protected function batch_limit_exceeded() {
        return true;
    }

    /**
     * Save queue
     */
    public function save() {
        global $wpdb;
        $unique  = md5( microtime() . rand() );
        $prepend = $this->identifier . '_batch_';

        if ( ! empty( $this->data ) ) {
            foreach ($this->data as $data) {
                $batch_key = $prepend . '_' . $data[0] . '__' . $data[3] . '__' . $data[4] . '__' . $data[1] . '__' . $data[2];

                $saved_batch = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $batch_key . "%';" );

                if (0 < $saved_batch) {
                    continue;
                }

                update_site_option( $batch_key, array($data) );
            }
        }

        return $this;
    }

    protected function complete() {
        parent::complete();
    }
}
