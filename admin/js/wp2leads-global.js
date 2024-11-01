(function ($) {

    $(document).ready(function () {
		// $('.wptl-tip').tipr();
    });

    $(document.body).on('click', '#refresh-all-bg-map-to-api', function() {
        refreshAllBgMapToApi();
    });

    function refreshAllBgMapToApi() {
        var data = {
            action: 'wp2l_refresh_all_map_to_api'
        };

        $.post(
            ajaxurl,
            data,
            function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    var noticeHolder = $('#map-to-api-bg-running-inner');
                    noticeHolder.html(decoded.html);

                } else {
                    alert(decoded.message);
                }
            }
        );
    }

    $(document.body).on('click', '#terminate-all-bg-map-to-api', function() {
        var data = {
            action: 'wp2l_terminate_all_map_to_api'
        };

        $.post(
            ajaxurl,
            data,
            function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    alert(decoded.message);
                    $('#wp2lead-map-to-api-bg-notice').remove();
                } else {
                    alert(decoded.message);
                }
            }
        );
    });

    $(document.body).on('click', '.terminate-bg-map-to-api', function() {
        var mapId = $(this).data('map');

        var data = {
            action: 'wp2l_terminate_map_to_api',
            mapId: mapId
        };

        var activeMapping = $_GET('active_mapping');
        var activeTab = $_GET('tab');
        var redirectMapping = '';
        var redirectTab = '';

        if (activeMapping) {
            redirectMapping = '&active_mapping=' + activeMapping;
        }

        if (activeTab) {
            redirectTab = '&tab=' + activeTab;
        }

        var activePage = $_GET('page');

        var noticeHolder = $('#wp2lead-map-to-api-bg-notice .api-spinner-holder');

        noticeHolder.addClass('api-processing');

        $.post(
            ajaxurl,
            data,
            function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    alert(decoded.message);

                    if ('wp2l-admin' === activePage) {
                        window.location.href = '?page=wp2l-admin' + redirectTab + redirectMapping;
                    } else {
                        window.location.reload();
                    }

                } else {
                    alert(decoded.message);
                    noticeHolder.removeClass('api-processing');
                }
            }
        );
    });

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


	$(document.body).on('click', '.wp2leads-hide-notice', function(e) {
        e.preventDefault();
		e.stopPropagation();

		var wrap = $(this).closest('.wp2leads-global-notice');
		wrap.animate({'opacity' : 0.3}, 1500);
		data = {
					action: 'wp2l_dismiss_notice',
					id: $(this).data('id')
				}

		setTimeout(function(){
			$.ajax({
				url: ajaxurl,
				method: 'post',
				async: false,
				data: data,
				success: function (response) {
					var decoded = $.parseJSON(response);

					if (decoded.success) {
						wrap.stop().animate({'opacity' : 0}, 150);
						setTimeout(function(){
							wrap.remove();
						}, 150);
					} else {
						wrap.stop().css({'opacity' : 1});
					}
				}
			});
		}, 50);
	});

	$(document.body).on('click', '.wp2leads-hide-all-notices', function(e) {
		e.preventDefault();
		e.stopPropagation();

		var wrap = $('.wp2leads-global-notice');
		wrap.animate({'opacity' : 0.3}, 1500);
		data = {
					action: 'wp2l_dismiss_all_notices',
				}

		setTimeout(function(){
			$.ajax({
				url: ajaxurl,
				method: 'post',
				async: false,
				data: data,
				success: function (response) {
					var decoded = $.parseJSON(response);

					if (decoded.success) {
						wrap.stop().animate({'opacity' : 0}, 150);
						setTimeout(function(){
							wrap.remove();
						}, 150);
					} else {
						wrap.stop().css({'opacity' : 1});
					}
				}
			});
		}, 50);
	});


	// check non active maps functions
	$('#wp-admin-bar-wp2leads_not_active_maps_trigger a').on('click', function(){
		$('#wp2leads-non-active-maps-popup').show();
		return false;
	});

	$(document.body).click(function(){
		$('#wp2leads-non-active-maps-popup').hide();
	});

	$(document.body).on('click', '#wp2leads-non-active-maps-popup>a', function(){
		$('#wp2leads-non-active-maps-popup').hide();
		return false;
	});

	$(document.body).on('click', '#wp2leads-non-active-maps-popup', function(e){
		e.stopPropagation();
	});

	$(document.body).on('click', '#activateCRM', function(){
		var wrap = $(this).closest('.wp2leads-global-notice');
		var strong = '<strong>' + wrap.find('strong').text() + '</strong> ';
		var message = strong + $(this).data('message');
		wrap.find('p').html(message);
		wrap.find('button').hide();

		// will install and activate CRM plugin
		$.ajax({
			url: ajaxurl,
			method: 'post',
			async: false,
			data: {
				action: 'wp2l_activate_crm',
			},
			success: function (response) {
				var decoded = $.parseJSON(response);
				wrap.find('p').html(strong + decoded.message);

				setTimeout(function(){
					wrap.find('button').click();
				}, 3000);

			}
		});
		return false;
	});
})(jQuery);

function wp2leads_save_cf7_fields() {
	jQuery("#wpcf7-admin-form-element .submit input").click();
}