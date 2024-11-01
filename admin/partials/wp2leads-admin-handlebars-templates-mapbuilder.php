<?php
/**
 * Created by PhpStorm.
 * Date: 2/4/18
 * Time: 9:00 AM
 */
?>
<script id="wp2l-map-builder-user-input-recomended-tags-item" type="text/x-handlebars-template">
    <div class="user-input-recomended-tags-item">
        <div class="user-input-recomended-tags-settings">
            <div class="user-input-recomended-tags-row">
                <div class="user-input-recomended-tags-label">
                    <?php _e( 'Tags Set Title', 'wp2leads' ) ?>
                </div>

                <div class="user-input-recomended-tags-setting recomended-tags-title-holder">
                    <div class="wptl-row">
                        <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6">
                            <input data-value="{{title}}" type="text" name="recomended-tags-title" class="recomended-tags-title form-control" value="{{title}}">
                        </div>

                        <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-6">
                            <div class="wptl-row">
                                <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-4">
                                    <p><?php _e( 'Prefix', 'wp2leads' ) ?></p>
                                </div>

                                <div class="wptl-col-xs-12 wptl-col-sm-12 wptl-col-md-8">
                                    <input data-value="{{prefix}}" type="text" name="recomended-tags-prefix" class="recomended-tags-prefix form-control" value="{{prefix}}">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="user-input-recomended-tags-row">
                <div class="user-input-recomended-tags-label">
                    <?php _e( 'Table From', 'wp2leads' ) ?>
                </div>

                <div class="user-input-recomended-tags-setting recomended-tags-from-table-holder">
                    <select data-value="" class="recomended-tags-from-table form-control form-control-medium" style="min-width:200px" name="recomended-tags-from-table">
                        <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
                        {{#each availableTables}}
                        <option value="{{this}}">{{this}}</option>
                        {{/each}}
                    </select>
                </div>
            </div>
            <div class="user-input-recomended-tags-row">
                <div class="user-input-recomended-tags-label">
                    <?php _e( 'Group By', 'wp2leads' ) ?>
                </div>

                <div class="user-input-recomended-tags-setting recomended-tags-group-by-holder">
                    <select data-value="" class="recomended-tags-group-by form-control form-control-medium" style="min-width:200px" name="recomended-tags-group-by" disabled>
                        <option value=""><?php _e( '-- Select Table From First --', 'wp2leads' ) ?></option>
                    </select>
                </div>
            </div>
            <hr>
            <div class="user-input-recomended-tags-row">
                <div class="user-input-recomended-tags-label">
                    <?php _e( 'Joined tables', 'wp2leads' ) ?>
                </div>

                <div class="user-input-recomended-tags-setting recomended-tags-joined-tables-holder">
                    <div class="recomended-tags-joined-tables-list"></div>

                    <button id="addUserInputTagJoin" type="submit" class="button">+ <?php _e('Add', 'wp2leads') ?></button>
                </div>
            </div>
            <hr>
            <div class="user-input-recomended-tags-row">
                <div class="user-input-recomended-tags-label">
                    <?php _e( 'Comparison', 'wp2leads' ) ?>
                </div>

                <div class="user-input-recomended-tags-setting recomended-tags-comparisons-holder">
                    <div class="recomended-tags-comparisons-list"></div>

                    <button id="addUserInputTagComparison" type="submit" class="button">+ <?php _e('Add', 'wp2leads') ?></button>
                </div>
            </div>
            <hr>
            <div class="user-input-recomended-tags-row">
                <div class="user-input-recomended-tags-label">
                    <?php _e( 'Tag Column', 'wp2leads' ) ?>
                </div>

                <div class="user-input-recomended-tags-setting recomended-tags-columns-holder">
                    <select data-value="" class="recomended-tags-columns form-control form-control-inline form-control-medium" name="recomended-tags-columns" style="min-width:200px" disabled>
                        <option value=""><?php _e( '-- Select Table From First --', 'wp2leads' ) ?></option>
                    </select>
                </div>
            </div>
            <hr>
            <div class="user-input-recomended-tags-row">
                <div class="user-input-recomended-tags-label">
                    <?php _e( 'Results summary', 'wp2leads' ) ?>
                </div>

                <div class="user-input-recomended-tags-setting recomended-tags-result-holder">
                    <div class="recomended-tags-results"></div>
                    <div class="recomended-tags-results-messages"></div>
                    <button id="getUserInputTagResults" class="button button-primary"><?php _e( 'Get Results', 'wp2leads' ) ?></button>
                </div>
            </div>
        </div>

        <button class="button button-danger button-remove remove-user-input-recomended-tags-item">&times;</button>
    </div>
</script>

<script id="wp2l-map-builder-user-input-recomended-tags-joined-tables-item" type="text/x-handlebars-template">
    <div class="recomended-tags-joined-tables-item">
        <div class="recomended-tags-joined-tables-settings">
            <select data-value="" class="recomended-tags-ref-table form-control form-control-inline form-control-medium" name="recomended-tags-ref-table" disabled>
                {{#unless onload}}
                <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
                {{/unless}}
                {{#each existingTableChoices}}
                <option value="{{this}}">{{this}}</option>
                {{/each}}
            </select> . <select data-value="" class="recomended-tags-ref-column form-control form-control-inline form-control-medium" name="recomended-tags-ref-column" disabled>
                {{#unless onload}}
                <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
                {{/unless}}
            </select> - <select data-value="" class="recomended-tags-join-table form-control form-control-inline form-control-medium" style="max-width:100%" name="recomended-tags-join-table" disabled>
                {{#unless onload}}
                <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
                {{/unless}}
                {{#each availableTables}}
                <option value="{{this}}">{{this}}</option>
                {{/each}}
            </select> . <select data-value="" class="recomended-tags-join-column form-control form-control-inline form-control-medium" name="recomended-tags-join-column" disabled>
                {{#unless onload}}
                <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
                {{/unless}}
            </select>
        </div>

        <button class="button button-danger button-remove remove-recomended-tags-joined-tables-item">&times;</button>
    </div>
</script>

<script id="wp2l-map-builder-user-input-recomended-tags-comparisons-item" type="text/x-handlebars-template">
    <div class="recomended-tags-comparisons-item">
        <div class="recomended-tags-comparisons-settings">
            <select data-value="" class="recomended-tags-comparison-column form-control form-control-inline form-control-medium" name="recomended-tags-comparison-column" disabled>
                {{#unless onload}}
                <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
                {{/unless}}
                {{#each allColumns}}
                <option value="{{this}}">{{this}}</option>
                {{/each}}
            </select> <select data-value="{{operator}}" class="recomended-tags-comparison-operator form-control form-control-inline form-control-medium" name="recomended-tags-comparison-operator" disabled>
                {{#unless onload}}
                <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
                {{/unless}}
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
            </select> <input data-value="{{string}}" type="text" name="recomended-tags-comparison-string" class="recomended-tags-comparison-string form-control form-control-inline form-control-medium" value="{{string}}" disabled>
        </div>

        <button class="button button-danger button-remove remove-recomended-tags-comparisons-item">&times;</button>
    </div>
</script>

<script id="wp2l-empty-select-val" type="text/x-handlebars-template">
    <option value=""><?php _e( '-- Select --', 'wp2leads' ) ?></option>
</script>

<script id="wp2l-map-builder-user-input-recomended-tags-message-select-from-table" type="text/x-handlebars-template">
    <p><small><?php _e( 'Select Group By Column', 'wp2leads' ) ?></small></p>
</script>

<script id="wp2l-map-builder-user-input-recomended-tags-message-select-group-by" type="text/x-handlebars-template">
    <p><small><?php _e( 'Select Group By Column', 'wp2leads' ) ?></small></p>
</script>

<script id="wp2l-map-builder-user-input-recomended-tags-message-select-tag-column" type="text/x-handlebars-template">
    <p><small><?php _e( 'Select Tag Column', 'wp2leads' ) ?></small></p>
</script>