<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 29.05.19
 * Time: 18:20
 */

class Wp2leads_Background_Maptoapi_Load_Request extends Wp2leads_Background_Process {
    use Wp2leads_Logger;

    protected $map_id = null;

    protected $from_time = null;

    protected $till_time = null;

    /**
     * Initiate new background process.
     */
    public function __construct() {
        $this->action = 'wp2leads_maptoapi_load';

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

                $load_in_progress = BackgroundProcessManager::get_transient($this->identifier . '__' . $current_map_id);

                if (!empty($load_in_progress) && !empty($map_object)) {
                    $load_data = $this->load_data($value);

                    if (0 === (int) $load_data) {
                        $this->log( 'Finished Loading: ' . $load_data . ' - ' . $current_map_id );
                        $prepare = $this->prepare_data($value);
                    } else {
                        $this->log('Next iteration Loading' . $load_data . ' - ' . $current_map_id);
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

    protected function load_data($value) {
        global $wpdb;

        $map_id = $value[0];
        $start = $value[2];
        $end = $value[2] + $value[1] - 1;
        $map = MapsModel::get($value[0]);
        $mapping = unserialize($map->mapping);
        $result = MapsModel::get_map_query_results($mapping, $value[1], $value[2], false, $map_id);
        $counter = (int) BackgroundProcessManager::get_transient($this->identifier . '__' . $map_id);
        $option_key = 'wp2lead_map_to_api_results_load__' . $map_id . '__' . $start . '__' . $end;
        $saved_results_transient = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%" . $option_key . "%';" );

        if (0 === $saved_results_transient) {
            BackgroundProcessManager::set_transient($option_key, $result);
            $counter = $counter - 1;

            if (0 < $counter) {
                BackgroundProcessManager::set_transient($this->identifier . '__' . $map_id, $counter);
            } else {
                BackgroundProcessManager::delete_transient($this->identifier . '__' . $map_id);
            }
        }

        return $counter;
    }

    protected function prepare_data($value) {
        $map_id = $value[0];
        Wp2leads_Background_Maptoapi_Prepare::run($map_id, true);
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
