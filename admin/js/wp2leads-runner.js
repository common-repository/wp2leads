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

(function ($) {
    $(document.body).on('click', '#update-result-limit-mr', function() {
        var mapId = $_GET('active_mapping');

        if (!mapId) {
            mapId = $('#map-runner__container').data('map-id');
        }

        if (mapId) {
            if ($('#map-runner__results').length > 0) {
                $('#map-runner__results .api-spinner-holder').addClass('api-processing');
                showMapRunnerTableResults(mapId);
            }
        }
    });

    $(document).ready(function () {
        var mapId = $_GET('active_mapping');

        if (!mapId) {
            mapId = $('#map-runner__container').data('map-id');
        }

        if (mapId) {
            if ($('#map-runner__results').length > 0) {
                showMapRunnerTableResults(mapId);
            }
        }
    });

    function showMapRunnerTableResults(mapId, cb, errcb) {
        getMapRowsCountPromise()
            .then(function(response) {
                // TODO: Show if no results in DB
                var decoded = $.parseJSON(response);
                var count = decoded.message;

                var keyBy = $('#map-runner__results').data('key-by'),
                    limitTo = parseInt($('#map-sample-results-limit-mr').val()),
                    limit = 100,
                    offset = 0,
                    iterations = Math.ceil(count / limit),
                    results = [],
                    dataLoaded = false,
                    counter = 0;

                if (0 === count) {
                    var resultTemplate = Handlebars.compile($('#wp2l-map-runner-results-table')[0].innerHTML);
                    $('#wp2l-results-preview-wrap', document.body).empty();

                    $('#wp2l-results-preview-wrap', document.body).append(resultTemplate({
                        results: results,
                        keyByColumn: keyBy
                    }));

                    $('table#wp2l-results-preview').floatThead({
                        scrollContainer: function(table) {
                            return table.closest('#wp2l-results-preview-wrap-inner');
                        }
                    });

                    $('#map-runner__results .api-spinner-holder').removeClass('api-processing');
					$('body').trigger('wizard9');
                } else {
                    while (!dataLoaded) {
                        offset = limit * counter;

                        var data = {
                            action: 'wp2l_fetch_map_query_results',
                            map_id: mapId,
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

                            var resultTemplate = Handlebars.compile($('#wp2l-map-runner-results-table')[0].innerHTML);

                            $('#wp2l-results-preview-wrap', document.body).empty();

                            $('#wp2l-results-preview-wrap', document.body).append(resultTemplate({
                                results: resultsToShow,
                                keyByColumn: keyBy
                            }));

                            $('table#wp2l-results-preview').floatThead({
                                scrollContainer: function(table) {
                                    return table.closest('#wp2l-results-preview-wrap-inner');
                                }
                            });

                            $('#map-runner__results .api-spinner-holder').removeClass('api-processing');
							$('body').trigger('wizard9');
                        }
                    }
                }
            });
    }

    function getMapRowsCountPromise() {
        var mapId = $_GET('active_mapping');

        if (!mapId) {
            mapId = $('#map-runner__container').data('map-id');
        }

        var data = {
            action: 'wp2l_get_map_rows_count',
            mapId: mapId
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
})(jQuery);