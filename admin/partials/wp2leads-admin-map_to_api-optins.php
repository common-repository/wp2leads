<?php
/**
 * Optins Section
 *
 * @package Wp2Leads/Partials/MapToAPI
 * @version 1.0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<h3 class="accordion-header strict<?php echo $is_initial_settings_done ? '' : ' disabled'; ?>"><?php _e( 'Opt-In', 'wp2leads' ) ?></h3>

<div class="accordion-body">
    <h3 class="accordion-subheader">
        <?php _e( 'Default Opt-In Process:', 'wp2leads' ) ?>
    </h3>

    <div class="accordion-subbody">
        <div class="api-optins-wrapper">
            <select class="optins-list" class="form-control">
                <?php
                $default_optin_id = !empty($api['default_optin']) ? $api['default_optin'] : '';
				$first_opt = array();
				$i = 0;
                foreach ( $optins as $key => $optin ) { 
				$sp = $connector->subscription_process_get($key);
					if (!$i) {
						$i++;
						if ($sp->pendingurl) $first_opt['confirm'] = $sp->pendingurl;
						if ($sp->thankyouurl) $first_opt['thankyou'] = $sp->thankyouurl;
					}
					
					
					if ($key == $default_optin_id) {
						$first_opt = array();
						if ($sp->pendingurl) $first_opt['confirm'] = $sp->pendingurl;
						if ($sp->thankyouurl) $first_opt['thankyou'] = $sp->thankyouurl;
					}
				?>
                    <option value="<?php echo $key ?>" <?php
							echo $key == $default_optin_id ? 'selected ' : '';
							if ($sp = $connector->subscription_process_get($key)) {
								echo 'data-confirm="'.$sp->pendingurl.'" ';
								echo 'data-thankyou="'.$sp->thankyouurl.'" ';
								echo 'data-default="'.__('Klick Tipp standard link', 'wp2leads').'" ';
							} ?>><?php echo $optin ? $optin : __( 'Default Opt-In Process', 'wp2leads' ); ?></option>
                <?php } ?>
            </select>
			<div class="optin-urls">
				<div class="confirm-url">
					<?php _e('Confirmation redirect URL: ', 'wp2leads'); ?>
					<span> <?php echo empty($first_opt['confirm']) ? __('Klick Tipp standard link', 'wp2leads') : '<a href="'.$first_opt['confirm'].'" target="_blank">'.$first_opt['confirm'].'</a>'; ?></span>
				</div>
				<div class="thankyou-url">
					<?php _e('Thank You redirect URL: ', 'wp2leads'); ?>
					<span> <?php echo empty($first_opt['thankyou']) ? __('Klick Tipp standard link', 'wp2leads') : '<a href="'.$first_opt['thankyou'].'" target="_blank">'.$first_opt['thankyou'].'</a>'; ?></span>
				</div>
				<?php _e('You can change links in double opt in settings in Klick Tipp.', 'wp2leads'); ?><br>
				<?php if (!empty($mapping['form_code']) && (explode('_', $mapping['form_code'])[0] == 'cf')) { ?>
						<strong><?php _e('Contact Form 7 will redirecting after submitting form', 'wp2leads'); ?></strong>
						<?php } ?>
			</div>
        </div>
    </div>

    <div class="optins-conditions-wrapper">
        <h3 class="accordion-subheader"><?php _e( 'Conditions', 'wp2leads' ) ?>:</h3>

        <div class="accordion-subbody">
            <div id="optins-conditions">
                <div class="conditions-list"
                     data-saved-value='<?php echo isset( $api['conditions']['optins'] ) ? json_encode( $api['conditions']['optins'], JSON_FORCE_OBJECT ) : ''; ?>'></div>
                <a href="#" class="button add_new_condition"
                   data-type="optins">+ <?php _e( 'Add Condition', 'wp2leads' ) ?></a>
            </div>
        </div>
    </div>

    <div class="donot-optins-conditions-wrapper">
        <h3 class="accordion-subheader"><?php _e( 'Conditions for Do NOT opt-in/transfer this user', 'wp2leads' ) ?>:</h3>

        <div class="accordion-subbody">
            <div id="donot-optins-conditions">
                <div class="conditions-list"
                     data-saved-value='<?php echo isset( $api['conditions']['donot_optins'] ) ? json_encode( $api['conditions']['donot_optins'], JSON_FORCE_OBJECT ) : ''; ?>'
                ></div>

                <button id="addConditionForDoNotOptin" class="button" data-type="donot-optins">
                    + <?php _e( 'Add Condition', 'wp2leads' ) ?>
                </button>
            </div>
        </div>
    </div>
</div>