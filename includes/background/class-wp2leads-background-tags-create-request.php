<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 29.05.19
 * Time: 18:20
 */

class Wp2leads_Background_Tags_Create_Request extends Wp2leads_Background_Process {
    use Wp2leads_Logger;

    protected $map_id = null;
    protected $tags_set_id = null;

    /**
     * Initiate new background process.
     */
    public function __construct() {
        $this->action = 'wp2leads_tags_create';

        // This is needed to prevent timeouts due to threading. See https://core.trac.wordpress.org/ticket/36534.
        if (function_exists('putenv')) {
            @putenv( 'MAGICK_THREAD_LIMIT=1' );  // @codingStandardsIgnoreLine.
        }

        parent::__construct();
    }

    public function set_map_id( $map_id ) {
        $this->map_id = $map_id;
    }

    public function set_tags_set_id( $tags_set_id ) {
        $this->tags_set_id = $tags_set_id;
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
        $klick_tip_connector = new Wp2leads_KlicktippConnector();
        $login_response = $klick_tip_connector->login();

        if ($login_response) {
            $result = $klick_tip_connector->tag_create(trim($value));
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
        $unique  = md5( $this->map_id . $this->tags_set_id );
        $prepend = $this->identifier . '_batch_';

        if ( ! empty( $this->data ) ) {
            if ( ! empty( $this->data ) ) {
                update_site_option( $prepend . $unique, $this->data );
            }
        }

        return $this;
    }

    protected function complete() {
        parent::complete();
    }
}
