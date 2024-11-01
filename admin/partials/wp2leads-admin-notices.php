<?php
$wp2l_is_dev_env = defined( 'WP2LEADS_DEV_ENV' ) && WP2LEADS_DEV_ENV;
$wp2l_activation_in_progress = get_transient( 'wp2l_activation_in_progress' );
$wp2l_no_server_response = get_transient('wp2l_no_server_response');
$wp2l_no_map_server_response = get_transient('wp2l_no_map_server_response');
$wp2l_last_paid_day_message = get_transient('wp2l_last_paid_day_message');
$wp2l_payment_issue_message = get_transient('wp2l_payment_issue_message');
$wp2l_payment_missed_message = get_transient('wp2l_payment_missed_message');
$wp2l_plugin_version_status = get_transient('wp2l_plugin_version_status');
$wp2l_is_cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;

$timezone_string = get_option('timezone_string');

if ($wp2l_is_dev_env) {
    ?>
    <div class="notice notice-warning">
        <p>
            <strong><?php _e('WP2Leads', 'wp2leads') ?> DEV env:</strong>  version <strong><?php echo WP2LEADS_VERSION ?></strong>

            <?php
            if (defined( 'WP2LEADS_BRANCH' ) && WP2LEADS_BRANCH) {
                ?>
                current branch <strong><?php echo WP2LEADS_BRANCH ?></strong>
                <?php
            }
            ?>
        </p>
    </div>
    <?php
}

$klicktipp_username = get_option('wp2l_klicktipp_username');
$klicktipp_password = get_option('wp2l_klicktipp_password');

if (empty($klicktipp_username) || empty($klicktipp_password)) {
    ?>
    <div class="notice notice-warning">
        <p>
            <strong><?php _e('WP2Leads', 'wp2leads') ?></strong>
            <?php _e( 'No Klick Tipp yet? Join  <a href="https://www.klick-tipp.com/bestellen/15194" target="_blank">here</a>.', 'wp2leads' ) ?>
        </p>
    </div>
    <?php
}

$wp2leads_upgrade_kt_package = get_transient('wp2leads_upgrade_kt_package');

if (!empty($wp2leads_upgrade_kt_package)) {
    ?>
    <div class="notice notice-error">
        <p>
            <strong><?php _e('WP2Leads', 'wp2leads') ?></strong>
            <?php _e( 'You are on Klick Tipp Standard package with no API access! To connect please upgrade at least to Klick Tipp Premium.', 'wp2leads' ) ?>
            <?php _e( 'To upgrade click <a href="https://www.klick-tipp.com/15194" target="_blank">here</a>.', 'wp2leads' ) ?>
        </p>
    </div>
    <?php
}

if ($wp2l_is_cron_disabled) {
    ?>
    <div class="notice notice-error">
        <p>
            <strong><?php _e('WP2Leads', 'wp2leads') ?></strong>

            <?php _e('You have WP Cron disabled on your site, background and cron transferring could not be run. Please, remove <strong>"DISABLE_WP_CRON"</strong> constant in your <strong>wp-config.php</strong> file, or set it to <strong>false</strong>', 'wp2leads') ?>
        </p>
    </div>
    <?php
}

if (false !== $wp2l_plugin_version_status) {
    if (-1 === (int) $wp2l_plugin_version_status) {
        ?>
        <div class="notice notice-error">
            <p>
                <strong><?php _e('WP2Leads', 'wp2leads') ?></strong>:
                <?php
                echo sprintf (
                    __('Your %s Version is outdated and stopped working! Please update the plugin: <a href="plugins.php?plugin_status=upgrade">Go to the update.</a>', 'wp2leads'),
                    WP2LEADS_VERSION
                );
                ?>
            </p>
        </div>
        <?php
    } elseif (0 === (int) $wp2l_plugin_version_status) {
        ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php _e('WP2Leads', 'wp2leads') ?></strong>:
                <?php
                echo sprintf (
                    __('Your %s Version nearly outdated and will stopped working with the release of the next version! Please update the plugin: <a href="plugins.php?plugin_status=upgrade">Go to the update.</a>', 'wp2leads'),
                    WP2LEADS_VERSION
                );
                ?>
            </p>
        </div>
        <?php
    }
}

if ($wp2l_activation_in_progress) {
    ?>
    <div class="notice notice-warning wp2lead-notice notice-can-disable">
        <p>
            <strong><?php _e('WP2Leads', 'wp2leads') ?></strong>:
            <?php _e('Your activation is in progress. You need to Complete this to securely save your license data.', 'wp2leads') ?>
            <a href="?page=wp2l-admin&tab=settings"><?php _e('Open settings tab', 'wp2leads') ?></a>
        </p>
    </div>
    <?php
}

if ($wp2l_payment_missed_message) {
    ?>
    <div id="payment_missed_message" class="notice notice-warning wp2lead-notice notice-can-disable">
        <p>
            <strong><?php _e('WP2Leads', 'wp2leads') ?></strong>:
            <?php _e('The transfer of contacts stopped, because of an issue with payment. Please check your emails from support@digistore24.com!', 'wp2leads') ?>
            <button class="notice-disable"><?php _e('Dismiss this notice.', 'wp2leads') ?></button>
        </p>
    </div>
    <?php
}

if ($wp2l_payment_issue_message) {
    ?>
    <div id="payment_issue_message" class="notice notice-warning wp2lead-notice notice-can-disable">
        <p>
            <strong><?php _e('WP2Leads', 'wp2leads') ?></strong>:
            <?php _e('The transfer of contacts will stop soon, because of an issue with payment. Please check your emails from support@digistore24.com!', 'wp2leads') ?>
            <button class="notice-disable"><?php _e('Dismiss this notice.', 'wp2leads') ?></button>
        </p>
    </div>
    <?php
}


if (empty($timezone_string)) {
    ?>
    <div class="notice notice-warning">
        <p>
            <strong><?php _e('WP2Leads', 'wp2leads') ?></strong>:
            <?php _e('Manual timezone offset selected.', 'wp2leads') ?>
            <?php _e('We recommend you to use timezone by searching for your city to always have the right time e.g. during winter, summer time.', 'wp2leads') ?>
            <?php _e('You can change timezone settings <a href="options-general.php#timezone_string" target="_blank">here.</a>', 'wp2leads') ?>
        </p>
    </div>
    <?php
}


if ($wp2l_no_server_response) {
    ?>
    <div class="notice notice-error">
        <p>
            <strong><?php _e('WP2Leads', 'wp2leads') ?></strong>:
            <?php _e('In 24 hours your license will be removed completely (no server response).', 'wp2leads') ?>
            <strong><?php _e('Possible reason', 'wp2leads') ?>:</strong> <?php _e('Your web hosting is blocking the connection to our server so we can not load license and catalog items. Please write your web hosting and let them add our ip address 116.202.216.211 to the server whitelist. Sorry for the trouble.', 'wp2leads') ?>
        </p>
    </div>
    <?php
}


if ($wp2l_no_map_server_response) {
    ?>
    <div class="notice notice-error">
        <p>
            <strong><?php _e('WP2LEADS', 'wp2leads') ?></strong>:
            <?php _e('No response from map server', 'wp2leads') ?>
            <strong><?php _e('Possible reason', 'wp2leads') ?>:</strong> <?php _e('Your web hosting is blocking the connection to our server so we can not load catalog items. Please write your web hosting and let them add the full URL https://maps.wp2leads.com/server/maps.php to the server whitelist. Sorry for the trouble.', 'wp2leads') ?>
        </p>
    </div>
    <?php
}

$wp2l_license = get_option('wp2l_license');

if ('ktcc' === $wp2l_license["version"]) {
    $message = '';
    $set_timer = false;

    if (
        empty($wp2l_license["license_activated_at"]) ||
        empty($wp2l_license["site_activated_at"]) ||
        empty($wp2l_license["multiplicator_validation_status"]) ||
        empty($wp2l_license["imprint_validation_status"])
    ) {
        $message .= ' <strong>';
        $message .= __('Your Professional license was deactivated because of activation issues.', 'wp2leads');
        $message .= '</strong>';
    } elseif ('unapproved' === $wp2l_license["multiplicator_validation_status"]) {
        $message .= ' <strong>';
        $message .= __('Your Professional license deactivated because your link to Klick-Tipp Consultant profile page or marketing agency home page was rejected.', 'wp2leads');
        $message .= '</strong>';
    } elseif ('unapproved' === $wp2l_license["imprint_validation_status"]) {
        $message .= ' <strong>';
        $message .= __('Your Professional license deactivated because your link to Imprint page was rejected.', 'wp2leads');
        $message .= '</strong>';
    } else {
        $set_timer = true;
        $first_activation = $wp2l_license["license_activated_at"];

        if (empty($wp2l_license["multiplicator_validation_link"]) || empty($wp2l_license["imprint_validation_link"])) {
            if (empty($wp2l_license["multiplicator_validation_link"]) && empty($wp2l_license["imprint_validation_link"])) {
                $message .= ' ' . __('Provide us with correct link to Klick-Tipp Consultant profile page or marketing agency home page and link to Imprint page.', 'wp2leads');
            } else {
                if (empty($wp2l_license["multiplicator_validation_link"])) {
                    $message .= ' '. __('Provide us with correct link to Klick-Tipp Consultant profile page or marketing agency home page.', 'wp2leads');
                }

                if (empty($wp2l_license["imprint_validation_link"])) {
                    $first_activation = $wp2l_license["site_activated_at"];
                    $message .= ' '. __('Provide us with correct link to Imprint page.', 'wp2leads');
                }
            }
        } elseif (
                'pending' === $wp2l_license["imprint_validation_status"]
        ) {
            $message .= ' ' . __('Your link to link to Imprint page still not approved.', 'wp2leads');
        } elseif (
                'pending' === $wp2l_license["multiplicator_validation_status"]
        ) {
            $message .= ' ' . __('Your link to Klick-Tipp Consultant profile page or marketing agency home page still not approved.', 'wp2leads');
        }
    }

    if (!empty($message)) {
        if ($set_timer) {
            $now = time();
            $period = Wp2leads_License::$multiplicator_free_period;
            $expire_period = $first_activation + ($period * 24 * 60 * 60);
            $expire_period_date = date('Y-m-d H:i', $expire_period);

            if ($now < $expire_period) {
                $message .= ' <strong>';
                $message .= sprintf( __('You can use Professional license until %s.', 'wp2leads'), $expire_period_date );
                $message .= '</strong>';
            } else {
                $message .= ' <strong>';
                $message .= sprintf( __('Your license was deactivated.', 'wp2leads') );
                $message .= '</strong>';
            }
        }

        $settings_url = '<a href="'.get_admin_url('/').'/admin.php?page=wp2l-admin&tab=settings">'. __('here', 'wp2leads') .'</a>';
        ?>
        <div class="notice notice-error">
            <p>
                <strong><?php _e('WP2Leads', 'wp2leads') ?></strong>: <?php _e('You have an issue with your Multiplicator license.', 'wp2leads') ?>
                <?php echo $message; ?>
                <?php echo sprintf(__('Check license details %s.', 'wp2leads'), $settings_url); ?>
            </p>
        </div>
        <?php
    }
}
?>
