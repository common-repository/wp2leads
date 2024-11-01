<?php
/**
 * Map Builder - Additional settings section
 */
?>
<h2 id="wp2l-create-map-header">
    <?php _e('Additional settings', 'wp2leads') ?>
</h2>

<hr>

<section id="map-additional-section">
    <?php
    if ($is_create_own_map_allowed) {
        $possibleStandartTags = '';

        if (!empty( $decodedInfo['possibleUsedTags']['standartTags'] )) {
            $possibleStandartTags = json_encode( $decodedInfo['possibleUsedTags']['standartTags'], JSON_FORCE_OBJECT );
        } elseif (!empty( $duplicatedInfo['possibleUsedTags']['standartTags']) ) {
            $possibleStandartTags = json_encode( $duplicatedInfo['possibleUsedTags']['standartTags'], JSON_FORCE_OBJECT );
        }

        $possibleUserInputTags = '';

        if (!empty( $decodedInfo['possibleUsedTags']['userInputTags'] )) {
            $possibleUserInputTags = json_encode( $decodedInfo['possibleUsedTags']['userInputTags'], JSON_FORCE_OBJECT );
        } elseif (!empty( $duplicatedInfo['possibleUsedTags']['userInputTags']) ) {
            $possibleUserInputTags = json_encode( $duplicatedInfo['possibleUsedTags']['userInputTags'], JSON_FORCE_OBJECT );
        }
        ?>
        <table style="width:100%;table-layout: fixed" class="form-table">
            <tr id="manualPrecreatedTags_row">
                <td>
                    <p><?php _e('Manual Recomended Tags', 'wp2leads') ?></p>
                    <p style="line-height: 1">
                        <small>
                            <?php _e('Use this section for create tags from standart WP values like: <strong>post status</strong> or <strong>user capability</strong>', 'wp2leads') ?>
                            <br>
                            <i>
                                <?php _e('f.e. <strong>wc-completed</strong>, <strong>customer</strong> etc.', 'wp2leads') ?>
                            </i>
                        </small>
                    </p>
                </td>

                <td class="api-processing-holder">
                    <div  id="manualPrecreatedTags_container"  data-saved-value='<?php echo $possibleStandartTags; ?>'></div>

                    <div id="manualPrecreatedTags_control">
                        <form id="tagCreateForm">
                            <div class="wptl-row" style="margin: 0 -2px">
                                <div class="wptl-col-xs-6 wptl-col-sm-4 wptl-col-md-4 wptl-col-lg-4" style="padding: 0 2px">
                                    <input id="newTagPrefixInput" placeholder="<?php _e( 'Add Prefix', 'wp2leads' ) ?>" class="form-control" type="text">
                                </div>

                                <div class="wptl-col-xs-6 wptl-col-sm-5 wptl-col-md-5 wptl-col-lg-5" style="padding: 0 2px">
                                    <input id="newTagInput" placeholder="<?php _e( 'Add Tag Name', 'wp2leads' ) ?>" class="form-control" type="text">
                                </div>

                                <div class="wptl-col-xs-12 wptl-col-sm-3 wptl-col-md-3 wptl-col-lg-3" style="padding: 0 2px">
                                    <button id="createTag" type="submit" class="button"><?php _e('Add', 'wp2leads') ?></button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="api-spinner-holder api-processing">
                        <div class="api-spinner"></div>
                    </div>
                </td>
            </tr>

            <tr id="userInputTags_row">
                <td>
                    <p><?php _e('User Input Recomended Tags', 'wp2leads') ?></p>

                    <p style="line-height: 1">
                        <small>
                            <?php _e('Use this section for create tags from user input values like: <strong>post title</strong> or <strong>category title</strong>', 'wp2leads') ?>
                            <br>
                            <i>
                                <?php _e('f.e. <strong>Sunglasses</strong>, <strong>Accessories</strong> etc.', 'wp2leads') ?>
                            </i>
                        </small>
                    </p>
                </td>
                <td class="api-processing-holder">
                    <div
                            id="userInputPrecreatedTags_container"
                            data-saved-value='<?php echo $possibleUserInputTags; ?>'
                    ></div>

                    <div id="userInputPrecreatedTags_control">
                        <button id="createUserInputTag" type="submit" class="button"><?php _e('Add Item', 'wp2leads') ?></button>
                    </div>

                    <div class="api-spinner-holder api-processing">
                        <div class="api-spinner"></div>
                    </div>
                </td>
            </tr>

            <?php
            $transfer_modules = Wp2leads_Transfer_Modules::get_transfer_modules_class_names();
            $selected_transfer_module = !empty($decodedMap["transferModule"]) ? $decodedMap["transferModule"] : '';

            if (!empty($transfer_modules)) {
                ?>
                <tr>
                    <td>
                        <p><?php _e('Transfer modules', 'wp2leads') ?></p>

                        <p style="line-height: 1">
                            <small>
                                <?php _e('Transfer modules are used to transfer data to KT once some some action is fired', 'wp2leads') ?>
                                <br>
                                <i>
                                    <?php _e('f.e. <strong>New order created</strong>, <strong>Order\'s status is changed</strong> etc.', 'wp2leads') ?>
                                </i>
                            </small>
                        </p>
                    </td>
                    <td>
                        <div id="transfer_module-wrapper" style="width:100%;max-width:400px;">
                            <select name="transfer_module" id="transfer_module" class="form-control">
                                <option value=""><?php echo __('-- Select module to transfer data in background', 'wp2leads') ?></option>
                                <?php
                                foreach ($transfer_modules as $slug => $transfer_module) {
                                    $class_name = $transfer_module;
                                    $module_label = $class_name::get_label();
                                    $module_required = $class_name::get_required_column();
                                    ?>
                                    <option
                                            value="<?php echo $slug ?>" data-required-column="<?php echo $module_required ?>"
                                        <?php echo $slug === $selected_transfer_module ? ' selected' : ''; ?>
                                    >
                                        <?php echo $module_label ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>

                            <div class="module-instruction">
                                <?php
                                if ('' !== $selected_transfer_module) {
                                    $selected_transfer_module_class_name = $transfer_modules[$selected_transfer_module];
                                    if (class_exists($selected_transfer_module_class_name)) {
                                        $selected_module_instruction = $selected_transfer_module_class_name::get_instruction();

                                        echo $selected_module_instruction;
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php
            }
            ?>

            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <?php
    }
    ?>
</section>