<?php
/**
 * Created by PhpStorm.
 * Date: 2/4/18
 * Time: 9:00 AM
 */
?>
<script id="wp2l-map-to-api-message-in-progress" type="text/x-handlebars-template">
    <div id="map-to-api-message-in-progress" class="notice notice-warning inline">
        <h4><?php _e( 'We are collecting your users data. It could take up to few minutes depending on your server and number of data in DB.', 'wp2leads' ) ?></h4>
        <p><?php _e( 'Please be patient and not close or reload the page. Freeze of the browser tab and browser ask to "wait or cancel" is normal. Please click Wait button to see if finished.', 'wp2leads' ) ?></p>
    </div>
</script>
<script id="wp2l-map-to-api-prepare-message-in-progress" type="text/x-handlebars-template">
    <div id="map-to-api-message-in-progress" class="notice notice-warning inline">
        <h4><?php _e( 'We are preparing your users data for transfering to Klick Tipp. It could take up to few minutes depending on your server and number of data in DB. Please be patient and not close or reload the page.', 'wp2leads' ) ?></h4>
    </div>
</script>
<script id="wp2l-map-to-api-bg-in-progress" type="text/x-handlebars-template">
    <div id="map-to-api-message-in-progress" class="notice notice-warning inline">
        <h4><?php _e( 'We are starting transfering your users data for to Klick Tipp. It could take up to few minutes depending on your server and number of data in DB. Please be patient and not close or reload the page.', 'wp2leads' ) ?></h4>
    </div>
</script>
<script id="wp2l-no-starter-date" type="text/x-handlebars-template">
    <div class="no-starter-date-message">
        <p class="field_value"><?php _e( 'Select starter data first', 'wp2leads' ) ?></p>
    </div>
</script>
<script id="wp2l-multisearch-tags" type="text/x-handlebars-template">
    <div id="tag-seq-{{seq}}" data-seq="{{seq}}" class="multi-search-tag active"><span class="tag-use">{{tag}}</span>
        <span class="tag-close"></span></div>
</script>
<script id="wp2l-multisearch-table-label" type="text/x-handlebars-template">
    <div class="table-label {{tableClass}}-label">{{tableName}}</div>
</script>
<script id="wp2l-multisearch-results" type="text/x-handlebars-template">
    <div id="result-seq-{{seq}}" class="wp2l-multi-search-result active"">
    <input class="search-string-value" type="hidden" value="{{searchString}}">
    <input class="sequence-value" type="hidden" value="{{seq}}">

    <div class="multisearch-results-table-wrap">
        <table class="multisearch-results-table" style="width: 100%">
            <thead>
            <tr>
                <th class="sort-col sort-no" data-sort="byTable">
                    <?php _e( 'Table', 'wp2leads' ) ?>
                    <div class="sort_holder">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                        <span class="dashicons dashicons-sort"></span>
                    </div>
                </th>
                <th class="sort-col sort-desc" data-sort="byCount">
                    <?php _e( 'Column', 'wp2leads' ) ?>
                    <div class="sort_holder">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                        <span class="dashicons dashicons-sort"></span>
                    </div>
                </th>
                <th>
                    <?php _e( 'Actions', 'wp2leads' ) ?>
                </th>
                <th>
                    <?php _e( 'Used as', 'wp2leads' ) ?>
                </th>
            </tr>
            </thead>

            <tbody>
            {{#each tables as |table tableName|}}
            <tr class="multisearch-results-table-row table-{{tableName}}-row" data-count="{{table.total}}">
                <td class="table-holder">
                    <div style="padding: 3px">{{tableName}}</div>
                </td>
                <td>
                    {{#each table.columns}}
                    <div class="column-holder" data-column="{{this.column}}" style="padding: 3px">
                        {{this.column}} ({{this.count}})
                    </div>
                    {{/each}}
                </td>
                <td>
                    <div class="actions-holder" style="padding: 3px">
                        <button data-table="{{tableName}}"
                                class="button button-small wp2l-multi-search-view"><?php _e( 'View search results', 'wp2leads' ) ?></button>
                        {{#if table.group}}
                        <button data-table_group="{{table.group}}"
                                class="button button-small wp2l-multi-search-group"><?php _e( 'View more tables for', 'wp2leads' ) ?>
                            <strong>{{table.group}}_</strong></button>
                        {{/if}}
                        <button data-table="{{tableName}}"
                                class="button button-small wp2l-multi-search-use"><?php _e( 'Use as starter data', 'wp2leads' ) ?></button>
                    </div>
                </td>
                <td>
                    <div class="label-holder" style="padding: 3px"></div>
                </td>
            </tr>

            {{/each}}
            </tbody>
        </table>
    </div>
    </div>
</script>
<script id="wp2l-multisearch-single-result" type="text/x-handlebars-template">
    <div id="single-result-{{table}}" class="single-result-seq-{{seq}} wp2l-multi-search-single-result active">
        <div class="wp2l-multi-search-single-header">
            <h4>{{label}}</h4>
            <button data-table="{{table}}"
                    class="button button-small wp2l-table-search-view"><?php _e( 'View table', 'wp2leads' ) ?></button>
            <div class="wp2l-multi-search-single-close" data-table="{{table}}">&times;</div>
        </div>

        <div class="multisearch-single-result-table-wrap">
            <table class="multisearch-single-result-table">
                <thead>
                <tr>
                    {{#each columns}}
                    <th class="header-{{this}}">{{this}}</th>
                    {{/each}}
                </tr>
                </thead>

                <tbody>
                {{#each results}}
                <tr>
                    {{#each this}}
                    <td>
                        <div style="padding: 3px">{{this}}</div>
                    </td>
                    {{/each}}
                </tr>
                {{/each}}
                </tbody>
            </table>
        </div>
    </div>
</script>
<script id="wp2l-table-search-result" type="text/x-handlebars-template">
    <div id="table-search-result-{{table}}" class="wp2l-table-search-result">
        <div class="wp2l-table-search-header">
            <h4>{{label}}</h4>
            <div class="wp2l-table-search-close" data-table="{{table}}">&times;</div>
        </div>

        <div class="table-search-result-table-wrap">
            <table class="table-search-result-table">
                <thead>
                <tr>
                    {{#each columns as |column index|}}
                    <th class="header-{{column}}" data-column="{{column}}" data-table="{{table}}">{{lookup
                        ../columnsTitles index}}
                        <div class="sorting-holder">
                            <span class="dashicons dashicons-arrow-up-alt2"></span>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                    </th>
                    {{/each}}
                </tr>
                </thead>

                <tbody>
                {{#each results}}
                <tr>
                    {{#each this}}
                    <td>
                        <div style="padding: 3px">{{this}}</div>
                    </td>
                    {{/each}}
                </tr>
                {{/each}}
                </tbody>
            </table>
        </div>
    </div>
</script>
<script id="wp2l-select" type="text/x-handlebars-template">
    <select class='wp2l_relationship_column' name="" id="">
        <option><?php _e( '-- Select --', 'wp2leads' ) ?></option>
        {{#each options}}
        <option value="{{this}}">{{this}}</option>
        {{/each}}
    </select>
</script>
<script id="wp2l-columns-option" type="text/x-handlebars-template">
    <option value="{{table}}.{{column}}">{{table}}.{{column}}</option>
</script>
<script id="wp2l-relationship-map-fields" type="text/x-handlebars-template">
    <div class="relationship-map-fields">
        <?php _e( 'Map Table', 'wp2leads' ) ?>
        <select data-current_value="{{referenceTable}}" class="relationship-reference-table"
                name="relationship[{{index}}][reference-table]" {{disabled}}>
            <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
            {{#each existingTables}}
            <option value="{{this}}">{{this}}</option>
            {{/each}}
        </select>.<select data-current_value="{{referenceColumn}}" class="relationship-reference-column"
                          name="relationship[{{index}}][reference-column]" {{disabled}}>
            <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
        </select>
        <?php _e('To', 'wp2leads') ?>
        <select data-current_value="{{joinTable}}" class="relationship-join-table"
                name="relationship[{{index}}][join-table]" {{disabled}}>
            <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
            {{#each availableTables}}
            <option value="{{this}}">{{this}}</option>
            {{/each}}
        </select>.<select data-current_value="{{joinColumn}}" class="relationship-join-column"
                          name="relationship[{{index}}][join-column]" {{disabled}}>
            <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
        </select>

        {{#unless disabled}}
        <span class="button remove-relationship"><?php _e( 'Remove', 'wp2leads' ) ?></span>
        {{/unless}}

    </div>
</script>
<script id="wp2l-virtual-relationship-map-fields" type="text/x-handlebars-template">
    <div class="virtual-relationship">
        <div class="relationship">
            <?php _e( 'Map Table', 'wp2leads' ); ?> <select data-current_value="" name="table_from" class="virtual-table_from" title="">
                <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
            </select>
            . <select data-current_value="" name="column_from" class="virtual-column_from" title="">
                <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
            </select>
            <?php _e('To', 'wp2leads') ?> <select data-current_value="" name="table_to" class="virtual-table_to" title="">
                <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
            </select>
            . <select data-current_value="" name="column_to" class="virtual-column_to" title="">
                <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
            </select>
        </div>

        <div class="column">
            <?php _e( 'Column is', 'wp2leads' ) ?> <select data-current_value="" name="column_key" class="virtual-column_key" title="">
                <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
            </select>
        </div>

        <div class="values">
            <?php _e( 'Value is', 'wp2leads' ) ?> <select data-current_value="" name="column_value" class="virtual-column_value" title="">
                <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
            </select>
        </div>

        <div class="submenu">
            <span data-action="remove-virtual-relationship"
                  class="button remove-virtual-relationship"><?php _e( 'Remove', 'wp2leads' ) ?></span>
        </div>
    </div>
</script>


<script id="wp2l-map-results-table" type="text/x-handlebars-template">
    <div id="wp2l-results-preview-wrap-inner" style="overflow: auto; position: relative;">
        <table id="wp2l-results-preview" class="widefat">
            {{#if results}}
            <thead>
            <tr rel="{{keyByColumn}}">
                {{#each results as |row i|}}
                {{#if @first}}
                {{#each row as |columnValue columnKey|}}
                {{#ifCond ../../keyByColumn '==' columnKey}}
                <th class="column-key">
                    <span style="display: block"><span data-column_key="{{columnKey}}"
                                                       class="exclude-this-column button button-secondary button-small hidden"></span></span>
                    <strong>{{dotToBr columnKey}}</strong>
                </th>
                {{else}}
                <th>
                    <span style="display: block"><span data-column_key="{{columnKey}}"
                                                       class="exclude-this-column button button-secondary button-small hidden"></span></span>
                    {{dotToBr columnKey}}
                </th>
                {{/ifCond}}
                {{/each}}
                {{/if}}
                {{/each}}
            </tr>
            </thead>

            <tbody>
            {{#each results as |row i|}}
            <tr>
                {{#each row as |columnValue columnKey|}}
                {{#ifCond columnValue 'istypeof' 'object'}}
                <td>
                    {{#each columnValue as |v k|}}
                    {{#ifCond v 'istypeof' 'object'}}
                    {{#each v as |sv sk|}}
                    {{#ifCond sv '===' true}}
                    {{sk}}<br>
                    {{else}}
                    {{sk}}: {{sv}}<br>
                    {{/ifCond}}
                    {{/each}}
                    {{else}}
                    {{#ifCond v '===' true}}
                    {{k}}<br>
                    {{else}}
                    {{k}}: {{v}}<br>
                    {{/ifCond}}
                    {{/ifCond}}
                    {{/each}}
                </td>
                {{else}}
                {{#ifCond ../keyByColumn '==' columnKey}}
                <td class="column-key">
                    {{else}}
                <td>{{/ifCond}}{{columnValue}}</td>
                {{/ifCond}}
                {{/each}}
            </tr>
            {{/each}}
            </tbody>
            {{else}}
            <tbody>
            <tr>
                <td><?php _e( 'No results found. Please update your map!', 'wp2leads' ) ?></td>
            </tr>
            </tbody>
            {{/if}}
        </table>
    </div>
</script>

<script id="wp2l-map-runner-results-table" type="text/x-handlebars-template">
    {{#if results}}
    <div id="wp2l-results-preview-wrap-inner" style="overflow: auto; position: relative;">
        <table id="wp2l-results-preview" class="widefat">
            <thead>
            <tr rel="{{keyByColumn}}">
                {{#each results as |row i|}}
                {{#if @first}}
                {{#each row as |columnValue columnKey|}}
                {{#ifCond ../../keyByColumn '==' columnKey}}
                <th class="column-key" style="text-align: center">
                    <?php _e( 'Chosen Key', 'wp2leads' ) ?><br><strong>{{dotToBr columnKey}}</strong>
                </th>
                {{else}}
                <th style="text-align: center">
                    {{dotToBr columnKey}}
                </th>
                {{/ifCond}}
                {{/each}}
                {{/if}}
                {{/each}}
            </tr>
            </thead>

            <tbody>
            {{#each results as |row i|}}
            <tr>
                {{#each row as |columnValue columnKey|}}
                {{#ifCond columnValue 'istypeof' 'object'}}
                <td>
                    {{#each columnValue as |v k|}}
                    {{#ifCond v 'istypeof' 'object'}}
                    {{#each v as |sv sk|}}
                    {{#ifCond sv '===' true}}
                    {{sk}}<br>
                    {{else}}
                    {{sk}}: {{sv}}<br>
                    {{/ifCond}}
                    {{/each}}
                    {{else}}
                    {{#ifCond v '===' true}}
                    {{k}}<br>
                    {{else}}
                    {{k}}: {{v}}<br>
                    {{/ifCond}}
                    {{/ifCond}}
                    {{/each}}
                </td>
                {{else}}
                {{#ifCond ../keyByColumn '==' columnKey}}
                <td class="column-key">
                    {{else}}
                <td>{{/ifCond}}{{columnValue}}</td>
                {{/ifCond}}
                {{/each}}
            </tr>
            {{/each}}
            </tbody>
        </table>
    </div>
    {{else}}
    <div id="no-results">
        <p>
            <?php _e( 'There is no results for this map in your DB. You can change settings in map builder.', 'wp2leads' ) ?>
        </p>
    </div>
    {{/if}}
</script>

<script id="wp2l-comparison-set" type="text/x-handlebars-template">
    <div class="column-comparison-map-fields">
        <?php _e( 'Ensure', 'wp2leads' ) ?>
        <select data-current_value="{{tableColumn}}" name="comparison[{{index}}][table-column]" id=""
                class="table-column-identifier" {{disabled}}>
            <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
            {{#each availableTableColumns}}
            <option value="{{this}}">{{this}}</option>
            {{/each}}
        </select>

        <select data-current_value="{{operator}}" name="comparison[{{index}}][operator]" id=""
                class="table-column-operator" {{disabled}}>
            <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
            <option value="like" {{#ifCond operator
            '==' "like"}}selected{{/ifCond}}><?php _e( 'is like', 'wp2leads' ) ?></option>
            <option value="not-like" {{#ifCond operator
            '==' "not-like"}}selected{{/ifCond}}><?php _e( 'is not like', 'wp2leads' ) ?></option>
            <option value="contains" {{#ifCond operator
            '==' "contains"}}selected{{/ifCond}}><?php _e( 'contains', 'wp2leads' ) ?></option>
            <option value="not contains" {{#ifCond operator
            '==' "not contains"}}selected{{/ifCond}}><?php _e( 'not contains', 'wp2leads' ) ?></option>
            <option value="bigger" {{#ifCond operator
            '==' "bigger"}}selected{{/ifCond}}><?php _e( 'bigger than', 'wp2leads' ) ?></option>
            <option value="smaller" {{#ifCond operator
            '==' "smaller"}}selected{{/ifCond}}><?php _e( 'less than', 'wp2leads' ) ?></option>
        </select>

        <input type="text" name="comparison[{{index}}][string]" class="table-column-string" value="{{string}}"
               {{disabled}}>

        {{#unless disabled}}
        <div class="button remove-column-comparison"><?php _e( 'Remove', 'wp2leads' ) ?></div>
        {{/unless}}
    </div>
</script>
<script id="wp2l-sample-map-results-processing" type="text/x-handlebars-template">
    <tbody>
    <tr>
        <td>
            <span class="spinner is-active" style="float: none; margin: 10px;"></span>
        </td>
    </tr>
    </tbody>
</script>
<script id="wp2l-tag-prefix-warning-noprefix" type="text/x-handlebars-template">
    <p class="warning-text tagPrefixWarning__noprefix">
        <span>
            <i>
                <?php _e( 'If you ever want to connect more than one website with same, similar tags please choose a prefix (f.e. <strong>web1</strong>). So the tags are clean separated.', 'wp2leads' ); ?>
            </i>
        </span>
    </p>
</script>
<script id="wp2l-tag-prefix-warning-globalchange" type="text/x-handlebars-template">
    <p class="warning-text tagPrefixWarning__globalchange">
        <span>
            <i>
                <?php _e( 'New tags with new prefix will be created and sent to user of all maps/current map.', 'wp2leads' ); ?>
                <?php _e( 'Please go through all conditions and update to the tag name with new prefix.', 'wp2leads' ); ?>
            </i>
        </span>
    </p>
</script>
<script id="wp2l-api-tag-options" type="text/x-handlebars-template">
    {{#each availableOptions}}
    <option value="{{this.value}}">{{this.label}}</option>
    {{/each}}
</script>
<script id="wp2l-api-default-optin-text" type="text/x-handlebars-template">
    <?php echo __( 'Default Opt-In Process', 'wp2leads' ); ?>
</script>
<script id="wp2l-api-multiple-autotags-add-set" type="text/x-handlebars-template">
    <div class="multiple-autotag-item {{valueType}}">
        <div class="multiple-autotag-inner">
            <div class="multiple-autotags-add-conditions" style="margin-bottom: 5px">
                <div class="conditions-list"></div>

                <button class="button add-condition-for-multiple-autotags" data-type="add">
                    + <?php _e( 'Add Condition', 'wp2leads' ) ?>
                </button>
            </div>

            <div class="multiple-autotags-single multiple-autotags-value">
                <div class="wptl-row">
                    <div class="wptl-col-xs-12">
                        <h3 style="margin-top:5px;margin-bottom:5px;font-size:15px;"><?php echo _e( 'For single values:', 'wp2leads' ); ?></h3>
                    </div>

                    <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-3">
                        <div class="wptl-row">
                            <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-5">
                                <div class="field_label" style="margin-top:5px;margin-bottom:5px;"><?php _e( 'Prefix', 'wp2leads' ) ?></div>
                            </div>

                            <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-7">
                                <input type="text" name="multiple-autotags-single-prefix" value="" class="form-control small-form-control">
                            </div>
                        </div>
                    </div>

                    <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-9">
                        <div class="wptl-row">
                            <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-2">
                                <div class="field_label" style="margin-top:5px;margin-bottom:5px;"><?php _e( 'Options', 'wp2leads' ) ?></div>
                            </div>

                            <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-10">
                                <select class="multiple-autotags-options-list" multiple>
                                    {{#each availableOptions}}
                                    <option value="{{this.value}}">{{this.label}}</option>
                                    {{/each}}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="multiple-autotags-concat multiple-autotags-value">
                <div class="wptl-row">
                    <div class="wptl-col-xs-12">
                        <h3 style="margin-top:15px;margin-bottom:5px;font-size:15px;"><?php echo _e( 'For concatenated values:', 'wp2leads' ); ?></h3>
                    </div>

                    <div class="wptl-col-xs-12">
                        <div class="wptl-row">
                            <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-2">
                                <div class="field_label" style="margin-top:5px;margin-bottom:5px;"><?php _e( 'Prefix', 'wp2leads' ) ?></div>
                                <input type="text" name="multiple-autotags-concat-prefix" value="" class="form-control small-form-control">
                            </div>

                            <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-4">
                                <div class="field_label" style="margin: 5px 0;"><?php _e( 'Filter values', 'wp2leads' ) ?></div>
                                <input type="text" style="margin-top:5px;margin-bottom:5px;" name="multiple-autotags-concat-filter" value="" class="form-control small-form-control">

                                <div class="field_label" style="margin: 5px 0;">
                                    <label><input type="checkbox" value="1" name="multiple-autotags-concat-filter-type"> <?php _e( 'Only transfer these (otherwise do not transfer these)', 'wp2leads' ) ?></label>
                                </div>
                            </div>

                            <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-6">
                                <div class="field_label" style="margin-top:5px;margin-bottom:5px;"><?php _e( 'Options', 'wp2leads' ) ?></div>
                                <select class="multiple-autotags-options-concat-list" multiple>
                                    {{#each availableOptions}}
                                    <option value="{{this.value}}">{{this.label}}</option>
                                    {{/each}}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="multiple-autotags-separator multiple-autotags-value">
                <div class="wptl-row simple-auto-tags">
                    <div class="wptl-col-xs-12">
                        <h3 style="margin-top:10px;margin-bottom:5px;font-size:15px;"><?php echo _e( 'For custom separated values:', 'wp2leads' ); ?></h3>
                    </div>

                    <div class="wptl-col-xs-12">
                        <div class="multiple-autotags-add-separators">
                            <div class="separator-wrapper">
                                <div class="wptl-row">
                                    <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-2">
                                        <div class="wptl-row">
                                            <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-12">
                                                <div class="separator-holder" style="margin-bottom: 10px;">
                                                    <div class="field_label"><?php _e( 'Prefix', 'wp2leads' ) ?>:</div>
                                                    <input type="text" name="multiple-autotags-add-separators-prefix" value="" class="form-control small-form-control">
                                                </div>
                                            </div>

                                            <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-12">
                                                <div class="separator-holder">
                                                    <div class="field_label"><?php echo _e( 'Separator', 'wp2leads' ); ?>:</div>
                                                    <input type="text" class="form-control small-form-control" name="separator" value="">

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-4">
                                        <div class="separator-holder">
                                            <div class="field_label"><?php _e( 'Filter values', 'wp2leads' ) ?>:</div>
                                            <input type="text" name="multiple-autotags-add-separators-filter" value="" class="form-control small-form-control">

                                            <div class="field_label" style="margin: 5px 0;">
                                                <label><input type="checkbox" value="1" name="multiple-autotags-add-separators-filter-type"> <?php _e( 'Only transfer these (otherwise do not transfer these)', 'wp2leads' ) ?></label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-6">
                                        <div class="field_label"><?php _e( 'Options', 'wp2leads' ) ?>:</div>
                                        <select class="multiple-autotags-options-separator-list" multiple>
                                            {{#each availableOptions}}
                                            <option value="{{this.value}}">{{this.label}}</option>
                                            {{/each}}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <div class="button button-danger button-remove remove-multiple-autotag-item">&times;</div>
        </div>
    </div>
</script>
<script id="wp2l-api-multiple-autotags-add-condition-set" type="text/x-handlebars-template">
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

        <div class="button button-danger button-remove remove-multiple-autotags-condition">&times;</div>
    </div>
</script>
<script id="wp2l-api-autotags-add-condition-set" type="text/x-handlebars-template">
    <div class="condition">
        <div class="wptl-row">
            <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-4">
                <div class="option-select-holder">
                    <div class="api_field_box" style="margin-bottom: 0;">
                        <div class="api_field_head">
                            <p class="field_label"><?php _e( 'if value in', 'wp2leads' ) ?></p>
                            <p class="field_value"><?php _e( 'Choose an option', 'wp2leads' ) ?></p>
                        </div>
                        <div class="api_field_body">
                            <select class="options_where" name="option" multiple>
                                {{#each availableOptions}}
                                <option value="{{this.value}}">{{this.label}}</option>
                                {{/each}}
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-8">
                <div class="wptl-row">
                    <div class="wptl-col-xs-12 wptl-col-sm-6 wptl-col-md-12 wptl-col-lg-6">
                        <div class="condition-operator-string-holder">
                            <select name="operator" class="form-control" style="margin: 2px 0;">
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
                    </div>

                    <div class="wptl-col-xs-12 wptl-col-sm-6 wptl-col-md-12 wptl-col-lg-6">
                        <div class="condition-operator-string-holder">
                            <input type="text" name="string" value="" class="form-control" style="margin: 2px 0;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="removeAutotagsCondition" class="button button-danger button-remove">&times;</div>
    </div>
</script>
<script id="wp2l-api-autotags-detach-condition-set" type="text/x-handlebars-template">
    <div class="condition">
        <div class="wptl-row">
            <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-4">
                <div class="option-select-holder">
                    <div class="api_field_box" style="margin-bottom: 0;">
                        <div class="api_field_head">
                            <p class="field_label"><?php _e( 'if value in', 'wp2leads' ) ?></p>
                            <p class="field_value"><?php _e( 'Choose an option', 'wp2leads' ) ?></p>
                        </div>
                        <div class="api_field_body">
                            <select class="options_where" name="option" multiple>
                                {{#each availableOptions}}
                                <option value="{{this.value}}">{{this.label}}</option>
                                {{/each}}
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-8">
                <div class="wptl-row">
                    <div class="wptl-col-xs-12 wptl-col-sm-6 wptl-col-md-12 wptl-col-lg-6">
                        <div class="condition-operator-string-holder">
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

                    </div>
                    <div class="wptl-col-xs-12 wptl-col-sm-6 wptl-col-md-12 wptl-col-lg-6">
                        <div class="condition-operator-string-holder">
                            <input type="text" name="string" value="" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="removeAutotagsCondition" class="button button-danger button-remove">&times;</div>
    </div>
</script>
<script id="wp2l-api-optins-condition-set" type="text/x-handlebars-template">
    <div class="condition">
        <div>
            <div class="text"><?php _e( 'Use', 'wp2leads' ) ?></div>
            <select name="optins" class="form-control">
                {{#each connectTo as |connection index|}}
                <option value="{{connection.code}}">{{connection.value}}</option>
                {{/each}}
            </select>
        </div>
        <div>
            <div class="api_field_box">
                <div class="api_field_head">
                    <p class="field_label"><?php _e( 'if value in', 'wp2leads' ) ?></p>
                    <p class="field_value"><?php _e( 'Choose an option', 'wp2leads' ) ?></p>
                </div>
                <div class="api_field_body">
                    <select class="options_where" name="option" multiple>
                        {{#each availableOptions}}
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

            <div>
                <input type="text" name="string" value="{{string}}" class="form-control">
            </div>
        </div>
        <div class="button button-danger remove-api-connection button-remove">&times;</div>
    </div>
</script>
<script id="wp2l-api-tags-condition-set" type="text/x-handlebars-template">
    <div class="condition">
        <div>
            <div class="text"><?php _e( 'Use', 'wp2leads' ) ?></div>
            <select name="tags-add" class="form-control">
                {{#each connectTo as |connection index|}}
                <option value="{{connection.code}}">{{connection.value}}</option>
                {{/each}}
            </select>
        </div>
        <div>
            <div class="api_field_box">
                <div class="api_field_head">
                    <p class="field_label"><?php _e( 'if value in', 'wp2leads' ) ?></p>
                    <p class="field_value"><?php _e( 'Choose an option', 'wp2leads' ) ?></p>
                </div>
                <div class="api_field_body">
                    <select class="options_where" name="option" multiple>
                        {{#each availableOptions}}
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

            <div>
                <input type="text" name="string" value="{{string}}" class="form-control">
            </div>
        </div>
        <div class="button button-danger remove-api-connection button-remove">&times;</div>
    </div>
</script>
<script id="wp2l-api-tags-separator-set" type="text/x-handlebars-template">
    <div class="separator-wrapper">
        <div class="separator-inner">
            <div class="wptl-row">
                <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-6">
                    <div class="wptl-row">
                        <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-4">
                            <div class="wptl-row">
                                <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-12">

                                    <div class="separator-holder" style="margin-bottom: 10px;">
                                        <div class="field_label"><?php _e( 'Prefix', 'wp2leads' ) ?>:</div>
                                        <input type="text" name="separator-prefix" value="" class="form-control small-form-control">
                                    </div>
                                </div>
                                <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-12">
                                    <div class="separator-holder">
                                        <div class="field_label"><?php _e( 'Separator', 'wp2leads' ) ?>:</div>
                                        <input type="text" name="separator" value="" class="form-control small-form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-8">
                            <div class="separator-holder">
                                <div class="field_label"><?php _e( 'Filter values', 'wp2leads' ) ?>:</div>
                                    <input type="text" name="separator-filter" value="" class="form-control small-form-control">
                                    <div class="field_label" style="margin: 5px 0 10px 0;">
                                        <label><input type="checkbox" value="1" name="separator-filter-type"> <?php _e( 'Only transfer these (otherwise do not transfer these)', 'wp2leads' ) ?></label>
                                    </div>
                                    <div class="separator-description">
                                        <p style="margin: 3px 0">
                                            <?php _e( 'Input one or more values for filter separated by "||"', 'wp2leads' ) ?>
                                            <br>
                                            <?php _e( 'F.e.', 'wp2leads' ) ?>: rate-1||rate-2||rate-3||rate-4||rate5
                                        </p>
                                    </div>
                            </div>
                        </div>
                    </div>

                    <div class="wptl-row">
                        <div class="wptl-col-xs-12">
                            <div style="height: 5px;"></div>
                        </div>
                    </div>
                </div>
                <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-6">
                    <div class="option-holder">
                        <div class="api_field_box">
                            <div class="api_field_head">
                                <p class="field_label"><?php _e( 'for values in option', 'wp2leads' ) ?></p>
                                <p class="field_value"><?php _e( 'Choose an option', 'wp2leads' ) ?></p>
                            </div>
                            <div class="api_field_body">
                                <select class="options_where" name="option" multiple>
                                    {{#each availableOptions}}
                                    <option value="{{this.value}}">{{this.label}}</option>
                                    {{/each}}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="button button-danger remove-api-separator button-remove">&times;</div>
    </div>
</script>
<script id="wp2l-api-tags-condition-detach" type="text/x-handlebars-template">
    <div class="condition">
        <div>
            <div class="wptl-row">
                <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-6">
                    <div class="wptl-row">
                        <div class="wptl-col-xs-12 wptl-col-sm-6 wptl-col-md-5 wptl-col-lg-4">
                            <div class="wptl-row">
                                <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-12">
                                    <div class="text" style="margin: 4px 0"><?php _e( 'Prefix', 'wp2leads' ) ?></div>
                                </div>
                                <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-12">
                                    <input type="text" name="tags-detach-prefix" value="" class="form-control small-form-control">
                                </div>
                            </div>
                        </div>

                        <div class="wptl-col-xs-12 wptl-col-sm-6 wptl-col-md-7 wptl-col-lg-8">
                            <div class="wptl-row">
                                <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-12">
                                    <div class="text" style="margin: 4px 0"><?php _e( 'Detach this tag:', 'wp2leads' ) ?></div>
                                </div>
                                <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-12 wptl-col-lg-12">
                                    <select name="tags-detach" class="form-control">
                                        {{#each connectTo as |connection index|}}
                                        <option value="{{connection.code}}">{{connection.value}}</option>
                                        {{/each}}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-5 wptl-col-lg-3">
                    <div>
                        <div class="api_field_box">
                            <div class="api_field_head">
                                <p class="field_label"><?php _e( 'if value in', 'wp2leads' ) ?></p>
                                <p class="field_value"><?php _e( 'Choose an option', 'wp2leads' ) ?></p>
                            </div>
                            <div class="api_field_body">
                                <select class="options_where" name="option" multiple>
                                    {{#each availableOptions}}
                                    <option value="{{this.value}}">{{this.label}}</option>
                                    {{/each}}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-7 wptl-col-lg-3">
                    <div class="wptl-row">
                        <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-12">
                            <select name="operator" class="form-control" style="margin: 2px 0">
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

                        <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6 wptl-col-lg-12">
                            <input type="text" name="string" value="{{string}}" class="form-control" style="margin: 2px 0">
                        </div>
                    </div>

                </div>
            </div>


            <div class="button button-danger remove-api-connection button-remove">&times;</div>
        </div>
    </div>
</script>
<script id="wp2l-simple-modal" type="text/x-handlebars-template">
    <div id='modal-overlay'>
        <div id='modal'>
            <div id='content'></div>
            <span id='modal-close'><span class="dashicons dashicons-no"></span></span>
        </div>
    </div>
</script>
<script id="wp2l-license-form" type="text/x-handlebars-template">
    <div id="license_modal_holder">
        <form>
            <table style="width: 100%">
                <thead>
                <tr>
                    <th></th>
                    <th><h3><?php _e( 'Information about license', 'wp2leads' ) ?></h3></th>
                </tr>
                </thead>

                <tbody>
                <tr>
                    <td><?php _e( 'Email', 'wp2leads' ) ?></td>
                    <td><input type="text" style="width: 100%;" id="wp2l-modal-license-email" name="wp2l-license-email"
                               value=""></td>
                </tr>
                <tr>
                    <td><?php _e( 'License key', 'wp2leads' ) ?></td>
                    <td><input type="text" style="width: 100%;" id="wp2l-modal-license-key" name="wp2l-license-key"
                               value=""></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button id="wp2lActivateModal" class="button button-primary" data-action="activation"
                                type="button"><?php _e( 'Activate', 'wp2leads' ) ?></button>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="error-message-holder"></td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
</script>
<script id="wp2l-license-page-link" type="text/x-handlebars-template">
    <p><?php _e( 'Please, visit <a href="?page=wp2l-admin&tab=settings">Settings Page</a>', 'wp2leads' ) ?></p>
</script>


<script id="wp2l-magic-import-modal" type="text/x-handlebars-template">
    <div class="transfer-data-modal magic-transfer">
        <h2><?php _e( 'Map Import', 'wp2leads' ) ?></h2>
        <div class="notice_holder"></div>

        <div class="main-wrapper api-processing-holder">
            <div class="side transfer-info">
                <div class="inner">
                    <hr>
                    <h3><?php _e( 'Name of the map', 'wp2leads' ); ?></h3>
                    <p>
						<input type="text" name="magic-name" id="magicName" value="">
					</p>
					<label class="magic-name-checkbox">
						<input type="checkbox" class="mnche" checked="checked">
						<span><?php _e('Create form name title as tag and sent tag on each transfer', 'wp2leads'); ?></span>
					</label>
					<span class="magic-title-tag tag-name" style="display: none;"></span>
					<hr>
					<h3><?php _e( 'Choose one or more fields from which values you want to create tags (optional).', 'wp2leads' ); ?></h3>
					<div class="magic-tags-choose">
						<div class="tags-header">
							<label class="check-all"><input type="checkbox" class="check-all-checkbox"><?php _e('All', 'wp2leads'); ?></label>
							<label class="label-name"><input type="radio" name="label-name" value="label" class="general-magic-radio" checked="checked"><?php _e('Label', 'wp2leads'); ?></label>
							<label class="label-name"><input type="radio" name="label-name" value="name" class="general-magic-radio"><?php _e('Name', 'wp2leads'); ?></label>
						</div>
						<div class="tags-preset"></div>
					</div>
                </div>
            </div>


            <div class="api-spinner-holder api-processing">
                <div class="api-spinner"></div>
            </div>
        </div>

        <div class="buttons-wrapper">
            <button id="magicSave" class="button button-primary magic-import-button">
                <?php _e( 'Continue with create Tags', 'wp2leads' ) ?>
            </button>
        </div>

        <div class="close">&times;</div>
    </div>
    <div class="gray-back"></div>
</script>

<script id="wp2l-plugins-install-modal" type="text/x-handlebars-template">
    <div class="wp2l-plugins-install-modal">
        <h2><?php _e( 'Install plugins', 'wp2leads' ) ?></h2>
        <div class="notice_holder"></div>

        <div class="main-wrapper api-processing-holder">
            <div class=" transfer-info">
                <div class="inner">
                    <hr>
                    <h3><?php _e( 'Required plugins', 'wp2leads' ); ?></h3>
					<div class="required-plugins"><?php _e('Checking plugins...', 'wp2leads'); ?></div>
					<hr>
					<h3><?php _e( 'Recommend plugins', 'wp2leads' ); ?></h3>
                    <div class="recommend-plugins"><?php _e('Checking plugins...', 'wp2leads'); ?></div>
                </div>
            </div>

			<div class="buttons-wrapper" >
				<button id="installPlugins" class="button button-primary install-plugins-button" style="display: none;">
					<?php _e( 'Install', 'wp2leads' ) ?>
				</button>
				<button id="skipPlugins" class="button button-secondary install-plugins-button" style="display: none;">
					<?php _e( 'No, thanks', 'wp2leads' ) ?>
				</button>
			</div>

            <div class="api-spinner-holder api-processing">
                <div class="api-spinner"></div>
            </div>

        </div>



        <div class="close">&times;</div>
    </div>
    <div class="gray-back"></div>
</script>

<script id="wp2l-show-video" type="text/x-handlebars-template">
    <div class="wp2l-plugins-install-modal wp2l-plugins-show-video">
        <div class="main-wrapper api-processing-holder">
			<video class="modal-video" controls="controls">
			   <source src="{{video}}" type="video/mp4;">
			</video>
        </div>
        <div class="close">&times;</div>
    </div>
    <div class="gray-back"></div>
</script>

<script id="wp2l-magic-tags-settings-modal" type="text/x-handlebars-template">
    <div class="transfer-data-modal magic-transfer">
        <h2><?php _e( 'Auto create & Replace tags', 'wp2leads' ); ?></h2>
        <div class="notice_holder"></div>
		<input type="hidden" name="magic-name" id="magicName" value="">

        <div class="main-wrapper api-processing-holder">
            <div class="side transfer-info">
                <div class="inner">
                    <hr>
					<h3><?php _e( 'Check tags settings for map:', 'wp2leads' ); ?></h3>
					<div class="magic-tags-choose">
						<div class="tags-header">
							<label class="check-all"><input type="checkbox" class="check-all-checkbox"><?php _e('All', 'wp2leads'); ?></label>
							<label class="label-name"><input type="radio" name="label-name" value="label" class="general-magic-radio" checked="checked"><?php _e('Label', 'wp2leads'); ?></label>
							<label class="label-name"><input type="radio" name="label-name" value="name" class="general-magic-radio"><?php _e('Name', 'wp2leads'); ?></label>
						</div>
						<div class="tags-preset"></div>
					</div>
                </div>
            </div>

            <div class="api-spinner-holder api-processing">
                <div class="api-spinner"></div>
            </div>
        </div>

        <div class="buttons-wrapper">
            <button id="magicSave" class="button button-primary magic-import-button">
                <?php _e( 'Save', 'wp2leads' ) ?>
            </button>
			<span class="magic-save-info">
				<span class="dashicons dashicons-warning"></span>
				<?php _e( 'We will update your current tags where it is possible instead of creation new tags.', 'wp2leads' ); ?>
			</div>
        </div>

        <div class="close">&times;</div>
    </div>
    <div class="gray-back"></div>
</script>
