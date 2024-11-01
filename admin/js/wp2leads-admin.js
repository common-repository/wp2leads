Handlebars.registerHelper('ifCond', function (v1, operator, v2, options) {

    switch (operator) {
        case '==':
            return (v1 == v2) ? options.fn(this) : options.inverse(this);
        case '===':
            return (v1 === v2) ? options.fn(this) : options.inverse(this);
        case '!=':
            return (v1 != v2) ? options.fn(this) : options.inverse(this);
        case '!==':
            return (v1 !== v2) ? options.fn(this) : options.inverse(this);
        case '<':
            return (v1 < v2) ? options.fn(this) : options.inverse(this);
        case '<=':
            return (v1 <= v2) ? options.fn(this) : options.inverse(this);
        case '>':
            return (v1 > v2) ? options.fn(this) : options.inverse(this);
        case '>=':
            return (v1 >= v2) ? options.fn(this) : options.inverse(this);
        case '&&':
            return (v1 && v2) ? options.fn(this) : options.inverse(this);
        case '||':
            return (v1 || v2) ? options.fn(this) : options.inverse(this);
        case 'istypeof':
            return (typeof v1 == v2) ? options.fn(this) : options.inverse(this);
        case 'indexOf':
            v1 = v1 + "";
            return (v1.indexOf(v2) !== -1) ? options.fn(this) : options.inverse(this);
        default:
            return options.inverse(this);
    }
});

Handlebars.registerHelper('dotToBr', function (columnHeader) {
    var split = columnHeader.split('.');
    return new Handlebars.SafeString(split[0] + "<br>" + split[1]);
});

Handlebars.registerHelper('everyNth', function(context, every, options) {
    var fn = options.fn, inverse = options.inverse;
    var ret = "";
    if(context && context.length > 0) {
        for(var i=0, j=context.length; i<j; i++) {
            var modZero = i % every === 0;
            ret = ret + fn(_.extend({}, context[i], {
                isModZero: modZero,
                isModZeroNotFirst: modZero && i > 0,
                isLast: i === context.length - 1
            }));
        }
    } else {
        ret = inverse(this);
    }
    return ret;
});

function debounce(func, wait, immediate) {
    var timeout;
    return function () {
        var context = this, args = arguments;

        var later = function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };

        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

(function ($) {
    'use strict';

    window.onLoadFirst = true;
    window.setOnLoad = {};
    window.setOnChange = {};
    window.refreshTimer = false;
    window.activeMapping = $_GET('active_mapping');
    window.mapResults = [];

    var iterationLimit = ktAdminObject.iteration_limit;

    $.fn.hasAttr = function(name) {
        return this.attr(name) !== undefined;
    };

    var previousLimitValue = $('#map-sample-results-limit').val();
    var comparisonFieldsTemplate = Handlebars.compile($('#wp2l-comparison-set')[0].innerHTML);
    var relationshipFieldsTemplate = Handlebars.compile($('#wp2l-relationship-map-fields')[0].innerHTML);

    $(document.body).on('wp2lead_block_mapbuilder', function() {
        blockMapBuilderFields();
    });

    $(document.body).on('wp2lead_unblock_mapbuilder', function() {
        unblockMapBuilderFields();
    });

    $(document.body).on('wp2lead_block_search_results', function() {
        blockSearchResults();
    });

    $(document.body).on('wp2lead_unblock_search_results', function() {
        unblockSearchResults();
    });

    /**
     * ===============================
     *  Table search Events
     * ===============================
     */

    /** Submit table search form */
    $(document.body).on('submit', '#wp2l-table-search-form', function(e) {
        blockSearchResults();
        e.preventDefault();

        var searchForm = $(this);
        var table = searchForm.find('.wp2l-search-table').val(),
            searchFormOptionDefault = searchForm.find('option[value=""]');

        setTimeout(function () {
            if (table) {
                var data = {action: 'wp2l_global_table_search_results', 'table': table};

                getTableSearchResultPromise(data).then(function(response) {
                    var decoded = $.parseJSON(response);

                    renderTableSearchResult(table, decoded, null);

                    searchFormOptionDefault.attr('selected', true);
                    unblockSearchResults();
                });
            } else {
                unblockSearchResults();
            }
        }, 100);
    });

    /** Open table from search by string table */
    $(document.body).on('click', '.wp2l-table-search-view', function(e) {
        e.preventDefault();
        blockSearchResults();

        var table = $(this).data('table');

        setTimeout(function() {
            if (table) {
                var data = {action: 'wp2l_global_table_search_results', 'table': table};

                getTableSearchResultPromise(data).then(function(response) {
                    var decoded = $.parseJSON(response);

                    var existed = $(document.body).find('#table-search-result-' + table);

                    if(existed.length > 0) {
                        existed.remove();
                    }

                    renderTableSearchResult(table, decoded, null, unblockSearchResults);
                });
            } else {
                unblockSearchResults();
            }
        }, 100);
    });

    /** Find all tables group for plugin by slug */
    $(document.body).on('click', '.wp2l-multi-search-group', function(e) {
        blockSearchResults();
        var tableGroup = $(this).data('table_group');
        var searchForm = $('#wp2l-table-search-form');

        setTimeout(function() {
            searchForm.find('select option').each(function() {
                var table = $(this).val();

                var isGroup = table.startsWith(tableGroup);

                if (isGroup) {
                    var data = {action: 'wp2l_global_table_search_results', 'table': table};

                    getTableSearchResultPromise(data).then(function(response) {
                        var decoded = $.parseJSON(response);

                        var existed = $(document.body).find('#table-search-result-' + table);

                        if(existed.length > 0) {
                            existed.remove();
                        }

                        renderTableSearchResult(table, decoded, null);
                    });
                }

                unblockSearchResults();
            });
        }, 100);
    });

    $(document.body).on('click', '.table-search-result-table .sorting-holder .dashicons-arrow-up-alt2, .table-search-result-table .sorting-holder .dashicons-arrow-down-alt2', function(e) {
        var button = $(this),
            sortingHolder = button.parent(),
            resultHolder = button.parents('.wp2l-table-search-result'),
            resultHolderPrev = resultHolder.prev(),
            tableHeader = resultHolder.find('.table-search-result-table thead th'),
            order = null,
            table = resultHolder.find('.wp2l-table-search-close').data('table'),
            column = button.parents('th').data('column');

        blockSearchResults();

        setTimeout(function() {
            var after = null;

            if (resultHolderPrev.length > 0) {
                after = resultHolderPrev;
            }

            tableHeader.find('.sorting-holder').removeClass('sorting-asc').removeClass('sorting-desc');

            if (button.hasClass('dashicons dashicons-arrow-up-alt2')) {
                order = 'asc';
            } else if (button.hasClass('dashicons-arrow-down-alt2')) {
                order = 'desc';
            }

            var data = {action: 'wp2l_global_table_search_results', 'table': table, 'column': column, 'order': order};

            getTableSearchResultPromise(data).then(function(response) {
                var decoded = $.parseJSON(response),
                    existed = $(document.body).find('#table-search-result-' + table);

                if(existed.length > 0) {
                    existed.remove();
                }

                renderTableSearchResult(table, decoded, after, function() {
                    var newSortedTable = $(document.body).find('#table-search-result-' + table);
                    newSortedTable.find('thead .header-' + column + ' .sorting-holder').addClass('sorting-' + order);
                    unblockSearchResults();
                });
            });
        }, 10000);
    });

    $(document.body).on('click', '.wp2l-table-search-close', function() {
        var button = $(this),
            table = button.data('table'),
            result = button.parents('.wp2l-table-search-result');

        blockSearchResults();

        setTimeout(function() {
            $(document.body).find('#wp2l-table-search-form option[value="' + table + '"]').attr('disabled', false);
            result.remove();
            unblockSearchResults();
        }, 100);
    });

    /**
     * ===============================
     *  Miltisearch Events
     * ===============================
     */
    $(document.body).on('submit', '#wp2l-multi-search-form', function(e) {
        e.preventDefault();
        blockSearchResults();
        var searchString = $('.wp2l-multi-search-string').val(),

            searchResultsHolder = $('#wp2l-multi-search-result-holder'),
            searchResults = searchResultsHolder.find('.wp2l-multi-search-result'),

            singleSearchResultsHolder = $('#wp2l-multi-search-single-result-holder'),
            singleSearchResults = singleSearchResultsHolder.find('.wp2l-multi-search-single-result'),

            searchTagsHolder = $('#wp2l-multi-search-result-tags'),
            searchTags = searchTagsHolder.find('.multi-search-tag'),

            newSearch = true;

        setTimeout(function() {

            searchTags.each(function () {
                var value = $(this).find('.tag-use').text();

                if (value === searchString) {
                    newSearch = false;
                    return newSearch;
                }
            });

            if ( '' !== searchString && newSearch ) {
                var data = {
                    action: 'wp2l_global_multisearch_results',
                    string: searchString
                };

                $.post(ajaxurl, data, function(response) {
                    $('.wp2l-multi-search-string').val('');

                    var seq = searchResults.length + 1;

                    searchResults.removeClass('active');
                    searchTags.removeClass('active');
                    singleSearchResults.removeClass('active');

                    var resultTemplate = Handlebars.compile($('#wp2l-multisearch-results')[0].innerHTML);
                    searchResultsHolder.append(resultTemplate({tables: $.parseJSON(response), seq: seq, searchString: searchString}));

                    var serchTagTemplate = Handlebars.compile($('#wp2l-multisearch-tags')[0].innerHTML);
                    searchTagsHolder.append(serchTagTemplate({tag: searchString, seq: seq}));

                    var tableToScroll = $('#result-seq-' + seq + ' .multisearch-results-table');

                    tableToScroll.floatThead({
                        scrollContainer: function(table){
                            return table.closest('.multisearch-results-table-wrap');
                        }
                    });

                    setUsedTableOnSearchResults();
                    unblockSearchResults();
                });
            } else {
                unblockSearchResults();
            }
        }, 100);
    });

    /** Switch search results */
    $(document.body).on('click', '.multi-search-tag .tag-use', function() {
        var button = $(this);

        blockSearchResults();

        setTimeout(function() {
            var tag = button.parent('.multi-search-tag'),
                seq = tag.data('seq'),
                result = $('#result-seq-' + seq),
                singleResult = $('.single-result-seq-' +  + seq);

            $('.wp2l-multi-search-single-result').removeClass('active');
            $('.wp2l-multi-search-result').removeClass('active');
            $('.multi-search-tag').removeClass('active');
            singleResult.addClass('active');
            result.addClass('active');
            tag.addClass('active');

            unblockSearchResults();
        }, 100);
    });

    /** Delete Search tag and search result table */
    $(document.body).on('click', '.multi-search-tag .tag-close', function() {
        blockSearchResults();

        var button = $(this);

        setTimeout(function() {
            var tag = button.parent('.multi-search-tag'),
                seq = tag.data('seq'),
                result = $('#result-seq-' + seq);

            result.remove();
            tag.remove();
            $('.single-result-seq-' + seq).remove();

            var tags = $('.multi-search-tag');

            if (tags.length > 0) {
                $('.wp2l-multi-search-result').removeClass('active');
                tags.removeClass('active');

                tags.each(function() {
                    tag = $(this);
                    seq = tag.data('seq');
                    result = $('#result-seq-' + seq);

                    $(result).addClass('active');
                    $(tag).addClass('active');
                    $('.single-result-seq-' + seq).addClass('active');

                    return false;
                });
            }

            unblockSearchResults();
        }, 100);
    });

    /** Use this table as starter point */
    $(document.body).on('click', '.wp2l-multi-search-use', function() {
        blockSearchResults();

        var button = $(this);

        setTimeout(function() {
            var table = button.attr('data-table'),
                from_table = $('.wp2l_starter_data');

            from_table.find('option[value="' + table + '"]').prop('selected', true);
            from_table.change();

            unblockSearchResults();
        }, 100);
    });

    /** View database table */
    $(document.body).on('click', '.wp2l-multi-search-view', function() {
        blockSearchResults();

        var button = $(this);

        setTimeout(function() {
            var table = button.data('table'),
                column = button.data('column'),
                string = button.parents('.wp2l-multi-search-result').find('input.search-string-value').val(),
                seq = button.parents('.wp2l-multi-search-result').find('input.sequence-value').val(),
                searchResultsHolder = $('#wp2l-multi-search-single-result-holder'),
                label = '`' + table + '` : ' + string;

            var singleResults = $('.wp2l-multi-search-single-result'),
                singleResultNew = true;

            var row = button.parents('.multisearch-results-table-row'),
                columns = row.find('.column-holder');

            if (singleResults.length > 0) {
                singleResults.each(function() {
                    var resultLabel = $(this).find('.wp2l-multi-search-single-header h4').text();

                    if (resultLabel === label) {
                        singleResultNew = false;

                        return singleResultNew;
                    }
                });
            }

            if (singleResultNew) {
                var data = {
                    action: 'wp2l_single_multisearch_table',
                    table: table,
                    string: string
                };

                $.post(ajaxurl, data, function(response) {
                    var decoded = $.parseJSON(response);
                    var resultTemplate = Handlebars.compile($('#wp2l-multisearch-single-result')[0].innerHTML);
                    searchResultsHolder.prepend(resultTemplate({label: label, column: column, table: table, columns: decoded.columns, seq:seq, results: decoded.results}));

                    var tableToScroll = $('#single-result-' + table + ' .multisearch-single-result-table');

                    var tableValues = tableToScroll.find('tbody tr td div');

                    tableValues.highlight(string);

                    columns.each(function() {
                        var activeColumn = $(this).data('column');
                        var columnHeader = tableToScroll.find('thead tr th.header-' + activeColumn);
                        columnHeader.addClass('active');
                    });

                    unblockSearchResults();
                });
            } else {
                unblockSearchResults();
            }
        }, 100);
    });

    /** Close DB table */
    $(document.body).on('click', '.wp2l-multi-search-single-close', function() {
        blockSearchResults();

        var button = $(this);

        setTimeout(function() {
            var result = button.parents('.wp2l-multi-search-single-result');

            result.remove();

            unblockSearchResults();
        }, 100);
    });

    /** Sort table */
    $(document.body).on('click', '.multisearch-results-table thead th .sort_holder .dashicons', function() {
        blockSearchResults();

        var sortBtn = $(this);

        setTimeout(function() {
            var sortColumn = sortBtn.parents('th.sort-col'),
                sortTableHolder = sortColumn.parents('.wp2l-multi-search-result'),
                sortBy = sortColumn.data('sort'),
                sortOrder,
                sortColumns = $('.multisearch-results-table thead th.sort-col'),
                x, y;

            var switching = true;

            sortColumns.removeClass('sort-asc').removeClass('sort-desc').removeClass('sort-no').addClass('sort-no');

            if (sortBtn.hasClass('dashicons-arrow-up')) {
                sortColumn.addClass('sort-desc');
                sortOrder = 'desc';
            } else if (sortBtn.hasClass('dashicons-arrow-down')) {
                sortColumn.addClass('sort-asc');
                sortOrder = 'asc';
            } else if (sortBtn.hasClass('dashicons-sort')) {
                sortColumn.addClass('sort-desc');
                sortOrder = 'desc';
            }

            while (switching) {
                switching = false;

                var sortRows = sortTableHolder.find('.multisearch-results-table tbody tr');

                for (var i = 0; i < (sortRows.length - 1); i++) {
                    var shouldSwitch = false;

                    if (sortBy === 'byTable') {
                        x =  $(sortRows[i]).find('.table-holder > div').text();
                        y =  $(sortRows[i + 1]).find('.table-holder > div').text();

                        if (sortOrder === 'asc') {
                            if (x.toLowerCase() > y.toLowerCase()) {
                                // If so, mark as a switch and break the loop:
                                shouldSwitch = true;
                                break;
                            }
                        } else if (sortOrder === 'desc') {
                            if (x.toLowerCase() < y.toLowerCase()) {
                                // If so, mark as a switch and break the loop:
                                shouldSwitch = true;
                                break;
                            }
                        }

                    } else if(sortBy === 'byCount') {
                        x =  $(sortRows[i]).data('count');
                        y =  $(sortRows[i + 1]).data('count');

                        if (sortOrder === 'asc') {
                            if (x > y) {
                                // If so, mark as a switch and break the loop:
                                shouldSwitch = true;
                                break;
                            }
                        } else if (sortOrder === 'desc') {
                            if (x < y) {
                                // If so, mark as a switch and break the loop:
                                shouldSwitch = true;
                                break;
                            }
                        }
                    }
                }

                if (shouldSwitch) {
                    sortRows[i].parentNode.insertBefore(sortRows[i + 1], sortRows[i]);
                    switching = true;
                }
            }

            unblockSearchResults();
        }, 100);
    });

    $(document.body).on('click', '#reset-map-builder-form', function (e) {
        e.preventDefault();

        if (confirm(wp2leads_i18n_get('Are you sure you want to completely start over?'))) {
            $('.wp2l_starter_data').find('option[value=""]').prop('selected', true);
            $('.relationship-map-fields').remove();
            $('.column-comparison-map-fields').remove();
            $('#group-map-by-key').empty();
            $('#wp2l-column-options').empty();
            $('#wp2l-group-concat-for').empty();
            $('#wp2l-group-map-results-by').empty();

            var virtual_relationships = $('.virtual-relationship');
            $.each(virtual_relationships, function (index, relationship) {
                $(relationship).find('[data-action="remove-virtual-relationship"]').trigger('click');
            });

            refreshMapResult();
        } else {
            var starterData = $('.wp2l_starter_data'),
                preValue = starterData.data('previousValue');

            starterData.val(preValue);
            return false;
        }
    });

    /** Change starter date */
    $(document.body).on('change', '.wp2l_starter_data', function () {
        var preValue = $(this).data('previousValue');
        var newValue = $(this).val();

        if ('' === newValue) {
            $('#reset-map-builder-form').click();
        } else  {
            $(this).data('previousValue', newValue);

            if (preValue !== undefined && preValue !== newValue) {
                if (confirm(wp2leads_i18n_get('Are you sure you want to change the starter data? This will remove any existing relationships.'))) {
                    $('.relationship-map-fields').remove();
                    $('.column-comparison-map-fields').remove();
                    $('#wp2l-group-map-results-by').empty();
                    $('#wp2l-group-concat-for').empty();
                    $('#wp2l-date-time-columns').empty();
                    $('#wp2l-column-options').empty();

                    var virtual_relationships = $('.virtual-relationship');

                    $.each(virtual_relationships, function (index, relationship) {
                        $(relationship).find('[data-action="remove-virtual-relationship"]').trigger('click');
                    });

                    $('.no-starter-date-message').remove();
                    // updateActiveSearchResultRow();
                    setUsedTableOnSearchResults();
                    updateSelectsOptionsOnStart(refreshMappingOnChange);
                } else {
                    $(this).val(preValue);
                    return false;
                }
            } else {
                $('.no-starter-date-message').remove();
                // updateActiveSearchResultRow();
                setUsedTableOnSearchResults();
                updateSelectsOptionsOnStart(refreshMappingOnChange);
            }
        }
    });

    $(document.body).on('change', '#wp2l-group-map-results-by', function () {
        var preValue = $(this).data('current_value');
        var newValue = $(this).val();

        $(this).data('current_value', newValue);

        refreshMappingOnChange();
    });

    /**
     * Relationship Data Events
     * =======================================
     * onClick #add-new-relationship-map
     * onChange .relationship-join-table
     * onChange .relationship-reference-table
     * onClick .remove-relationship
     */
    $(document.body).on('click', '#add-new-relationship-map', function (e) {
        e.preventDefault();

        if ($(this).hasClass('disabled')) {
            return false;
        }

        var fromTable = $('#from-table').val();

        if ('' === fromTable) {
            var noFromTable = Handlebars.compile($('#wp2l-no-starter-date')[0].innerHTML);
            $('#relationship-map-holder', document.body).html(noFromTable({}));
        } else {
            $(this).addClass('disabled');

            blockMapBuilderFieldsOnChange('relationship');

            var relationshipFieldsTemplate = Handlebars.compile($('#wp2l-relationship-map-fields')[0].innerHTML);
            var availableTables;

            getAllTablesPromise().then(function (availableTables) {
                // modify availableTables to support self-referential tables.
                var existingTableChoices = findExistingTableChoices();

                // if one of the table choices exists within the available tables...
                // add an up-indexed reference _in addition_
                // simplified: foreachExistingTableChoices... add an up-indexed reference
                var incrementedTables = existingTableChoices.map(function(existingTableChoice) {
                    var splitter = existingTableChoice.split('-');

                    if(existingTableChoice === splitter[0]) {
                        return existingTableChoice + '-2';
                    } else {
                        return splitter[0] + '-' + (splitter[1] + 1);
                    }
                });

                availableTables = availableTables.concat(incrementedTables).sort();

                var relationshipFields = $('#relationship-map-holder .relationship-map-fields'),
                    relationshipFieldsCount = relationshipFields.length;

                relationshipFields.each(function() {
                    $(this).find('select').attr('disabled', true);
                    $(this).find('.remove-relationship').addClass('disabled');
                });

                var index = $('#relationship-map-holder .relationship-map-fields').length;

                $('#relationship-map-holder', document.body).append(relationshipFieldsTemplate({
                    index: index,
                    availableTables: availableTables,
                    existingTables: existingTableChoices
                }));

                var currentRelation = $('#relationship-map-holder .relationship-map-fields:last-child');
                currentRelation.addClass('in-progress');

                currentRelation.find('.relationship-reference-column').attr('disabled', true);
                currentRelation.find('.relationship-join-table').attr('disabled', true);
                currentRelation.find('.relationship-join-column').attr('disabled', true);

                currentRelation.append('<span class="button run-relationship disabled">'+wp2leads_i18n_get('Done')+'</span>');

                if (existingTableChoices) {
                    for (var j = 0; j < existingTableChoices.length; j++) {
                        currentRelation.find('.relationship-join-table option[value="' + existingTableChoices[j] + '"]').attr('disabled', true);
                    }
                }

                currentRelation.find('.relationship-reference-table').focus();
            });
        }

    });

    $(document.body).on('change', '.relationship-reference-table', function (e) {
        var currentRelationship = $(this).parents('.relationship-map-fields'),

            isChanged = false,

            referenceTable = currentRelationship.find('.relationship-reference-table'),
            referenceTableSelected = referenceTable.val(),
            referenceTablePrev = referenceTable.data('current_value'),

            referenceColumn = currentRelationship.find('.relationship-reference-column');

        if (referenceTableSelected && referenceTableSelected !== referenceTablePrev) {
            isChanged = true;
        }

        if(referenceTableSelected && isChanged) {
            referenceTable.find('option').filter(function() {
                return !this.value;
            }).remove();

            $('#add-new-relationship-map').addClass('disabled');

            currentRelationship.addClass('in-progress');

            $.post(ajaxurl, {action: 'wp2l_fetch_column_options', 'table': referenceTableSelected, 'indexes': 1}, function (response) {
                referenceColumn.empty();

                var columns = $.parseJSON(response);

                referenceColumn.append($('<option value="">'+wp2leads_i18n_get(''+wp2leads_i18n_get('-- Select --')+'')+'</option>'));

                $.each(columns, function (index, column) {
                    referenceColumn.append($('<option value="' + column + '">' + column + '</option>'));
                });

                referenceColumn.attr('disabled', false).focus();

                if (currentRelationship.find('.run-relationship').length === 0) {
                    currentRelationship.append('<span class="button run-relationship disabled">'+wp2leads_i18n_get('Done')+'</span>');
                }
            });
        } else {

        }
    });

    $(document.body).on('change', '.relationship-join-table', function () {
        var currentRelationship = $(this).parents('.relationship-map-fields'),

            isChanged = false,

            joinTable = currentRelationship.find('.relationship-join-table'),
            joinTableSelected = joinTable.val(),
            joinTablePrev = joinTable.data('current_value'),
            joinColumn = currentRelationship.find('.relationship-join-column');

        if (joinTableSelected && joinTableSelected !== joinTablePrev) {
            isChanged = true;
            joinTable.data('current_value', joinTableSelected);
        }

        var $this = $(this);

        if (joinTableSelected && isChanged) {
            joinTable.find('option').filter(function() {
                return !this.value;
            }).remove();

            $('#add-new-relationship-map').addClass('disabled');

            currentRelationship.addClass('in-progress');

            updateSelectsOptionsOnRelations(function (tables) {

                $.post(ajaxurl, {action: 'wp2l_fetch_column_options', 'table': $this.val(), 'indexes': 2}, function (response) {

                    var columns = $.parseJSON(response);
                    var $target = $this.parent().find('.relationship-join-column');
                    var objectIndex = _.findIndex(tables, {'table': $this.val()});
                    joinColumn.empty();

                    joinColumn.append($('<option value="">'+wp2leads_i18n_get(''+wp2leads_i18n_get('-- Select --')+'')+'</option>'));

                    $.each(columns, function (index, column) {
                        joinColumn.append($('<option value="' + column + '">' + column + '</option>'));
                    });

                    // for (var j = 0; j < tables[objectIndex]['columns'].length; j++) {
                    //     joinColumn.append($('<option value="' + tables[objectIndex]['columns'][j] + '">' + tables[objectIndex]['columns'][j] + '</option>'));
                    // }

                    joinColumn.attr('disabled', false).focus();

                    if (currentRelationship.find('.run-relationship').length === 0) {
                        currentRelationship.append('<span class="button run-relationship disabled">'+wp2leads_i18n_get('Done')+'</span>');
                    }
                });
            });
        } else {

        }
    });

    $(document.body).on('change', '.relationship-join-column, .relationship-reference-column', function() {
        var relationshipFields = $(this).parent();

        var columnChanged = null;
        var isRelationChanged = false;

        var relationshipJoinTableSelect = relationshipFields.find('.relationship-join-table'),
            relationshipJoinTableSelected = relationshipJoinTableSelect.val(),
            relationshipJoinTablePrev = relationshipJoinTableSelect.data('current_value'),

            relationshipJoinColumnSelect = relationshipFields.find('.relationship-join-column'),
            relationshipJoinColumnSelected = relationshipJoinColumnSelect.val(),
            relationshipJoinColumnPrev = relationshipJoinColumnSelect.data('current_value'),

            relationshipReferenceTableSelect = relationshipFields.find('.relationship-reference-table'),
            relationshipReferenceTableSelected = relationshipReferenceTableSelect.val(),
            relationshipReferenceTablePrev = relationshipReferenceTableSelect.data('current_value'),

            relationshipReferenceColumnSelect = relationshipFields.find('.relationship-reference-column'),
            relationshipReferenceColumnSelected = relationshipReferenceColumnSelect.val(),
            relationshipReferenceColumnPrev = relationshipReferenceColumnSelect.data('current_value');


        if ($(this).hasClass('relationship-join-column')) {
            columnChanged = 'join';

            var $object = relationshipJoinColumnSelect,
                $selected = relationshipJoinColumnSelected,
                $prev = relationshipJoinColumnPrev;
        } else if ($(this).hasClass('relationship-reference-column')) {
            columnChanged = 'reference';

            var $object = relationshipReferenceColumnSelect,
                $selected = relationshipReferenceColumnSelected,
                $prev = relationshipReferenceColumnPrev;
        }

        if ($selected !== $prev) {
            isRelationChanged = true;
        }

        if (isRelationChanged) {
            $object.find('option').filter(function() {
                return !this.value;
            }).remove();

            relationshipFields.addClass('in-progress');

            if (relationshipFields.find('.run-relationship').length === 0) {
                relationshipFields.append('<span class="button run-relationship disabled">'+wp2leads_i18n_get('Done')+'</span>');
            }

            if ( columnChanged === 'reference' && !relationshipJoinTableSelected ) {
                relationshipJoinTableSelect.attr('disabled', false).focus();
            }

            $('#add-new-relationship-map').addClass('disabled');
        }

        if (
            relationshipReferenceTableSelected &&
            relationshipReferenceColumnSelected &&
            relationshipJoinTableSelected &&
            relationshipJoinColumnSelected
        ) {
            updateAllFieldsOnChange(function() {
                relationshipFields.find('.run-relationship').attr('disabled', false).removeClass('disabled');
            }, function() {
                console.log('!!! Error on Change relation !!!');
            });
        } else {
            relationshipFields.find('.run-relationship').addClass('disabled');
        }
    });

    $(document.body).on('click', '.run-relationship', function(e) {
        e.preventDefault();

        if ($(this).hasClass('disabled')) {
            return false;
        }

        var relationshipFields = $(this).parents('.relationship-map-fields');
        relationshipFields.removeClass('in-progress');

        unblockMapBuilderFieldsOnChange('relationship');
        $('#add-new-relationship-map').removeClass('disabled');

        refreshMappingOnChange();
    });

    $(document.body).on('click', '.remove-relationship', function (e) {
        e.preventDefault();

        if ($(this).hasClass('disabled')) {
            return false;
        }

        var relationToRemove = $(this).parent(),
            joinTable = relationToRemove.find('.relationship-join-table').val(),
            joinColumn = relationToRemove.find('.relationship-join-column').val();

        if (joinTable) {
            var columnsToRemove = fetchColumnsForTable(joinTable);
            var columnsTablesToRemove = [];

            for (var j = 0; j < columnsToRemove.columns.length; j++) {
                columnsTablesToRemove.push(joinTable + '.' + columnsToRemove.columns[j]);
            }

            $('#wp2l-column-options option', document.body).each(function() {
                if ($.inArray($(this).val(), columnsTablesToRemove) !== -1) {
                    $(this).remove();
                }
            });

            $('#wp2l-group-concat-for option', document.body).each(function() {
                if ($.inArray($(this).val(), columnsTablesToRemove) !== -1) {
                    $(this).remove();
                }
            });
        }

        relationToRemove.remove();


        unblockMapBuilderFieldsOnChange('relationship');
        $('#add-new-relationship-map').removeClass('disabled').attr('disabled', false);

        refreshMappingOnChange();
    });

    // TODO no such selector
    $(document.body).on('change', '.wp2l_add_relationship', function () {
        var $that = $(this);
        // clean up previous selections
        $that.siblings('select').remove();
        // fetch the relevant table columns
        $.post(ajaxurl, {action: 'wp2l_fetch_column_options', 'table': $that.val(), 'indexes': 1}, function (response) {
            var template = Handlebars.compile($('#wp2l-select')[0].innerHTML);
            $that.after(template({options: $.parseJSON(response)}));
        });
    });

    $(document.body).on('click', '#virtual-relationship-button-add', function(e) {
        e.preventDefault();

        if ($(this).hasClass('disabled')) {
            return false;
        }

        var fromTable = $('#from-table').val();

        if ('' === fromTable) {
            var noFromTable = Handlebars.compile($('#wp2l-no-starter-date')[0].innerHTML);
            $('.virtual-relationship-list', document.body).html(noFromTable({}));
        } else {
            blockMapBuilderFieldsOnChange('virtual-relationship');

            var virtualRelationshipHolder = $('#virtual-relationships');
            var virtualRelationships = virtualRelationshipHolder.find('.virtual-relationship');

            if (virtualRelationships.length > 0) {
                virtualRelationships.find('select').attr('disabled', true);
                virtualRelationships.find('.remove-virtual-relationship').addClass('disabled');
            }

            $(this).addClass('disabled');

            var virtualRelationshipFieldsTemplate = Handlebars.compile($('#wp2l-virtual-relationship-map-fields')[0].innerHTML);

            $(document.body).find('#virtual-relationships .virtual-relationship-list').append(virtualRelationshipFieldsTemplate);

            getAllTablesPromise().then(function(availableTables) {
                var currentVirtualRelation = $(document.body).find('.virtual-relationship-list .virtual-relationship:last-child');

                currentVirtualRelation.addClass('in-progress');

                var selectFromTable = currentVirtualRelation.find('.virtual-table_from');
                var selectFromColumn = currentVirtualRelation.find('.virtual-column_from');
                var selectToTable = currentVirtualRelation.find('.virtual-table_to');
                var selectToColumn = currentVirtualRelation.find('.virtual-column_to');
                var selectColumnKey = currentVirtualRelation.find('.virtual-column_key');
                var selectColumnValue = currentVirtualRelation.find('.virtual-column_value');

                selectFromTable.focus();

                selectFromColumn.attr('disabled', true);
                selectToTable.attr('disabled', true);
                selectToColumn.attr('disabled', true);
                selectColumnKey.attr('disabled', true);
                selectColumnValue.attr('disabled', true);

                $.each(availableTables, function(i, table) {
                    selectFromTable.append('<option value="' + table + '">' + table + '</option>');
                    selectToTable.append('<option value="' + table + '">' + table + '</option>');
                });

                var submenu = currentVirtualRelation.find('.submenu');
                submenu.append('<span class="button run-virtual-relationship disabled">'+wp2leads_i18n_get('Done')+'</span>');
            });
        }
    });

    $(document.body).on('change', '.virtual-table_from, .virtual-table_to', function() {
        var virtualRelationshipFields = $(this).parents('.virtual-relationship');

        var tableChanged = null,
            isRelationChanged = false,
            $object = null,
            $target = null,
            $selected = null,
            $prev = null;

        var virtualFromTable = virtualRelationshipFields.find('.virtual-table_from'),
            virtualFromTableSelected = virtualFromTable.val(),
            virtualFromTablePrev = virtualFromTable.data('current_value'),

            virtualFromColumn = virtualRelationshipFields.find('.virtual-column_from'),
            virtualFromColumnSelected = virtualFromColumn.val(),
            virtualFromColumnPrev = virtualFromColumn.data('current_value'),

            virtualToTable = virtualRelationshipFields.find('.virtual-table_to'),
            virtualToTableSelected = virtualToTable.val(),
            virtualToTablePrev = virtualToTable.data('current_value'),

            virtualToColumn = virtualRelationshipFields.find('.virtual-column_to'),
            virtualToColumnSelected = virtualToColumn.val(),
            virtualToColumnPrev = virtualToColumn.data('current_value'),

            virtualColumnKey = virtualRelationshipFields.find('.virtual-column_key'),
            virtualColumnValue = virtualRelationshipFields.find('.virtual-column_value');

        if ($(this).hasClass('virtual-table_from') && virtualFromTableSelected) {
            tableChanged = 'from';

            $object = virtualFromTable;
            $target = virtualFromColumn;
            $selected = virtualFromTableSelected;
            $prev = virtualFromTablePrev;

        } else if ($(this).hasClass('virtual-table_to') && virtualToTableSelected) {
            tableChanged = 'to';

            $object = virtualToTable;
            $target = virtualToColumn;
            $selected = virtualToTableSelected;
            $prev = virtualToTablePrev;
        }

        if ( $selected !== $prev ) {
            isRelationChanged = true;
            // relationshipReferenceColumnSelect.data('current_value', relationshipReferenceColumnSelected);
        }

        $object.find('option').filter(function() {
            return !this.value;
        }).remove();

        if ($selected && isRelationChanged) {
            $.post(ajaxurl, {action: 'wp2l_fetch_column_options', 'table': $selected}, function (response) {
                $target.empty();

                var columns = $.parseJSON(response);

                $target.append($('<option value="">'+wp2leads_i18n_get(''+wp2leads_i18n_get('-- Select --')+'')+'</option>'));

                $.each(columns, function (index, column) {
                    $target.append($('<option value="' + column + '">' + column + '</option>'));
                });

                $target.attr('disabled', false).focus();

                var submenu = virtualRelationshipFields.find('.submenu');

                if (submenu.find('.run-virtual-relationship').length === 0 ) {
                    submenu.append('<span class="button run-virtual-relationship disabled">'+wp2leads_i18n_get('Done')+'</span>');
                }

                virtualColumnKey.empty().append($('<option value="">'+wp2leads_i18n_get('-- Select --')+'</option>')).attr('disabled', true);
                virtualColumnValue.empty().append($('<option value="">'+wp2leads_i18n_get('-- Select --')+'</option>')).attr('disabled', true);

                $(document.body).find('#virtual-relationship-button-add').addClass('disabled');
            });
        }
    });

    $(document.body).on('change', '.virtual-column_from, .virtual-column_to', function() {
        var virtualRelationshipFields = $(this).parents('.virtual-relationship');

        var tableChanged = null,
            isRelationChanged = false,
            $object = null,
            $target = null,
            $selected = null,
            $prev = null;

        var virtualFromTable = virtualRelationshipFields.find('.virtual-table_from'),
            virtualFromTableSelected = virtualFromTable.val(),
            virtualFromTablePrev = virtualFromTable.data('current_value'),

            virtualFromColumn = virtualRelationshipFields.find('.virtual-column_from'),
            virtualFromColumnSelected = virtualFromColumn.val(),
            virtualFromColumnPrev = virtualFromColumn.data('current_value'),

            virtualToTable = virtualRelationshipFields.find('.virtual-table_to'),
            virtualToTableSelected = virtualToTable.val(),
            virtualToTablePrev = virtualToTable.data('current_value'),

            virtualToColumn = virtualRelationshipFields.find('.virtual-column_to'),
            virtualToColumnSelected = virtualToColumn.val(),
            virtualToColumnPrev = virtualToColumn.data('current_value'),

            virtualColumnKey = virtualRelationshipFields.find('.virtual-column_key'),
            virtualColumnValue = virtualRelationshipFields.find('.virtual-column_value');

        if ($(this).hasClass('virtual-column_from') && virtualFromColumnSelected) {
            tableChanged = 'from';

            $object = virtualFromColumn;
            $target = virtualFromColumn;
            $selected = virtualFromColumnSelected;
            $prev = virtualFromColumnPrev;

            if ($selected && !virtualToTableSelected) {
                virtualToTable.attr('disabled', false).focus();
            }

        } else if ($(this).hasClass('virtual-column_to') && virtualToColumnSelected) {
            tableChanged = 'to';

            $object = virtualToColumn;
            $target = virtualToColumn;
            $selected = virtualToColumnSelected;
            $prev = virtualToColumnPrev;
        }

        if ( $selected !== $prev ) {
            isRelationChanged = true;
            // relationshipReferenceColumnSelect.data('current_value', relationshipReferenceColumnSelected);
        }

        $object.find('option').filter(function() {
            return !this.value;
        }).remove();

        var submenu = virtualRelationshipFields.find('.submenu');

        if (submenu.find('.run-virtual-relationship').length === 0 ) {
            submenu.append('<span class="button run-virtual-relationship disabled">'+wp2leads_i18n_get('Done')+'</span>');
        }

        if (virtualFromTableSelected, virtualFromColumnSelected, virtualToTableSelected, virtualToColumnSelected) {
            var tableFromInfo = fetchColumnsForTable(virtualFromTableSelected);
            var tableToInfo = fetchColumnsForTable(virtualToTableSelected);

            var virtualOptions = [];

            for (var j = 0; j < tableFromInfo['columns'].length; j++) {
                // virtualOptions.push(tableFromInfo['table'] + '.' + tableFromInfo['columns'][j]);
            }

            for (var j = 0; j < tableToInfo['columns'].length; j++) {
                virtualOptions.push(tableToInfo['table'] + '.' + tableToInfo['columns'][j]);
            }

            var dedupVirtualOptions = _.uniq(virtualOptions);

            dedupVirtualOptions.sort();

            virtualColumnKey.empty().append($('<option value="">'+wp2leads_i18n_get('-- Select --')+'</option>')).attr('disabled', true);
            virtualColumnValue.empty().append($('<option value="">'+wp2leads_i18n_get('-- Select --')+'</option>')).attr('disabled', true);

            $.each(dedupVirtualOptions, function(index, column) {
                virtualColumnKey.append('<option value="' + column + '">' + column + '</option>');
                virtualColumnValue.append('<option value="' + column + '">' + column + '</option>');
            });

            virtualColumnKey.attr('disabled', false).focus();
            virtualColumnValue.attr('disabled', false);
        }
    });

    $(document.body).on('change', '.virtual-column_value, .virtual-column_key', function() {
        var virtualRelationshipFields = $(this).parents('.virtual-relationship');

        var tableChanged = null,
            isRelationChanged = false,
            $object = null,
            $target = null,
            $selected = null,
            $prev = null,

            virtualColumnKey = virtualRelationshipFields.find('.virtual-column_key'),
            virtualColumnValue = virtualRelationshipFields.find('.virtual-column_value');

        if ($(this).hasClass('virtual-column_value') && virtualColumnValue) {
            tableChanged = 'value';
            $object = virtualColumnValue;
        } else if ($(this).hasClass('virtual-column_key') && virtualColumnKey) {
            tableChanged = 'key';
            $object = virtualColumnKey;
        }

        $object.find('option').filter(function() {
            return !this.value;
        }).remove();

        var submenu = virtualRelationshipFields.find('.submenu');

        if (submenu.find('.run-virtual-relationship').length === 0 ) {
            submenu.append('<span class="button run-virtual-relationship disabled">'+wp2leads_i18n_get('Done')+'</span>');
        }

        if (virtualColumnKey.val() && virtualColumnValue.val()) {
            updateAllFieldsOnChange(function() {
                virtualRelationshipFields.find('.run-virtual-relationship').attr('disabled', false).removeClass('disabled');
            }, function() {
                console.log('!!! Error on Change virtual !!!');
            });
        }
    });

    $(document.body).on('click', '.run-virtual-relationship', function(e) {
        e.preventDefault();

        if ($(this).hasClass('disabled')) {
            return false;
        }

        var virtualRelationshipFields = $(this).parents('.virtual-relationship');
        virtualRelationshipFields.removeClass('in-progress');
        $('#virtual-relationship-button-add').removeClass('disabled');
        $(this).remove();

        unblockMapBuilderFieldsOnChange('virtual-relationship');
        unblockVirtualRelationsOnChange();
        refreshMappingOnChange();
    });

    $(document.body).on('click', '.remove-virtual-relationship', function(e) {
        e.preventDefault();

        if ($(this).hasClass('disabled')) {
            return false;
        }

        $(this).parents('.virtual-relationship').remove();
        $(document.body).find('#virtual-relationship-button-add').removeClass('disabled');
        unblockMapBuilderFieldsOnChange('virtual-relationship');
        unblockVirtualRelationsOnChange();
        refreshMappingOnChange();
    });

    /**
     * Add Comparison Events
     */
    $(document.body).on('click', '#add-new-comparison-map', function (e) {
        e.preventDefault();

        if ($(this).hasClass('disabled')) {
            return false;
        }

        var fromTable = $('#from-table').val(),

            // Get exclude selected fields
            excludesSelect = $('#wp2l-column-options'),
            excludesSelected = excludesSelect.val();

        if ('' === fromTable) {
            var noFromTable = Handlebars.compile($('#wp2l-no-starter-date')[0].innerHTML);
            $('#column-comparison-holder', document.body).html(noFromTable({}));
        } else {

            blockMapBuilderFieldsOnChange('comparison');

            $(this).addClass('disabled');

            var comparisonsHolder = $('#column-comparison-holder');
            var comparisons = comparisonsHolder.find('.column-comparison-map-fields');

            if (comparisons.length > 0) {
                comparisons.find('select').attr('disabled', true);
                comparisons.find('input').attr('disabled', true);
                comparisons.find('.remove-column-comparison').attr('disabled', true).addClass('disabled');
            }

            getAllColumnsPromise().then(function(allColumnsJson) {

                var allColumns = $.parseJSON(allColumnsJson);

                if (excludesSelected) {
                    allColumns = allColumns.filter( function( el ) {
                        return excludesSelected.indexOf( el ) < 0;
                    } );
                }

                allColumns.sort();

                var index = $('#column-comparison-holder .column-comparison-map-fields').length;

                $('#column-comparison-holder').append(comparisonFieldsTemplate({
                    index: index,
                    availableTableColumns: allColumns
                }));

                var currentComparison = $(document.body).find('#column-comparison-holder .column-comparison-map-fields:last-child');
                currentComparison.addClass('in-progress');
                currentComparison.find('.table-column-identifier').focus();
                currentComparison.find('.table-column-operator').attr('disabled', true);
                currentComparison.find('.table-column-string').attr('disabled', true);
                currentComparison.append('<span class="button run-comparison disabled">'+wp2leads_i18n_get('Done')+'</span>');
            });
        }
    });

    $(document.body).on('change', '.table-column-identifier', function() {
        var currentComparison = $(this).parents('.column-comparison-map-fields'),

            comparisonColumn = currentComparison.find('.table-column-identifier'),
            comparisonColumnSelected = comparisonColumn.val(),
            comparisonColumnPrev = comparisonColumn.data('current_value'),

            comparisonOperator = currentComparison.find('.table-column-operator'),
            comparisonOperatorSelected = comparisonOperator.val();

        if ( comparisonColumnSelected && comparisonColumnPrev !== comparisonColumnSelected ) {
            comparisonColumn.find('option').filter(function() {
                return !this.value;
            }).remove();

            currentComparison.addClass('in-progress');
            blockMapBuilderFieldsOnChange('comparison');
            $(document.body).find('#add-new-comparison-map').addClass('disabled');

            if (currentComparison.find('.run-comparison').length === 0) {
                currentComparison.append('<span class="button run-comparison disabled">'+wp2leads_i18n_get('Done')+'</span>');
            }

            if (comparisonOperatorSelected) {
                currentComparison.find('.run-comparison').removeClass('disabled');
            }

            var virtualOptions = [];

            var groupBySelect = $('#wp2l-group-map-results-by');
            var groupBySelected = groupBySelect.val();

            var groupConcatSelect = $('#wp2l-group-concat-for');
            var groupConcatSelected = groupConcatSelect.val();

            var excludesSelect = $('#wp2l-column-options');
            var excludesSelected = excludesSelect.val();

            var comparisons = $('.column-comparison-map-fields .table-column-identifier');
            var comparisonsSelected = [];

            if (comparisons.length > 0) {
                comparisons.each(function() {
                    comparisonsSelected.push($(this).val());
                });
            }

            fetchUpdatedColumnOptions().then(function(tables) {
                if (excludesSelect.find('option').length > 0) {
                    excludesSelect.find('option').each(function() {
                        virtualOptions.push($(this).val());
                    });
                }

                virtualOptions.push(groupBySelected);

                for (var i = 0; i < tables.length; i++) {
                    for (var j = 0; j < tables[i]['columns'].length; j++) {
                        virtualOptions.push(tables[i]['table'] + '.' + tables[i]['columns'][j]);
                    }
                }

                var template = Handlebars.compile($('#wp2l-columns-option')[0].innerHTML);
                var dedupVirtualOptions = _.uniq(virtualOptions);
                dedupVirtualOptions.sort();
                excludesSelect.empty();

                // Generate Exclude Options
                for (j = 0; j < dedupVirtualOptions.length; j++) {
                    var excludeData = dedupVirtualOptions[j].split('.');

                    var excludeOptions = template({
                        table: excludeData[0],
                        column: excludeData[1]
                    });

                    excludesSelect.append(excludeOptions);
                }

                if (groupConcatSelected) {
                    for (j = 0; j < groupConcatSelected.length; j++) {
                        excludesSelect.find('option[value="' + groupConcatSelected[j] + '"]').remove();
                    }
                }

                if (comparisonsSelected) {
                    for (j = 0; j < comparisonsSelected.length; j++) {
                        excludesSelect.find('option[value="' + comparisonsSelected[j] + '"]').remove();
                    }
                }

                excludesSelect.find('option[value="' + groupBySelected + '"]').remove();

                if (excludesSelected) {
                    for (j = 0; j < excludesSelected.length; j++) {
                        excludesSelect.find('option[value="' + excludesSelected[j] + '"]').prop('selected', true);
                    }
                }

                if (!comparisonOperatorSelected) {
                    comparisonOperator.attr('disabled', false).focus();
                }
            });
        }
    });

    $(document.body).on('change', '.table-column-operator', function() {
        var currentComparison = $(this).parents('.column-comparison-map-fields'),

            comparisonSelect = $(this),
            comparisonSelected = comparisonSelect.val(),

            comparisonOperator = currentComparison.find('.table-column-operator'),
            comparisonOperatorSelected = comparisonOperator.val(),
            comparisonOperatorPrev = comparisonOperator.data('current_value'),

            comparisonString = currentComparison.find('.table-column-string'),
            comparisonStringType = comparisonString.attr('type'),
            comparisonStringSelected = comparisonString.val();

        if (comparisonOperatorSelected && comparisonOperatorSelected !== comparisonOperatorPrev) {
            comparisonOperator.find('option').filter(function() {
                return !this.value;
            }).remove();

            currentComparison.addClass('in-progress');
            blockMapBuilderFieldsOnChange('comparison');
            $(document.body).find('#add-new-comparison-map').addClass('disabled');

            if (currentComparison.find('.run-comparison').length === 0) {
                currentComparison.append('<span class="button run-comparison disabled">'+wp2leads_i18n_get('Done')+'</span>');
            }

            if (comparisonSelected) {
                currentComparison.find('.run-comparison').removeClass('disabled');
            }

            comparisonString.attr('disabled', false).focus();
            currentComparison.find('.run-comparison').attr('disabled', false).removeClass('disabled');
        }
    });

    $(document.body).on('input', '.table-column-string', function() {
        var currentComparison = $(this).parents('.column-comparison-map-fields'),

            comparisonColumn = currentComparison.find('.table-column-identifier'),
            comparisonColumnSelected = comparisonColumn.val(),
            comparisonColumnPrev = comparisonColumn.data('current_value'),

            comparisonOperator = currentComparison.find('.table-column-operator'),
            comparisonOperatorSelected = comparisonOperator.val(),
            comparisonOperatorPrev = comparisonOperator.data('current_value'),

            comparisonString = currentComparison.find('.table-column-string'),
            comparisonStringType = comparisonString.attr('type'),
            comparisonStringSelected = comparisonString.val();

        currentComparison.addClass('in-progress');
        blockMapBuilderFieldsOnChange('comparison');
        $(document.body).find('#add-new-comparison-map').addClass('disabled');

        if (currentComparison.find('.run-comparison').length === 0) {
            currentComparison.append('<span class="button run-comparison disabled">'+wp2leads_i18n_get('Done')+'</span>');
        }

        if (comparisonColumnSelected && comparisonOperatorSelected) {
            currentComparison.find('.run-comparison').removeClass('disabled');
        }
    });

    $(document.body).on('click', '.run-comparison', function (e){
        e.preventDefault();

        if ($(this).hasClass('disabled')) {
            return false;
        }

        getAllFieldsOnChange(function() {
//            console.log('!!! error on comparison !!!');
        }).then(function(data) {
            var options = data.options;
            var virtualOptions = data.virtualOptions;
        });

        var currentComparison = $(this).parents('.column-comparison-map-fields');
        currentComparison.removeClass('in-progress');
        $('#add-new-comparison-map').removeClass('disabled');

        unblockMapBuilderFieldsOnChange('comparison');
        refreshMappingOnChange();
    });

    $(document.body).on('click', '.remove-column-comparison', function (e) {
        e.preventDefault();

        if ($(this).hasClass('disabled')) {
            return false;
        }

        $(this).parent().remove();
        $('#add-new-comparison-map').removeClass('disabled');
        unblockMapBuilderFieldsOnChange('comparison');

        refreshMappingOnChange();
    });

    /**
     * Date / Time columns events
     */
    $(document.body).on('change', '#wp2l-date-time-columns', function() {

        var changedOption = $(this);
        var changedOptionVal = changedOption.val();

        changedOption.off('mouseleave');
        changedOption.on('mouseleave', function() {
            changedOption.off('mouseleave');
            changedOption.blur();
        });
    });

    $(document.body).on('blur', '#wp2l-date-time-columns', function() {
        var changedOption = $(this);
        changedOption.off("blur");

        refreshMappingOnChange();
    });

    /**
     * Concat results for events
     */
    $(document.body).on('change', '#wp2l-group-concat-for', function() {
        var changedOption = $(this);
        var changedOptionVal = changedOption.val();

        changedOption.off('mouseleave');
        changedOption.on('mouseleave', function() {
            changedOption.off('mouseleave');
            changedOption.blur();
        });
    });

    $(document.body).on('blur', '#wp2l-group-concat-for', function() {
        var groupConcatOptions = $(this);
        groupConcatOptions.off("blur");
        refreshMappingOnChange();
    });

    $(document.body).on('hover', '#wp2l-column-options', function () {
        var excludesSelect = $(this);

        excludesSelect.off('mouseleave');
    });

    $(document.body).on('blur', '#wp2l-group-concat-separator', function() {
        var groupConcatOptions = $(this);
        groupConcatOptions.off("blur");
        refreshMappingOnChange();
    });

    /**
     * Exclude Columns events
     */
    $(document.body).on('change', '#wp2l-column-options', function () {

        var options = [],

            // Get Value of Group By
            groupBySelect = $('#wp2l-group-map-results-by'),
            groupBySelected = groupBySelect.val(),

            // Get Values of group concat selected
            groupConcatSelect = $('#wp2l-group-concat-for'),
            groupConcatSelected = groupConcatSelect.val(),

            // Get Values of date time selected
            dateTimeSelect = $('#wp2l-date-time-columns'),
            dateTimeSelected = dateTimeSelect.val(),

            // Get values of excludes selected
            excludesSelect = $(this),
            excludesSelected = excludesSelect.val(),

            // Get values of comparisons selected
            comparisonsSelect = $('.column-comparison-map-fields'),
            comparisonsSelected = [];

        excludesSelect.off('mouseleave');
        excludesSelect.on('mouseleave', function() {
            excludesSelect.off('mouseleave');
            excludesSelect.blur();
        });
    });

    $(document.body).on('blur', '#wp2l-column-options', function () {
        var excludesOptions = $(this);
        excludesOptions.off("blur");
        refreshMappingOnChange();
    });

    $(document.body).on('click', '#select-all-column-options', function (e) {
        $('#wp2l-column-options option', document.body).each(function () {
            $(this).prop('selected', true);
        });

        $('#wp2l-column-options', document.body).change();

        refreshMappingOnChange();
    });

    $(document.body).on('click', '#deselect-all-column-options', function (e) {
        $('#wp2l-column-options option', document.body).each(function () {
            $(this).prop('selected', false);
        });

        $('#wp2l-column-options', document.body).change();
        refreshMappingOnChange();
    });

    $(document.body).on('click', '#invert-selected-column-options', function (e) {
        $('#wp2l-column-options option', document.body).each(function () {
            var currentlySelected = $(this).prop('selected');

            if (currentlySelected) {
                $(this).prop('selected', false);
            } else {
                $(this).prop('selected', true);
            }
        });

        $('#wp2l-column-options', document.body).change();
        refreshMappingOnChange();
    });

    $(document.body).on('click', '#wp2l-toggle-direct-results-selection', function () {
        if ($(this).text() == $(this).data('active_text')) {
            // moving from active to inactive

            $(this)
                .removeClass($(this).data('active_class'))
                .text($(this).data('inactive_text'))
                .addClass($(this).data('inactive_class'));

            $('.exclude-this-column').toggleClass('hidden');

            if ($('#wp2l-results-preview-wrap th.marked-for-exclusion').length) {
                $('#wp2l-results-preview-wrap th.marked-for-exclusion').each(function () {
                    var columnKey = $(this).find('.exclude-this-column').first().data('column_key');
                    $('#wp2l-column-options option[value="' + columnKey + '"]').prop('selected', true);
                });

                $('#wp2l-column-options').change();
                refreshMappingOnChange();
            }
        } else {
            // moving from inactive to active
            $(this)
                .removeClass($(this).data('inactive_class'))
                .text($(this).data('active_text'))
                .addClass($(this).data('active_class'));

            $('.exclude-this-column').toggleClass('hidden');
        }
    });

    /**
     * Create Possible tags list in Map Builder
     */
    $(document.body).on('click', '#createExcludedFilter', function(e) {
        var create = false;

        var filterToCreate = $.trim($('#excludedColumnsFilterInput').val());
        var filtersCloudHolder = $('#excludedColumnsFilter_container');

        if ('' !== filterToCreate) {
            var alreadyCreated = filtersCloudHolder.find('.created-filter .filter-name');

            if (alreadyCreated.length > 0) {
                alreadyCreated.each(function() {
                    var value = $.trim($(this).text());

                    if (value !== filterToCreate) {
                        create = true;
                    }
                });
            } else {
                create = true;
            }
        }

        if (create) {
            filtersCloudHolder.append(createExcludedFilter(filterToCreate));
        }

        $('#excludedColumnsFilterInput').val('')

        refreshMappingOnChange();
    });

    $(document.body).on('click', '#manualPrecreatedTags_container .tag-close-btn', function() {
        $(this).parent().remove();
    });

    $(document.body).on('click', '#excludedColumnsFilter_container .filter-close-btn', function() {
        $(this).parent().remove();

        refreshMappingOnChange();
    });

    /**
     * Create Possible tags list in Map Builder
     */
    $(document.body).on('submit', '#tagCreateForm', function(e) {
        e.preventDefault();
        var create = false;

        var tagToCreate = $.trim($('#newTagInput').val());
        var prefixToCreate = $.trim($('#newTagPrefixInput').val());
        var tagCloudHolder = $('#manualPrecreatedTags_container');

        if ('' !== tagToCreate) {
            var alreadyCreated = tagCloudHolder.find('.created-tag .tag-name');
            var prefixedTag = '' !== prefixToCreate ? prefixToCreate + ' ' + tagToCreate : tagToCreate;

            if (alreadyCreated.length > 0) {
                alreadyCreated.each(function() {
                    var value = $.trim($(this).text());

                    if (value !== prefixedTag) {
                        create = true;
                    }
                });
            } else {
                create = true;
            }
        }

        if (create) {
            tagCloudHolder.append(createPossibleTag(tagToCreate, prefixToCreate));
        }

        $('#newTagInput').val('')
    });

    $(document.body).on('click', '#excludedColumnsFilter_container .filter-close-btn', function() {
        $(this).parent().remove();
    });

    function createExcludedFilter(value) {
        var tag = $('<div class="created-filter">');
        var tag_name_holder = $('<span class="filter-name">');
        var close_icon = $('<span class="filter-close-btn">');

        tag_name_holder.text(value);

        tag.append(tag_name_holder);
        tag.append(close_icon);

        return tag;
    }

    function createPossibleTag(tagName, prefixName) {
        var tag = $('<div class="created-tag">');
        var tag_name_holder = $('<span class="tag-name" data-tag="'+tagName+'" data-prefix="'+prefixName+'">');
        var close_icon = $('<span class="tag-close-btn">');
        var prefixedTag = '' !== prefixName ? prefixName + ' ' + tagName : tagName;

        tag_name_holder.text(prefixedTag);

        tag.append(tag_name_holder);
        tag.append(close_icon);

        return tag;
    }

    function createPossibleInputTag(tagName) {
        var tag = $('<div class="created-input-tag">');
        var tag_name_holder = $('<span class="tag-name">');

        tag_name_holder.text(tagName);

        tag.append(tag_name_holder);

        return tag;
    }

    $(document.body).on('click', '#createUserInputTag', function() {
        var tagsSetHolder = $('#userInputPrecreatedTags_container');
        var template = Handlebars.compile($('#wp2l-map-builder-user-input-recomended-tags-item')[0].innerHTML);

        getAllTablesPromise().then(function(availableTables) {
            tagsSetHolder.append(template({
                availableTables: availableTables
            }));
        });
    });

    $(document.body).on('click', '#addUserInputTagJoin', function() {
        var tagHolder = $(this).parents('.user-input-recomended-tags-item');
        var tagJoinedHolder = tagHolder.find('.recomended-tags-joined-tables-holder .recomended-tags-joined-tables-list');
        var template = Handlebars.compile($('#wp2l-map-builder-user-input-recomended-tags-joined-tables-item')[0].innerHTML);

        getAllTablesPromise().then(function(availableTables) {
            var existingTableChoices = findExistingRecomendedTagsTableChoices(tagHolder);

            tagJoinedHolder.append(template({
                availableTables: availableTables,
                existingTableChoices: existingTableChoices
            }));

            tagJoinedHolder.find('.recomended-tags-ref-table', document.body).attr('disabled', false);

            setTimeout(function() {
                tagJoinedHolder.find('.recomended-tags-ref-table', document.body).focus();
            }, 50);
        });
    });

    $(document.body).on('click', '#addUserInputTagComparison', function() {
        var tagHolder = $(this).parents('.user-input-recomended-tags-item');
        var tagComparisonsHolder = tagHolder.find('.recomended-tags-comparisons-holder .recomended-tags-comparisons-list');
        var template = Handlebars.compile($('#wp2l-map-builder-user-input-recomended-tags-comparisons-item')[0].innerHTML);

        getAllColumnsRecomendedTagsPromise(tagHolder).then(function(response) {
            var allColumns = $.parseJSON(response);

            tagComparisonsHolder.append(template({
                allColumns: allColumns.columns
            }));

            tagComparisonsHolder.find('.recomended-tags-comparison-column', document.body).attr('disabled', false).focus();
        });

    });

    function getAllColumnsRecomendedTagsPromise(tagHolder, mapping) {
        if (!mapping) {
            mapping = compileRecomendedTagsObject(tagHolder);
        }

        var data = {
            action: 'wp2l_get_all_columns_for_recomended_tags',
            mapping: mapping
        };

        return $.ajax({
            url: ajaxurl,
            method: 'post',
            data: data,
            success: function (response) {},
            error: function (xhr, status, error) {
                console.log(xhr);
                console.log(status);
                console.log(error);
            },
            complete: function (xhr, status) {
            }
        });
    }

    $(document.body).on('click', '.remove-user-input-recomended-tags-item', function() {
        $(this).parents('.user-input-recomended-tags-item').remove();
    });

    $(document.body).on('click', '.remove-recomended-tags-comparisons-item', function() {
        $(this).parents('.recomended-tags-comparisons-item').remove();
    });

    $(document.body).on('click', '.remove-recomended-tags-joined-tables-item', function() {
        $(this).parents('.recomended-tags-joined-tables-item').remove();
    });

    $(document.body).on('change', '#transfer_module', function() {
        var moduleSelect = $(this);
        var selectedModule = $(this).val();
        var requiredColumn = $(this).find('option:selected').data('required-column');
        var mapping = compileMapObject();
        var selects = mapping.selects;
        var excludes = mapping.excludes;
        var allowed = 'allowed';
        var exists = selects.includes(requiredColumn);

        if (!exists) {
            allowed = 'notexisted';
        } else {
            var excluded = excludes.includes(requiredColumn);

            if (excluded) {
                allowed = 'excluded';
            }
        }

        var data = {
            action: 'wp2l_change_transfer_module',
            selectedModule: selectedModule,
            allowed: allowed
        };

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: data,
            success: function(response) {
                var decoded;

                try {
                    decoded = $.parseJSON(response);
                } catch(err) {
                    decoded = false;
                }

                if (decoded) {
                    if (decoded.success) {
                        var descriptionHolder = $('.module-description');
                        var instructionHolder = $('.module-instruction');
                        moduleSelect.removeClass('notexisted').removeClass('warning-field');

                        if (descriptionHolder.length > 0) {
                            descriptionHolder.html(decoded.moduleDescription);
                        }

                        if (instructionHolder.length > 0) {
                            instructionHolder.html(decoded.moduleInstruction);
                        }

                        if ('notexisted' === allowed) {
                            moduleSelect.addClass('notexisted').addClass('warning-field');
                        }

                        refreshMappingOnChange();
                    } else {
                        alert(decoded.message);
                    }
                } else {
                    alert(wp2leads_i18n_get('Something went wrong.'));
                }

            },
            error: function(xhr, status, error) {

            },
            complete: function(xhr, status) {

            }
        });
    });

    $(document.body).on('change', '.recomended-tags-from-table', function() {
        var fromTableSelect  = $(this);
        var parentTagsSet = fromTableSelect.parents('.user-input-recomended-tags-item');
        var fromTableOld = fromTableSelect.data('value');
        var fromTableSelected = fromTableSelect.val();
        var groupBySelect = parentTagsSet.find('.recomended-tags-group-by');
        var tagsColumnSelect = parentTagsSet.find('.recomended-tags-columns');
        var tagsJoinedTablesList = parentTagsSet.find('.recomended-tags-joined-tables-list');
        var tagsComparisonList = parentTagsSet.find('.recomended-tags-comparisons-list');
        var emptyTemplate = Handlebars.compile($('#wp2l-empty-select-val')[0].innerHTML);
        var changeAllowed = false;

        if ( '' !== fromTableOld && fromTableOld !== fromTableSelected ) {
            if (confirm(wp2leads_i18n_get('Are you sure you want to change the starter data? This will remove any current settings.'))) {
                changeAllowed = true;
            } else {
                fromTableSelect.val(fromTableOld);
                return false;
            }
        } else {
            changeAllowed = true;
        }

        if (changeAllowed) {
            fromTableSelect.find('option').filter(function() {
                return !this.value;
            }).remove();

            fromTableSelect.data('value', fromTableSelected);
            groupBySelect.empty();
            tagsColumnSelect.empty();
            tagsJoinedTablesList.empty();
            tagsComparisonList.empty();

            $.post(ajaxurl, {
                action: 'wp2l_fetch_column_options',
                table: fromTableSelected
            }, function (response) {
                var columns = $.parseJSON(response);
                var noticeHolder = parentTagsSet.find('.recomended-tags-results-messages');

                tagsColumnSelect.empty().append(emptyTemplate({}));
                $.each(columns, function (index, column) {
                    tagsColumnSelect.append($('<option value="' + fromTableSelected + '.' + column + '">' + fromTableSelected + '.' + column + '</option>'));
                });

                groupBySelect.empty().append(emptyTemplate({}));
                $.each(columns, function (index, column) {
                    groupBySelect.append($('<option value="' + fromTableSelected + '.' + column + '">' + fromTableSelected + '.' + column + '</option>'));
                });

                setTimeout(function () {
                    groupBySelect.attr('disabled', false).focus();
                }, 200);

                var noticeTemplate = Handlebars.compile($('#wp2l-map-builder-user-input-recomended-tags-message-select-group-by')[0].innerHTML);
                noticeHolder.empty().append(noticeTemplate());
            });
        }
    });

    $(document.body).on('change', '.recomended-tags-group-by', function() {
        var groupBySelect  = $(this);
        var parentTagsSet = groupBySelect.parents('.user-input-recomended-tags-item');
        var groupByOld = groupBySelect.data('value');
        var groupBySelected = groupBySelect.val();
        var tagsColumnSelect = parentTagsSet.find('.recomended-tags-columns');
        var noticeHolder = parentTagsSet.find('.recomended-tags-results-messages');

        var changed = false;

        if ( groupByOld !== groupBySelected ) {
            changed = true;
        }

        if (changed) {
            groupBySelect.find('option').filter(function() {
                return !this.value;
            }).remove();

            groupBySelect.data('value', groupBySelected);
            noticeHolder.empty();

            if (tagsColumnSelect.val() === '') {
                var noticeTemplate = Handlebars.compile($('#wp2l-map-builder-user-input-recomended-tags-message-select-tag-column')[0].innerHTML);
                noticeHolder.append(noticeTemplate());
                tagsColumnSelect.attr('disabled', false).focus();
            }
        }
    });

    $(document.body).on('change', '.recomended-tags-columns', function() {
        var tagColumnSelect  = $(this);
        var parentTagsSet = tagColumnSelect.parents('.user-input-recomended-tags-item');
        var tagColumnOld = tagColumnSelect.data('value');
        var tagColumnSelected = tagColumnSelect.val();
        var noticeHolder = parentTagsSet.find('.recomended-tags-results-messages');

        var changed = false;

        if ( tagColumnOld !== tagColumnSelected ) {
            changed = true;
        }

        if (changed) {
            tagColumnSelect.find('option').filter(function() {
                return !this.value;
            }).remove();

            tagColumnSelect.data('value', tagColumnSelected);
            noticeHolder.empty();
        }
    });

    $(document.body).on('click', '#getUserInputTagResults', function() {
        var control = $(this);
        var parentTagsSet = control.parents('.user-input-recomended-tags-item');
        var noticeHolder = parentTagsSet.find('.recomended-tags-results-messages');

        var check = checkIfAllRecomendedTagsReady(parentTagsSet);

        if (check.errors) {
            noticeHolder.empty();
            $.each(check.errors, function(index, error) {
                var template = Handlebars.compile($('#wp2l-map-builder-user-input-recomended-tags-message-' + error)[0].innerHTML);

                noticeHolder.append(template());
            });
        } else {
            var mapping = compileRecomendedTagsObject(parentTagsSet);

            var data = {
                action: 'wp2l_get_recomended_tags_result',
                mapping: mapping
            };

            $.ajax({
                url: ajaxurl,
                method: 'post',
                data: data,
                success: function (response) {
                    noticeHolder.empty();
                    var result;

                    try {
                        result = $.parseJSON(response);
                    } catch(err) {
                        result = false;
                    }

                    if (result) {
                        if (result.success) {
                            var tagsCloud = parentTagsSet.find('.recomended-tags-results');

                            tagsCloud.empty();

                            $.each(result.tags, function (index, tag) {
                                tagsCloud.append(createPossibleInputTag(tag));
                            });
                        } else {

                        }
                    } else {

                    }

                },
                error: function (xhr, status, error) {
                    console.log(xhr);
                    console.log(status);
                    console.log(error);
                },
                complete: function (xhr, status) {
                }
            });
        }
    });

    function checkIfAllRecomendedTagsReady(tagHolder) {
        var tagSet = $(tagHolder);
        var errors = [];

        var fromTableSelect = tagSet.find('.recomended-tags-from-table');
        var fromTableSelected = $.trim(fromTableSelect.val());

        var groupBySelect = tagSet.find('.recomended-tags-group-by');
        var groupBySelected = $.trim(groupBySelect.val());

        var tagColumnSelect = tagSet.find('.recomended-tags-columns');
        var tagColumnSelected = $.trim(tagColumnSelect.val());

        if ($.trim(fromTableSelected) === '') {
            errors.push('select-from-table');
        }

        if ($.trim(groupBySelected) === '') {
            errors.push('select-group-by');
        }

        if ($.trim(tagColumnSelected) === '') {
            errors.push('select-tag-column');
        }

        var check = {};

        if (errors.length > 0) {
            check.errors = errors;
        } else {
            check.success = true;
        }

        return check;
    }

    $(document.body).on('change', '.recomended-tags-ref-table', function() {
        var refTableSelect  = $(this);
        var parentTagsSet = refTableSelect.parents('.user-input-recomended-tags-item');
        var parentJoinSettings = refTableSelect.parents('.recomended-tags-joined-tables-settings');
        var refColumnSelect  = parentJoinSettings.find('.recomended-tags-ref-column');
        var refTableOld = refTableSelect.data('value');
        var refTableSelected = refTableSelect.val();
        var changed = false;
        var emptyTemplate = Handlebars.compile($('#wp2l-empty-select-val')[0].innerHTML);

        if ( refTableOld !== refTableSelected ) {
            changed = true;
        }

        if (changed) {
            $.post(ajaxurl, {
                action: 'wp2l_fetch_column_options',
                table: refTableSelected
            }, function (response) {
                var columns = $.parseJSON(response);
                refTableSelect.data('value', refTableSelected);
                refColumnSelect.empty().append(emptyTemplate({}));

                $.each(columns, function (index, column) {
                    refColumnSelect.append($('<option value="' + column + '">' + column + '</option>'));
                });

                setTimeout(function () {
                    refColumnSelect.attr('disabled', false).focus();
                }, 200);
            });
        }

    });

    $(document.body).on('change', '.recomended-tags-ref-column', function() {
        var refColumnSelect  = $(this);
        var parentJoinSettings = refColumnSelect.parents('.recomended-tags-joined-tables-settings');
        var refColumnOld = refColumnSelect.data('value');
        var refColumnSelected = refColumnSelect.val();
        var changed = false;

        if ( refColumnOld !== refColumnSelected ) {
            changed = true;
        }

        if (changed) {
            refColumnSelect.data('value', refColumnSelected);

            setTimeout(function () {
                parentJoinSettings.find('.recomended-tags-join-table').attr('disabled', false).focus();
            }, 200);
        }
    });

    $(document.body).on('change', '.recomended-tags-join-table', function() {
        var joinTableSelect  = $(this);
        var parentTagsSet = joinTableSelect.parents('.user-input-recomended-tags-item');
        var parentJoinSettings = joinTableSelect.parents('.recomended-tags-joined-tables-settings');
        var joinColumnSelect  = parentJoinSettings.find('.recomended-tags-join-column');
        var joinTableOld = joinTableSelect.data('value');
        var joinTableSelected = joinTableSelect.val();
        var changed = false;
        var emptyTemplate = Handlebars.compile($('#wp2l-empty-select-val')[0].innerHTML);

        if ( joinTableOld !== joinTableSelected ) {
            changed = true;
        }

        if (changed) {
            $.post(ajaxurl, {
                action: 'wp2l_fetch_column_options',
                table: joinTableSelected
            }, function (response) {
                var columns = $.parseJSON(response);
                joinTableSelect.data('value', joinTableSelected);
                joinColumnSelect.empty().append(emptyTemplate({}));

                $.each(columns, function (index, column) {
                    joinColumnSelect.append($('<option value="' + column + '">' + column + '</option>'));
                });

                setTimeout(function () {
                    joinColumnSelect.attr('disabled', false).focus();
                }, 200);
            });
        }

    });

    $(document.body).on('change', '.recomended-tags-join-column', function() {
        var joinColumnSelect  = $(this);
        var recomendedTagsItem = joinColumnSelect.parents('.user-input-recomended-tags-item');

        updateRecomendedTagsColumnOptions(recomendedTagsItem);
    });

    $(document.body).on('change', '.recomended-tags-comparison-column', function() {
        var columnSelect  = $(this);
        var parentTagsSet = columnSelect.parents('.user-input-recomended-tags-item');
        var parentComparisonSettings = columnSelect.parents('.recomended-tags-comparisons-settings');
        var columnOld = columnSelect.data('value');
        var columnSelected = columnSelect.val();
        var operator = parentComparisonSettings.find('.recomended-tags-comparison-operator');

        var changed = false;

        if ( columnOld !== columnSelected ) {
            changed = true;
        }

        if (changed) {
            columnSelect.find('option').filter(function () {
                return !this.value;
            }).remove();

            columnSelect.data('value', columnSelected);
            operator.attr('disabled', false).focus();
        }
    });

    $(document.body).on('change', '.recomended-tags-comparison-operator', function() {
        var operatorSelect  = $(this);
        var parentTagsSet = operatorSelect.parents('.user-input-recomended-tags-item');
        var parentComparisonSettings = operatorSelect.parents('.recomended-tags-comparisons-settings');
        var operatorOld = operatorSelect.data('value');
        var operatorSelected = operatorSelect.val();
        var string = parentComparisonSettings.find('.recomended-tags-comparison-string');

        var changed = false;

        if ( operatorOld !== operatorSelected ) {
            changed = true;
        }

        if (changed) {
            operatorSelect.find('option').filter(function () {
                return !this.value;
            }).remove();

            operatorSelect.data('value', operatorSelected);
            string.attr('disabled', false).focus();
        }
    });

    function compileRecomendedTagsObject(tagHolder) {
        var parentTagsSet = tagHolder;
        var fromTable = parentTagsSet.find('.recomended-tags-from-table');
        var title = parentTagsSet.find('.recomended-tags-title').val();
        var prefix = parentTagsSet.find('.recomended-tags-prefix').val();
        var joinSettings = parentTagsSet.find('.recomended-tags-joined-tables-settings');
        var comparisonSettings = parentTagsSet.find('.recomended-tags-comparisons-settings');
        var groupBy = parentTagsSet.find('.recomended-tags-group-by');
        var tagColumn = parentTagsSet.find('.recomended-tags-columns');

        var tagsSettings = {
            title: title,
            prefix: prefix,
            fromTable: fromTable.val(),
            selects: [],
            tagColumn: tagColumn.val(),
            groupBy: groupBy.val(),
            joins: [],
            comparisons: []
        };

        if ('' !== tagColumn.val()) {
            tagsSettings.selects.push(tagColumn.val());
        }

        $.each(joinSettings, function (index, item) {
            var join = $(item);

            var newJoin = {
                referenceTable: join.find('.recomended-tags-ref-table').val(),
                referenceColumn: join.find('.recomended-tags-ref-column').val(),
                joinTable: join.find('.recomended-tags-join-table').val(),
                joinColumn: join.find('.recomended-tags-join-column').val()
            };

            if(newJoin.joinTable &&
                newJoin.joinColumn &&
                newJoin.referenceTable &&
                newJoin.referenceColumn
            ) {
                tagsSettings.joins.push(newJoin);
            }
        });

        $.each(comparisonSettings, function (index, item) {
            var comparison = $(item);
            var comparisonColumn = comparison.find('.recomended-tags-comparison-column').val();
            var comparisonOperator = comparison.find('.recomended-tags-comparison-operator').val();
            var comparisonString = comparison.find('.recomended-tags-comparison-string').val();

            if(comparisonColumn && comparisonOperator && comparisonString) {
                var exist = false;
                var key = 0;

                $.each(tagsSettings.comparisons, function (index, comp) {
                    if (comparisonColumn === comp.tableColumn) {
                        exist = true;
                        key = index;
                    }
                });

                tagsSettings.selects.push(comparisonColumn);

                if (exist) {
                    tagsSettings.comparisons[key].conditions.push({
                        operator: comparisonOperator,
                        string: comparisonString
                    });
                } else {
                    tagsSettings.comparisons.push({
                        tableColumn: comparisonColumn,
                        conditions: [{
                            operator: comparisonOperator,
                            string: comparisonString
                        }]
                    });
                }
            }
        });

        return tagsSettings;
    }

    function findExistingRecomendedTagsTableChoices(recomendedTagsItem) {
        var tables = [];

        tables.push($(recomendedTagsItem).find('.recomended-tags-from-table').val());

        $(recomendedTagsItem).find('.recomended-tags-join-table').each(function () {
            tables.push($(this).val());
        });

        return tables;
    }

    function updateRecomendedTagsColumnOptions(tagHolder) {

        getAllColumnsRecomendedTagsPromise(tagHolder).then(function(response) {
            var allColumns = $.parseJSON(response);
            var comparisonSelects = tagHolder.find('.recomended-tags-comparison-column');

            $.each(comparisonSelects, function(index, comparison) {
                var comparisonSelected = $(comparison).val();
                $(comparison).empty();

                $.each(allColumns.columns, function (index, column) {
                    $(comparison).append($('<option value="' + column + '">'  + column + '</option>'));
                });

                $(comparison).val(comparisonSelected);
            });

            var tagsColumn = tagHolder.find('.recomended-tags-columns');
            var tagsColumnSelected = tagsColumn.val();
            tagsColumn.empty();

            $.each(allColumns.columns, function (index, column) {
                tagsColumn.append($('<option value="' + column + '">'  + column + '</option>'));
            });

            tagsColumn.val(tagsColumnSelected);
        });
    }

    function getMapRowsCountPromise(map) {

        var data = {
            action: 'wp2l_get_map_rows_count'
        };

        if (!map) {
            var mapId = $_GET('active_mapping');

            if (!mapId) {
                mapId = $('#map-runner__container').data('map-id');
            }

            data.mapId = mapId;
        } else {
            data.map = JSON.stringify(map);
        }

        return $.ajax({
            url: ajaxurl,
            method: 'post',
            data: data,
            success: function (response) {},
            error: function (xhr, status, error) {
                console.log(xhr);
                console.log(status);
                console.log(error);
            },
            complete: function (xhr, status) {
            }
        });
    }

    function getMapResultsPromise(count) {
        var limit = iterationLimit;
        var offset = 0;
        var iterations = Math.ceil(count / limit);
        var mapId = $_GET('active_mapping');
        var promises = [];
        var results = [];

        window.mapResults = [];

        for (var i = 0; i < iterations; i++) {
            offset = limit * i;

            promises.push(
                $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    async: false,
                    data: {
                        action: 'wp2l_get_map_query_results_by_map_id_limited',
                        mapId: mapId,
                        limit: limit,
                        offset: offset
                    },
                    success: function(response) {
                        var decoded = $.parseJSON(response);

                        if (decoded.success) {
                            $.merge(window.mapResults, decoded.result);
                            $.merge(results, decoded.result);
                        }
                    },
                    error: function(xhr, status, error) {

                    },
                    complete: function(xhr, status) {

                    }
                })
            );
        }

        return $.when.apply($, promises).then(function() {
            return results;
        });
    }

    $(document.body).on('click', '#btnCreateNewMap, #btnCreateExitNewMap', function (e) {
        e.preventDefault();

        var mapname = $('#title').val();
        var action = $(this).data('action');
        var deleteOriginalMap = $('#deleteOriginalMap');
        var deleteOriginalMapId = '';
        var originalMap = $('#originalMap');
        var originalMapId = '';

        if (deleteOriginalMap.length) {
            deleteOriginalMapId = deleteOriginalMap.val();
        }

        if (originalMap.length) {
            originalMapId = originalMap.val();
        }

        if (!mapname) {
            alert(wp2leads_i18n_get('You must provide a name for the map'));
            return false;
        }

        var mapId = $('#existing_map_id').val() || null;
        var domain = $('#wp2l-map-owner').val();
        var publicMapId = $('#wp2l-public-map-id').val();
        var publicMapHash = $('#wp2l-public-map-hash').val();
        var publicMapContent = $('#wp2l-public-map-content').val();
        var publicMapKind = $('#wp2l-public-map-kind').val();
        var publicMapOwner = $('#wp2l-public-map-owner').val();
        var publicMapStatus = $('#wp2l-public-map-status').val();
        var publicMapVersion = $('#wp2l-public-map-version').val();
        var initialSettings = $('#wp2l-initial-settings').val();
        var isExclusive = $('#wp2l-is-exclusive').val();
        var serverId = $('#wp2l-server-id').val();
        var api = $('#wp2l-public-map-to-api').val();
        var standartTags = $('#manualPrecreatedTags_container .created-tag');
        var userInputTags = $('#userInputPrecreatedTags_container .user-input-recomended-tags-item');

        var possibleUsedTags = {
            standartTags: [],
            userInputTags: []
        };

        if (standartTags.length > 0) {
            standartTags.each(function () {
                var tag_holder = $(this).find('.tag-name');
                var tag = tag_holder.data('tag');
                var prefix = tag_holder.data('prefix');
                var tag_to_save = prefix && '' !== prefix ? prefix + '||' + tag : tag;

                possibleUsedTags.standartTags.push(tag_to_save);
            });
        }

        if (userInputTags.length > 0) {
            userInputTags.each(function () {
                possibleUsedTags.userInputTags.push(compileRecomendedTagsObject($(this)));
            });
        }

        var multiSearchResultSingleRow = $(document.body).find('.multi-search-single-result-row');
        var multiSearchResultRowCurrentValue = multiSearchResultSingleRow.data('current_value');

        var tableSearchResultsRow = $(document.body).find('.table-search-result-row');
        var tableSearchResultsRowCurrentValue = tableSearchResultsRow.data('current_value');

        // shoot off the ajax request
        $.post(
            ajaxurl,
            {
                action: 'wp2l_save_new_map',
                nonce: wp2leads_ajax_object.nonce,
                tab: $_GET('tab'),
                map: JSON.stringify(compileMapObject()),
                name: mapname,
                search: multiSearchResultRowCurrentValue,
                searchTable: tableSearchResultsRowCurrentValue,
                possibleUsedTags: JSON.stringify(possibleUsedTags),
                domain: domain,
                publicMapId: publicMapId,
                publicMapHash: publicMapHash,
                publicMapContent: publicMapContent,
                publicMapKind: publicMapKind,
                publicMapOwner: publicMapOwner,
                publicMapStatus: publicMapStatus,
                publicMapVersion: publicMapVersion,
                initialSettings: initialSettings,
                isExclusive: isExclusive,
                serverId: serverId,
                api: api,
                deleteOriginalMapId: deleteOriginalMapId,
                originalMapId: originalMapId,
                map_id: mapId
            },
            function (response) {
                var decoded;

                try {
                    decoded = $.parseJSON(response);
                } catch(err) {
                    decoded = false;
                }

                if (decoded) {
                    if (decoded.success) {
                        if (decoded.mapping) {
                            $('.mapping').val(decoded.mapping);
                        }

                        if (decoded.map_id) {
                            $('#existing_map_id').val(decoded.map_id);
                        }

                        if (decoded.map_owner) {
                            $('#wp2l-map-owner').val(decoded.map_owner);
                        }

                        alert(wp2leads_i18n_get('Saved Successfully!'));

                        if ( 'exit' === action ) {
                            window.location.href = '?page=wp2l-admin&tab=map_runner&active_mapping=' + decoded.map_id;
                        } else if ( decoded.new_map ) {
                            window.location.href = '?page=wp2l-admin&tab=map_builder&active_mapping=' + decoded.map_id;
                        } else {
                            var navigationTabs = $('.nav-tab-wrapper .nav-tab');

                            if (navigationTabs.length > 0) {
                                navigationTabs.each(function(index) {
                                    var oldUrl = $(this).attr('href');

                                    var page = 'page';
                                    var tab = 'tab';
                                    var activeMapping = 'active_mapping';

                                    var pageResults = new RegExp('[\?&]' + page + '=([^&#]*)').exec(oldUrl);
                                    var pageParam = decodeURI(pageResults[1]);

                                    var tabResults = new RegExp('[\?&]' + tab + '=([^&#]*)').exec(oldUrl);
                                    var tabParam = decodeURI(tabResults[1]);

                                    var activeMappingResults = new RegExp('[\?&]' + activeMapping + '=([^&#]*)').exec(oldUrl);

                                    if (activeMappingResults == null) {
                                        var activeMappingParam = decoded.map_id;
                                    } else {
                                        activeMappingParam = decodeURI(activeMappingResults[1]);
                                    }

                                    var newUrl = '?page=' + pageParam + '&tab='+tabParam+'&active_mapping=' + activeMappingParam;

                                    $(this).attr('href', newUrl);
                                });
                            }
                        }
                    } else {
                        alert(wp2leads_i18n_get('Something went wrong.'));
                    }
                } else {
                    alert(wp2leads_i18n_get('Something went wrong'));
                }
            })
    });

    $(document.body).on('click', '#btnRunSampleMap', function (e) {
        var newLimitValue = $('#map-sample-results-limit').val();

        if (newLimitValue !== previousLimitValue) {
            previousLimitValue = newLimitValue;

            refreshMapResult();
        }
    });

    $(document.body).on('click', '#fetchGeneratedSql', function() {
        var map = compileMapObject();

        $.ajax({
            url: ajaxurl,
            method: 'post',
            async: false,
            data: {
                action: 'wp2l_debug_fetch_query_for_map',
                map: map,
                limit: $('#map-sample-results-limit').val()
            },
            success: function (response) {
                console.log(response);
            }
        })
    });

    $(document.body).on('click', '.exclude-this-column', function () {
        if ($(this).hasClass('disabled')) {
            return false;
        }

        $(this).parent().parent().toggleClass('marked-for-exclusion');
    });

    $(document.body).on('click', '.api_fields_container .api_field_box', function() {
        updateTagListOnFieldContainers($(this));
    });

    $(document.body).on('focusout', '.tokenize', function() {
        const el = $(this);
        const tokens = el.find('.tokens-container li.token');

        $.each(tokens, function(i, tokenHTML) {
            const token = $(tokenHTML);
            const tokenSpan = token.find('span');

            if(token.hasAttr('data-value')) {
                tokenSpan.text(token.attr('data-value'));
            }
        });

        const apiFieldsContainer = el.closest('.api_fields_container');
        const apiFieldBox = el.closest('.api_field_box');

        if (!apiFieldBox.length) {
            return;
        }

        if (apiFieldsContainer.length) {
            updateTagInformationOnFieldContainers(apiFieldBox);
        }

        updateTagListOnFieldContainers(apiFieldBox);
    });

    /**
     * ================================
     *  Starting on load
     * ================================
     */

    /**
     * ==============================================
     *  Map Builder on load page
     * ==============================================
     */
    var searchFromMapOnLoad = $('#wp2l-multi-search-results-map');
    var searchTableFromMapOnLoad = $('.table-search-result-row').data('current_value');
    var starterDataOnLoad = $('.wp2l_starter_data').val();
    var relationsOnLoad = $('#relationship-map-holder').data('current_values');
    var columnExcludesOnLoad = $('#wp2l-column-options').data('current_values');
    var columnComparisonOnLoad = $('#column-comparison-holder').data('current_values');
    var columnGroupConcatOnLoad = $('#wp2l-group-concat-for', document.body).data('current_value');
    var columnDateTimeOnLoad = $('#wp2l-date-time-columns', document.body).data('current_value');

    function showMapBuilderTableResults(mapId, cb, errcb) {
        var map = compileMapObject();

        getMapRowsCountPromise(map)
            .then(function(response) {
                var decoded = $.parseJSON(response);
                var count = decoded.message;

                var keyBy = map.keyBy,
                    limitTo = parseInt($('#map-sample-results-limit').val()),
                    limit = 100,
                    offset = 0,
                    iterations = Math.ceil(count / limit),
                    results = [],
                    dataLoaded = false,
                    counter = 0;

                var resultTemplate = Handlebars.compile($('#wp2l-map-results-table')[0].innerHTML);
                $('#wp2l-results-preview-wrap', document.body).empty();

                if (0 === count) {
                    $('#wp2l-results-preview-wrap', document.body).append(resultTemplate({
                        results: results,
                        keyByColumn: keyBy
                    }));

                    $('table#wp2l-results-preview').floatThead({
                        scrollContainer: function(table) {
                            return table.closest('#wp2l-results-preview-wrap-inner');
                        }
                    });

                    $('.toggle-label').on('click', 'tr', function(){
                        $(this).parents().next('.hide').toggle();
                    });

                    $( document.body ).trigger( 'wp2lead_unblock_mapbuilder' );

                    window.onLoadFirst = false;

                    var resultTableExcludeColumn = $(document.body).find('.exclude-this-column');

                    var columnsNotToExclude = [];

                    var groupBySelect = $('#wp2l-group-map-results-by'),
                        groupBySelected = groupBySelect.val(),
                        groupConcatSelect = $('#wp2l-group-concat-for'),
                        groupConcatSelected = groupConcatSelect.val(),
                        comparisonFields = $('.column-comparison-map-fields');

                    columnsNotToExclude.push(groupBySelected);

                    if (groupConcatSelected) {
                        columnsNotToExclude = columnsNotToExclude.concat(groupConcatSelected);
                    }

                    if (comparisonFields.length > 0) {
                        comparisonFields.each(function() {
                            var tableColumnIdentifier = $(this).find('.table-column-identifier');
                            var tableColumnIdentifierSelected = tableColumnIdentifier.val();

                            if (tableColumnIdentifierSelected) {
                                columnsNotToExclude.push(tableColumnIdentifierSelected);
                            }
                        });
                    }

                    var virtualRelationItem = $('.virtual-relationship');

                    if (virtualRelationItem.length) {
                        virtualRelationItem.each(function() {
                            var tableFrom = $(this).find('.virtual-table_from').val();
                            var columnFrom = $(this).find('.virtual-column_from').val();

                            var vNotToExclude = tableFrom + '.' + columnFrom;

                            columnsNotToExclude.push(vNotToExclude);
                        });
                    }

                    if (resultTableExcludeColumn.length > 0 && columnsNotToExclude.length > 0) {
                        for (var e = 0; e < columnsNotToExclude.length; e++) {
                            resultTableExcludeColumn.each(function() {
                                var columnToExclude = $(this).data('column_key');

                                var isNotForExclude = columnToExclude.startsWith(columnsNotToExclude[e]);

                                if (isNotForExclude) {
                                    $(this).addClass('disabled');
                                }
                            });
                        }
                    }

                    setTimeout(function () {
                        unblockElement($('#wp2l-results-preview-wrap'));
                    }, 100);
                } else {
                    while (!dataLoaded) {
                        offset = limit * counter;

                        var data = {
                            action: 'wp2l_fetch_map_query_results',
                            map: JSON.stringify(map),
                            limit: limit,
                            offset: offset
                        };

                        $.ajax({
                            type: 'post',
                            url: ajaxurl,
                            async: false,
                            data: data,
                            success: function(response) {
                                var decoded;

                                try {
                                    decoded = $.parseJSON(response);
                                } catch(err) {
                                    decoded = false;
                                }

                                if (decoded) {
                                    $.merge(results, decoded);
                                }
                            },
                            error: function(xhr, status, error) {},
                            complete: function(xhr, status) {}
                        });

                        counter++;

                        var dataLength = results.length;

                        if (dataLength > limitTo || counter === iterations) {
                            dataLoaded = true;
                            var resultsToShow = results.slice(0, limitTo);

                            $('#wp2l-results-preview-wrap', document.body).append(resultTemplate({
                                results: resultsToShow,
                                keyByColumn: keyBy
                            }));

                            $('table#wp2l-results-preview').floatThead({
                                scrollContainer: function(table) {
                                    return table.closest('#wp2l-results-preview-wrap-inner');
                                }
                            });

                            $('.toggle-label').on('click', 'tr', function(){
                                $(this).parents().next('.hide').toggle();
                            });

                            $( document.body ).trigger( 'wp2lead_unblock_mapbuilder' );

                            window.onLoadFirst = false;

                            var resultTableExcludeColumn = $(document.body).find('.exclude-this-column');

                            var columnsNotToExclude = [];

                            var groupBySelect = $('#wp2l-group-map-results-by'),
                                groupBySelected = groupBySelect.val(),
                                groupConcatSelect = $('#wp2l-group-concat-for'),
                                groupConcatSelected = groupConcatSelect.val(),
                                comparisonFields = $('.column-comparison-map-fields');

                            columnsNotToExclude.push(groupBySelected);

                            if (groupConcatSelected) {
                                columnsNotToExclude = columnsNotToExclude.concat(groupConcatSelected);
                            }

                            if (comparisonFields.length > 0) {
                                comparisonFields.each(function() {
                                    var tableColumnIdentifier = $(this).find('.table-column-identifier');
                                    var tableColumnIdentifierSelected = tableColumnIdentifier.val();

                                    if (tableColumnIdentifierSelected) {
                                        columnsNotToExclude.push(tableColumnIdentifierSelected);
                                    }
                                });
                            }

                            var virtualRelationItem = $('.virtual-relationship');

                            if (virtualRelationItem.length) {
                                virtualRelationItem.each(function() {
                                    var tableFrom = $(this).find('.virtual-table_from').val();
                                    var columnFrom = $(this).find('.virtual-column_from').val();

                                    var vNotToExclude = tableFrom + '.' + columnFrom;

                                    columnsNotToExclude.push(vNotToExclude);
                                });
                            }

                            if (resultTableExcludeColumn.length > 0 && columnsNotToExclude.length > 0) {
                                for (var e = 0; e < columnsNotToExclude.length; e++) {
                                    resultTableExcludeColumn.each(function() {
                                        var columnToExclude = $(this).data('column_key');

                                        var isNotForExclude = columnToExclude.startsWith(columnsNotToExclude[e]);

                                        if (isNotForExclude) {
                                            $(this).addClass('disabled');
                                        }
                                    });
                                }
                            }

                            setTimeout(function () {
                                unblockElement($('#wp2l-results-preview-wrap'));
                            }, 100);
                        }
                    }
                }
            })
    }

    var mapGeneratorForm = $('#map-generator');

    // Check if we are on Map builder page
    if (mapGeneratorForm.length > 0) {
        getAllTablesPromise()
            .then(function() {
                return getAllColumnsPromise();
            })
            .then(function(allColumnsJson) {
                try {
                    window.allColumnsOnLoad = $.parseJSON(allColumnsJson);
                } catch(err) {
                    window.allColumnsOnLoad = [];
                }

                if (starterDataOnLoad) {
                    $('.wp2l_starter_data').data('previousValue', starterDataOnLoad);
                    updateGroupByOptions();
                }

                return allColumnsJson;
            })
            .then(function (allColumnsJson) {
                try {
                    var allColumns = $.parseJSON(allColumnsJson);
                } catch(err) {
                    var allColumns = [];
                }

                var mapping = getMapping();

                if (!mapping) {
                    $( document.body ).trigger( 'wp2lead_unblock_mapbuilder' );
                    setPossibleTagsOnLoad();
                } else {
                    setRelationsOnLoad();
                    setComparisonsOnLoad();
                    setGroupConcatOnLoad();
                    setDateTimeColumnsOnLoad();
                    setExcludesOnLoad();
                    setSearchResultsOnLoad();
                    setPossibleTagsOnLoad();
                }
            });
    }

    function setPossibleTagsOnLoad() {
        var tagCloudHolder = $('#manualPrecreatedTags_container');
        var createdTags = tagCloudHolder.data('saved-value');

        if (typeof createdTags == 'object') {
            $.each(createdTags, function (index, createdTag) {
                if (createdTag) {
                    var tag = createdTag;
                    var prefix = '';
                    var tagArray = createdTag.split('||');

                    if (tagArray.length === 2) {
                        prefix = tagArray[0];
                        tag = tagArray[1];
                    }

                    tagCloudHolder.append(createPossibleTag(tag, prefix));
                }
            });

            $('#manualPrecreatedTags_row .api-processing-holder .api-spinner-holder').removeClass('api-processing');
        } else {
            $('#manualPrecreatedTags_row .api-processing-holder .api-spinner-holder').removeClass('api-processing');
        }

        var inputTagsContainer = $('#userInputPrecreatedTags_container');
        var inputTagsSaved = inputTagsContainer.data('saved-value');
        var template = Handlebars.compile($('#wp2l-map-builder-user-input-recomended-tags-item')[0].innerHTML);

        if (inputTagsSaved) {
            getAllTablesPromise().then(function(availableTables) {
                $.each(inputTagsSaved, function (index, createdTag) {
                    // newInputTagsContainerButton.trigger('click');

                    inputTagsContainer.append(template({
                        availableTables: availableTables,
                        title: createdTag.title,
                        prefix: createdTag.prefix
                    }));

                    var lastRecomendedTagsItem = inputTagsContainer.find('.user-input-recomended-tags-item').last();
                    setUserInputRecomendedTagsFromTable(lastRecomendedTagsItem, createdTag, availableTables);
                });
            });
        } else {
            $('#userInputTags_row .api-spinner-holder').removeClass('api-processing');
        }

    }

    function setUserInputRecomendedTagsFromTable(recomendedTagsItem, settings, availableTables) {
        getAllColumnsRecomendedTagsPromise(recomendedTagsItem, settings).then(function(response) {
            var allColumns = $.parseJSON(response);
            var columns = allColumns.columns;
            var groupBySelect = recomendedTagsItem.find('.recomended-tags-group-by');
            var fromTableSelect = recomendedTagsItem.find('.recomended-tags-from-table');
            var tagColumnSelect = recomendedTagsItem.find('.recomended-tags-columns').empty();
            var tagComparisonsHolder = recomendedTagsItem.find('.recomended-tags-comparisons-holder .recomended-tags-comparisons-list');
            var tagJoinedHolder = recomendedTagsItem.find('.recomended-tags-joined-tables-holder .recomended-tags-joined-tables-list');
            var joins = settings.joins;

            var templateTagComparisons = Handlebars.compile($('#wp2l-map-builder-user-input-recomended-tags-comparisons-item')[0].innerHTML);
            var templateTagJoined = Handlebars.compile($('#wp2l-map-builder-user-input-recomended-tags-joined-tables-item')[0].innerHTML);


            var groupByColumnsList = fetchColumnsForTable(settings.fromTable).columns;

            fromTableSelect.val(settings.fromTable).data('value', settings.fromTable);

            $.each(columns, function (index, column) {
                tagColumnSelect.append($('<option value="' + column + '">'  + column + '</option>'));
            });

            tagColumnSelect.val(settings.tagColumn).data('value', settings.tagColumn).attr('disabled', false);

            $.each(groupByColumnsList, function (index, column) {
                groupBySelect.append($('<option value="' + settings.fromTable + '.' + column + '">' + settings.fromTable + '.' + column + '</option>'));
            });

            groupBySelect.val(settings.groupBy).attr('disabled', false).data('value', settings.groupBy);

            $.each(settings.comparisons, function (index, comparison) {
                if (Object.keys(comparison.conditions).length > 1) {
                    $.each(comparison.conditions, function (index, condition) {
                        tagComparisonsHolder.append(templateTagComparisons({
                            allColumns: columns,
                            operator: condition.operator,
                            string: condition.string,
                            onload: true
                        }));

                        tagComparisonsHolder.find('.recomended-tags-comparison-column').last().attr('disabled', false).val(comparison.tableColumn).data('value', comparison.tableColumn);
                    });
                } else {
                    tagComparisonsHolder.append(templateTagComparisons({
                        allColumns: columns,
                        operator: comparison.conditions[0].operator,
                        string: comparison.conditions[0].string,
                        onload: true
                    }));

                    tagComparisonsHolder.find('.recomended-tags-comparison-column').last().attr('disabled', false).val(comparison.tableColumn).data('value', comparison.tableColumn);
                }
            });

            $.each(joins, function (index, join) {
                var existingTableChoices = findExistingRecomendedTagsTableChoices(recomendedTagsItem),
                    referenceTable = join.referenceTable,
                    referenceColumn = join.referenceColumn,
                    referenceColumnsList = fetchColumnsForTable(referenceTable).columns,
                    joinTable = join.joinTable,
                    joinColumn = join.joinColumn,
                    joinColumnsList = fetchColumnsForTable(joinTable).columns;

                tagJoinedHolder.append(templateTagJoined({
                    availableTables: availableTables,
                    existingTableChoices: existingTableChoices,
                    onload: true
                }));

                var refColumnSelect = tagJoinedHolder.find('.recomended-tags-ref-column').last();
                var joinColumnSelect = tagJoinedHolder.find('.recomended-tags-join-column').last();

                $.each(referenceColumnsList, function (index, column) {
                    refColumnSelect.append($('<option value="' + column + '">' + column + '</option>'));
                });

                $.each(joinColumnsList, function (index, column) {
                    joinColumnSelect.append($('<option value="' + column + '">' + column + '</option>'));
                });

                tagJoinedHolder.find('.recomended-tags-ref-table').last().attr('disabled', false).val(referenceTable).data('value', referenceTable);
                tagJoinedHolder.find('.recomended-tags-ref-column').last().attr('disabled', false).val(referenceColumn).data('value', referenceColumn);
                tagJoinedHolder.find('.recomended-tags-join-table').last().attr('disabled', false).val(joinTable).data('value', joinTable);
                tagJoinedHolder.find('.recomended-tags-join-column').last().attr('disabled', false).val(joinColumn).data('value', joinColumn);
            });

            $('#userInputTags_row .api-spinner-holder').removeClass('api-processing');
        });
    }

    function setSearchResultsOnLoad() {

        if (searchFromMapOnLoad.length > 0 && '' !== searchFromMapOnLoad.val()) {
            blockSearchResults();
            var i = 0;
            var s = 0;
            var t = 0;
            var searchFromMapOnLoadValues = $.parseJSON(searchFromMapOnLoad.val());

            window.searchFromMap = searchFromMapOnLoadValues;

            var resultsTables = 0;

            for (s = 0; s < searchFromMapOnLoadValues.length; s++) {
                for (t = 0; t < searchFromMapOnLoadValues[s].tables.length; t++ ) {
                    resultsTables++;
                }
            }

            window.searchTablesFromMap = resultsTables;

            searchFromMapOnLoadValues.forEach(function(item) {
                i++;
                getSearchResultPromise(i, item.string, item.tables);

                unblockSearchResults();
            });
        }

        if (searchTableFromMapOnLoad.length > 0) {
            blockSearchResults();
            for (var t = 0; t < searchTableFromMapOnLoad.length; t++) {
                var table = searchTableFromMapOnLoad[t];

                var data = {action: 'wp2l_global_table_search_results', 'table': table};

                getTableSearchResultPromise(data).then(function(response) {
                    var decoded = $.parseJSON(response);

                    var existed = $(document.body).find('#table-search-result-' + table);

                    if(existed.length > 0) {
                        existed.remove();
                    }

                    renderTableSearchResult(table, decoded, null);

                    unblockSearchResults();
                });
            }
        }
    }

    function getAllFieldsOnChange(cbError) {
        var options = [],
            virtualOptions = [],
            errors = [],

            dedupOptions = [],
            dedupVirtualOptions = [],

            starterData = $('.wp2l_starter_data'),
            relationShips = $(document.body).find('.relationship-join-table');

        var promises = [];

        promises.push(
            $.ajax({
                type: 'post',
                url: ajaxurl,
                async: false,
                data: {action: 'wp2l_fetch_column_options', 'table': starterData.val()},
                success: function (response) {
                    var columnsJson = $.parseJSON(response);

                    if (columnsJson.length > 0) {
                        for (var c = 0; c < columnsJson.length; c++) {
                            options.push(starterData.val() + '.' + columnsJson[c]);
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.log(xhr);
                    console.log(status);
                    console.log(error);
                },
                complete: function (xhr, status) {
                }
            })
        );

        if (relationShips.length > 0) {
            relationShips.each(function() {
                var relationTable = $(this).val();

                promises.push(
                    $.ajax({
                        type: 'post',
                        url: ajaxurl,
                        async: false,
                        data: {action: 'wp2l_fetch_column_options', 'table': relationTable},
                        success: function (response) {
                            var columnsJson = $.parseJSON(response);

                            if (columnsJson.length > 0) {
                                for (var c = 0; c < columnsJson.length; c++) {
                                    options.push(relationTable + '.' + columnsJson[c]);
                                }
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log(xhr);
                            console.log(status);
                            console.log(error);
                        },
                        complete: function (xhr, status) {
                        }
                    })
                );
            });
        }

        var mapping = compileMapObject();

        promises.push(
            $.ajax({
                type: 'post',
                url: ajaxurl,
                async: false,
                data: {
                    action: 'wp2l_fetch_all_columns_for_map',
                    map_id: null,
                    new_map: JSON.stringify(mapping),
                    is_new_map: true
                },
                success: function (response) {
                    try {
                        virtualOptions = $.parseJSON(response);
                    } catch(err) {
                        virtualOptions = [];
                    }
                },
                error: function (xhr, status, error) {
                    errors.push(
                        {
                            "virtualOptions" : {
                                status: status,
                                error: error
                            }
                        }
                    );
                    console.log(xhr);
                    console.log(status);
                    console.log(error);
                },
                complete: function (xhr, status) {
                }
            })
        );

        return $.when.apply($, promises).then(function() {
            dedupOptions = _.uniq(options);
            dedupOptions.sort();

            virtualOptions = virtualOptions.concat(options);

            dedupVirtualOptions = _.uniq(virtualOptions);
            dedupVirtualOptions.sort();

            return true;
        }).then(function() {

            var data = {
                options: dedupOptions,
                virtualOptions: dedupVirtualOptions,
                errors: errors
            };
            return data;

        }).fail(function() {
            var data = {
                options: dedupOptions,
                virtualOptions: dedupVirtualOptions,
                errors: errors
            };

            // Callback
            if (typeof cbError === 'function') {
                cbError(data);
            }
        });
    }

    function compileSearchResultByTable() {
        var tableSearchResultsRow = $(document.body).find('.table-search-result-row');
        var tableSearchResultsRowCurrentValue = tableSearchResultsRow.data('current_value');
        var tableSearchResultsHolder = $(document.body).find('#wp2l-table-search-result-holder');
        var tableSearchResults = tableSearchResultsHolder.find('.wp2l-table-search-result');
        var searchTables = [];

        if (tableSearchResults.length > 0) {
            tableSearchResults.each(function() {
                var table = $(this).find('.wp2l-table-search-header .wp2l-table-search-close').data('table');
                searchTables.push(table);
            });
        }

        tableSearchResultsRow.data('current_value', JSON.stringify(searchTables));
    }

    function getTableSearchResultPromise(data) {
        return $.ajax({
            type: 'post',
            url: ajaxurl,
            async: false,
            data: data,
            success: function (response) {},
            error: function (xhr, status, error) {
                console.log(xhr);
                console.log(status);
                console.log(error);
            },
            complete: function (xhr, status) {
            }
        });
    }

    function renderTableSearchResult(searchTable, searchResult, after, cb) {
        var searchForm = $('#wp2l-table-search-form');
        var searchFormOption = searchForm.find('option[value="' + searchTable + '"]');
        var resultTemplate = Handlebars.compile($('#wp2l-table-search-result')[0].innerHTML);

        if (after) {
            after.after(resultTemplate({label: searchTable, table: searchTable, columns: searchResult.columns, columnsTitles: searchResult.columnsTitles, results: searchResult.results}));
        } else {
            $('#wp2l-table-search-result-holder').prepend(resultTemplate({label: searchTable, table: searchTable, columns: searchResult.columns, columnsTitles: searchResult.columnsTitles, results: searchResult.results}));
        }

        var tableToScroll = $('#table-search-result-' + searchTable + ' .table-search-result-table');

        tableToScroll.floatThead({
            scrollContainer: function(table){
                return table.closest('.table-search-result-table-wrap');
            }
        });

        searchFormOption.attr('disabled', true);

        if (typeof cb === 'function') {
            cb();
        }
    }

    function compileSearchResultByString() {
        var multiSearchResultRow = $(document.body).find('.multi-search-result-row');
        var multiSearchResultSingleRow = $(document.body).find('.multi-search-single-result-row');
        var multiSearchResultRowCurrentValue = multiSearchResultSingleRow.data('current_value');
        var multiSearchResultTagsHolder = multiSearchResultRow.find('#wp2l-multi-search-result-tags');
        var multiSearchResultTags = multiSearchResultTagsHolder.find('.multi-search-tag');

        var searchStrings = [];

        if (multiSearchResultTags.length > 0) {
            multiSearchResultTags.each(function() {
                var index = $(this).data('seq');
                var searchString = $(this).find('.tag-use').text();
                var singleResults = multiSearchResultSingleRow.find('.single-result-seq-' + index);

                var searchItem = {
                    string: searchString,
                    tables: []
                };

                if (singleResults.length > 0) {
                    singleResults.each(function() {
                        var table = $(this).find('.wp2l-multi-search-single-close').data('table');
                        searchItem.tables.push(table);
                    });
                }

                searchStrings.push(searchItem);
            });
        }

        multiSearchResultSingleRow.data('current_value', JSON.stringify(searchStrings));
    }

    function updateSelectsOptionsOnStart(cb) {
        var groupBySelect = $('#wp2l-group-map-results-by');
        var groupBySelected = null;
        var groupConcatSelect = $('#wp2l-group-concat-for');
        var dateTimeSelect = $('#wp2l-date-time-columns');
        var excludesSelect = $('#wp2l-column-options');
        var options = [];

        fetchUpdatedColumnOptions().then(function(tables) {
            for (var i = 0; i < tables.length; i++) {
                for (var j = 0; j < tables[i]['columns'].length; j++) {
                    options.push(tables[i]['table'] + '.' + tables[i]['columns'][j]);
                }
            }

            var dedupOptions = _.uniq(options);
            dedupOptions.sort();

            var template = Handlebars.compile($('#wp2l-columns-option')[0].innerHTML);

            for (var j = 0; j < dedupOptions.length; j++) {
                var concatData = dedupOptions[j].split('.');

                var concatOptions = template({
                    table: concatData[0],
                    column: concatData[1]
                });

                groupBySelect.append(concatOptions);
                groupConcatSelect.append(concatOptions);
                dateTimeSelect.append(concatOptions);
                excludesSelect.append(concatOptions);
            }

            groupBySelected = groupBySelect.val();
            groupBySelect.data('current_value', groupBySelected);
            excludesSelect.find('option[value="' + groupBySelected + '"]').remove();

            // Callback
            if (typeof cb === 'function') {
                cb();
            }
        });
    }

    function unblockVirtualRelationsOnChange() {
        var virtualRelationships = $(document.body).find('.virtual-relationship');

        if (virtualRelationships.length > 0) {
            virtualRelationships.find('select').attr('disabled', false);
            virtualRelationships.find('.remove-virtual-relationship').removeClass('disabled');
        }
    }

    function getSelectedOptionsColumns(changedOptionVal, cb) {

        var options = [];
        var virtualOptions = [];

        var groupBySelect = $('#wp2l-group-map-results-by');
        var groupBySelected = groupBySelect.val();

        if (groupBySelected) {
            virtualOptions.push(groupBySelected);
        }

        var comparisons = $('.column-comparison-map-fields .table-column-identifier');
        var comparisonsSelected = [];

        if (comparisons.length > 0) {
            comparisons.each(function() {
                comparisonsSelected.push($(this).val());
            });
        }

        var dateTimeSelect = $('#wp2l-date-time-columns');
        var dateTimeSelected = dateTimeSelect.val();

        var groupConcatSelect = $('#wp2l-group-concat-for');
        var groupConcatSelected = groupConcatSelect.val();

        var excludesSelect = $('#wp2l-column-options');
        var excludesSelected = excludesSelect.val();

        if (excludesSelect.find('option').length > 0) {
            excludesSelect.find('option').each(function() {
                virtualOptions.push($(this).val());
            });
        }

        fetchUpdatedColumnOptions().then(function(tables) {
            if (tables.length > 0) {
                for (var i = 0; i < tables.length; i++) {
                    for (var j = 0; j < tables[i]['columns'].length; j++) {
                        virtualOptions.push(tables[i]['table'] + '.' + tables[i]['columns'][j]);
                    }
                }
            }

            var template = Handlebars.compile($('#wp2l-columns-option')[0].innerHTML);
            var dedupVirtualOptions = _.uniq(virtualOptions);
            dedupVirtualOptions.sort();

            // Generate Exclude Options
            for (j = 0; j < dedupVirtualOptions.length; j++) {
                var excludeData = dedupVirtualOptions[j].split('.');

                var excludeOptions = template({
                    table: excludeData[0],
                    column: excludeData[1]
                });

                excludesSelect.append(excludeOptions);
            }

            if (comparisonsSelected) {
                for (j = 0; j < comparisonsSelected.length; j++) {
                    excludesSelect.find('option[value="' + comparisonsSelected[j] + '"]').remove();
                }
            }

            if (groupConcatSelected) {
                for (j = 0; j < groupConcatSelected.length; j++) {
                    excludesSelect.find('option[value="' + groupConcatSelected[j] + '"]').remove();
                }
            }

            if (dateTimeSelected) {
                for (j = 0; j < dateTimeSelected.length; j++) {
                    excludesSelect.find('option[value="' + dateTimeSelected[j] + '"]').remove();
                }
            }

            excludesSelect.find('option[value="' + groupBySelected + '"]').remove();

            if (excludesSelected) {
                for (j = 0; j < excludesSelected.length; j++) {
                    excludesSelect.find('option[value="' + excludesSelected[j] + '"]').prop('selected', true);
                }
            }

            if (typeof cb === 'function') {
                cb();
            }
        });
    }

    function updateAllFieldsOnChange(callback, callbackError) {
        getAllFieldsOnChange(function() {
            if (typeof callbackError === 'function') {
                callbackError();
            }
        })
            .then(function(data) {
                var options = data.options;
                var virtualOptions = data.virtualOptions;

                var groupBySelect = $('#wp2l-group-map-results-by'),
                    groupBySelected = groupBySelect.val(),

                    groupConcatSelect = $('#wp2l-group-concat-for'),
                    groupConcatSelected = groupConcatSelect.val(),

                    excludesSelect = $('#wp2l-column-options'),
                    excludesSelected = excludesSelect.val(),

                    dateTimeSelect = $('#wp2l-date-time-columns'),
                    dateTimeSelected = dateTimeSelect.val(),

                    comparisonFields = $('.column-comparison-map-fields');

                var template = Handlebars.compile($('#wp2l-columns-option')[0].innerHTML);

                excludesSelect.empty();

                // Generate Exclude Options
                for (var j = 0; j < virtualOptions.length; j++) {
                    var excludeData = virtualOptions[j].split('.');

                    var excludeOptions = template({
                        table: excludeData[0],
                        column: excludeData[1]
                    });

                    excludesSelect.append(excludeOptions);
                }

                excludesSelect.find('option[value="' + groupBySelected + '"]').remove();

                // Generate Comparison fields
                if (comparisonFields.length > 0) {
                    comparisonFields.each(function() {
                        var tableColumnIdentifier = $(this).find('.table-column-identifier');
                        var tableColumnIdentifierSelected = tableColumnIdentifier.val();

                        tableColumnIdentifier.empty();

                        for (var j = 0; j < virtualOptions.length; j++) {
                            var excludeData = virtualOptions[j].split('.');

                            var excludeOptions = template({
                                table: excludeData[0],
                                column: excludeData[1]
                            });

                            tableColumnIdentifier.append(excludeOptions);
                        }

                        tableColumnIdentifier.find('option[value="' + tableColumnIdentifierSelected + '"]').prop('selected', true);
                    });
                }

                groupConcatSelect.empty();

                for (var j = 0; j < options.length; j++) {
                    var concatData = options[j].split('.');

                    var concatOptions = template({
                        table: concatData[0],
                        column: concatData[1]
                    });

                    groupConcatSelect.append(concatOptions);
                }

                dateTimeSelect.empty();

                for (var j = 0; j < options.length; j++) {
                    var dateTimeData = options[j].split('.');

                    var dateTimeDataOptions = template({
                        table: dateTimeData[0],
                        column: dateTimeData[1]
                    });

                    dateTimeSelect.append(dateTimeDataOptions);
                }

                if (groupConcatSelected) {
                    for (j = 0; j < groupConcatSelected.length; j++) {
                        excludesSelect.find('option[value="' + groupConcatSelected[j] + '"]').remove();
                        groupConcatSelect.find('option[value="' + groupConcatSelected[j] + '"]').prop('selected', true);
                    }
                }

                if (dateTimeSelected) {
                    for (j = 0; j < dateTimeSelected.length; j++) {
                        excludesSelect.find('option[value="' + dateTimeSelected[j] + '"]').remove();
                        dateTimeSelect.find('option[value="' + dateTimeSelected[j] + '"]').prop('selected', true);
                    }
                }

                if (excludesSelected) {
                    for (j = 0; j < excludesSelected.length; j++) {
                        groupConcatSelect.find('option[value="' + excludesSelected[j] + '"]').remove();
                        dateTimeSelect.find('option[value="' + excludesSelected[j] + '"]').remove();
                        excludesSelect.find('option[value="' + excludesSelected[j] + '"]').prop('selected', true);
                    }
                }

                if (typeof callback === 'function') {
                    callback();
                }
            });
    }

    /**
     * Get All Columns from DB Tables
     *
     * @returns {*}
     */
    function fetchUpdatedColumnOptions() {
        return new Promise(function (resolve, reject) {
            var columns = getColumnsOptions();

            resolve(columns);
        });
    }

    function getColumnsOptions() {
        var options = [];

        var starterDataTable = $('.wp2l_starter_data').val();
        options.push(fetchColumnsForTable(starterDataTable));

        $(document.body).find('.relationship-join-table').each(function () {
            options.push(fetchColumnsForTable($(this).val()));
        });

        var result = _.uniq(options, function (x) {
            return x.table;
        });

        return result;
    }

    function updateSelectsOptionsOnRelations(cb) {
        fetchUpdatedColumnOptions().then(function(tables) {
            // Callback
            if (typeof cb === 'function') {
                cb(tables);
            }
        });
    }

    function fetchColumnsForTable(table, indexes) {
        var options = null;
        var allTablesColumns = window.allTablesColumns;

        for (var i = 0; i < allTablesColumns.length; i++) {
            if (allTablesColumns[i].table === table) {
                options = allTablesColumns[i];
                break;
            }
        }

        var data = {
            action: 'wp2l_fetch_column_options',
            table: table
        };

        if (indexes) {
            data.indexes = 2;
        }

        if (!options || indexes) {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                async: false,
                data: data,
                success: function (response) {
                    options = {
                        table: table,
                        columns: $.parseJSON(response)
                    };
                }
            });

            if (!indexes) {
                allTablesColumns.push(options);
            }
        }

        window.allTablesColumns = allTablesColumns;

        return options;
    }

    function setTimerForRefreshMappingOnChange() {
        resetTimerForRefreshOnChange();
        window.refreshTimer = setTimeout( maybeRefreshMappingOnChange, '600000' );
    }

    function resetTimerForRefreshOnChange() {
        clearTimeout( window.refreshTimer );
    }

    function maybeRefreshMappingOnChange() {
        refreshMappingOnChange();
    }

    /**
     * Refresh map fields on change any field
     */
    function refreshMappingOnChange() {
        clearTimeout( window.refreshTimer );

        $( document.body ).trigger( 'wp2lead_block_mapbuilder' );

        getAllTablesPromise().then(function () {
                return getAllColumnsPromise();
            })
            .then(function(allColumnsJson) {
                window.allColumnsOnChange = $.parseJSON(allColumnsJson);
            })
            .then(function() {
                setRelationsOnChange();
                setComparisonsOnChange();
                setGroupConcatOnChange();
                setDateTimeColumnsOnChange();
                setExcludesOnChange();
            });
    }

    function setRelationsOnChange() {
        window.setOnChange.relationsOnChange = true;
        var mapping = compileMapObject();

        var relations = mapping.joins;

        if (relations) {
            setRelations(relations, function() {
                delete window.setOnChange.relationsOnChange;

                refreshMapResultOnChange();
            });

        } else {
            delete window.setOnChange.relationsOnChange;

            refreshMapResultOnChange();
        }
    }

    function setRelationsOnLoad() {
        window.setOnLoad.relationsOnLoad = true;

        if (relationsOnLoad) {
            var relations = relationsOnLoad;

            setRelations(relations, function() {
                delete window.setOnLoad.relationsOnLoad;

                refreshMapResultOnLoad();
            });
        } else {
            delete window.setOnLoad.relationsOnLoad;

            refreshMapResultOnLoad();
        }
    }

    function setRelations(relations, cb) {
        getAllTablesPromise().then(function(availableTables) {
            var isDisabled = $('#relationship-map-holder').hasClass('disabled'),
                disabled = '';

            if ( isDisabled ) {
                disabled = ' disabled';
            }

            if (relations.length > 0) {
                $('#relationship-map-holder').empty();
            }

            var relationshipFieldsTemplate = Handlebars.compile($('#wp2l-relationship-map-fields')[0].innerHTML);

            for (var i = 0; i < relations.length; i++) {

                var existingTableChoices = findExistingTableChoices();

                var incrementedTables = existingTableChoices.map(function(existingTableChoice) {
                    var splitter = existingTableChoice.split('-');

                    if(existingTableChoice === splitter[0]) {
                        return existingTableChoice + '-2';
                    } else {
                        return splitter[0] + '-' + (splitter[1] + 1);
                    }
                });

                var availableTablesIndex = availableTables.concat(incrementedTables).sort();

                var index = $('#relationship-map-holder .relationship-map-fields').length,
                    referenceTable = relations[i].referenceTable,
                    referenceColumn = relations[i].referenceColumn,
                    referenceColumnsList = fetchColumnsForTable(referenceTable),
                    joinTable = relations[i].joinTable,
                    joinColumn = relations[i].joinColumn,
                    joinColumnsList = fetchColumnsForTable(joinTable, true);

                $('#relationship-map-holder', document.body).append(relationshipFieldsTemplate({
                    index: index,
                    availableTables: availableTablesIndex,
                    existingTables: existingTableChoices,
                    joinTable: joinTable,
                    joinColumn: joinColumn,
                    referenceTable: referenceTable,
                    referenceColumn: referenceColumn,
                    disabled: disabled
                }));

                $('#relationship-map-holder .relationship-map-fields select[name=relationship\\[' + index + '\\]\\[reference-table\\]]')
                    .val(referenceTable);

                $('#relationship-map-holder .relationship-map-fields select[name=relationship\\[' + index + '\\]\\[join-table\\]]')
                    .val(joinTable);


                var referenceColumnSelect = $('#relationship-map-holder .relationship-map-fields select[name=relationship\\[' + index + '\\]\\[reference-column\\]]');
                var joinColumnSelect = $('#relationship-map-holder .relationship-map-fields select[name=relationship\\[' + index + '\\]\\[join-column\\]]');

                referenceColumnSelect.empty();
                joinColumnSelect.empty();

                for (var j = 0; j < referenceColumnsList['columns'].length; j++) {
                    referenceColumnSelect.append($('<option value="' + referenceColumnsList['columns'][j] + '">' + referenceColumnsList['columns'][j] + '</option>'));
                }

                $('#relationship-map-holder .relationship-map-fields select[name=relationship\\[' + index + '\\]\\[reference-column\\]]')
                    .val(referenceColumn);

                for (var j = 0; j < joinColumnsList['columns'].length; j++) {
                    joinColumnSelect.append($('<option value="' + joinColumnsList['columns'][j] + '">' + joinColumnsList['columns'][j] + '</option>'));
                }

                $('#relationship-map-holder .relationship-map-fields select[name=relationship\\[' + index + '\\]\\[join-column\\]]')
                    .val(joinColumn);

                if (existingTableChoices) {
                    for (var j = 0; j < existingTableChoices.length; j++) {
                        $('#relationship-map-holder .relationship-map-fields select[name=relationship\\[' + index + '\\]\\[join-table\\]]').find('option[value="' + existingTableChoices[j] + '"]').attr('disabled', true);
                    }
                }
            }

            var relationshipFields = $('.relationship-map-fields'),
                relationshipFieldsCount = relationshipFields.length;

            relationshipFields.each(function(count) {
                if ((count + 1) === relationshipFieldsCount) {

                } else {
                    $(this).find('select').attr('disabled', true);
                    $(this).find('.remove-relationship').addClass('disabled');
                }
            });

            $(".relationship-map-fields select option").filter(function() {
                return !this.value;
            }).remove();

            cb();
        });
    }

    function setComparisonsOnLoad() {
        window.setOnLoad.comparisonOnLoad = true;

        var mapping = compileMapObject();
        var excludes = mapping.excludes;
        var allColumns = window.allColumnsOnLoad;

        if (mapping.selects_only && mapping.selects_only.length > 0) {
            $.each(allColumns, function (index, item) {
                if ($.inArray(item, mapping.selects_only) < 0) {
                    excludes.push(item);
                }
            });
        }

        excludes = _.uniq(excludes);

        if (columnComparisonOnLoad) {
            var comparisons = columnComparisonOnLoad;

            setComparisons(comparisons, excludes, allColumns, function() {
                delete window.setOnLoad.comparisonOnLoad;

                refreshMapResultOnLoad();
            });

        } else {
            delete window.setOnLoad.comparisonOnLoad;

            refreshMapResultOnLoad();
        }
    }

    function setComparisonsOnChange() {
        window.setOnChange.comparisonOnChange = true;
        var mapping = compileMapObject();

        var comparisons = mapping.comparisons;

        var excludes = mapping.excludes;
        var allColumns = window.allColumnsOnChange;

        // if (mapping.selects_only && mapping.selects_only.length > 0) {
        //     $.each(allColumns, function (index, item) {
        //         if ($.inArray(item, mapping.selects_only) < 0) {
        //             excludes.push(item);
        //         }
        //     });
        // }

        excludes = _.uniq(excludes);

        if (comparisons) {
            setComparisons(comparisons, excludes, allColumns, function() {
                delete window.setOnChange.comparisonOnChange;

                refreshMapResultOnChange();
            });

        } else {
            delete window.setOnChange.comparisonOnChange;

            refreshMapResultOnChange();
        }
    }

    function setComparisons(comparisons, excludes, allColumns, cb) {
        var isDisabled = $('#column-comparison-holder').hasClass('disabled'),
            disabled = '';

        if ( isDisabled ) {
            disabled = ' disabled';
        }

        if (comparisons.length > 0) {
            $('#column-comparison-holder').empty();
        }

        if (excludes.length > 0) {
            allColumns = allColumns.filter( function( el ) {
                return excludes.indexOf( el ) < 0;
            } );
        }

        allColumns.sort();

        for (var i = 0; i < comparisons.length; i++) {
            $.each(comparisons[i].conditions, function (index, condition) {
                var index = $('#column-comparison-holder .column-comparison-map-fields').length;

                $('#column-comparison-holder', document.body).append(comparisonFieldsTemplate({
                    index: index,
                    availableTableColumns: allColumns,
                    tableColumn: comparisons[i].tableColumn,
                    operator: condition.operator,
                    string: condition.string,
                    disabled: disabled
                }));

                $('#column-comparison-holder .column-comparison-map-fields select[name=comparison\\[' + index + '\\]\\[table-column\\]]').val(comparisons[i].tableColumn);
            });
        }

        cb();
    }

    function setGroupConcatOnLoad() {
        window.setOnLoad.groupConcatOnLoad = true;

        var selectedConcat = [];

        if (columnGroupConcatOnLoad) {
            for (var field in columnGroupConcatOnLoad) {
                if (columnGroupConcatOnLoad.hasOwnProperty(field)) {
                    selectedConcat.push(columnGroupConcatOnLoad[field]);
                }
            }
        }

        var mapping = getMapping();
        var excludes = mapping.excludes;

        setGroupConcat(mapping, selectedConcat, excludes, starterDataOnLoad, relationsOnLoad, function() {
            delete window.setOnLoad.groupConcatOnLoad;

            refreshMapResultOnLoad();
        });
    }

    function setGroupConcatOnChange() {
        window.setOnChange.groupConcatOnChange = true;

        var mapping = compileMapObject();
        var selectedConcat = $('#wp2l-group-concat-for', document.body).val();
        var excludes = mapping.excludes;
        var starterData = $('#from-table', document.body).val();
        var relations = mapping.joins;

        setGroupConcat(mapping, selectedConcat, excludes, starterData, relations, function() {
            delete window.setOnChange.groupConcatOnChange;

            refreshMapResultOnChange();
        });
    }

    function setGroupConcat(mapping, selectedConcat, excludes, starterData, relations, cb) {

        var tables = [];
        var options = [];

        tables.push(fetchColumnsForTable(starterData));

        for (var i = 0; i < relations.length; i++) {
            tables.push(fetchColumnsForTable(relations[i].joinTable));
        }

        for (var i = 0; i < tables.length; i++) {
            for (var j = 0; j < tables[i]['columns'].length; j++) {
                options.push(tables[i]['table'] + '.' + tables[i]['columns'][j]);
            }
        }

        var dedupOptions = _.uniq(options);
        dedupOptions.sort();

        var template = Handlebars.compile($('#wp2l-columns-option')[0].innerHTML);
        $('#wp2l-group-concat-for', document.body).empty();

        for (var j = 0; j < dedupOptions.length; j++) {
            if ($.inArray(dedupOptions[j], excludes) < 0) {
                var concatData = dedupOptions[j].split('.');
                var concatOptions = template({
                    table: concatData[0],
                    column: concatData[1]
                });

                $('#wp2l-group-concat-for').append(concatOptions);
            }
        }

        $.each(selectedConcat, function (index, item) {
            $('#wp2l-group-concat-for', document.body).find('option[value="' + item + '"]').prop('selected', true);
        });

        cb();
    }

    function setDateTimeColumnsOnChange() {
        window.setOnChange.dateTimeOnLoad = true;

        var mapping = compileMapObject();
        var selectedDateTime = $('#wp2l-date-time-columns', document.body).val();
        var excludes = mapping.excludes;
        var starterData = $('#from-table', document.body).val();
        var relations = mapping.joins;

        var allColumns = window.allColumnsOnChange;

        // if (mapping.selects_only && mapping.selects_only.length > 0) {
        //     $.each(allColumns, function (index, item) {
        //         if ($.inArray(item, mapping.selects_only) < 0) {
        //             excludes.push(item);
        //         }
        //     });
        // }

        excludes = _.uniq(excludes);

        setDateTimeColumns(mapping, selectedDateTime, excludes, starterData, relations, allColumns, function() {
            delete window.setOnChange.dateTimeOnLoad;

            refreshMapResultOnLoad();
        });
    }

    function setDateTimeColumnsOnLoad() {
        window.setOnLoad.dateTimeOnLoad = true;

        var mapping = getMapping();
        var selectedDateTime = columnDateTimeOnLoad;
        var excludes = mapping.excludes;

        var allColumns = window.allColumnsOnLoad;

        if (mapping.selects_only && mapping.selects_only.length > 0) {
            $.each(allColumns, function (index, item) {
                if ($.inArray(item, mapping.selects_only) < 0) {
                    excludes.push(item);
                }
            });
        }

        excludes = _.uniq(excludes);

        setDateTimeColumns(mapping, selectedDateTime, excludes, starterDataOnLoad, relationsOnLoad, allColumns, function() {
            delete window.setOnLoad.dateTimeOnLoad;

            refreshMapResultOnLoad();
        });
    }

    function setDateTimeColumns(mapping, selectedDateTime, excludes, starterData, relations, allColumns, cb) {

        var tables = [];
        var options = [];

        tables.push(fetchColumnsForTable(starterData));

        for (var i = 0; i < relations.length; i++) {
            tables.push(fetchColumnsForTable(relations[i].joinTable));
        }

        for (var i = 0; i < tables.length; i++) {
            for (var j = 0; j < tables[i]['columns'].length; j++) {
                options.push(tables[i]['table'] + '.' + tables[i]['columns'][j]);
            }
        }

        var dedupOptions = _.uniq(options);
        dedupOptions.sort();

        allColumns.sort();

        var template = Handlebars.compile($('#wp2l-columns-option')[0].innerHTML);
        $('#wp2l-date-time-columns', document.body).empty();

        for (var j = 0; j < allColumns.length; j++) {
            if ($.inArray(allColumns[j], excludes) < 0) {
                var concatData = allColumns[j].split('.');
                var concatOptions = template({
                    table: concatData[0],
                    column: concatData[1]
                });

                $('#wp2l-date-time-columns').append(concatOptions);
            }
        }

        if (typeof selectedDateTime == 'object') {
            $.each(selectedDateTime, function (index, item) {
                $('#wp2l-date-time-columns', document.body).find('option[value="' + item + '"]').prop('selected', true);
            });
        }

        cb();
    }

    function setExcludesOnChange() {
        window.setOnChange.excludesOnChange = true;
        var mapping = compileMapObject();
        var selectedComparisons = mapping.comparisons;
        var selectedGroupConcat = mapping.groupConcat;
        var selectedDateTime = mapping.dateTime;

        var allColumns = window.allColumnsOnChange;

        setExcludes(mapping, selectedComparisons, selectedGroupConcat, selectedDateTime, allColumns, false, function() {
            delete window.setOnChange.excludesOnChange;

            refreshMapResultOnChange();
        });
    }

    function setExcludesOnLoad() {
        window.setOnLoad.excludesOnLoad = true;

        var mapping = getMapping();
        var selectedComparisons = columnComparisonOnLoad;
        var selectedGroupConcat = [];
        var selectedDateTime = [];

        if (columnGroupConcatOnLoad) {
            for (var field in columnGroupConcatOnLoad) {
                if (columnGroupConcatOnLoad.hasOwnProperty(field)) {
                    selectedGroupConcat.push(columnGroupConcatOnLoad[field]);
                }
            }
        }

        if (columnDateTimeOnLoad) {
            for (var field in columnDateTimeOnLoad) {
                if (columnDateTimeOnLoad.hasOwnProperty(field)) {
                    selectedDateTime.push(columnDateTimeOnLoad[field]);
                }
            }
        }

        var allColumns = window.allColumnsOnLoad;

        setExcludes(mapping, selectedComparisons, selectedGroupConcat, selectedDateTime, allColumns, true, function() {
            delete window.setOnLoad.excludesOnLoad;

            refreshMapResultOnLoad();
        });
    }

    function setExcludes(mapping, selectedComparisons, selectedGroupConcat, selectedDateTime, allColumns, onLoad, cb) {
        var disabled_columns = [];

        var selectedGroupBy = $('#wp2l-group-map-results-by').data('current_value');

        // Remove Group by field from excluded options
        allColumns = allColumns.filter(function(column) { return column !== selectedGroupBy });
        disabled_columns.push(selectedGroupBy);

        // Remove Comparison fields from excluded options
        for (var i = 0; i < selectedComparisons.length; i++) {
            var selectedComparison = selectedComparisons[i].tableColumn;
            disabled_columns.push(selectedComparison);
            allColumns = allColumns.filter(function(column) { return column !== selectedComparison });
        }

        if (selectedGroupConcat && selectedGroupConcat.length > 0) {
            $.each(selectedGroupConcat, function (index, groupConcat) {
                disabled_columns.push(groupConcat);
                allColumns = allColumns.filter(function(column) { return column !== groupConcat });
            });
        }

        if (selectedDateTime && selectedDateTime.length > 0) {
            $.each(selectedDateTime, function (index, dateTime) {
                disabled_columns.push(dateTime);
                allColumns = allColumns.filter(function(column) { return column !== dateTime });
            });
        }

        var virtualRelationItem = $('.virtual-relationship');

        if (virtualRelationItem.length) {
            virtualRelationItem.each(function() {
                var tableFrom = $(this).find('.virtual-table_from').val();
                var columnFrom = $(this).find('.virtual-column_from').val();
                var vNotToExclude = tableFrom + '.' + columnFrom;
                disabled_columns.push(vNotToExclude);
                allColumns = allColumns.filter(function(column) { return column !== vNotToExclude });
            });
        }

        var selectedModule = mapping.transferModule;

        if (selectedModule) {
            var requiredColumn = $('#transfer_module option[value="' + selectedModule + '"]').data('required-column');
            allColumns = allColumns.filter(function(column) { return column !== requiredColumn });
        }

        var selectedExcludes = [];

        allColumns.sort();
        var allColumnsFull = allColumns.concat(disabled_columns);
        allColumnsFull.sort();
        allColumnsFull = _.uniq(allColumnsFull);

        if (onLoad) {
            if (mapping.selects_only && mapping.selects_only.length > 0) {
                $.each(allColumns, function (index, item) {
                    if ($.inArray(item, mapping.selects_only) < 0) {
                        selectedExcludes.push(item);
                        mapping.excludes.push(item);
                    }
                });
            }
        }


        var template = Handlebars.compile($('#wp2l-columns-option')[0].innerHTML);
        $('#wp2l-column-options', document.body).empty();
        $('#wp2l-column-options-all', document.body).empty();

        for (var i = 0; i < allColumns.length; i++) {
            var data = allColumns[i].split('.');

            var resolvedTemplate = template({
                table: data[0],
                column: data[1]
            });

            $('#wp2l-column-options', document.body).append(resolvedTemplate);
        }

        mapping.excludes = _.uniq(mapping.excludes);

        $.each(mapping.excludes, function (index, item) {
            $('#wp2l-column-options', document.body).find('option[value="' + item + '"]').prop('selected', true);
            $('#wp2l-group-map-results-by').find('option[value="' + item + '"]').remove();
        });

        cb();
    }

    function refreshMapResultOnChange() {
        var onChangeCount = Object.keys(window.setOnChange).length;

        if ( 0 === onChangeCount ) {
            setTimeout(refreshMapResult, 500);
        }
    }

    function refreshMapResultOnLoad() {
        var onLoadCount = Object.keys(window.setOnLoad).length;

        if ( 0 === onLoadCount ) {
            setTimeout(refreshMapResult, 500);
        }
    }

    function refreshMapResult() {
        blockElement($('#wp2l-results-preview-wrap'));
        setUsedTableOnSearchResults();
        showMapBuilderTableResults();
    }

    function updateGroupByOptions() {

        var groupBySelected = $('#wp2l-group-map-results-by').data('current_value');

        if (!groupBySelected) {
            groupBySelected = $('#wp2l-group-map-results-by').val();
        }

        var columnsPromise = fetchUpdatedColumnOptions();

        columnsPromise.then(function(tables) {

            var template = Handlebars.compile($('#wp2l-columns-option')[0].innerHTML);

            // loop through the tables
            for (var i = 0; i < tables.length; i++) {
                // loop through the columns
                for (var j = 0; j < tables[i]['columns'].length; j++) {
                    var groupByOptions = template({
                        table: tables[i]['table'],
                        column: tables[i]['columns'][j]
                    });

                    $('#wp2l-group-map-results-by').append(groupByOptions);
                }
            }

            if (!groupBySelected) {
                $('#wp2l-group-map-results-by').data('current_value', $('#wp2l-group-map-results-by').val());
            } else {
                $('#wp2l-group-map-results-by', document.body).find('option[value="' + groupBySelected + '"]').prop('selected', true);
            }
        });
    }

    updateTagInformationOnFieldContainers();
    updateTagListOnFieldContainers();

    function getAllTablesPromise() {
        return new Promise(function (resolve, reject) {
            window.allTablesColumns = [];

            if (window.allTables) {
                resolve(window.allTables);
            } else {
                $.ajax({
                    url: ajaxurl,
                    method: 'post',
                    data: {action: 'wp2l_fetch_tables'},
                    success: function (response) {
                        window.allTables = $.parseJSON(response);
                        resolve(window.allTables);
                    }
                });
            }
        });
    }

    function getAllColumnsPromise() {
        var currentMapping = compileMapObject(),
            mapping = getMapping(),
            isNewMap = JSON.stringify(currentMapping) !== JSON.stringify(mapping),
            activeMapId = $_GET('active_mapping');

        var mapId = $_GET('active_mapping');

        if (currentMapping.from === '') {
            return false;
        }

        var data = {
            'action': 'wp2l_fetch_all_columns_for_map',
            'map_id': activeMapId,
            'new_map': JSON.stringify(currentMapping),
            'is_new_map': isNewMap
        };

        var jqxhr =  $.post(ajaxurl, data, function(response) {
            // console.log(response);
            // console.log($.parseJSON(response));
        });

        return jqxhr;
    }

    function getMapping () {
        var mappingHolder = $('input.mapping');
        var mapping;

        try {
            mapping = $.parseJSON(mappingHolder.val());
        } catch(err) {
            mapping = false;
        }

        return mapping;
    }

    function compileMapObject(mapping) {
        if (window.onLoadFirst) {
            mapping = getMapping();
        }

        if (mapping) {
            return mapping;
        } else {
            var selects = $('#wp2l-column-options option', document.body).map(function () {
                return $(this).prop('value');
            }).toArray();

            var groupByKey = $('#wp2l-group-map-results-by').val();

            selects.push(groupByKey);

            var excludesFilters = [];
            var excludesFiltersNames = $('#excludedColumnsFilter_container .created-filter .filter-name');

            if (excludesFiltersNames.length > 0) {
                excludesFiltersNames.each(function () {
                    excludesFilters.push($(this).text());
                });
            }

            var excludes = $('#wp2l-column-options option:selected', document.body).map(function () {
                return $(this).prop('value');
            }).toArray();

            var disable_grouping = $('#disable-grouping').attr('checked');

            var comparisons = [];

            var comparison_fields = $('.column-comparison-map-fields');

            $.each(comparison_fields, function (index, field) {
                var fieldOb = $(field);
                var tableColumn = fieldOb.find('.table-column-identifier').val();
                var operator = fieldOb.find('.table-column-operator').val();
                var string = fieldOb.find('.table-column-string').val();

                if(tableColumn && operator && string) {
                    var exist = false;
                    var key = 0;

                    $.each(comparisons, function (index, comp) {
                        if (tableColumn === comp.tableColumn) {
                            exist = true;
                            key = index;
                        }
                    });

                    selects.push(tableColumn);

                    if (exist) {
                        comparisons[key].conditions.push({
                            operator: operator,
                            string: string
                        });
                    } else {
                        comparisons.push({
                            tableColumn: tableColumn,
                            conditions: [{
                                operator: operator,
                                string: string
                            }]
                        });
                    }
                }
            });

            var groupConcat = $('#wp2l-group-concat-for').val();
            var groupConcatSeparator = $('#wp2l-group-concat-separator').val().trim();

            if (!groupConcatSeparator) {
                groupConcatSeparator = ',';
            }

            if (groupConcat) {
                for (var j = 0; j < groupConcat.length; j++ ) {
                    selects.push(groupConcat[j]);
                }
            }

            var dateTimeColumns = $('#wp2l-date-time-columns').val();

            if (dateTimeColumns) {
                for (var j = 0; j < dateTimeColumns.length; j++ ) {
                    selects.push(dateTimeColumns[j]);
                }
            }

            var virtualRelationItem = $('.virtual-relationship');

            if (virtualRelationItem.length) {
                virtualRelationItem.each(function() {
                    var tableFrom = $(this).find('.virtual-table_from').val();
                    var columnFrom = $(this).find('.virtual-column_from').val();

                    var vNotToExclude = tableFrom + '.' + columnFrom;
                    selects.push(vNotToExclude);
                });
            }

            var selects_only = [];

            $.each(selects, function (index, select) {
                if ($.inArray(select, excludes) < 0) {
                    selects_only.push(select);
                }
            });

            var selectedModule = '';
            var moduleSelector = $('#transfer_module');

            if (moduleSelector.length > 0) {
                if (moduleSelector.hasClass('notexisted')) {
                    selectedModule = '';
                } else {
                    selectedModule = moduleSelector.val();

                    if (selectedModule) {
                        var requiredColumn = $('#transfer_module option[value="' + selectedModule + '"]').data('required-column');

                        var requiredColumnArray = requiredColumn.split(".");

                        if ($.inArray(requiredColumnArray[0], getUsedTables()) > -1) {
                            selects.push(requiredColumn);
                            selects_only.push(requiredColumn);
                        }
                    }
                }
            }

            var map = {
                selects: _.uniq(selects),
                selects_only: _.uniq(selects_only),
                from: $('#from-table', document.body).val(),
                joins: [],
                keyBy: disable_grouping ? '' : $('#wp2l-group-map-results-by').val(),
                excludesFilters: excludesFilters,
                excludes: excludes,
                comparisons: comparisons,
                virtual_relationships: [],
                groupConcat: groupConcat,
                groupConcatSeparator: groupConcatSeparator,
                dateTime: dateTimeColumns,
                transferModule: selectedModule,
                disableGrouping: disable_grouping ? disable_grouping : ''
            };

            var relationships = $('.relationship-map-fields');

            $.each(relationships, function (index, item) {
                var relationship = $(item);
                var newRelation = {
                    joinTable: relationship.find('.relationship-join-table').val(),
                    joinColumn: relationship.find('.relationship-join-column').val(),
                    referenceTable: relationship.find('.relationship-reference-table').val(),
                    referenceColumn: relationship.find('.relationship-reference-column').val()
                };

                if(newRelation.joinTable &&
                    newRelation.joinColumn &&
                    newRelation.referenceTable &&
                    newRelation.referenceColumn) {
                    map.joins.push(newRelation);
                }
            });

            var vRelationships = $(document.body).find('#virtual-relationships .virtual-relationship-list .virtual-relationship');

            $.each(vRelationships, function(i, relationshipHTML) {
                var relationship = $(relationshipHTML);

                map['virtual_relationships'].push({
                    'table_from': relationship.find('select[name="table_from"] option:selected').val(),
                    'column_from': relationship.find('select[name="column_from"] option:selected').val(),
                    'table_to': relationship.find('select[name="table_to"] option:selected').val(),
                    'column_to': relationship.find('select[name="column_to"] option:selected').val(),
                    'column_key': relationship.find('select[name="column_key"] option:selected').val(),
                    'column_value': relationship.find('select[name="column_value"] option:selected').val()
                });
            });

            return map;
        }
    }

    /**
     * Load Multisearch result on page ready
     * @param i
     * @param searchString
     * @param searchTables
     */
    function getSearchResultPromise(i, searchString, searchTables) {
        var searchResultsHolder = $('#wp2l-multi-search-result-holder'),
            searchTagsHolder = $('#wp2l-multi-search-result-tags');

        var data = {
            action: 'wp2l_global_multisearch_results',
            string: searchString
        };

        $.post(ajaxurl, data, function(response) {
            $('.wp2l-multi-search-string').val('');

            var seq = i;

            var resultTemplate = Handlebars.compile($('#wp2l-multisearch-results')[0].innerHTML);
            searchResultsHolder.append(resultTemplate({tables: $.parseJSON(response), seq: seq, searchString: searchString}));

            var serchTagTemplate = Handlebars.compile($('#wp2l-multisearch-tags')[0].innerHTML);
            searchTagsHolder.append(serchTagTemplate({tag: searchString, seq: seq}));

            var tableToScroll = $('#result-seq-' + seq + ' .multisearch-results-table');

            tableToScroll.floatThead({
                scrollContainer: function(table){
                    return table.closest('.multisearch-results-table-wrap');
                }
            });

            if (i !== 1) {
                $('#tag-seq-' + i).removeClass('active');
                $('#result-seq-' + i).removeClass('active');
            }

            window.searchFromMap.shift();

            if (searchTables.length > 0) {

                searchTables.forEach(function(searchTable) {
                    getSearchSingleResultPromise(i, searchString, searchTable);
                });

            }

            if (0 === window.searchFromMap.length && 0 === window.searchTablesFromMap) {
                $( document.body ).trigger( 'wp2lead_unblock_search_results' );
            }

            // updateActiveSearchResultRow();
            setUsedTableOnSearchResults();
        });
    }

    /**
     * Load single search results table on page ready
     *
     * @param i
     * @param searchString
     * @param searchTable
     */
    function getSearchSingleResultPromise(i, searchString, searchTable) {
        var table = searchTable,
            string = searchString,
            seq = i,
            searchResultsHolder = $('#wp2l-multi-search-single-result-holder'),
            label = '`' + table + '` : ' + string;

        var row = $('#result-seq-' + i).find('.table-'+table+'-row'),
            columns = row.find('.column-holder');

        var data = {
            action: 'wp2l_single_multisearch_table',
            table: table,
            string: string
        };

        $.post(ajaxurl, data, function(response) {
            var decoded = $.parseJSON(response);
            var resultTemplate = Handlebars.compile($('#wp2l-multisearch-single-result')[0].innerHTML);
            searchResultsHolder.prepend(resultTemplate({label: label, table: table, columns: decoded.columns, seq:seq, results: decoded.results}));

            var tableToScroll = $('#single-result-' + table + ' .multisearch-single-result-table');

            var tableValues = tableToScroll.find('tbody tr td div');

            tableValues.highlight(string);

            columns.each(function() {
                var activeColumn = $(this).data('column');
                var columnHeader = tableToScroll.find('thead tr th.header-' + activeColumn);
                columnHeader.addClass('active');
            });

            var activeColumnsIndex = [];

            $.each(tableToScroll.find('thead tr th'), function(i) {
                if ($(this).hasClass('active')) {
                    activeColumnsIndex.push(i);
                }
            });

            $.each(tableToScroll.find('tbody tr'), function() {
                $.each($(this).find('td'), function(j) {
                    if ($.inArray(j, activeColumnsIndex) >= 0) {
                        $(this).addClass('active');
                    }
                })
            });

            tableToScroll.floatThead({
                scrollContainer: function(table){
                    return table.closest('.multisearch-single-result-table-wrap');
                }
            });

            if (i !== 1) {
                $('.single-result-seq-' + i).removeClass('active');
            }

            window.searchTablesFromMap--;

            if (0 === window.searchFromMap.length && 0 === window.searchTablesFromMap) {
                $( document.body ).trigger( 'wp2lead_unblock_search_results' );
            }
        });
    }

    /**
     * Label Tables from search by used labels
     */
    function setUsedTableOnSearchResults() {
        var fromTable = $('#from-table');
        var fromTableSelected = fromTable.val();

        var relationships = $('.relationship-map-fields');
        var relationshipsSelected = [];

        var virtualRelationships = $('.virtual-relationship');
        var virtualRelationshipsSelected = [];

        var searchResult = $(document.body).find('#wp2l-multi-search-result-holder .multisearch-results-table-row');

        // Label for starter data
        if (fromTableSelected) {
            if (searchResult.length > 0) {
                searchResult.each(function() {
                    var searchTable = $(this).find('.table-holder').text();
                    var searchTableLabelsHolder = $(this).find('.label-holder');

                    if (fromTableSelected === searchTable && searchTableLabelsHolder.find('.starter-data-label').length === 0) {
                        var labelTemplate = Handlebars.compile($('#wp2l-multisearch-table-label')[0].innerHTML);
                        searchTableLabelsHolder.append(labelTemplate({
                                tableName: wp2leads_i18n_get('Starter'),
                                tableClass: 'starter-data'
                            })
                        );
                    }
                });
            }
        }

        // Label for relations
        if (relationships.length > 0) {
            $.each(relationships, function (index, relationship) {
                var reference = $(relationship).find('.relationship-reference-table option:selected').val();
                if ($.inArray(reference, relationshipsSelected) < 0) {
                    relationshipsSelected.push(reference);

                }

                var join = $(relationship).find('.relationship-join-table option:selected').val();
                if ($.inArray(join, relationshipsSelected) < 0) {
                    relationshipsSelected.push(join);

                }
            });
        }

        // Label for
        if (relationshipsSelected.length > 0) {
            if (searchResult.length > 0) {
                searchResult.each(function() {
                    var searchTable = $(this).find('.table-holder').text();
                    var searchTableLabelsHolder = $(this).find('.label-holder');

                    if ($.inArray(searchTable, relationshipsSelected) !== -1 && searchTableLabelsHolder.find('.relation-data-label').length === 0) {
                        var labelTemplate = Handlebars.compile($('#wp2l-multisearch-table-label')[0].innerHTML);
                        searchTableLabelsHolder.append(labelTemplate({
                                tableName: wp2leads_i18n_get('Relation'),
                                tableClass: 'relation-data'
                            })
                        );
                    }
                });
            }
        }

        // Label for
        if (virtualRelationships.length > 0) {
            $.each(virtualRelationships, function (index, relationship) {
                var from = $(relationship).find('[name="table_from"] option:selected').val();

                if ($.inArray(from, virtualRelationshipsSelected) < 0) {
                    virtualRelationshipsSelected.push(from);

                }

                var to = $(relationship).find('[name="table_to"] option:selected').val();

                if ($.inArray(to, virtualRelationshipsSelected) < 0) {
                    virtualRelationshipsSelected.push(to);

                }
            });
        }

        if (virtualRelationshipsSelected.length > 0) {
            if (searchResult.length > 0) {
                searchResult.each(function() {
                    var searchTable = $(this).find('.table-holder').text();
                    var searchTableLabelsHolder = $(this).find('.label-holder');

                    if ($.inArray(searchTable, virtualRelationshipsSelected) !== -1 && searchTableLabelsHolder.find('.virtual-relation-data-label').length === 0) {
                        var labelTemplate = Handlebars.compile($('#wp2l-multisearch-table-label')[0].innerHTML);
                        searchTableLabelsHolder.append(labelTemplate({
                                tableName: wp2leads_i18n_get('Virtual Relation'),
                                tableClass: 'virtual-relation-data'
                            })
                        );
                    }
                });
            }
        }
    }

    function getUsedTables() {
        var from_table = $('#from-table');
        var relationships = $('.relationship-map-fields');
        var virtual_relationships = $('.virtual-relationship');

        var already_used_tables = [];

        already_used_tables.push(from_table.find('option:selected').val());

        $.each(relationships, function (index, relationship) {
            var reference = $(relationship).find('.relationship-reference-table option:selected').val();
            if ($.inArray(reference, already_used_tables) < 0) {
                already_used_tables.push(reference);

            }

            var join = $(relationship).find('.relationship-join-table option:selected').val();
            if ($.inArray(join, already_used_tables) < 0) {
                already_used_tables.push(join);

            }
        });

        $.each(virtual_relationships, function (index, relationship) {
            var val = $(relationship).find('[name="table_from"] option:selected').val();

            if ($.inArray(val, already_used_tables) < 0) {
                already_used_tables.push(val);

            }

            val = $(relationship).find('[name="table_to"] option:selected').val();

            if ($.inArray(val, already_used_tables) < 0) {
                already_used_tables.push(val);

            }
        });

        return already_used_tables;
    }

    function setEmptySampleMapResults() {
        $('#wp2l-results-preview').html('<tbody><tr><td>'+wp2leads_i18n_get('Update your map to begin showing results!')+'</td></tr></tbody>');
    }

    function findExistingTableChoices() {
        var tables = [];
        // add the starter table
        tables.push($('#from-table').val());
        // find all current relationships loop through all but the current relationship
        $('.relationship-join-table').each(function () {
            tables.push($(this).val());
        });
        // return
        return tables;
    }


    var updateTagListOnFieldtimeout;

    function updateTagListOnFieldContainers(apiFieldBoxFieldValues = null) {
        if (!apiFieldBoxFieldValues || !apiFieldBoxFieldValues.length) {
            apiFieldBoxFieldValues = $(document.body).find('.api_fields_container .api_field_box');
        }

        $.each(apiFieldBoxFieldValues, function(index, fieldBoxHTML) {
            let fieldBox = $(fieldBoxHTML);
            let fieldBoxTags = fieldBox.find('ul.tokens-container li.token span');

            let arrayTagsList = [];
            $.each(fieldBoxTags, function(index, tagHTML) {
                let tag = $(tagHTML);
                let tag_value = tag.attr('data-value');

                if (!tag_value) {
                    tag_value = tag.text();
                }

                arrayTagsList.push(tag_value);
            });
            let textTagsList = arrayTagsList.join(', ');

            if(textTagsList.length > 0) {
                fieldBox.find('.field_value').text(textTagsList);
            } else {
                fieldBox.find('.field_value').text(wp2leads_i18n_get('Choose an option'));
            }
        });
    }

    var updateTagInformationOnFieldtimeout;

    function updateTagInformationOnFieldContainers(fieldBoxes = null) {
        if (!fieldBoxes || !fieldBoxes.length) {
            fieldBoxes = $(document.body).find('.api_field_box');
        }

        $.each(fieldBoxes, function(index, fieldBoxHTML) {
            var fieldBox = $(fieldBoxHTML);
            var tokens = fieldBox.find('.tokenize .tokens-container li.token span');


            $.each(tokens, function(i, tokenHTML) {
                var token = $(tokenHTML);

                var tokenText = token.text().trim(' ');
                var tokenArray = tokenText.split(': (');

                if (tokenArray.length === 2) {
                    token.attr('data-option', tokenArray[0]);
                    token.attr('data-value', tokenArray[0]);

                    token.text(tokenArray[0]);
                } else {
                    var tokenData = tokenText.match(/(.*) \((.*)\)/);

                    if(tokenData !== null) {
                        var tokenOption = tokenData[1];
                        var tokenValue = tokenData[2];

                        token.attr('data-option', tokenOption);
                        token.attr('data-value', tokenValue);

                        token.text(tokenValue);
                    }
                }
            });
        });
    }

    jQuery.extend({
        compare: function (arrayA, arrayB) {
            if (arrayA.length != arrayB.length) { return false; }
            // sort modifies original array
            // (which are passed by reference to our method!)
            // so clone the arrays before sorting
            var a = jQuery.extend(exctrue, [], arrayA);
            var b = jQuery.extend(true, [], arrayB);
            a.sort();
            b.sort();

            for (var i = 0, l = a.length; i < l; i++) {
                if (a[i] !== b[i]) {
                    return false;
                }
            }
            return true;
        }
    });

    function preventLoosingData() {
        var active_tab_name = $.trim($('.nav-tab-active').text());
        var isSameVersion = true;
        var saved_map = $('input.mapping').val();
        var current_map = '';
        var map_builder_form = $('#map-generator');
        var api_settings_form = $('.map2api_body');

        if (api_settings_form.is(':visible') || map_builder_form.is(':visible')) {
            if (wp2leads_i18n_get('Map Builder') === active_tab_name) {
                current_map = JSON.stringify(compileMapObject());
            } else {
                return true;
            }

            if (saved_map !== current_map) {
                isSameVersion = false;
            }
        }

        return isSameVersion;
    }

    function $_GET(param) {
        var vars = {};
        window.location.href.replace( location.hash, '' ).replace(
            /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
            function( m, key, value ) { // callback
                vars[key] = value !== undefined ? value : '';
            }
        );

        if ( param ) {
            return vars[param] ? vars[param] : null;
        }
        return vars;
    }

    function blockMapBuilderFieldsOnChange(field) {
        $('#from-table').attr('disabled', true);
        $('#wp2l-group-map-results-by').attr('disabled', true);
        $('#disable-grouping').attr('disabled', true);
        $('#wp2l-group-concat-for').attr('disabled', true);
        $('#wp2l-date-time-columns').attr('disabled', true);
        $('#wp2l-column-options').attr('disabled', true);

        if ('relationship' !== field) {
            $('.relationship-map-fields select').attr('disabled', true);
            $('.relationship-map-fields .remove-relationship').addClass('disabled');
            $('#add-new-relationship-map').addClass('disabled');
        }

        if ('virtual-relationship' !== field) {
            $('.virtual-relationship select').attr('disabled', true);
            $('.column-comparison-map-fields .remove-virtual-relationship').addClass('disabled');
            $('#virtual-relationship-button-add').addClass('disabled');

        }

        if ('comparison' !== field) {
            $('.column-comparison-map-fields select').attr('disabled', true);
            $('.column-comparison-map-fields input').attr('disabled', true);
            $('.column-comparison-map-fields .remove-column-comparison').addClass('disabled');
            $('#add-new-comparison-map').addClass('disabled');
        }
    }

    function unblockMapBuilderFieldsOnChange(field) {
        setTimeout(function () {
            var fromTable = $('#from-table');
            var groupBy = $('#wp2l-group-map-results-by');
            var virtualRelation = $('.virtual-relationship');
            var groupConcat = $('#wp2l-group-concat-for');
            var relationsHolder = $('#relationship-map-holder');

            if (!fromTable.hasClass('disabled-for-editing')) {
                fromTable.attr('disabled', false);
            }

            if (!groupBy.hasClass('disabled-for-editing')) {
                groupBy.attr('disabled', false);
            }

            if (!groupConcat.hasClass('disabled-for-editing')) {
                groupConcat.attr('disabled', false);
            }

            $('#disable-grouping').attr('disabled', false);
            $('#wp2l-date-time-columns').attr('disabled', false);
            $('#wp2l-column-options').attr('disabled', false);

            if ('relationship' !== field) {
                if (!relationsHolder.hasClass('disabled-for-editing')) {
                    $('.relationship-map-fields:last-child select').attr('disabled', false);
                    $('.relationship-map-fields:last-child .remove-relationship').removeClass('disabled');
                    $('#add-new-relationship-map').removeClass('disabled');
                }
            }

            if ('virtual-relationship' !== field) {
                if (!virtualRelation.hasClass('disabled-for-editing')) {
                    $('.virtual-relationship select').attr('disabled', false);
                    $('.column-comparison-map-fields .remove-virtual-relationship').removeClass('disabled');
                    $('#virtual-relationship-button-add').removeClass('disabled');
                }

            }

            if ('comparison' !== field) {
                $('.column-comparison-map-fields select').attr('disabled', false);
                $('.column-comparison-map-fields input').attr('disabled', false);
                $('.column-comparison-map-fields .remove-column-comparison').removeClass('disabled');
                $('#add-new-comparison-map').removeClass('disabled');
            }
        }, 500);
    }

    function blockMapBuilderFields() {
        // blockElement($('#from-table').parent('td'));
        // blockElement($('#wp2l-group-map-results-by').parent('td'));
        // blockElement($('#relationship-map-holder').parent('td'));
        // blockElement($('#virtual-relationships').parent('td'));
        // blockElement($('#wp2l-column-options').parent('td'));
        // blockElement($('#column-comparison-holder').parent('td'));
        // blockElement($('#wp2l-date-time-columns').parent('td'));
        // blockElement($('#wp2l-group-concat-for').parent('td'));
        $('#create-map-section .api-processing-holder .api-spinner-holder').addClass('api-processing');
    }

    function unblockMapBuilderFields() {
        setTimeout(function () {
            // unblockElement($('#from-table').parent('td'));
            // unblockElement($('#wp2l-group-map-results-by').parent('td'));
            // unblockElement($('#relationship-map-holder').parent('td'));
            // unblockElement($('#virtual-relationships').parent('td'));
            // unblockElement($('#wp2l-column-options').parent('td'));
            // unblockElement($('#column-comparison-holder').parent('td'));
            // unblockElement($('#wp2l-date-time-columns').parent('td'));
            // unblockElement($('#wp2l-group-concat-for').parent('td'));

            $('#create-map-section .api-processing-holder .api-spinner-holder').removeClass('api-processing');
        }, 500);
    }

    function blockSearchResults() {
        blockElement($('#wp2l-multi-search-holder').parent('td'), 'change');
        blockElement($('#wp2l-multi-search-result-holder').parent('td'), 'change');
        blockElement($('#wp2l-multi-search-single-result-holder').parent('td'), 'change');
        blockElement($('#wp2l-table-search-result-holder').parent('td'), 'change');
    }

    function unblockSearchResults() {
        setTimeout(function () {
            compileSearchResultByTable();
            compileSearchResultByString();

            unblockElement($('#wp2l-multi-search-holder').parent('td'), 'change');
            unblockElement($('#wp2l-multi-search-result-holder').parent('td'), 'change');
            unblockElement($('#wp2l-multi-search-single-result-holder').parent('td'), 'change');
            unblockElement($('#wp2l-table-search-result-holder').parent('td'), 'change');
        }, 500);
    }

    /**
     * Block element
     * @param el
     * @param action
     */
    function blockElement(el, action) {
        var bg = '#fff';
        var opacity = 0.6;

        if (!action) {
            action = 'processing'
        }

        if ('processing' !== action) {
            bg = '#f1f1f1';
            opacity = 0.7;
        }

        if ( ! isElementBlocked( el ) ) {
            el.addClass( action ).block( {
                message: null,
                overlayCSS: {
                    background: bg,
                    opacity: opacity
                }
            } );
        }
    }

    function unblockElement(el, action) {
        if (!action) {
            action = 'processing'
        }

        el.removeClass( action ).unblock();
    }

    function isElementBlocked(el, action) {
        if (!action) {
            action = 'processing'
        }

        return el.is( '.' + action ) || el.parents( '.' + action ).length;
    }

	function auto_grow(element) {
		element.style.height = "5px";
		element.style.height = (element.scrollHeight)+"px";
	}

	$('.hidden-editable textarea').each(function(el) {
		auto_grow(this);
	});

	$('.hidden-editable textarea').on('keyup change', function(el) {
		auto_grow(this);
	});

})(jQuery);
