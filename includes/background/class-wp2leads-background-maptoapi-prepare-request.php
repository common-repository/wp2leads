<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 29.05.19
 * Time: 18:20
 */

class Wp2leads_Background_Maptoapi_Prepare_Request extends Wp2leads_Background_Process {
    use Wp2leads_Logger;

    protected $map_id = null;

    protected $from_time = null;

    protected $till_time = null;

    /**
     * Initiate new background process.
     */
    public function __construct() {
        $this->action = 'wp2leads_maptoapi_prepare';

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
                $current_map_id = $value[0];
                $map_object = MapsModel::get($current_map_id);

                $prepare_in_progress = BackgroundProcessManager::get_transient($this->identifier . '__' . $current_map_id);

                if (!empty($prepare_in_progress) && !empty($map_object)) {
                    $load_data = $this->prepare_data($value);

                    if (0 === (int) $load_data) {
                        $this->log('Finished Preparartion' . $load_data . ' - ' . $current_map_id);
                        $prepare = $this->transfer_data($value);
                    } else {
                        $this->log('Next iteration Preparartion' . $load_data . ' - ' . $current_map_id);
                    }

                    $task = $this->task( $value );

                    if ( false !== $task ) {
                        $batch->data[ $key ] = $task;
                    } else {
                        unset( $batch->data[ $key ] );
                    }
                } else {
                    $batch->data = array();
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
        $counter = (int) BackgroundProcessManager::get_transient($this->identifier . '__' . $map_id);
        $map = MapsModel::get($map_id);
        $api = unserialize($map->api);
        $mapping = unserialize($map->mapping);
        $start = $value[2];
        $end = $value[2] + $value[1] - 1;
        $loaded_key = 'wp2lead_map_to_api_results_load__' . $map_id . '__' . $start . '__' . $end;

        $prepared_key = 'wp2lead_klicktipp_data_for_transfer__' . $map_id . '__' . $start . '__' . $end;
        $loaded_results = $wpdb->get_results( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE '%". $loaded_key ."%'", ARRAY_A );

        if (count($loaded_results) > 0) {
            $results = unserialize($loaded_results[0]['option_value']);
            $tags_prefix = ApiHelper::get_map_tags_prefix($map_id);
            $date_to_compare = array();

            if (
                !empty($mapping['dateTime']) && is_array($mapping['dateTime']) &&
                (!empty($api['start_date_data']) || !empty($api['end_date_data']))
            ) {
                $date_to_compare = array( 'fields' => $mapping['dateTime'] );

                if (!empty($api['start_date_data'])) $date_to_compare['date_range']['start'] = $api['start_date_data'];

                if (!empty($api['end_date_data'])) $date_to_compare['date_range']['end'] = $api['end_date_data'];
            }

            $data_for_transfer = ApiHelper::prepareDataForTransfer($api, $results, $tags_prefix, $date_to_compare);

            $transfer_count = Wp2leads_Background_MapToApi::maptoapi_bg($map_id, $data_for_transfer);

            $wpdb->delete($wpdb->options, array(
                'option_name' => '_transient_wp2lead_map_to_api_results_load__' . $map_id . '__' . $start . '__' . $end,
            ));

            $counter = $counter - 1;

            if (0 < $counter) {
                BackgroundProcessManager::set_transient($this->identifier . '__' . $map_id, $counter);
            } else {
                BackgroundProcessManager::delete_transient($this->identifier . '__' . $map_id);
            }
        }

        return $counter;
    }

    protected function transfer_data($value) {
        $map_id = $value[0];

        $map_to_api_total = BackgroundProcessManager::get_transient('wp2lead_map_to_api_total');
        unset($map_to_api_total[$map_id]);

        BackgroundProcessManager::set_transient('wp2lead_map_to_api_total', $map_to_api_total);
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
        $prepend = $this->identifier . '_batch_';

        if ( ! empty( $this->data ) ) {
            foreach ($this->data as $data) {
                $batch_key = $prepend . '_' . $data[0] . '__' . $data[1] . '__' . $data[2];

                $saved_batch = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $batch_key . "%';" );

                if (0 < $saved_batch) continue;
                update_site_option( $batch_key, array($data) );
                $count = BackgroundProcessManager::get_transient($this->identifier . '__' . $data[0]);

                if (empty($count)) {
                    $count = 1;
                } else {
                    $count = (int) $count + 1;
                }

                BackgroundProcessManager::set_transient($this->identifier . '__' . $data[0], $count);
            }
        }

        return $this;
    }

    protected function complete() {
        parent::complete();
    }
}
