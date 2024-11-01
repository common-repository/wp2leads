<?php
/**
 * No KT Credentials Section
 *
 * @package Wp2Leads/Partials/MapToAPI
 * @version 1.0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$kt_username = get_option('wp2l_klicktipp_username');
$kt_password = get_option('wp2l_klicktipp_password');
?>

<h3 class="accordion-header active"><?php _e( 'KlickTipp credentials', 'wp2leads' ) ?></h3>

<div class="accordion-body accordion-body-visible api-processing-holder">

    <div class="accordion-subbody">
        <p style="margin-top:5px;margin-bottom:15px">
            <?php echo $logged_in_error['message']; ?>
        </p>

        <div id="tagPrefixesContainer">
            <div id="globalTagPrefixContainer">
                <p class="globalTagPrefix__holder" style="margin-top:0;">
                    <label for="klicktippUsername">
                        <?php echo __( 'Username', 'wp2leads' ); ?> (*)
                    </label>

                    <input id="klicktippUsername"
                           type="text"
                           value="<?php echo $kt_username ? $kt_username : ''; ?>"
                           class="form-control form-control-medium">
                </p>
            </div>

            <div id="mapTagPrefixContainer">
                <p id="mapTagPrefix__holder" style="margin-top:0;">
                    <label for="klicktippPassword">
                        <?php echo __( 'Password', 'wp2leads' ); ?> (*)
                    </label>

                    <input id="klicktippPassword"
                           type="password"
                           value="<?php echo $kt_password ? $kt_password : ''; ?>"
                           class="form-control form-control-medium">
                </p>
            </div>
        </div>

        <button id="klicktippSubmit" class="button button-primary"><?php echo __( 'Submit', 'wp2leads' ); ?></button>
    </div>

    <div class="api-spinner-holder">
        <div class="api-spinner"></div>
    </div>
</div>
