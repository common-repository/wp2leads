<?php
/**
 * Statistic Page Failed Items Template
 *
 * @package Wp2Leads/Partials/Statistics
 * @version 1.0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="statistics-wrapper">
    <?php
    if (empty($failed_items_list) || !is_array($failed_items_list)) {
        ?>
        <p>
            <?php _e('You do not have any failed items for current map.', 'wp2leads') ?>
        </p>

        <p>
            <a href="?page=wp2l-admin&tab=statistics" class="button button-primary">
                <?php _e('See statistic for all maps', 'wp2leads') ?>
            </a>
        </p>
        <?php
    } else {
        ?>

                <?php
                foreach ($failed_items_list as $failed_single_item) {
                    $failed_single_item_data = unserialize($failed_single_item['user_data']);
                    ?>

                    <!-- Email, Failed Reason and Opt-In - Start -->
                    <div>
                        <h3 style="margin-top:15px;margin-bottom:10px;"><?php _e('User Email:', 'wp2leads'); ?> <?php echo $failed_single_item['user_email']; ?></h3>

                        <p style="margin-top:0;margin-bottom:0;">
                            <?php _e('Failed error direct from Klick-Tipp:', 'wp2leads'); ?>

                            <strong>
                                <?php
                                if (!empty($failed_single_item_data['reason'])) {
                                    echo $failed_single_item_data['reason'];
                                } else {
                                    _e('Unknown', 'wp2leads');
                                }
                                ?>
                            </strong>
                        </p>

                        <p style="margin-top:0;margin-bottom:0;">
                            <?php _e('Optin:', 'wp2leads'); ?>

                            <strong>
                                <?php
                                if (!empty($failed_single_item_data['optin'])) {
                                    if (!empty($statistic_optins)) {
                                        $statistic_optin = isset($statistic_optins[$failed_single_item_data['optin']]) ? $statistic_optins[$failed_single_item_data['optin']] : __('Unknown', 'wp2leads');
                                    }

                                    if (empty($statistic_optin)) {
                                        echo __( 'Default Opt-In Process', 'wp2leads' );
                                    } else {
                                        echo $statistic_optin;
                                    }
                                } else {
                                    _e('Unknown', 'wp2leads');
                                }
                                ?>
                            </strong>
                        </p>
                    </div>
                    <!-- Email, Failed Reason and Opt-In - End -->

                    <!-- Fields and Tags - Start -->
                    <table id="statistics-table">
                        <thead>
                            <tr>
                                <th><?php _e('Fields', 'wp2leads') ?></th>
                                <th><?php _e('Tags', 'wp2leads') ?></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td style="vertical-align:top;width:50%">
                                    <div style="padding:10px;">
                                        <?php
                                        if (!empty($failed_single_item_data['fields']) && is_array($failed_single_item_data['fields'])) {
                                            foreach ($failed_single_item_data['fields'] as $failed_field_name => $failed_field_value) {
                                                if (!empty($statistic_fields) && is_array($statistic_fields)) {
                                                    if (!empty($statistic_fields[$failed_field_name])) {
                                                        $failed_field_name = $statistic_fields[$failed_field_name];
                                                    }
                                                }
                                                ?>
                                                <p style="margin-top:0;margin-bottom:0;">
                                                    <?php echo $failed_field_name; ?>: <strong><?php echo $failed_field_value; ?></strong>
                                                </p>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td style="vertical-align:top;">
                                    <div style="padding:10px;">
                                        <?php
                                        if (!empty($failed_single_item_data['tags']) || !empty($failed_single_item_data['detach_tags'])) {
                                            if (!empty($failed_single_item_data['tags']) && is_array($failed_single_item_data['tags'])) {
                                                ?>
                                                <h4 style="margin-top:0;margin-bottom:5px;"><?php _e('Tags to add:', 'wp2leads') ?></h4>
                                                <div class="selected-tags-cloud-wrapper">
                                                    <?php
                                                    foreach ($failed_single_item_data['tags'] as $failed_item_tag) {
                                                        if (!empty($statistic_tags) && is_array($statistic_tags)) {
                                                            if (!empty($statistic_tags[$failed_item_tag])) {
                                                                ?>
                                                                <div class="selected-tag selected-tag-new">
                                                                    <?php echo $statistic_tags[$failed_item_tag]; ?>
                                                                </div>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                <div class="selected-tag selected-tag-new">
                                                                    <?php echo $failed_item_tag; ?>
                                                                </div>
                                                                <?php
                                                            }
                                                        } else {
                                                            ?>
                                                            <div class="selected-tag selected-tag-new">
                                                                <?php echo $failed_item_tag; ?>
                                                            </div>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <?php
                                            }

                                            if (!empty($failed_single_item_data['detach_tags']) && is_array($failed_single_item_data['detach_tags'])) {
                                                ?>
                                                <h4 style="margin-bottom:5px"><?php _e('Tags to remove:', 'wp2leads') ?></h4>

                                                <div class="selected-tags-cloud-wrapper">
                                                    <?php
                                                    foreach ($failed_single_item_data['detach_tags'] as $failed_item_tag) {
                                                        if (!empty($statistic_tags) && is_array($statistic_tags)) {
                                                            if (!empty($statistic_tags[$failed_item_tag])) {
                                                                ?>
                                                                <div class="selected-tag selected-tag-detach">
                                                                    <?php echo $statistic_tags[$failed_item_tag]; ?>
                                                                </div>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                <div class="selected-tag selected-tag-detach">
                                                                    <?php echo $failed_item_tag; ?>
                                                                </div>
                                                                <?php
                                                            }
                                                        } else {
                                                            ?>
                                                            <div class="selected-tag selected-tag-detach">
                                                                <?php echo $failed_item_tag; ?>
                                                            </div>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Fields and Tags - End -->

                    <hr>
                    <?php
                }
                ?>
        <?php
    }
    ?>
</div>
