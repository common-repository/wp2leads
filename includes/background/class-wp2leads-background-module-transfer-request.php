<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 29.05.19
 * Time: 18:20
 */

class Wp2leads_Background_Module_Transfer_Request extends Wp2leads_Background_Process {
    use Wp2leads_Logger;

    /**
     * Initiate new background process.
     */
    public function __construct() {
        $this->action = 'wp2leads_module_transfer';

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
     * Maybe process queue
     *
     * Checks whether data exists within the queue and that
     * the process is not already running.
     */
    public function maybe_handle() {
        // Don't lock up other requests while processing
        session_write_close();

        if ( $this->is_process_running() ) {
            // Background process already running.
            wp_die();
        }

        if ( $this->is_queue_empty() ) {
            // No data to process.
            wp_die();
        }

       // check_ajax_referer( $this->identifier, 'nonce' );
        $this->handle();

        wp_die();
    }

	protected function get_query_args() {
        if ( property_exists( $this, 'query_args' ) ) {
            return $this->query_args;
        }

        return array(
            'action' => $this->identifier,
            'nonce'  => wp_create_nonce( $this->identifier ),
        );
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
                $transfer_data = $this->transfer_data($value);

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

    protected function transfer_data($value) {
        global $wpdb;

        $map_id = $value[0];
        $condition = $value[1];

        $map = MapsModel::get($map_id);

		if (!$map) {
			return true;
		}

        $mapping = unserialize($map->mapping);
        $api = unserialize($map->api);

        if (empty($mapping["comparisons"]) || !is_array($mapping["comparisons"])) {
            $mapping["comparisons"] = array();
        }

        $mapping["comparisons"][] = $condition;

        $results = MapsModel::get_map_query_results($mapping);
        $tags_prefix = ApiHelper::get_map_tags_prefix($map_id);
        $data_for_transfer = ApiHelper::prepareDataForTransfer($api, $results, $tags_prefix);

        if (count($data_for_transfer) > 0) {
            $connector = new Wp2leads_KlicktippConnector();
            $logged_in = $connector->login(get_option('wp2l_klicktipp_username'), get_option('wp2l_klicktipp_password'));

            if ($logged_in) {
                $available_tags = $connector->tag_index();
                $detach_tags = ApiHelper::getDetachTags($map_id);

                $added_subscribers = array();
                $existed_subscribers = array();
                $failed_subscribers = array();
                $last_transferred_time = time();

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
                                    'time' => date('Y-m-d H:i:s', $last_transferred_time)
                                );

                                $failed_id = FailedTransferModel::insert($data);

                                if ($failed_id) {
                                    $failed_subscribers[] = $failed_id;
                                }
                            }
                        }
                    }
                }

                $available_users = count($data_for_transfer);
                $new_subscribers_amount = count($added_subscribers);
                $updated_subscribers_amount = count($existed_subscribers);
                $failed_subscribers_amount = count($failed_subscribers);
                $total_transferred = $new_subscribers_amount + $updated_subscribers_amount;

                $counters = array('unique' => $new_subscribers_amount);

                ApiHelper::setSubscribersCounter($map_id, $counters);

                KlickTippManager::save_statistics(
                    $map_id,
                    $available_users,
                    $new_subscribers_amount,
                    $updated_subscribers_amount,
                    $failed_subscribers,
                    $total_transferred,
                    $last_transferred_time,
                    'instant'
                );
            }
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
        $unique  = md5( microtime() . rand() );
        $prepend = $this->identifier . '_batch_';
        $key = substr( $prepend . $unique, 0, 64 );

        if ( ! empty( $this->data ) ) {
            update_site_option( $key, $this->data );
        }

        $this->data = array();

        return $this;
    }

    protected function complete() {
        parent::complete();
    }
}
