<?php
/**
 *
 */
?>

<section id="map-messages">
    <div>
        <?php echo __('To get a quick solution please record a <a href="https://chrome.google.com/webstore/detail/screencastify-screen-vide/mmeijimgabbpbgpdklnllpncmdofkcpn" target="_blank">screencast</a> with this software, Upload to Google Drive and <a href="https://wp2leads-for-klick-tipp.com/?p=644" target="_blank">share</a> link with all users in documentation!', 'wp2leads') ?>
    </div>
</section>

<h2 id="wp2l-map-headstart-header"><?php _e('Map Headstart', 'wp2leads') ?>
    <?php include dirname(__FILE__) . '/wp2leads-admin-map_builder-buttons.php'; ?>
</h2>

<hr>

<section id="map-headstart-section">
    <?php
    if ($is_create_own_map_allowed) {
        $map_search = '';

        if (!empty($decodedInfo['search'])) {
            $map_search = htmlspecialchars ($decodedInfo['search']);
        } elseif (!empty($duplicatedInfo['search'])) {
            $map_search = htmlspecialchars ($duplicatedInfo['search']);
        }

        $map_table_search = '';

        if (!empty($decodedInfo['searchTable'])) {
            $map_table_search = htmlspecialchars ($decodedInfo['searchTable']);
        } elseif (!empty($duplicatedInfo['searchTable'])) {
            $map_table_search = htmlspecialchars ($duplicatedInfo['searchTable']);
        }

        $map_server_id = '';

        if (!empty($decodedInfo['serverId'])) {
            $map_server_id = htmlspecialchars ($decodedInfo['serverId']);
        } elseif (!empty($duplicatedInfo['serverId'])) {
            $map_server_id = htmlspecialchars ($duplicatedInfo['serverId']);
        }
        ?>
        <table style="width:100%;table-layout: fixed">
            <tr class="multi-search-form-row">
                <col width="200">
                <td>
                    <?php _e('Multi Search', 'wp2leads') ?>
                    <input id="wp2l-multi-search-results-map" type="hidden" name="multi-search-results-map" value="<?php echo $map_search; ?>">
                    <input id="wp2l-map-owner" type="hidden" name="multi-map-owner" value="<?php echo !empty($decodedInfo['domain']) ? $decodedInfo['domain'] : '' ?>">
                    <input id="wp2l-server-id" type="hidden" name="server-id" value="<?php echo $map_server_id ?>">
                    <input id="wp2l-public-map-id" type="hidden" name="public-map-id" value="<?php echo !empty($decodedInfo['publicMapId']) ? $decodedInfo['publicMapId'] : '' ?>">
                    <input id="wp2l-public-map-hash" type="hidden" name="public-map-hash" value="<?php echo !empty($decodedInfo['publicMapHash']) ? $decodedInfo['publicMapHash'] : '' ?>">
                    <input id="wp2l-public-map-content" type="hidden" name="public-map-content" value="<?php echo !empty($decodedInfo['publicMapContent']) ? $decodedInfo['publicMapContent'] : '' ?>">
                    <input id="wp2l-public-map-kind" type="hidden" name="public-map-kind" value="<?php echo !empty($decodedInfo['publicMapKind']) ? $decodedInfo['publicMapKind'] : '' ?>">
                    <input id="wp2l-public-map-owner" type="hidden" name="public-map-owner" value="<?php echo !empty($decodedInfo['publicMapOwner']) ? $decodedInfo['publicMapOwner'] : '' ?>">
                    <input id="wp2l-public-map-status" type="hidden" name="public-map-status" value="<?php echo !empty($decodedInfo['publicMapStatus']) ? $decodedInfo['publicMapStatus'] : '' ?>">
                    <input id="wp2l-public-map-version" type="hidden" name="public-map-version" value="<?php echo !empty($decodedInfo['publicMapVersion']) ? $decodedInfo['publicMapVersion'] : '' ?>">
                    <input id="wp2l-initial-settings" type="hidden" name="initial-settings" value="<?php echo !empty($decodedInfo['initial_settings']) ? 1 : 0 ?>">
                    <input id="wp2l-is-exclusive" type="hidden" name="is-exclusive" value="<?php echo !empty($decodedInfo['isExclusive']) ? 1 : '' ?>">
                    <input id="wp2l-public-map-to-api" type="hidden" name="public-map-to-api" value='<?php echo !empty($mapForDuplicate) ? $mapForDuplicate->api : '' ?>'>
                </td>
                <td>
                    <div id="wp2l-multi-search-holder" class="columns-two">
                        <div class="multisearch-form-holder column-1">
                            <form id="wp2l-multi-search-form" action="" class="multisearch-form-container">
                                <?php _e('Input search string', 'wp2leads') ?>

                                <div class="input-group_holder">
                                    <div class="tag-input_holder input_holder">
                                        <input type="text" class="wp2l-multi-search-string form-control" name="multi-search-string">
                                    </div>

                                    <div class="tag-create-btn_holder btn_holder">
                                        <button type="submit" class="button wp2l-multi-search-start"><?php _e('Search', 'wp2leads') ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="multisearch-form-holder column-2">
                            <form id="wp2l-table-search-form" action="" class="multisearch-form-container">
                                <?php _e('or select table', 'wp2leads') ?>
                                <div class="input-group_holder">
                                    <div class="tag-input_holder input_holder">
                                        <select class="wp2l-search-table form-control" name="search-table">
                                            <option value=""><?php _e('-- Select --', 'wp2leads') ?></option>
                                            <?php
                                            $tables = MapBuilderManager::fetch_tables();

                                            foreach ($tables as $table) {
                                                ?>
                                                <option value="<?php echo $table ?>"><?php echo $table ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="tag-create-btn_holder btn_holder">
                                        <button type="submit" class="button wp2l-table-search-start"><?php _e('Search', 'wp2leads') ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>

            <tr class="multi-search-result-row" data-current_value="">
                <td><div id="wp2l-multi-search-result-tags"></div></td>
                <td><div id="wp2l-multi-search-result-holder"></div></td>
            </tr>

            <tr class="multi-search-single-result-row" data-current_value="<?php echo !empty($decodedInfo['search']) ? htmlspecialchars ($decodedInfo['search']) : '' ?>">
                <td></td>
                <td><div id="wp2l-multi-search-single-result-holder"></div></td>
            </tr>

            <tr class="table-search-result-row" data-current_value="<?php echo $map_table_search; ?>">
                <td></td>
                <td><div id="wp2l-table-search-result-holder"></div></td>
            </tr>
        </table>
        <?php
    }
    ?>
</section>