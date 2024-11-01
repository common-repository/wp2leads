<?php
/**
 * Created by PhpStorm.
 * Date: 2/4/18
 * Time: 9:00 AM
 */
?>
<script id="wp2l-map-to-api-recomended-tags-filter" type="text/x-handlebars-template">
    <textarea class="form-control" style="height: 110px;margin-bottom: 10px;">{{filter_tags}}</textarea>
</script>

<script id="wp2l-map-to-api-no-new-recomended-tags" type="text/x-handlebars-template">
    <p style="margin-top:0;margin-bottom:0">
        <?php _e( 'There is no tags to create', 'wp2leads' ) ?>
    </p>
</script>

<script id="wp2l-no-limit-for-transfer" type="text/x-handlebars-template">
    <?php
    $kt_limitation = KlickTippManager::get_initial_kt_limitation();

    if ($kt_limitation) {
        $kt_limit_users = $kt_limitation['limit_users'];
        $kt_limit_message = $kt_limitation['limit_message'];
        $kt_limit_days = $kt_limitation['limit_days'];
        $kt_counter = KlickTippManager::get_transfer_counter();

        if ($kt_counter) {
            $kt_limit_counter = $kt_counter['limit_counter'];
            $kt_limit_counter_timeout = $kt_counter['limit_counter_timeout'];
            $kt_limit_counter_timeout_left = $kt_limit_counter_timeout - time();

            ?>
            <div class="notice notice-warning inline">
                <p style="margin-top:0;margin-bottom:0;">
                    <?php echo sprintf( __( 'You have exceeded your <strong>Pro Version</strong> limit for <strong>%s</strong> users per <strong>%s</strong> days.', 'wp2leads' ), $kt_limit_users, $kt_limit_days ); ?>
                </p>
            </div>
            <?php
        }

    }
    ?>
</script>

<script id="wp2l-no-users-for-transfer" type="text/x-handlebars-template">
    <div class="notice notice-warning inline">
        <h4><?php _e( "There is no data to transfer", "wp2leads" ); ?></h4>
    </div>
</script>

<script id="wp2l-map-to-api-recomended-tags-created" type="text/x-handlebars-template">
    <p style="margin-top:0;margin-bottom:0">
        <?php _e( 'All tags from this settings already on KT', 'wp2leads' ) ?>
    </p>
</script>

<script id="wp2l-map-to-api-recomended-tags-get" type="text/x-handlebars-template">
    <p style="margin-top:0;margin-bottom:0">
        <?php _e( 'To get tags list, click Get tags button', 'wp2leads' ) ?>
    </p>
</script>

<script id="wp2l-map-to-api-recomended-tags-bg" type="text/x-handlebars-template">
    <p style="margin-top:0;margin-bottom:0">
        <?php _e( 'Background process for this tags set is running.', 'wp2leads' ) ?>
    </p>
</script>

<script id="wp2l-api-transfer-modal-current" type="text/x-handlebars-template">
    <div class="transfer-data-modal">
        <h2><?php _e( 'Transfer current user data to Klick Tipp', 'wp2leads' ) ?></h2>

        <div class="main-wrapper api-processing-holder">
            <div class="side transfer-info">
                <div class="inner">
                    <h3><?php _e( 'Current transfer', 'wp2leads' ) ?></h3>

                    <div class="info-message">
                        <p style="margin-top:0;margin-bottom:10px;"></p>
                    </div>
                    <div class="total-transferred-data">
                        <p style="margin-top:0;margin-bottom:0;"><?php _e( 'Transferred', 'wp2leads' ) ?>: <strong class="total">0</strong></p>
                    </div>
                    <div class="transfered-data">
                        <p style="margin-top:0;margin-bottom:0;"><?php _e( 'New users', 'wp2leads' ) ?>: <strong class="total">0</strong></p>
                    </div>
                    <div class="updated-data">
                        <p style="margin-top:0;margin-bottom:0;"><?php _e( 'Updated users', 'wp2leads' ) ?>: <strong class="total">0</strong></p>
                    </div>
                    <div class="failed-data">
                        <p style="margin-top:0;margin-bottom:0;"><?php _e( 'Failed users', 'wp2leads' ) ?>: <strong class="total">0</strong></p>
                    </div>
                </div>
            </div>

            <div class="side cron-settings">
                <div class="inner">
                    <h3><?php _e( 'Current user data', 'wp2leads' ) ?></h3>
                    <div class="transferred-data-email">
                        <p style="margin-top:0;"><?php _e( 'User email', 'wp2leads' ) ?>: <strong>{{current_email}}</strong></p>
                    </div>

                    <div class="transferred-data-tags-title">
                        <p style="margin-top:0;margin-bottom:0;"><?php _e( 'User tags', 'wp2leads' ) ?>:</p>
                    </div>

                    <div class="transferred-data-tags">
                        {{{tags}}}
                    </div>
                </div>
            </div>

            <div class="api-spinner-holder api-processing">
                <div class="api-spinner"></div>
            </div>
        </div>

        <div class="buttons-wrapper">
            <button id="transferCurrent" data-active-map="{{active_map}}" class="button button-primary">
                <?php _e( 'Transfer current user', 'wp2leads' ) ?>
            </button>
			 <button id="transferCurrentClose" class="button button-secondary" style="display:none;">
                <?php _e( 'Close Popup', 'wp2leads' ) ?>
            </button>
        </div>

        <div class="close">&times;</div>
    </div>

    <div class="gray-back"></div>
</script>

<script id="wp2l-api-map-transfer-in-bg" type="text/x-handlebars-template">
    <p class="warning-text">
        <?php echo __( 'Background transfer not available right now for this map as far as another process running.', 'wp2leads' ); ?>
    </p>
</script>

<script id="wp2l-api-transfer-modal" type="text/x-handlebars-template">
    <div class="transfer-data-modal">
        <h2><?php _e( 'Transfer data to Klick Tipp', 'wp2leads' ) ?></h2>
        <div class="notice_holder"></div>

        <div class="main-wrapper api-processing-holder">
            <div class="side transfer-info">
                <div class="inner">
                    <div class="available-data">
                        <p><?php _e( 'You can transfer', 'wp2leads' ) ?>: <strong class="total">0</strong></p>
                    </div>
                </div>
            </div>

            <div class="side cron-settings">
                <div class="inner"></div>
            </div>

            <div class="api-spinner-holder api-processing">
                <div class="api-spinner"></div>
            </div>
        </div>

        <div class="buttons-wrapper">
            <button id="transferAllBg" data-active-map="{{active_map}}" class="button" disabled="disabled">
                <?php _e( 'Transfer all users in Background', 'wp2leads' ) ?>
            </button>
        </div>

        <div class="close">&times;</div>
    </div>
    <div class="gray-back"></div>
</script>

<script id="wp2l-api-transfer-modal-old" type="text/x-handlebars-template">
    <div class="transfer-data-modal">
        <h2><?php _e( 'Transfer data to Klick Tipp', 'wp2leads' ) ?></h2>
        <div class="notice_holder"></div>

        <div class="main-wrapper api-processing-holder">
            <div class="side transfer-info">
                <div class="inner">
                    <div class="available-data">
                        <p><?php _e( 'You can transfer', 'wp2leads' ) ?>: <strong class="total">0</strong></p>
                    </div>

                    <hr>

                    <h3><?php _e( 'Current transfer', 'wp2leads' ) ?></h3>

                    <div class="total-transferred-data">
                        <p style="margin-top:0;margin-bottom:0;"><?php _e( 'Transferred', 'wp2leads' ) ?>: <strong class="total">0</strong></p>
                    </div>
                    <div class="transfered-data">
                        <p style="margin-top:0;margin-bottom:0;"><?php _e( 'New users', 'wp2leads' ) ?>: <strong class="total">0</strong></p>
                    </div>
                    <div class="updated-data">
                        <p style="margin-top:0;margin-bottom:0;"><?php _e( 'Updated users', 'wp2leads' ) ?>: <strong class="total">0</strong></p>
                    </div>
                    <div class="failed-data">
                        <p style="margin-top:0;margin-bottom:0;"><?php _e( 'Failed users', 'wp2leads' ) ?>: <strong class="total">0</strong></p>
                    </div>
                </div>
            </div>

            <div class="side cron-settings">
                <div class="inner">

                </div>
            </div>

            <div class="api-spinner-holder api-processing">
                <div class="api-spinner"></div>
            </div>
        </div>

        <div class="buttons-wrapper">
            <button id="transferAllBg" data-active-map="{{active_map}}" class="button" disabled="disabled">
                <?php _e( 'Transfer all users in Background', 'wp2leads' ) ?>
            </button>
        </div>

        <div class="close">&times;</div>
    </div>
    <div class="gray-back"></div>
</script>

<script id="wp2l-api-donot-optins-condition-set" type="text/x-handlebars-template">
    <div class="condition">
        <div class="option-select-holder">
            <div class="api_field_box">
                <div class="api_field_head">
                    <p class="field_label"><?php _e( 'if value in', 'wp2leads' ) ?></p>
                    <p class="field_value"><?php _e( 'Choose an option', 'wp2leads' ) ?></p>
                </div>
                <div class="api_field_body">
                    <select class="options_where" name="option" multiple>
                        {{#each availableOptions}}
<!--                        <option value="{{this}}">{{this}}</option>-->
                        <option value="{{this.value}}">{{this.label}}</option>
                        {{/each}}
                    </select>
                </div>
            </div>
        </div>

        <div class="condition-operator-string-holder">
            <div>
                <select name="operator" class="form-control">
                    <option value="like" {{#ifCond operator
                    '==' "like"}}selected{{/ifCond}}><?php _e( 'is like', 'wp2leads' ) ?></option>
                    <option value="not-like" {{#ifCond operator
                    '==' "not-like"}}selected{{/ifCond}}><?php _e( 'is not like', 'wp2leads' ) ?></option>
                    <option value="contains" {{#ifCond operator
                    '==' "contains"}}selected{{/ifCond}}><?php _e( 'contains', 'wp2leads' ) ?></option>
                    <option value="not contains" {{#ifCond operator
                    '==' "not contains"}}selected{{/ifCond}}><?php _e( 'not contains', 'wp2leads' ) ?></option>
                    <option value="bigger as" {{#ifCond operator
                    '==' "bigger as"}}selected{{/ifCond}}><?php _e( 'bigger as', 'wp2leads' ) ?></option>
                    <option value="smaller as" {{#ifCond operator
                    '==' "smaller as"}}selected{{/ifCond}}><?php _e( 'smaller as', 'wp2leads' ) ?></option>
                </select>
            </div>
            <div><input type="text" name="string" value="" class="form-control"></div>
        </div>
        <div id="removeConditionForDoNotOptin" class="button button-danger button-remove">&times;</div>
    </div>
</script>

<script id="wp2l-api-message-current-user-donot-optins" type="text/x-handlebars-template">
    <?php echo __('This user can\'t be Opt-ined according to conditions.', 'wp2leads'); ?>
</script>
