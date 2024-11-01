<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 09.10.18
 * Time: 12:40
 */

defined( 'ABSPATH' ) || exit;

class Wp2leads_Background_MapToApi extends Wp2leads_Background_Process {

    use Wp2leads_Logger;

    /**
     * @var string
     */
    protected $action = 'wp2leads_maptoapi';

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
        $message = $this->get_message( $item );

        $this->really_long_running_task();
        $this->log( $message );

        return false;
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
        parent::complete();

        // Show notice to user or perform some other arbitrary task...
    }
}