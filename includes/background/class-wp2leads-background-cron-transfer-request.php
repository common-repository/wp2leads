<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 29.05.19
 * Time: 18:20
 */

class Wp2leads_Background_Cron_Transfer_Request extends Wp2leads_Background_Process {
    use Wp2leads_Logger;

    /**
     * Initiate new background process.
     */
    public function __construct() {
        $this->action = 'wp2leads_cron_transfer';

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

        $data_for_transfer = $wpdb->get_row( "SELECT option_value FROM $wpdb->options WHERE option_name = '".$value[5]."'", ARRAY_A );
        $data_for_transfer = unserialize($data_for_transfer['option_value']);

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
                    'cron'
                );

                $wpdb->delete( $wpdb->options, array(
                    'option_name' => $value[5]
                ) );
            }

        } else {
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
        $key     = substr( $prepend . $unique, 0, 64 );

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
