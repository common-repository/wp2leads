<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 09.10.18
 * Time: 12:40
 */

defined( 'ABSPATH' ) || exit;

class Wp2leads_Background_MapToApi_Request extends Wp2leads_Background_Process {

    use Wp2leads_Logger;

    protected $map_id = null;
    protected $total = null;
    protected $processes = false;

    /**
     * Initiate new background process.
     */
    public function __construct() {
        $this->action = 'wp2leads_maptoapi';

        // This is needed to prevent timeouts due to threading. See https://core.trac.wordpress.org/ticket/36534.
        if (function_exists('putenv')) {
            @putenv( 'MAGICK_THREAD_LIMIT=1' );  // @codingStandardsIgnoreLine.
        }

        parent::__construct();
    }

    public function set_map_id( $map_id ) {
        $this->map_id = $map_id;
    }

    public function set_total( $count ) {
        $this->total = $count;
    }

    public function kill_map_processes( $map_id ) {
        global $wpdb;

        $table  = $wpdb->options;
        $column = 'option_name';


        $maptoapi_bg_in_process = BackgroundProcessManager::get_transient('wp2leads_maptoapi_bg_in_process');

        if ($maptoapi_bg_in_process && !empty($maptoapi_bg_in_process[$map_id])) {
            foreach ($maptoapi_bg_in_process[$map_id] as $batch_key => $batch) {
                $bg_batch_key = $wpdb->esc_like( 'wp_wp2leads_maptoapi_batch_' . $batch_key);

                $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE {$column} LIKE %s", $bg_batch_key ) );
            }
        }

        return $this;
    }

    /**
     * Handle.
     *
     * Pass each queue item to the task handler, while remaining
     * within server memory and time limit constraints.
     */
    protected function handle() {
        $this->lock_process();

        do {
            $batch = $this->get_batch();

            $count = count($batch->data);
            $batch_key = str_replace('wp_wp2leads_maptoapi_batch_', '', $batch->key );

            foreach ( $batch->data as $key => $value ) {
                $current_map_id = null;
                $maptoapi_bg_in_process = BackgroundProcessManager::get_transient('wp2leads_maptoapi_bg_in_process');

                if (!empty($maptoapi_bg_in_process)) {
                    foreach ($maptoapi_bg_in_process as $map_id => $processes) {
                        if (!empty($processes[$batch_key])) {
                            $current_map_id = $map_id;
                            $map_object = MapsModel::get($current_map_id);

                            if (empty($map_object)) {
                                $current_map_id = null;
                            }
                        }
                    }
                }

                if (!empty($current_map_id)) {
                    $this->transfer_data_to_kt($current_map_id, $value, $batch_key, $count);

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

    /**
     * Transfer data to KT in BG
     *
     * @param $map_id
     * @param $data_for_transfer
     * @param $batch_key
     * @param $count
     *
     * @return bool
     */
    protected function transfer_data_to_kt($map_id, $data_for_transfer, $batch_key, $count) {
        if (empty($map_id)) {
            return false;
        }

        $connector = new Wp2leads_KlicktippConnector();
        $logged_in = $connector->login();
        if( $logged_in ) {

            $available_tags = $connector->tag_index();
            $detach_tags = ApiHelper::getDetachTags($map_id);

            $added_subscribers = array();
            $existed_subscribers = array();
            $failed_subscribers = array();

            foreach ($data_for_transfer as $email => $data) {
                $tags = ApiHelper::getTagsIds($data['tags'], $available_tags, $connector);

                $result = KlickTippManager::transfer_subscriber_to_kt($map_id, $connector, $email, $data, $tags, $detach_tags);

                if ( $result ) {
                    if ($result['added_subscriber']) {
                        $added_subscribers[] = $result['subscriber'];
                    } else if ($result['existed_subscriber']) {
                        $existed_subscribers[] = $result['subscriber'];
                    } else if ($result['failed_subscriber']) {
                        $failed_subscriber = $result['failed_subscriber'];

                        if (is_array($failed_subscriber)) {
                            $data = array(
                                'map_id' => $map_id,
                                'user_email' => $failed_subscriber['email'],
                                'user_data' => serialize($failed_subscriber['data']),
                                'user_status'  => 'failed',
                                'time' => date('Y-m-d H:i:s', time())
                            );

                            $failed_id = FailedTransferModel::insert($data);

                            if ($failed_id) {
                                $failed_subscribers[] = $failed_id;
                            }
                        }
                    }
                }
            }

            $new_subscribers_amount = count($added_subscribers);
            $updated_subscribers_amount = count($existed_subscribers);
            $failed_subscribers_amount = count($failed_subscribers);
            $maptoapi_bg_in_process = BackgroundProcessManager::get_transient('wp2leads_maptoapi_bg_in_process');

            if ( $maptoapi_bg_in_process ) {
                foreach ($maptoapi_bg_in_process as $mi => $processes) {
                    if ($mi === $map_id) {
                        foreach ($processes as $pk => $process) {
                            if ($pk === $batch_key) {
                                $total = $maptoapi_bg_in_process[$mi][$pk]['total'];
                                $done = $total - $count + 1;

                                $new = $maptoapi_bg_in_process[$mi][$pk]['new'] + $new_subscribers_amount;
                                $updated = $maptoapi_bg_in_process[$mi][$pk]['updated'] + $updated_subscribers_amount;

                                $maptoapi_bg_in_process[$mi][$pk]['done'] = $total - $count + 1;
                                $maptoapi_bg_in_process[$mi][$pk]['count'] = $count - 1;
                                $maptoapi_bg_in_process[$mi][$pk]['new'] = $new;
                                $maptoapi_bg_in_process[$mi][$pk]['updated'] = $updated;

                                if ($failed_subscribers_amount) {
                                    $maptoapi_bg_in_process[$mi][$pk]['failed'][] = $failed_subscribers;
                                }
                            }
                        }
                    }
                }

                BackgroundProcessManager::set_transient('wp2leads_maptoapi_bg_in_process', $maptoapi_bg_in_process);
            }
        }

        return false;
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
    protected function task( $item ) {
        return false;
    }

    /**
     * Save queue
     *
     * @return $this
     */
    public function save() {
        $unique  = md5( microtime() . rand() );
        $prepend = $this->identifier . '_batch_';
        $key = substr( $prepend . $unique, 0, 64 );

        $wp2leads_maptoapi_bg_in_process = BackgroundProcessManager::get_transient('wp2leads_maptoapi_bg_in_process');

        $new_data = array(
            'total'     =>  $this->total,
            'done'      =>  0,
            'count'     =>  0,
            'new'       =>  0,
            'updated'   =>  0,
            'failed'    =>  array(),
            'time_created'   =>  time(),
        );

        if (!$wp2leads_maptoapi_bg_in_process) {
            BackgroundProcessManager::set_transient('wp2leads_maptoapi_bg_in_process', array(
                $this->map_id => array (
                    $unique => $new_data
                )
            ));
        } else {

            if (!empty($wp2leads_maptoapi_bg_in_process[$this->map_id])) {
                $wp2leads_maptoapi_bg_in_process[$this->map_id][$unique] = $new_data;
            } else {
                $wp2leads_maptoapi_bg_in_process[$this->map_id] = array(
                    $unique => $new_data
                );
            }

            BackgroundProcessManager::set_transient('wp2leads_maptoapi_bg_in_process', $wp2leads_maptoapi_bg_in_process);
        }

        if ( ! empty( $this->data ) ) {
            update_site_option( $key, $this->data );
        }

        return $this;
    }

    /**
     * Limit each task ran per batch to 1 for image regen.
     *
     * @return bool
     */
    protected function batch_limit_exceeded() {
        return true;
    }

    /**
     * Complete
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete() {
        $transient = get_transient('wp2leads_maptoapi_bg_in_process');

        if ($transient && count($transient) > 0) {
            foreach ($transient as $map_id => $map_processes) {
                $bg_total = 0;
                $bg_done = 0;
                $bg_count = 0;
                $bg_new = 0;
                $bg_updated = 0;
                $bg_failed = array();

                foreach ($map_processes as $transfer_data) {
                    $bg_total += $transfer_data['total'];
                    $bg_done += $transfer_data['done'];
                    $bg_count += $transfer_data['count'];
                    $bg_new += $transfer_data['new'];
                    $bg_updated += $transfer_data['updated'];

                    if (!empty($transfer_data['failed'])) {
                        $bg_failed = array_merge($bg_failed, $transfer_data['failed']);
                    }

                }

                $total_transferred = $bg_new + $bg_updated;

                KlickTippManager::save_statistics(
                    $map_id,
                    $bg_total,
                    $bg_new,
                    $bg_updated,
                    $bg_failed,
                    $total_transferred,
                    time(),
                    'manually'
                );
            }
        }

        delete_transient('wp2leads_maptoapi_bg_in_process');

        parent::complete();
    }
}
