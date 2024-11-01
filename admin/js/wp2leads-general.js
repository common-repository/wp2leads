(function ($) {

    $(document).ready(function () {
        var availableMapsTable = $('.available-maps__table');
        var importMapsTable = $('.import-maps-from-server-table');
        var exportMapsTable = $('#export-maps_table');

        if (availableMapsTable.length > 0) {
            availableMapsTable.floatThead({
                scrollContainer: function(table){
                    return table.closest('.available-maps_table-wrap');
                }
            });
        }

        if (importMapsTable.length > 0) {
            importMapsTable.floatThead({
                scrollContainer: function(table){
                    return table.closest('.import-maps-from-server-table-wrap');
                }
            });
        }

        if (exportMapsTable.length > 0) {
            exportMapsTable.floatThead({
                scrollContainer: function(table){
                    return table.closest('.export-maps-to-server-table-wrap');
                }
            });
        }

        if (importMapsTable.length > 0 && exportMapsTable.length > 0) {
            var localMaps = exportMapsTable.find();
        }

        $('.wp2lead-tip').tipr({
            'marginAbove': -55,
            'marginBelow': 7,
            'space': 70
        });

		$( document ).tooltip();
    });

    /**
     * Activate / Deactivate site
     */
    $(document.body).on('click', '#wp2lActivate, #wp2lDectivate, #wp2lValidateKtCC, #wp2lGetKey', function() {
        var licenseEmail = $('#wp2l-license-email').val(),
            licenseKey = $('#wp2l-license-key').val(),
            action = $(this).data('action');

        if ('deactivation' === action) {
            if (!confirm(wp2leads_i18n_get("Are you sure? Without correct license key you are not be able to activate your plugin again."))) {
                return;
            }
        }

        var data = {
			action: 'wp2l_license_' + action,
			nonce: wp2leads_ajax_object.nonce,
			licenseEmail: licenseEmail,
			licenseKey: licenseKey
		};

        if ($('#wp2l-license-ktcc-url').length) {
			data.licenseKtccUrl = $('#wp2l-license-ktcc-url').val();
		}

        if ($('#wp2l-license-imprint-url').length) {
			data.licenseImprintUrl = $('#wp2l-license-imprint-url').val();
		}

        $.ajax({
            type: 'post',
            url: ajaxurl,
            async: false,
            data: data,
            success: function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    alert(decoded.message);

                    window.location.reload();
                } else {
                    alert(decoded.message);
                }
            }
        });
    });

    /**
     * Activate / Deactivate site
     */
    $(document.body).on('click', '.wp2lComplete', function(e) {
        e.preventDefault();

        $.ajax({
            type: 'post',
            url: ajaxurl,
            async: false,
            data: {
                action: 'wp2l_complete_activation',
				nonce: wp2leads_ajax_object.nonce,
            },
            success: function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    alert(decoded.message);

                    window.location.reload();
                } else {
                    alert(decoded.message);
                }
            }
        });
    });

    /**
     * Remove site activation
     */
    $(document.body).on('click', '.wp2lRemove', function() {
        var licenseEmail = $('#wp2l-license-email').val(),
            licenseKey = $('#wp2l-license-key').val(),
            site = $(this).data('site');

        $.ajax({
            type: 'post',
            url: ajaxurl,
            async: false,
            data: {
                action: 'wp2l_license_removing',
				nonce: wp2leads_ajax_object.nonce,
                licenseEmail: licenseEmail,
                licenseKey: licenseKey,
                site: site
            },
            success: function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    alert(decoded.message);

                    window.location.reload();
                } else {
                    alert(decoded.message);
                }
            }
        });
    });

    /**
     * KlickTip Auth
     */
    $(document.body).on('click','#btnKlicktippSubmit',function(e) {
        e.preventDefault();

        var klicktipp = {
            username: $("input[name=settings-klicktipp-username]").val(),
            password: $("input[name=settings-klicktipp-password]").val(),
            speed: $("input[name=settings-klicktipp-speed]").val()
        };

        $.post(
            ajaxurl,
            {
                action: 'wp2l_settings_klicktipp',
				nonce: wp2leads_ajax_object.nonce,
                klicktipp: klicktipp
            },
            function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    if(decoded.auth) {
                    	if (decoded.message) {
							alert(decoded.message);
						} else {
							alert(wp2leads_i18n_get('Authorization in KlickTipp was successful!'));
						}

						window.location.reload();
                    } else {
                        alert(wp2leads_i18n_get('The username or password you entered is incorrect!'));
                    }
                } else {
                    alert(wp2leads_i18n_get('Something went wrong.'));
                }
            });
    });

    $(document.body).on('submit', '#import-map-from-server-by-public-id-form', function(e) {
        e.preventDefault();
        var form = $("#import-map-from-server-by-public-id-form");

        var activeMapping = $_GET('active_mapping');
        var redirectMapping = '';

        if (activeMapping) {
            redirectMapping = '&active_mapping=' + activeMapping;
        }

        var mapimportids = [];
        var selectOneImport = form.find('#map-public-id').val();

        if (selectOneImport) {
            mapimportids.push({
                mapId: $.trim(selectOneImport)
            });
        }

        $.ajax({
            url: ajaxurl,
            method: 'post',
            async: false,
            data: {
                action: 'wp2l_import_maps',
				nonce: wp2leads_ajax_object.nonce,
                mapimportids: mapimportids,
            },
            success: function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    alert(decoded.message);
                    window.location.href = '?page=wp2l-admin&tab=map_port' + redirectMapping ;
                } else {
                    alert(decoded.message);
                }
            }
        });
    });

    $(document.body).on('submit', '#import-maps-from-server-form', function(e) {
        e.preventDefault();

        var form = $("#import-maps-from-server-form");
        var activeMapping = $_GET('active_mapping');
        var redirectMapping = '';

        if (activeMapping) {
            redirectMapping = '&active_mapping=' + activeMapping;
        }

        var mapimportids = [];

        var selectOneImport = form.find('.map-public-ids:checked');

        if (selectOneImport.length > 0) {
            selectOneImport.each(function() {
                var mapId = $(this).val();
                mapimportids.push({
                    mapId: mapId
                });
            });
        }


        $.ajax({
            url: ajaxurl,
            method: 'post',
            async: false,
            data: {
                action: 'wp2l_import_maps',
				nonce: wp2leads_ajax_object.nonce,
                mapimportids: mapimportids
            },
            success: function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    alert(decoded.message);

                    if (decoded.map_id) {
						window.location.href = '?page=wp2l-admin&tab=map_runner&active_mapping=' + decoded.map_id ;
					} else {
						window.location.href = '?page=wp2l-admin&tab=map_port' + redirectMapping ;
					}

                } else {
                    alert(decoded.message);
                }
            }
        });
    });

	$(document.body).on('click', '.gray-back, .close', function(e) {
		$('.import-maps-from-server-table-wrap .api-spinner-holder').removeClass('api-processing');
	});

	$(document.body).on('click', '.open-magic', function(e) {
		e.preventDefault();
		e.stopPropagation();

		var spinner = $(this).closest('.api-processing-holder').find('.api-spinner-holder');
		spinner.addClass('api-processing');
		var id = $(this).data('id');
		var wrap = $('.magic-'+id);
		wrap.find('.magic-info').html('');

		if (wrap.length) {
			setTimeout(function(){
				checkRequiredPlugins(id, false, function(installed){
					if (installed) {
						$('body').find('.close').click();
						// ajax request just in case - to prevent plugins redirect
						$.ajax({
							url: ajaxurl,
							method: 'post',
							async: false,
							data: {
								action: 'wp2l_update_magic_content',
								nonce: wp2leads_ajax_object.nonce,
								magic_id: id,
							},
							success: function () {
								$.ajax({ //  real ajax after redirect
									url: ajaxurl,
									method: 'post',
									async: false,
									data: {
										action: 'wp2l_update_magic_content',
										nonce: wp2leads_ajax_object.nonce,
										magic_id: id,
									},
									success: function (response) {
										var decoded = $.parseJSON(response);
										wrap.find('.magic-content').html(decoded.html);

										// preselect form if possible
										if ( $('.form_preselect').length ) {
											var val = $('.form_preselect').eq(0).val();

											// only for cf7 now!
											if ( $('select[name=magiccf]').length ) {
												$('select[name=magiccf]').val(val);

												// start process if we have form_preselect field
												$('select[name=magiccf]').parent().find('.magic-import').click();
											}
										}

										wrap.show();
										spinner.removeClass('api-processing');
										if ($('.start_magic').length == 0) {
											$('html, body').animate({
												scrollTop: wrap.offset().top-40
											}, 500);
										}
									}
								});

							}
						});
					} else {
						$('body').find('.close').click();
						$('.magic-maps').hide();
						wrap.show();
						spinner.removeClass('api-processing');
						if ($('.start_magic').length == 0) {
							$('html, body').animate({
								scrollTop: wrap.offset().top-40
							}, 500);
						}
					}

				}, function(){
					spinner.removeClass('api-processing');
				}, wrap.find('.magic-info'));
			}, 100);
		}

	});

	/** Check and install needed plugins */
	// map id - int, 169
	// div - where show dialog, jquery el, if empty - in fancy
	// callbacks - anonymus functions
	// ndiv - jquery block for the plugins notices
	function checkRequiredPlugins(mapId, div, callbackOk, callbackErr, ndiv) {
		// delete old plugins popup
		$('body').find('.wp2l-plugins-install-modal').remove();
		var modal = Handlebars.compile($('#wp2l-plugins-install-modal')[0].innerHTML);

		if (!div) {
			$('body').append(modal());
			var modalWrap = $('body').find('.wp2l-plugins-install-modal');
			modalWrap.addClass('transfer-data-modal');
			modalWrap.center();
		} else {
			div.append(modal);
			var modalWrap = $('body').find('.wp2l-plugins-install-modal');
		}

		// get and show plugins list
		$.ajax({
			url: ajaxurl,
			method: 'post',
			async: false,
			data: {
				action: 'wp2l_get_map_plugins',
				nonce: wp2leads_ajax_object.nonce,
				map_id: mapId,
			},
			success: function (response) {
				var decoded = $.parseJSON(response);
				modalWrap.find('.api-spinner-holder').removeClass('api-processing');

				if (decoded.success) {
					if (decoded.required) {
						modalWrap.find('.required-plugins').html(decoded.required);
						modalWrap.find('#installPlugins').show();
					} else {
						modalWrap.find('.required-plugins').hide();
						modalWrap.find('.required-plugins').prev().hide();
						modalWrap.find('.required-plugins').prev().prev().hide();
						if (decoded.recommend) modalWrap.find('#skipPlugins').show();
					}

					if (decoded.recommend) {
						modalWrap.find('.recommend-plugins').html(decoded.recommend);
						modalWrap.find('#installPlugins').show();
					} else {
						modalWrap.find('.recommend-plugins').hide();
						modalWrap.find('.recommend-plugins').prev().hide();
						modalWrap.find('.recommend-plugins').prev().prev().hide();
					}

					modalWrap.center();
				} else {
					alert(decoded.message);
					callbackErr();
				}

				if (!decoded.required && !decoded.recommend) {
					modalWrap.find('.close').click();
					callbackOk();
				}
			}
		});

		var installPluginIndex = -1;
		function installPlugin($el) {
			installPluginIndex ++;
			var currIndex = installPluginIndex;

			if (installPluginIndex == $el.length) {
				modalWrap.find('.api-spinner-holder').removeClass('api-processing');

				if (modalWrap.find('.error').length < 1) {
					callbackOk(1);
				} else {
					callbackErr();
				}
				return;
			}

			var currEl = $el.eq(installPluginIndex);
			var slug = currEl.val();

			currEl.parent().addClass('progress');
			currEl.parent().find('.response').text(wp2leads_i18n_get('Installing...'));
			currEl.parent().find('.error').remove();

			setTimeout(function(){
				$.ajax({
					url: ajaxurl,
					method: 'post',
					async: false,
					data: {
						action: 'wp2l_check_plugin_by_slug',
						nonce: wp2leads_ajax_object.nonce,
						plugin_slug: slug,
						map_id: mapId
					},
					success: function (response) {

						var decoded = $.parseJSON(response.split('&&&')[1]);

						currEl.parent().removeClass('progress');
						if (decoded.success) {
							currEl.parent().find('.response').text(wp2leads_i18n_get('Done'));
							currEl.parent().css({'opacity' : '0.8'});
							currEl.parent().click(false);

							if (decoded.message && ndiv) {
								ndiv.append(decoded.message);
							}

							if (decoded.redirect) {
								if (decoded.message) {
									alert(decoded.message);
								}

								window.location.assign(decoded.redirect);
							} else if (decoded.message && ndiv) {
								ndiv.append(decoded.message);
							}
						} else {
							currEl.parent().append(decoded.message);
							currEl.parent().find('.response').text('');
						}

						installPlugin($el);
					}
				});
			}, 100);
		}

		modalWrap.find('#installPlugins').click(function(){
			var $ch = modalWrap.find('input[type=checkbox]:checked');
			installPluginIndex = -1;

			if ($ch) {
				modalWrap.find('.api-spinner-holder').addClass('api-processing');
				setTimeout(function(){installPlugin($ch);}, 100);
			} else {
				callbackOk();
			}
		});

		modalWrap.find('#skipPlugins').click(function(){
			modalWrap.find('.close').click();
			callbackOk();
		});
	}

	$(document.body).on('click', '.map-public-ids', function() {
		if ($(this).prop('readonly')) {
			var that = $(this);

			$(this).prop('checked', false);

			checkRequiredPlugins($(this).data('map_id'), false, function(){
				that.prop('readonly', false);
				that.prop('checked', 'checked');
				$('body').find('.close').click();
			}, function(){

			});
		}
	});

	$(document.body).on('click', '.magic-import', function(e) {
        e.preventDefault();
		e.stopPropagation();

        var formId = $(this).parent().find('select').val();
		var formType = $(this).data('type');
		var mapId = $(this).data('map_id');
		var formName = '';
		var formGroupName = $(this).prev().prev().text();

		// check plugins

		var requiredPlugins = $(this).closest('tr').find('.requider-plugins div');

		if (!formId) {
			setTimeout(function(){
				alert(wp2leads_i18n_get('Select form to import.'));
			}, 250 );
		} else if (requiredPlugins.length) {
			alert(wp2leads_i18n_get('You should install and activate required plugins to use this map.'));
		} else {
			$(this).prev().find('option').each(function(){
				if ($(this).val() == formId) formName = $(this).text();
			});

			var modal = Handlebars.compile($('#wp2l-magic-import-modal')[0].innerHTML);

			$('body').append(modal());
			var modalWrap = $('body').find('.transfer-data-modal');

			setTimeout( function() {
				if ( typeof modalWrap.center !== 'undefined' && $.isFunction(modalWrap.center)) {
					modalWrap.center();
				}
			}, 150);

			$.ajax({
				url: ajaxurl,
				method: 'post',
				async: false,
				data: {
					action: 'wp2l_magic_import',
					nonce: wp2leads_ajax_object.nonce,
					form_id: formId,
					type: formType,
					name: formName,
					formGroup: formGroupName,
					map_id: mapId
				},
				success: function (response) {
					var decoded = $.parseJSON(response);

					modalWrap.find('.api-spinner-holder').removeClass('api-processing');

					if (decoded.success) {
						modalWrap.find('#magicName').val(decoded.map_title);
						modalWrap.find('.magic-title-tag').text(decoded.map_title);
						$('.mnche').prop('checked', 'checked');
						$('.magic-title-tag').show();

						modalWrap.find('.tags-preset').append(decoded.tags);
						modalWrap.find('#magicSave').data('mapId', decoded.map_id);
						modalWrap.find('#magicSkip').attr('href', decoded.redirect);
						modalWrap.find('#magicSave').data('href', decoded.redirect);
						modalWrap.find('#magicSave').data('form_code', decoded.form_code);
					} else {
						alert(decoded.message);
					}

					setTimeout( function() {
						if ( typeof modalWrap.center !== 'undefined' && $.isFunction(modalWrap.center)) {
							modalWrap.center();
						}
					}, 150);
				}
			});
	    }
    });

	// show popup like magic popup but for existing map
	function show_map_replacements_popup(formId) {

		var modal = Handlebars.compile($('#wp2l-magic-tags-settings-modal')[0].innerHTML);

		if ( $('.magic-transfer').length < 1 )	$('body').append(modal());
		var modalWrap = $('body').find('.transfer-data-modal');
		modalWrap.center();
		setTimeout(function(){
			$.ajax({
				url: ajaxurl,
				method: 'post',
				async: false,
				data: {
					action: 'wp2l_get_edit_replacements_popup',
					map_id: formId,
				},
				success: function (response) {
					var decoded = $.parseJSON(response);

					modalWrap.find('.api-spinner-holder').removeClass('api-processing');

					if (decoded.success) {
						modalWrap.find('#magicName').val(decoded.map_title).trigger('change');
						modalWrap.find('.magic-title-tag').text(decoded.map_title);
						modalWrap.find('.tags-preset').html(decoded.tags);
						modalWrap.find('#magicSave').data('mapId', formId);
						modalWrap.find('#magicSave').data('href', decoded.redirect);
						modalWrap.find('#magicSave').data('form_code', decoded.form_code);
						renameMagicTags();
					} else {
						alert(decoded.message);
					}

					$('body').trigger('magicShowed');
				}
			});
		}, 50);
	}

	$(document.body).on('click', '.change-magic-replacements', function(e) {
        e.preventDefault();
		e.stopPropagation();
		show_map_replacements_popup($(this).data('id'));
	});

	// show popup with tags on start
	if ($('.change-magic-replacements').length) {
		if ($('.change-magic-replacements').data('show')) {
			$(document.body).one('wp2l_get_subscriber_tags_from_klicktipp', function(){
				$('.change-magic-replacements').click();
			});
		}
	}

	$(document.body).on('click', '#magicSave', function(e) {
        e.preventDefault();
		e.stopPropagation();

		var wrap = $(this).closest('.magic-transfer');
		var mapId = $(this).data('mapId');
		var redirect = $(this).data('href');
		var formCode = $(this).data('form_code');
		var fields = [];

		wrap.find('.api-spinner-holder').addClass('api-processing');

		wrap.find('.tags-preset input[type=checkbox]:checked').each(function(){
			var field = {
				'value': $(this).val(),
				'label_prefix': ''
			};

			var radio = $(this).closest('.tag-wrap-row').find('input[type=radio]:checked');
			if (radio.length) {
				field.label_prefix = radio.data('text');
			}

			fields.push(field);
		});

		var mapName = wrap.find('#magicName').val();
		var updateTags = {};

		$('.twrap-info').each(function(){
			var oldValue = $(this).find('a').data('value');
			var newValue = $(this).closest('.tag-wrap-row').find('.labels input:checked').data('text');

			if ( $(this).closest('.tag-wrap-row').find('.twr-checkbox input').prop('checked') && newValue != oldValue ) {
				updateTags[newValue] = oldValue;
			}

		});

		setTimeout(function(){
			$.ajax({
				url: ajaxurl,
				method: 'post',
				async: false,
				data: {
					action: 'wp2l_magic_import_step2',
					nonce: wp2leads_ajax_object.nonce,
					map_id: mapId,
					fields: JSON.stringify(fields),
					name: mapName,
					nametag: $('.mnche').prop('checked') ? '1' : '',
					form_code: formCode,
					update_tags: updateTags
				},
				success: function (response) {
					var decoded = $.parseJSON(response);

					$('body').trigger('magicSaved');

					if (decoded.success) {
						location.href = redirect;
					} else {
						alert(decoded.message);
						wrap.find('.api-spinner-holder').removeClass('api-processing');
					}
				}
			});
		}, 100);
	});

	$(document.body).on('click changed', '.mnche', function() {
		if ($(this).prop('checked')) {
			$(this).parent().next().show();
		} else {
			$(this).parent().next().hide();
		}
	});

	$(document.body).on('change keyup', '#magicName', renameMagicTags);

	function renameMagicTags() {
		var name = $('#magicName').val();
		$('.magic-title-tag').text(name);

		$('.main-wrapper').find('.tags-preset .tag-name').each(function(){
			var preprefix = '';
			prefix = $(this).closest('.tag-wrap-row').find('input[type=radio]:checked').data('text');
			if (prefix) {
				prefix = '; ' + prefix;
				prefix += ': ';
			}

			$(this).text(name + prefix + $(this).data('name'));
		});
	}

	// listen general checkboxes on magic popup
	$(document.body).on('change', '.magic-tags-choose input[type=checkbox]', function(){
		if ($(this).hasClass('check-all-checkbox')) {
			// select all
			if ($(this).prop('checked')) {
				$(this).closest('.magic-tags-choose').find('.tags-preset .twr-checkbox input[type=checkbox]').prop('checked', 'checked');
			} else {
				$(this).closest('.magic-tags-choose').find('.tags-preset .twr-checkbox input[type=checkbox]').prop('checked', false);
			}

		} else {
			// another checkbox
			var allChecked = true;
			$(this).closest('.magic-tags-choose').find('.tags-preset .twr-checkbox input[type=checkbox]').each(function(){
				if (!$(this).prop('checked')) allChecked = false;
			});

			if (allChecked) {
				$(this).closest('.magic-tags-choose').find('.check-all-checkbox').prop('checked', 'checked');
			} else {
				$(this).closest('.magic-tags-choose').find('.check-all-checkbox').prop('checked', false);
			}
		}
	});

	// listen general radio on magic popup
	$(document.body).on('change', '.magic-tags-choose input[type=radio]', function(){
		var val = $(this).val();

		if ($(this).hasClass('general-magic-radio')) {
			// select all
			$(this).closest('.magic-tags-choose').find('.tags-preset input[type=radio]').each(function(){
				if ($(this).val() == val) {
					$(this).prop('checked', 'checked');
				} else {
					$(this).prop('checked', false);
				}
			});

		} else {
			// another radio
			var allChecked = true;

			$(this).closest('.magic-tags-choose').find('.tags-preset input[type=radio]').each(function(){
				if ($(this).val() !== val && $(this).prop('checked')) allChecked = false;
			});

			$(this).closest('.magic-tags-choose').find('.general-magic-radio').each(function(){

				if (allChecked && $(this).val() == val) {
					$(this).prop('checked', 'checked');
				} else {
					$(this).prop('checked', false);
				}
			});
		}

		renameMagicTags();
		toggleOldValueReplacements();
	});

	// listen textareas for the label
	$(document.body).on('keyup change click', '.radio-label-text', function(){
		var val = $(this).val();
		var radio = $(this).closest('.labels').find('.before-textarea input');

		val = val.replace(/,/g, '');
		val = val.replace(/;/g, '');
		val = val.replace(/:/g, '');
		$(this).val(val);

		radio.prop('checked', 'checked');
		radio.data('text', val);
		renameMagicTags();
		toggleOldValueReplacements();
	});

	// Return old value to the input
	$(document.body).on('click', '.twrap-info a', function(){
		var val = $(this).data('value');
		var wrap = $(this).closest('.tag-wrap-row');

		if ( val == wrap.find('input[value=name]').data('text') ) {
			wrap.find('input[value=name]').prop('checked', 'checked');
			wrap.find('input[value=name]').trigger('change');
		} else {
			wrap.find('.labels textarea').val();
			wrap.find('.labels textarea').trigger('change');
		}

		$(this).closest('.twrap-info').hide();
		$(this).closest('.tag-wrap-row').find('.twr-checkbox input').prop('checked', 'checked');
		return false;
	});

	// this function will hide/show old value
	function toggleOldValueReplacements() {
		$('.twrap-info').each(function(){
			// continue if we have a new form
			if ( $(this).find('a').length == 0 ) return true;

			// turn of if value will not used
			if ( !$(this).closest('.tag-wrap-row').find('.twr-checkbox input').prop('checked') ) {
				$(this).hide();
				return true;
			}

			// get current value
			if ( $(this).find('a').data('value') == $(this).closest('.tag-wrap-row').find('.labels input:checked').data('text') ) {
				$(this).hide();
			} else {
				$(this).show();
			}
		});
	}

	// turn on row if user clicked on any value
	$(document.body).on( 'click','.tag-wrap-row .labels input, .tag-wrap-row .labels textarea', function(){
		$(this).closest('.tag-wrap-row').find('.twr-checkbox input').prop('checked', 'checked');
		toggleOldValueReplacements();
	});

	// remove notices if we have remove notice input
	var remove_notice = $('#remove_notice');
	if ( remove_notice.length ) {
		$('.wp2leads-hide-notice').each(function(){
			if ($(this).data('id') == remove_notice.val() ) {
				$(this).click();
			}
		});
	}

    $(document.body).on('submit', '#import-pending-maps-from-server-form', function(e) {
        e.preventDefault();

        var form = $("#import-pending-maps-from-server-form");
        var activeMapping = $_GET('active_mapping');
        var redirectMapping = '';

        if (activeMapping) {
            redirectMapping = '&active_mapping=' + activeMapping;
        }

        var mapimportids = [];

        var selectOneImport = form.find('.map-public-ids:checked');

        if (selectOneImport.length > 0) {
            selectOneImport.each(function() {
                var mapId = $(this).val();
                mapimportids.push({
                    mapId: mapId
                });
            });
        }

        $.ajax({
            url: ajaxurl,
            method: 'post',
            async: false,
            data: {
                action: 'wp2l_import_pending_maps',
				nonce: wp2leads_ajax_object.nonce,
                mapimportids: mapimportids,
            },
            success: function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    alert(decoded.message);

					if (decoded.map_id) {
						window.location.href = '?page=wp2l-admin&tab=map_runner&active_mapping=' + decoded.map_id ;
					} else {
						window.location.href = '?page=wp2l-admin&tab=map_port' + redirectMapping ;
					}
                } else {
                    alert(decoded.message);
                }
            }
        });
    });

    $(document.body).on('click', '#wp2l_select_all_import_maps', function() {
        var isChecked = $(this).prop('checked');
        var table = $(this).parents('.import-maps-from-server-table');
        var selectOneDownload = table.find('.map-public-ids');

        if (isChecked) {
            selectOneDownload.prop('checked', true);
        } else {
            selectOneDownload.prop('checked', false);
        }
    });

    $(document.body).on('click', '#export-maps_table .map-ids, #export-maps_table .map-upload, #export-maps_table .map-update', function() {
        var checkBox = $(this);
        var isChecked = $(this).prop('checked');
        var action = null;
        var checkedCount = 0;
        var checkboxes = false;
        var table = $(this).parents('#export-maps_table');

        var selectAllFile = $('#wp2l_select_all_maps');
        var selectAllUpload = $('#wp2l_select_all_mapuploadids');
        var selectAllUpdate = $('#wp2l_select_all_mapupdateids');

        var selectOneFile = table.find('.map-ids');
        var selectOneUpload = table.find('.map-upload');
        var selectOneUpdate = table.find('.map-update');

        if (checkBox.hasClass('map-upload')) {
            action = 'upload';

            checkboxes = selectOneUpload;
        } else if (checkBox.hasClass('map-update')) {
            action = 'update';

            checkboxes = selectOneUpdate;
        } else if (checkBox.hasClass('map-ids')) {
            action = 'to-file';

            checkboxes = selectOneFile;
        }

        if (checkboxes && checkboxes.length > 0) {
            checkboxes.each(function() {
                if ($(this).is(":checked")) {
                    checkedCount++;
                }
            });
        }

        if (action === 'upload') {
            if (checkedCount > 0) {
                selectAllFile.prop('checked', false).prop('disabled', true);
                selectAllUpdate.prop('checked', false).prop('disabled', true);
                selectOneFile.prop('checked', false).prop('disabled', true);
                selectOneUpdate.prop('checked', false).prop('disabled', true);
            } else {
                selectAllFile.prop('checked', false).prop('disabled', false);
                selectAllUpdate.prop('checked', false).prop('disabled', false);
                selectOneFile.prop('checked', false).prop('disabled', false);
                selectOneUpdate.prop('checked', false).prop('disabled', false);
            }
        } else if (action === 'update') {
            if (checkedCount > 0) {
                selectAllUpload.prop('checked', false).prop('disabled', true);
                selectAllFile.prop('checked', false).prop('disabled', true);
                selectOneFile.prop('checked', false).prop('disabled', true);
                selectOneUpload.prop('checked', false).prop('disabled', true);
            } else {
                selectAllUpload.prop('checked', false).prop('disabled', false);
                selectAllFile.prop('checked', false).prop('disabled', false);
                selectOneFile.prop('checked', false).prop('disabled', false);
                selectOneUpload.prop('checked', false).prop('disabled', false);
            }
        } else if (action === 'to-file') {
            if (checkedCount > 0) {
                selectAllUpload.prop('checked', false).prop('disabled', true);
                selectAllUpdate.prop('checked', false).prop('disabled', true);
                selectOneUpload.prop('checked', false).prop('disabled', true);
                selectOneUpdate.prop('checked', false).prop('disabled', true);
            } else {
                selectAllUpload.prop('checked', false).prop('disabled', false);
                selectAllUpdate.prop('checked', false).prop('disabled', false);
                selectOneUpload.prop('checked', false).prop('disabled', false);
                selectOneUpdate.prop('checked', false).prop('disabled', false);
            }
        }
    });

    $(document.body).on('click', '#wp2l_select_all_mapuploadids', function() {
        var isChecked = $(this).prop('checked');
        var table = $(this).parents('#export-maps_table');
        var selectAllUpdate = $('#wp2l_select_all_mapupdateids');
        var selectOneUpdate = table.find('.map-update');

        $(this).closest('form').find('input.map-upload').each(function() {
            $(this).prop('checked', isChecked);
        });

        if (isChecked) {
            selectAllUpdate.prop('checked', false).prop('disabled', true);
            selectOneUpdate.prop('checked', false).prop('disabled', true);
        } else {
            selectAllUpdate.prop('checked', false).prop('disabled', false);
            selectOneUpdate.prop('checked', false).prop('disabled', false);
        }
    });

    $(document.body).on('click', '#wp2l_select_all_mapupdateids', function() {
        var isChecked = $(this).prop('checked');
        var table = $(this).parents('#export-maps_table');
        var selectAllUpload = $('#wp2l_select_all_mapuploadids');
        var selectOneUpload = table.find('.map-upload');

        $(this).closest('form').find('input.map-update').each(function() {
            $(this).prop('checked', isChecked);
        });

        if (isChecked) {
            selectAllUpload.prop('checked', false).prop('disabled', true);
            selectOneUpload.prop('checked', false).prop('disabled', true);
        } else {
            selectAllUpload.prop('checked', false).prop('disabled', false);
            selectOneUpload.prop('checked', false).prop('disabled', false);
        }
    });

    $(document.body).on('click', '#privacyPolicyConfirm', function() {
        var isChecked = $(this).prop('checked');
        var table = $('#export-maps_table');
        var selectAllUpload = $('#wp2l_select_all_mapuploadids');
        var selectAllUpdate = $('#wp2l_select_all_mapupdateids');
        var selectOneUpload = table.find('.map-upload');
        var selectOneUpdate = table.find('.map-update');
        var selectMapKind = table.find('select.map-kind-version');
        var exportBtn = $('#exportMaps');

        if (isChecked) {
            selectAllUpdate.prop('disabled', false);
            selectAllUpload.prop('disabled', false);
            selectOneUpload.prop('disabled', false);
            selectOneUpdate.prop('disabled', false);
            selectMapKind.prop('disabled', false);
            exportBtn.addClass('button-primary').prop('disabled', false);
        } else {
            selectAllUpdate.prop('checked', false).prop('disabled', true);
            selectAllUpload.prop('checked', false).prop('disabled', true);
            selectOneUpload.prop('checked', false).prop('disabled', true);
            selectOneUpdate.prop('checked', false).prop('disabled', true);
            selectMapKind.prop('disabled', true);
            exportBtn.removeClass('button-primary').prop('disabled', true);
        }
    });

    $(document.body).on('submit', '#export-maps-form', function(e) {
        var form = $("#export-maps-form");

        var confirmationSelect = $('#privacyPolicyConfirm');
        var isConfirmed = false;

        if (confirmationSelect.length > 0) {
            isConfirmed = confirmationSelect.prop('checked');

            if (!isConfirmed) {
                return;
            }
        }

        var activeMapping = $_GET('active_mapping');
        var redirectMapping = '';

        if (activeMapping) {
            redirectMapping = '&active_mapping=' + activeMapping;
        }

        var mapuploadids = [];
        var mapupdateids = [];
        var mapkindversion = [];

        var selectOneUpload = form.find('.map-upload:checked');
        var selectOneUpdate = form.find('.map-update:checked');

        if (selectOneUpload.length > 0) {
            selectOneUpload.each(function() {
				var mapId = $(this).val();
				var mapFormLink = '';
				var ktLinks = '';
				var plugins = '';

				if ($('#mapformlink_' + mapId).length) {

					mapFormLink = $('#mapformlink_' + mapId).val();
				}

				if ($('#kturl_' + mapId).length) {
					ktLinks = $('#kturl_' + mapId).val();
				}

				if ($('#ktplugins_' + mapId).val()) {

					$('#ktplugins_' + mapId).val().forEach(function(item) {
						if (plugins) plugins += ', ';
						plugins += item;
					});

					if(plugins) plugins = ' Required Plugins: ' + plugins;
				}

                var mapVersion = $('#mapkindversion_' + mapId).val();
                var isExclusive = false;
				var mapName = $('#map_name_' + mapId).val();
				var mapDescription = $('#map_description_' + mapId).val() + plugins;

                if ($('input[name="mapexclusive['+mapId+']"]:checked').length > 0) {
                    isExclusive = true;
                }

                var url = $('#mapurl_' + mapId).val();

                mapuploadids.push({
                    mapId: mapId,
                    mapVersion: mapVersion,
                    isExclusive: isExclusive,
                    url: url,
					ktLinks: ktLinks,
					mapFormLink: mapFormLink,
					mapName: mapName,
					mapDescription: mapDescription
                });

            });
        }

        if (selectOneUpdate.length > 0) {
            selectOneUpdate.each(function() {
				var mapId = $(this).val();
				var mapFormLink = '';
				var ktLinks = '';
				var plugins = '';

				if ($('#mapformlink_' + mapId).length) {
					mapFormLink = $('#mapformlink_' + mapId).val();
				}

				if ($('#kturl_' + mapId).length) {
					ktLinks = $('#kturl_' + mapId).val();
				}

				if ($('#ktplugins_' + mapId).val()) {

					$('#ktplugins_' + mapId).val().forEach(function(item) {
						if (plugins) plugins += ', ';
						plugins += item;
					});

					if(plugins) plugins = ' Required Plugins: ' + plugins;
				}

                var mapVersion = $('#mapkindversion_' + mapId).val();
                var isExclusive = false;
				var mapName = $('#map_name_' + mapId).val();
				var mapDescription = $('#map_description_' + mapId).val() + plugins;

                if ($('input[name="mapexclusive['+mapId+']"]:checked').length > 0) {
                    isExclusive = true;
                }

                var url = $('#mapurl_' + mapId).val();

                mapupdateids.push({
                    mapId: mapId,
                    mapVersion: mapVersion,
                    isExclusive: isExclusive,
                    url: url,
					ktLinks: ktLinks,
					mapFormLink: mapFormLink,
					mapName: mapName,
					mapDescription: mapDescription
                });
            });
        }

        if ( 0 && isConfirmed && ( mapupdateids.length > 0 || mapuploadids.length > 0 ) ) {
            e.preventDefault();

            $.ajax({
                url: ajaxurl,
                method: 'post',
                data: {
                    action: 'wp2l_save_policy_confirmed',
					nonce: wp2leads_ajax_object.nonce,
                },
                success: function (response) {
                    var decoded;

                    try {
                        decoded = $.parseJSON(response);
                    } catch(err) {
                        decoded = false;
                    }

                    if (decoded) {
                        if (decoded.success) {

                            $.ajax({
                                url: ajaxurl,
                                method: 'post',
                                async: false,
                                data: {
                                    action: 'wp2l_export_maps',
									nonce: wp2leads_ajax_object.nonce,
                                    mapuploadids: mapuploadids,
                                    mapupdateids: mapupdateids
                                },
                                success: function (response) {
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

                                            alert(decoded.message);
                                            window.location.href = '?page=wp2l-admin&tab=map_port' + redirectMapping ;
                                        } else {
                                            alert(decoded.message);
                                        }
                                    } else {
                                        alert(wp2leads_i18n_get('Something went wrong'));
                                    }

                                }
                            });

                        } else {
                            alert(decoded.message);
                        }
                    } else {
                        alert(wp2leads_i18n_get('Something went wrong'));
                    }

                }
            });
        } else if (mapupdateids.length > 0 || mapuploadids.length > 0) {
            e.preventDefault();

            $.ajax({
                url: ajaxurl,
                method: 'post',
                async: false,
                data: {
                    action: 'wp2l_export_maps',
					nonce: wp2leads_ajax_object.nonce,
                    mapuploadids: mapuploadids,
                    mapupdateids: mapupdateids
                },
                success: function (response) {
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

                            alert(decoded.message);
                            window.location.href = '?page=wp2l-admin&tab=map_port' + redirectMapping ;
                        } else {
                            alert(decoded.message);
                        }
                    } else {
                        alert(wp2leads_i18n_get('Something went wrong'));
                    }

                }
            });
        } else {
            e.preventDefault();

            alert(wp2leads_i18n_get('Select at least one map to export'));
        }

        return;
    });

    // TODO - This is only for DEV purposes need to be removed
    $(document.body).on('click', '.change-license', function(e) {
        e.preventDefault();
        var licenseToTest = $(this).data('license');

        $.ajax({
            type: 'post',
            url: ajaxurl,
            async: false,
            data: {
				action: 'wp2l_set_fake_license_level',
				nonce: wp2leads_ajax_object.nonce,
				license_level: licenseToTest,
			},

            success: function (response) {
                var licenseLevelCurrent = $('.license-level-current');
                var decoded = $.parseJSON(response);

                licenseLevelCurrent.text(decoded.license_level);

                window.location.reload();
            }
        });
    });

	$(document).on('click', '.wp2l-advanced-tab-switcher', function(){
		$(this).closest('.nav-tab-wrapper').removeClass('wp2l-advanced-non-active');
		$(this).closest('.nav-tab-wrapper').addClass('wp2l-advanced-active');
		return false;
	});

    $(document.body).on('click', '.wp2l-map-delete', function () {
        if (confirm(wp2leads_i18n_get('Are you sure you want to completely delete this map?'))) {
            var $row = $(this).parentsUntil('tr').parent();
            var $form = $(this).parentsUntil('form').parent().first();
            var mapId = _.find($form.serializeArray(), {'name': 'map_id'}).value;

            $.post(ajaxurl, {
				action: 'wp2l_map_delete',
				nonce: wp2leads_ajax_object.nonce,
				map_id: mapId
			}, function (response) {

                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    alert(wp2leads_i18n_get('Map successfully deleted!'));
                } else {
                    alert(wp2leads_i18n_get('Something went wrong.'));
                }

                window.location.reload();
            });
        }
    });

	/* Global maps list button */
	$(document.body).on('click', '#btnShowGlobalMapList', function () {
        var btn = $(this);
        var openText = btn.data('open-text');
        var closeText = btn.data('close-text');

        var mapList = $(document.body).find('#globalMapsList');

        if (!mapList.hasClass('active')) {
            mapList.addClass('active');
            btn.text(closeText);
        } else {
            mapList.removeClass('active');
            btn.text(openText);
        }
    });

    $(document.body).on('click', '#available-maps_actions-run', function(e) {
        e.preventDefault();
        var button = $(this);

        if (button.hasClass('disabled')) {
            return false;
        }

        var action = $('#available-maps_actions');
        var actionVal = action.val();
        var actionDefault = action.find('option[value=""]');
        var mapsList = $('.available-maps_table-wrap .map_checkbox');
        var allMapsId = [];
        var selectedMapsId = [];
        var allMaps = false;

        if ('delete_all' === actionVal) {
            allMaps = true;
        }

        if (mapsList.length > 0) {
            mapsList.each(function () {
                var mapItem = $(this);
                var mapId = mapItem.val();

                allMapsId.push(mapId);

                if (mapItem.prop('checked')) {
                    selectedMapsId.push(mapId);
                }
            });
        }

        var useMaps = selectedMapsId;

        if (allMaps) {
            useMaps = allMapsId;
        }

        if (confirm(wp2leads_i18n_get('Are you sure you want to completely delete this maps?'))) {
            var data = {
                action: 'wp2l_maps_actions_run',
                run: actionVal,
                maps: useMaps
            };

            $.post(ajaxurl, data, function(response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    alert(decoded.message);

                    window.location.reload();
                } else {
                    alert(decoded.message);
                }
            });

            actionDefault.attr('selected', true);
            action.trigger('change');
        }
    });

    $(document.body).on('change', '#available-maps_actions', function(e) {
        var action = $(this);
        var actionVal = action.val();
        var button = $('#available-maps_actions-run');

        if ('' !== actionVal) {
            button.removeClass('disabled');
        } else {
            button.addClass('disabled');
        }
    });

    $(document.body).on('click', '#wp2lActivateModal', function() {
        var licenseEmail = $('#wp2l-modal-license-email').val(),
            licenseKey = $('#wp2l-modal-license-key').val();

        var unsuccessMessageLinkTemplate = Handlebars.compile($('#wp2l-license-page-link')[0].innerHTML);
        var errorMessageHolder = $(document.body).find('.error-message-holder');

        $.ajax({
            type: 'post',
            url: ajaxurl,
            async: false,
            data: {
                action: 'wp2l_modal_license_activation',
				nonce: wp2leads_ajax_object.nonce,
                licenseEmail: licenseEmail,
                licenseKey: licenseKey
            },
            success: function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    if (200 === decoded.status) {
                        alert(decoded.message);
                        window.location.reload();
                    } else {
                        errorMessageHolder.append('<p>'+decoded.message+'</p>');
                        errorMessageHolder.append(unsuccessMessageLinkTemplate({}));
                    }
                } else {
                    errorMessageHolder.append('<p>'+decoded.message+'</p>');
                    errorMessageHolder.append(unsuccessMessageLinkTemplate({}));
                }
            }
        });
    });

    $(document.body).on('click', '#modal-close', function() {
        var modal = $(this).parents('#modal-overlay');

        if (modal.length > 0) {
            modal.remove();
        }
    });

	$(document.body).on('click', '.modal-video', function(){
		if ($(this).data('video')) {
			var modal = Handlebars.compile($('#wp2l-show-video')[0].innerHTML);
			$('body').append(modal({
				video: $(this).data('video'),
			}));
			$('body').find('.wp2l-plugins-show-video').center();
			return false;
		}
	});

    $(document.body).on('click', '#license-modal-open', function() {
        var modalTemplate = Handlebars.compile($('#wp2l-simple-modal')[0].innerHTML);
        var licenseFormTemplate = Handlebars.compile($('#wp2l-license-form')[0].innerHTML);

        $('body').append(modalTemplate({}));

        var modal = $(document.body).find('#modal-overlay #content');
        modal.append(licenseFormTemplate({}));
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

	// export cf7 templates (deprecated)
	$(document.body).on('click', '.export_cf7_template', function(e) {
        e.preventDefault();

		var button = $(this);
		var row = button.closest('tr');

		row.find('.map_server_id').removeClass('error');
		row.find('.form_title').removeClass('error');
		row.find('.example_link').removeClass('error');

		var errors = false;

		var form_id = button.data('id');
		var form_type = button.data('type');
		var map_id = row.find('.map_server_id').val();

		if (!map_id) {
			row.find('.map_server_id').addClass('error');
			errors = true;
		}

		var form_title = row.find('.form_title').val();

		if (!form_title) {
			row.find('.form_title').addClass('error');
			errors = true;
		}

		var example_link = row.find('.example_link').val();

		if (!example_link) {
			row.find('.example_link').addClass('error');
			errors = true;
		}

		if (errors) return false;

		var map_title = '';

		row.find('.map_server_id option').each(function(){
			if ($(this).val() == map_id) map_title = $(this).text();
		});

		button.prop('disabled', 'disabled');

        $.ajax({
            url: ajaxurl,
            method: 'post',
            async: false,
            data: {
                action: 'wp2l_export_form_template',
				nonce: wp2leads_ajax_object.nonce,
                form_id: form_id,
				form_type: form_type,
				form_title: form_title,
				example_link: example_link,
				map_id: map_id,
				map_title: map_title
            },
            success: function (response) {
				button.prop('disabled', false);
                var decoded = $.parseJSON(response);
				alert(decoded.message);
            }
        });

		return false;
    });

	// maps template page
	function load_more_catalog_maps() {
		var wrap = $('.catalog_wrap');

		if (wrap.length) {
			var per_page = 1200;
			var offset = wrap.find('.catalog-item').length;

			wrap.parent().find('.api-spinner-holder').addClass('api-processing');
			setTimeout(function(){
				$.ajax({
					url: ajaxurl,
					method: 'post',
					async: false,
					data: {
						action: 'wp2l_get_catalog_items',
						nonce: wp2leads_ajax_object.nonce,
						per_page: per_page,
						offset: offset,
					},
					success: function (response) {
						wrap.parent().find('.api-spinner-holder').removeClass('api-processing');
						var decoded = $.parseJSON(response);
						wrap.append(decoded.html);

						$('.catalog_wrap .catalog-item .d-description').scrollbar();

						//if (!decoded.html) $('.catalog_list .load_more').remove();
						$('.catalog_list .load_more').remove(); // always remove the button
						// check tags

						// check tags
						$('.catalog-tag.all').click();

						$('.catalog-tag').each(function(){
							if ($(this).hasClass('all')) {
								$(this).data('show', true);
							} else {
								$(this).data('show', false);
							}
						});

						wrap.find('.catalog-item').each(function(){
							var $that = $(this);
							var $tags = $that.data('tags').split('|');

							$tags.forEach(function(tag){
								if ($('.catalog-tag.tag-'+tag).length) {
									$('.catalog-tag.tag-'+tag).data('show', true);
								}
							});
						});

						$('.catalog-tag').each(function(){
							if ($(this).data('show')) {
								$(this).show();
							} else {
								$(this).hide();
							}
						});
					}
				});
			}, 50);
		}
	}

	// maps template page
	if ($('.catalog_wrap').length) load_more_catalog_maps();

	// load first line on the maps catalog page
	$('.catalog_list .load_more button').click(load_more_catalog_maps);

	// for recursion
	var magic_steps = {};

	// recursion function
	function make_magic_steps(map_id, data) {
		if (magic_steps[map_id] !== undefined) {
			var wrap = $('.catalog-item[data-id='+map_id+']');

			if (magic_steps[map_id].length) {
				wrap.addClass('blocked');
				var curr_step = magic_steps[map_id].shift();
				wrap.find('.d-console').append(curr_step['start_message']);

				// we have steps
				setTimeout(function() { // needs to prevent window freeze
					$.ajax({
						url: ajaxurl,
						method: 'post',
						async: false,
						data: {
							action: 'wp2l_make_magic_step',
							nonce: wp2leads_ajax_object.nonce,
							data: curr_step,
							info: data
						},
						success: function (response) {
							var decoded = $.parseJSON(response.split('&&&')[1]);

							if (decoded.error) {
								delete magic_steps[map_id];
								wrap.removeClass('blocked');
							}


								wrap.find('.d-console').append(decoded.message).scrollTop(wrap.find('.d-console')[0].scrollHeight);


							if (decoded.data) {
								for (key in decoded.data) {
									data[key] = decoded.data[key];
								}
							}

							make_magic_steps(map_id, data);
						}
					});
				}, 100);
			} else {
				// no more steps
				delete magic_steps[map_id];
				wrap.removeClass('blocked');

				// actions after import
				if (data) {
					var html = '';

					if (data.non_active_map !== undefined) {
						var current_count = parseInt($('.wp2leads-nam-count').text());

						if ( !current_count ) {
							$('#wp-admin-bar-wp2leads_not_active_maps_trigger').addClass('active');
						}

						current_count++;
						$('.wp2leads-nam-count').text(current_count);

						$('#wp2leads-non-active-maps-popup ul').append(data.non_active_map);
						$('#wp-admin-bar-wp2leads_not_active_maps_trigger').addClass('big');
						setTimeout(function(){
							$('#wp-admin-bar-wp2leads_not_active_maps_trigger').removeClass('big');
						}, 1000);
					}

					if (data.new_map_id !== undefined) {
						html += '<p style="text-align: center;"><a href="'+wp2leads_admin_url+'&tab=map_to_api&active_mapping='+data.new_map_id+'" class="button" target="_blank">'+wp2leads_i18n_get('Edit map')+'</a></p>';
					}

					if (data.form_link !== undefined) {
						html += '<p style="text-align: center;"><a href="'+data.form_link+'" class="button" target="_blank">'+wp2leads_i18n_get('Edit Contact Form')+'</a></p>';
					}

					/*
					if (data.kt_links !== undefined) {
						html += '<p style="text-align: center;"><a href="'+data.kt_links+'" class="button" target="_blank">'+wp2leads_i18n_get('Import KT campaign')+'</a></p>';
					}
					*/

					if (data.after_install_message !== undefined) {
						html += data.after_install_message;
					}

					wrap.find('.d-console').html(html);

					if (data.redirect_link !== undefined) {
						location.href = data.redirect_link;
					}
				}
			}

		}
	}

	$(document.body).on('click', '.start_magic_catalog', function(){
		var map_id = $(this).closest('.catalog-item').data('id');

		if (!confirm(wp2leads_i18n_get('Are you sure?'))) {
            return;
        }

		var item = $(this).closest('.catalog-item');
		var map_id = item.data('id');

		if (!map_id) {
			alert(wp2leads_i18n_get('Something went wrong.'));
			return;
		}

		item.addClass('blocked');
		item.find('.d-console').addClass('active');

		// get steps list and start import
		setTimeout(function() { // needs to prevent window freeze
			$.ajax({
				url: ajaxurl,
				method: 'post',
				async: false,
				data: {
					action: 'wp2l_get_magic_steps_for_map',
					nonce: wp2leads_ajax_object.nonce,
					map_id: map_id,
					map_name: item.find('.d-title').text(),
					redirect_link: item.data('redirect_link')
				},
				success: function (response) {
					var decoded = $.parseJSON(response);

					if (decoded.success) {
						item.find('.d-console').append(decoded.message);
						magic_steps[map_id] = decoded.data;
						make_magic_steps(map_id, {}); // start import steps with recursion
					} else {
						alert(decoded.message);
						item.removeClass('blocked');
						item.find('.d-console').removeClass('active');
					}
				}
			});
		}, 100);

	});


	$(document.body).on('click', '.map-to-api-kt-link', function(){
		$(this).parent().hide();
		$(this).parent().next().show();
		$('.map-to-api-kt-imported-yes').addClass('active-wz');
	});

	$(document.body).on('click', '.map-to-api-kt-imported-yes', function(e, silent){
		var spinner = $(this).closest('.accordion-group').find('.api-spinner-holder');
		spinner.addClass('api-processing');
		setTimeout(function(){
			$.ajax({
				url: ajaxurl,
				method: 'post',
				async: false,
				data: {
					action: 'wp2l_update_imported_campaings',
					nonce: wp2leads_ajax_object.nonce,
					id: $(this).data('id'),
				},
				success: function (response) {
					spinner.removeClass('api-processing');
					if ($('#updateApiFieldsOptions').length) {
						$('#updateApiFieldsOptions').trigger('click', [true]);
					} else {
						$('body').trigger('updateApiFieldsOptions');
					}

					if (response) {
						$('.optins-list').html(response);
						$('.optins-list').trigger('rebuild');
					}
				}
			});
		}, 50);
		return false;
	});

	$(document.body).on('rebuild change', '.optins-list', function(){
		if (!$(this).val()) {
			$(this).parent().find('.confirm-url, .thankyou-url').hide();
			return;
		}

		var val = $(this).val();
		var wrap = $(this).parent();

		$(this).find('option').each(function(){
			if ($(this).val() == val) {
				if ($(this).data('confirm')) {
					wrap.find('.confirm-url span').html('<a href="'+$(this).data('confirm')+'" target="_blank">' +$(this).data('confirm')+ '</a>');
				} else {
					wrap.find('.confirm-url span').html($(this).data('default'));
				}

				if ($(this).data('thankyou')) {
					wrap.find('.thankyou-url span').html('<a href="'+$(this).data('thankyou')+'" target="_blank">' +$(this).data('thankyou')+ '</a>');
				} else {
					wrap.find('.thankyou-url span').html($(this).data('default'));
				}
			}
		});
	});

	$(document.body).on('click', '.map-to-api-kt-imported-no', function(){
		$.ajax({
			url: ajaxurl,
			method: 'post',
			async: false,
			data: {
				action: 'wp2l_update_imported_campaings',
				nonce: wp2leads_ajax_object.nonce,
				id: $(this).data('id'),
			},
			success: function (response) {
				$('body').trigger('updateImportedCampaings');
				var wrap = $('.map-to-api-kt-imported-yes').closest('.accordion-subbody');

				wrap.prev().hide('fast');
				wrap.hide('fast');
			}
		});

		return false;
	});

	function updateCatalogItems() {
		// get data
		var activeTags = $('body').find('.catalog_tags .active');

		if (activeTags.length == 1 && activeTags.eq(0).hasClass('all')) {
			$('.catalog_wrap').find('.catalog-item').data('show', 1);
		} else {
			$('.catalog_wrap').find('.catalog-item').data('show', 0);

			activeTags.each(function(){
				var tag = $(this).data('tag');

				if (tag) {
					$('.catalog_wrap').find('.catalog-item').each(function(){

						var tags = $(this).data('tags').split('|');

						if ( tags.indexOf( tag + '' ) > -1 ) {
							$(this).data('show', 1);
						}

					});
				}
			});
		}

		$('.catalog_wrap').find('.catalog-item').each(function(){
			if ($(this).data('show')) {
				$(this).show('fast');
			} else {
				$(this).hide('fast');
			}
		});
	}

	$('.catalog_tags .catalog-tag').click(function(){
		if ($(this).hasClass('all')) {
			if (!$(this).hasClass('active')) {
				$('.catalog_tags').find('.active').removeClass('active');
				$(this).addClass('active');
			}
		} else {
			if ($(this).hasClass('active')) {
				$(this).removeClass('active');

				if ($('.catalog_tags').find('.active').length < 1) {
					$('.catalog_tags').find('.all').addClass('active');
				}
			} else {
				$(this).addClass('active');
				$('.catalog_tags').find('.all').removeClass('active');
			}
		}

		updateCatalogItems();
	});

	// action for the link from cf7

	if ($('.start_magic').length) {

		// if all plugins installed skip required/recommend plugins popup
		if ( $('.skip_check').length ) {
			$('.magic-' + $('.start_magic').eq(0).val() ).show();

			$([document.documentElement, document.body]).stop().animate({
				scrollTop: $('.magic-' + $('.start_magic').eq(0).val() ).offset().top - 70
			}, 150);

			$('.magic-import').eq(0).click();

		} else { // if not show popup
			var val = $('.start_magic').eq(0).val();
			$('.open-magic').each(function(){
				if ($(this).data('id') == val) $(this).trigger('click');
			});
		}
	}

	if ($('#mapTagPrefix').length) {
		if (!$('#mapTagPrefix').val()) {
			$('#mapTagPrefix').val(localStorage.getItem('lastMapTagPrefix' + $('#mapTagPrefix').data('id')));
		}

		$('#mapTagPrefix').on('blur', function(){
			localStorage.setItem('lastMapTagPrefix' + $('#mapTagPrefix').data('id'), $('#mapTagPrefix').val());
		});
	}


	// Map to API install plugins
	$('#installPluginsMapToAPI').click(function(){
		var button = $(this);
		button.prop('disabled', 'disabled');
		var mapId = button.data('id');

		$('.map-to-api-install-plugins').show();
		$.ajax({
			url: ajaxurl,
			method: 'post',
			data: {
				action: 'wp2l_install_map_to_api_plugins',
				nonce: wp2leads_ajax_object.nonce,
				map_id: mapId
			},
			success: function (response) {
				var decoded = $.parseJSON(response.split('&&&')[1]);
				$('.map-to-api-install-plugins').text(decoded.message);
				button.prop('disabled', false);

				if (decoded.success) {
					setTimeout(function(){
						location.reload();
					}, 2000);
				}
			}
		});
	});
})(jQuery);
