<?php
/**
 * Created by PhpStorm.
 * Date: 2/4/18
 * Time: 8:37 AM
 */

$wp2l_license = get_option('wp2l_license');
$wp2l_activation_in_progress = get_transient( 'wp2l_activation_in_progress' );
?>

<h2><?php _e('Settings', 'wp2leads') ?></h2>

<hr>

<form>
    <table id="table-setting">
        <thead>
            <tr>
                <th colspan="2"><?php _e('Information about license', 'wp2leads') ?></th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td><?php _e('Email', 'wp2leads') ?></td>

                <td>
                    <input type="text" style="width: 100%;" id="wp2l-license-email" name="wp2l-license-email" class="form-control" value="<?php echo $wp2l_license['email']; ?>">
                </td>
            </tr>

            <tr>
                <td><?php _e('License key', 'wp2leads') ?></td>

                <td>
                    <input type="text" style="width: 100%;" id="wp2l-license-key" name="wp2l-license-key" class="form-control"
                           value="<?php echo $wp2l_activation_in_progress ? $wp2l_license['key'] : $wp2l_license['secured_key']; ?>"
                    >
                </td>
            </tr>

            <?php
            if ($wp2l_activation_in_progress && 'ktcc' === $wp2l_license["version"]) {
                ?>

                <tr>
                    <td><?php _e('Multiplicator License info', 'wp2leads') ?></td>

                    <td>
                        <p style="margin: 0 0 5px 0;"><?php _e('Link to Klick-Tipp Consultant profile page (<a href="https://www.klick-tipp.com/consultants/tobias-b.-conrad" target="_blank">see example</a>) <strong>OR</strong> marketing agency home page.', 'wp2leads'); ?></p>

                        <input type="text" style="width: 100%;" id="wp2l-license-ktcc-url" name="wp2l-license-ktcc-url" class="form-control"
                               value="<?php echo !empty($wp2l_license['multiplicator_validation_link']) ? $wp2l_license['multiplicator_validation_link'] : ''; ?>"
                            <?php echo !empty($wp2l_license['multiplicator_validation_link']) ? 'readonly' : ''; ?>
                        >

                        <p style="margin: 10px 0 5px 0;"><?php _e('Link to Imprint page for current site', 'wp2leads'); ?></p>

                        <input type="text" style="width: 100%;" id="wp2l-license-imprint-url" name="wp2l-license-imprint-url" class="form-control"
                               value="<?php echo !empty($wp2l_license['imprint_validation_link']) ? $wp2l_license['imprint_validation_link'] : ''; ?>"
                            <?php echo !empty($wp2l_license['imprint_validation_link']) ? 'readonly' : ''; ?>
                        >
                    </td>
                </tr>
                <?php
                if (empty($wp2l_license['multiplicator_validation_link']) || empty($wp2l_license['imprint_validation_link'])) {
                    ?>
                    <tr>
                        <td></td>

                        <td>
                            <p style="margin: 0;">
                                <?php _e('You activated a Multiplicator License, which allows you to use the license for all sites you are the owner of.', 'wp2leads'); ?>
                                <br>
                                <?php _e('Therefore during the next 7 days,', 'wp2leads'); ?>
                                <br>
                                <?php
                                if (empty($wp2l_license['multiplicator_validation_link'])) {
                                    ?>
                                    <?php _e('Please provide us with a link to the Klick-Tipp Certified-Consultant Marketplace or the agency home page.', 'wp2leads'); ?><br>
                                    <?php
                                }

                                if (empty($wp2l_license['imprint_validation_link'])) {
                                    ?>
                                    <?php _e('Please provide us with a link to the imprint page for each site.', 'wp2leads'); ?>
                                    <br>
                                    <?php
                                }
                                ?>
                            </p>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>

            <?php
            if ($wp2l_activation_in_progress) {
                if ('ktcc' === $wp2l_license["version"]) {

                } else {
                    $site_list = Wp2leads_License::get_license_list();

                    if ($site_list) {
                        ?>
                        <tr>
                            <td>List of sites</td>

                            <td>
                                <?php
                                if (is_array($site_list)) {
                                    ?>
                                    <table>
                                        <tr>
                                            <td colspan="3">
                                                <p><?php echo __('Total licenses', 'wp2leads') ?>: <strong><?php echo  Wp2leads_License::count_licenses(); ?></strong></p>
                                                <p><?php echo __('Available licenses', 'wp2leads') ?>: <strong><?php echo  Wp2leads_License::count_licenses(false); ?></strong></p>
                                            </td>
                                        </tr>
                                        <?php
                                        foreach ($site_list as $site) {
                                            if ($site['site_url'] === Wp2leads_License::get_current_site()) {
                                                $status = __('Current', 'wp2leads');
                                                $current = true;
                                            } else {
                                                $status = $site['status'] === '1' ? __('Active', 'wp2leads') : __('Disabled', 'wp2leads');
                                                $current = false;
                                            }
                                            ?>
                                            <tr>
                                                <td style="padding: 5px;"><strong><?php echo $site['site_url'] ?></strong></td>
                                                <td style="padding: 5px;"><?php echo $status ?></td>
                                                <td style="padding: 5px;">
                                                    <?php
                                                    if (!$current) {
                                                        ?>
                                                        <button class="button wp2lRemove" data-site="<?php echo $site['site_url'] ?>" type="button"><?php _e( 'Remove', 'wp2leads' ) ?></button>
                                                        <?php
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </table>
                                    <?php
                                } else {
                                    echo $site_list;
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
            }
            ?>

            <tr>
                <td></td>
                <td>
                    <?php
                    if ($wp2l_activation_in_progress) {
                        ?>
                        <button id="wp2lActivate" class="button button-primary" data-action="updation" type="button"><?php _e('Update', 'wp2leads') ?></button>
                        <?php
                    } elseif (!empty($wp2l_license['email']) && !empty($wp2l_license['secured_key'])) {
                        ?>
                        <p style="margin-top:0;">
                            <?php _e('In order to manage your licenses, please input your correct license key.', 'wp2leads') ?>
                        </p>
                        <button id="wp2lActivate" class="button button-primary" data-action="login" type="button"><?php _e('Manage licenses', 'wp2leads') ?></button>
                        <button id="wp2lGetKey" class="button" data-action="get_key" type="button"><?php _e('Get license key to your email', 'wp2leads') ?></button>
                        <?php
                    } else {
                        ?>
                        <button id="wp2lActivate" class="button button-primary" data-action="activation" type="button"><?php _e('Activate', 'wp2leads') ?></button>
                        <?php
                    }

                    if (!$wp2l_activation_in_progress) {
                        if (!empty($wp2l_license['email']) && !empty($wp2l_license['secured_key'])) {
                            ?>
                            <button id="wp2lDectivate" class="button button-primary" data-action="deactivation" type="button"><?php _e('Deactivate', 'wp2leads') ?></button>
                            <?php
                        }
                    }

                    ?>
                    <?php
                    if ($wp2l_activation_in_progress) {
                        ?>
                        <button class="button button-danger wp2lComplete" type="button"><?php _e('Close manage licenses', 'wp2leads') ?></button>
                        <?php
                    }
                    ?>
                </td>
            </tr>

            <!-- Multiplicator data if activation not in progress -->
            <?php
            if ('ktcc' === $wp2l_license["version"] && !$wp2l_activation_in_progress) {
                ?>
                <tr>
                    <td><?php _e('Multiplicator License info', 'wp2leads') ?></td>

                    <td>
                        <?php
                        if (empty($wp2l_license['multiplicator_validation_link'])) {
                            ?>
                            <p style="margin: 0 0 5px 0;">
                                <?php _e('Link to Klick-Tipp Consultant profile page (<a href="https://www.klick-tipp.com/consultants/tobias-b.-conrad" target="_blank">see example</a>) <strong>OR</strong> marketing agency home page.', 'wp2leads'); ?>
                            </p>

                            <input type="text" style="width: 100%;" id="wp2l-license-ktcc-url" name="wp2l-license-ktcc-url" class="form-control"
                                   value="<?php echo !empty($wp2l_license['multiplicator_validation_link']) ? $wp2l_license['multiplicator_validation_link'] : ''; ?>"
                                <?php echo !empty($wp2l_license['multiplicator_validation_link']) ? 'readonly' : ''; ?>
                            >
                            <?php
                        } else {
                            $icon = '<span style="color:#858585" title="'.__('Link is waiting for validation', 'wp2leads').'" class="dashicons dashicons-marker"></span>';

                            if (!empty($wp2l_license["multiplicator_validation_status"])) {
                                if ('approved' === $wp2l_license["multiplicator_validation_status"]) {
                                    $icon = '<span style="color:#00ac56" title="'.__('License validated', 'wp2leads').'" class="dashicons dashicons-yes-alt"></span>';
                                } elseif ('unapproved' === $wp2l_license["multiplicator_validation_status"]) {
                                    $icon = '<span style="color:#ac1700" title="'.__('License rejected', 'wp2leads').'" class="dashicons dashicons-yes-alt"></span>';
                                }
                            }
                            ?>
                            <p style="margin: 10px 0 5px 0;">
                                <?php echo $icon; ?>
                                <?php _e('Link to Klick-Tipp Consultant profile page <strong>OR</strong> marketing agency home page', 'wp2leads'); ?>:

                                <a href="<?php echo StatisticsManager::get_clickable_link($wp2l_license['multiplicator_validation_link']); ?>" target="_blank">
                                    <?php echo $wp2l_license['multiplicator_validation_link']; ?>
                                </a>
                            </p>

                            <input type="hidden" id="wp2l-license-ktcc-url" name="wp2l-license-ktcc-url" value="<?php echo $wp2l_license['multiplicator_validation_link']; ?>">
                            <?php
                        }
                        if (empty($wp2l_license['imprint_validation_link'])) {
                            ?>
                            <p style="margin: 10px 0 5px 0;"><?php _e('Link to Imprint page for current site', 'wp2leads'); ?></p>

                            <input type="text" style="width: 100%;" id="wp2l-license-imprint-url" name="wp2l-license-imprint-url" class="form-control"
                                   value="<?php echo !empty($wp2l_license['imprint_validation_link']) ? $wp2l_license['imprint_validation_link'] : ''; ?>"
                                <?php echo !empty($wp2l_license['imprint_validation_link']) ? 'readonly' : ''; ?>
                            >
                            <?php
                        } else {
                            $icon = '<span style="color:#858585" title="'.__('Site is waiting for validation', 'wp2leads').'" class="dashicons dashicons-marker"></span>';

                            if (!empty($wp2l_license["imprint_validation_status"])) {
                                if ('approved' === $wp2l_license["imprint_validation_status"]) {
                                    $icon = '<span style="color:#00ac56" title="'.__('Site validated', 'wp2leads').'" class="dashicons dashicons-yes-alt"></span>';
                                } elseif ('unapproved' === $wp2l_license["imprint_validation_status"]) {
                                    $icon = '<span style="color:#ac1700" title="'.__('Site rejected', 'wp2leads').'" class="dashicons dashicons-yes-alt"></span>';
                                }
                            }
                            ?>
                            <p style="margin: 10px 0 5px 0;">
                                <?php echo $icon; ?>
                                <?php _e('Link to Imprint page', 'wp2leads'); ?>:
                                <a href="<?php echo StatisticsManager::get_clickable_link($wp2l_license['imprint_validation_link']); ?>" target="_blank">
                                    <?php echo $wp2l_license['imprint_validation_link']; ?>
                                </a>
                            </p>

                            <input type="hidden" id="wp2l-license-imprint-url" name="wp2l-license-imprint-url" value="<?php echo $wp2l_license['imprint_validation_link']; ?>">
                            <?php
                        }

                        if (
                        (!empty($wp2l_license['multiplicator_validation_link']) && 'unapproved' === $wp2l_license["multiplicator_validation_status"]) ||
                        (!empty($wp2l_license['imprint_validation_link']) && 'unapproved' === $wp2l_license["imprint_validation_status"])
                        ) {
                            ?>
                            <p>
                                <?php _e('Please write to <a href="mailto:support@saleswonder.biz">Support@saleswonder.biz</a> if you think your license/site should be valid', 'wp2leads'); ?>
                            </p>
                            <?php
                        }
                        ?>


                    </td>
                </tr>

                <tr>
                    <td></td>

                    <td>
                        <?php
                        if (empty($wp2l_license['multiplicator_validation_link']) || empty($wp2l_license['imprint_validation_link'])) {
                            $first_activation = $wp2l_license["license_activated_at"];

                            if (empty($wp2l_license["imprint_validation_link"])) {
                                $first_activation = $wp2l_license["site_activated_at"];
                            }

                            $now = time();
                            $period = Wp2leads_License::$multiplicator_free_period;
                            $expire_period = $first_activation + ($period * 24 * 60 * 60);
                            $expire_period_date = date('Y-m-d H:i', $expire_period);
                            ?>
                            <p style="margin: 0;">
                                <?php _e('You activated a Multiplicator License, which allows you to use the license for all sites you are the owner of.', 'wp2leads'); ?>
                                <br>
                                <?php
                                if ($now < $expire_period) {
                                    echo sprintf(__('Therefore until <strong>%s</strong>:', 'wp2leads'), $expire_period_date);
                                } else {
                                    echo sprintf(__('To use your Professional version, you need to:', 'wp2leads'));
                                }

                                ?>
                                <br>
                                <?php
                                if (empty($wp2l_license['multiplicator_validation_link'])) {
                                    ?>
                                    <?php _e('Please provide us with a link to the Klick-Tipp Certified-Consultant Marketplace or the agency home page.', 'wp2leads'); ?><br>
                                    <?php
                                }

                                if (empty($wp2l_license['imprint_validation_link'])) {
                                    ?>
                                    <?php _e('Please provide us with a link to the imprint page for each site.', 'wp2leads'); ?>
                                    <br>
                                    <?php
                                }
                                ?>
                            </p>
                            <?php
                        }
                        ?>
                    </td>
                </tr>

                <tr>
                    <td></td>
                    <td>
                        <?php
                        if ('ktcc' === $wp2l_license["version"] && (empty($wp2l_license['multiplicator_validation_link']) || empty($wp2l_license['imprint_validation_link']))) {
                            ?>
                            <button id="wp2lValidateKtCC" class="button button-primary" type="button" data-action="validate-ktcc" ><?php _e('Send link for validation', 'wp2leads') ?></button>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>

            <?php
            if (Wp2leads_License::is_dev_allowed()) {
                ?>
                <tr>
                    <td><?php _e( 'Developer Mode', 'wp2leads' ) ?></td>
                    <td>
                        <?php
                        // TODO - This block is only for DEV purposes - need to be romoved
                        ?>
                        <?php _e( 'License level', 'wp2leads' ) ?>: <strong class="license-level-current" style="display: inline-block;margin-right: 25px;">
                            <?php echo $wp2l_is_dev ? $dev_version : __('Dev Mode disabled', 'wp2leads'); ?>
                        </strong>

                        <button data-license="free" class="button change-license" type="button"><?php _e( 'Try Free', 'wp2leads' ) ?></button>
                        <button data-license="essent" class="button change-license" type="button"><?php _e( 'Try Essential', 'wp2leads' ) ?></button>
                        <button data-license="pro" class="button change-license" type="button"><?php _e( 'Try Pro', 'wp2leads' ) ?></button>

                        <button data-license="reset" class="button change-license" type="button"><?php _e( 'Reset', 'wp2leads' ) ?></button>
                        <?php
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <table id="table-setting" style="margin-top: 20px;">
        <thead>
        <tr>
            <th colspan="2"><?php _e('Information about KlickTipp', 'wp2leads') ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php _e('Username', 'wp2leads') ?></td>
            <td><input style="width: 100%;" type="text" id="wp2l-settings-klicktipp-username" class="form-control" name="settings-klicktipp-username" value="<?php echo(get_option('wp2l_klicktipp_username')); ?>"></td>
        </tr>
        <tr>
            <td><?php _e('Password', 'wp2leads') ?></td>
            <td><input style="width: 100%;" type="password" id="wp2l-settings-klicktipp-password" class="form-control" name="settings-klicktipp-password" value="<?php echo(get_option('wp2l_klicktipp_password')); ?>"></td>
        </tr>
        <tr>
            <td></td>
            <td><input type="submit" name="btnKlicktippSubmit" id="btnKlicktippSubmit" class="button button-primary" value="<?php _e( 'Save', 'wp2leads' ) ?>"></td>
        </tr>
        </tbody>
    </table>

    <table id="table-setting" style="margin-top: 20px;">
        <thead>
            <tr>
                <th colspan="3"><?php _e('System information', 'wp2leads') ?></th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td><?php _e('WordPress Version', 'wp2leads') ?></td>
                <td><?php _e('>= 4.5.0 (latest best)', 'wp2leads') ?></td>
                <td><?php echo $system_info['wp_version'] ?></td>
            </tr>

            <tr>
                <td><?php _e('Wp2Leads Version', 'wp2leads') ?></td>
                <td><?php

                    if(empty($wp2l_plugin_allowed_versions['minActiveVersion']) || empty($wp2l_plugin_allowed_versions['activeVersion'])) {
                        echo __('unknown', 'wp2leads');
                    } else {
                        echo sprintf (
                            __('>= %s (%s latest)', 'wp2leads'),
                            $wp2l_plugin_allowed_versions['minActiveVersion'], $wp2l_plugin_allowed_versions['activeVersion']
                        );
                    }
                    ?></td>
                <td><?php echo $system_info['wp2lead_version'] ?></td>
            </tr>

            <tr>
                <td><?php _e('PHP Version', 'wp2leads') ?></td>
                <td><?php _e('>= 5.6 (WordPress recommends 7.0+)', 'wp2leads') ?></td>
                <td><?php echo $system_info['php_version'] ?></td>
            </tr>

            <tr>
                <td><?php _e('MySQL Version', 'wp2leads') ?></td>
                <td><?php _e('>= 5.6.0 (WordPress recommends 5.6+)', 'wp2leads') ?></td>
                <td><?php echo $system_info['mysql_version'] ?></td>
            </tr>

            <tr>
                <td><?php _e('PHP Memory Limit', 'wp2leads') ?></td>
                <td><?php _e('>= 64 MB (recomended >= 128 MB)', 'wp2leads') ?></td>
                <td><?php echo size_format($system_info['php_memory_limit']) ?></td>
            </tr>
        </tbody>
    </table>
</form>