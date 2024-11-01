<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 29.05.19
 * Time: 18:20
 */

class Wp2leads_Background_Cron_Prepare_Request extends Wp2leads_Background_Process {
    use Wp2leads_Logger;

    /**
     * Initiate new background process.
     */
    public function __construct() {
        $this->action = 'wp2leads_cron_prepare';

        // This is needed to prevent timeouts due to threading. See https://core.trac.wordpress.org/ticket/36534.
        if (function_exists('putenv')) {
            @putenv( 'MAGICK_THREAD_LIMIT=1' );  // @codingStandardsIgnoreLine.
        }

        parent::__construct();
    }

    public function set_map_id( $map_id ) {
        $this->map_id = $map_id;
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
                $prepare_data = $this->prepare_data($value);

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

    protected function prepare_data($value) {
        global $wpdb;

        $map_id = $value[0];



        $data_to_prepare = $wpdb->get_row( "SELECT option_value FROM $wpdb->options WHERE option_name = '".$value[5]."'", ARRAY_A );
        $data_to_prepare = unserialize($data_to_prepare['option_value']);

        if (count($data_to_prepare) > 0) {
            $map = MapsModel::get( $map_id );
            $mapping = unserialize($map->mapping);
            $api = unserialize($map->api);
            $results = $data_to_prepare;
            $tags_prefix = ApiHelper::get_map_tags_prefix($map_id);

            if (
                !empty($mapping['dateTime']) && is_array($mapping['dateTime']) &&
                (!empty($value[3]) || !empty($value[4]))
            ) {
                $date_to_compare = array(
                    'fields' => $mapping['dateTime']
                );

                if (!empty($value[3])) {
                    $date_to_compare['date_range']['start'] = date('Y-m-d H:i:s', $value[3]);
                }

                if (!empty($value[4])) {
                    $date_to_compare['date_range']['end'] = date('Y-m-d H:i:s', $value[4]);
                }
            }

            $data_for_transfer = ApiHelper::prepareDataForTransfer($api, $results, $tags_prefix, $date_to_compare);
            $ser_result = serialize($data_for_transfer);

            $option_key = 'wp2lead_cron_map_to_api_prepared__' . $value[0] . '__' . $value[3] . '__' . $value[4] . '__' . $value[1] . '__' . $value[2];

            $wpdb->insert( $wpdb->options, array(
                'option_name' => '_transient_' . $option_key,
                'option_value' => $ser_result,
                'autoload' => 'no'
            ));

            $wpdb->delete( $wpdb->options, array(
                'option_name' => $value[5]
            ) );
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
