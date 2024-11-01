'use strict';

(function ($) {

    var $window = $(window);
    var resizerTimeout;
    var stickyTimeout;
    var stickyTagsTimeout;
    var tagsCloudTimeout;

    $.fn.center = function () {
        this.css("position","absolute");
        this.css("top", ( jQuery(window).height() - this.height() ) / 2+jQuery(window).scrollTop() + "px");
        this.css("left", ( jQuery(window).width() - this.width() ) / 2+jQuery(window).scrollLeft() + "px");
        return this;
    }

    $(document.body).on('tokenize:select', '.api-field', function(e, items){
        $(this).trigger('tokenize:search', "");
    });

    $(document.body).on('tokenize:select', '.options_where', function(e, items){
        $(this).trigger('tokenize:search', "");
    });

    $(document.body).on('tokenize:select', '.options-list, .multiple-autotags-options-list', function(e, items){
        $(this).trigger('tokenize:search', "");
    });

    $(document.body).on('tokenize:select', '.options-concat-list, .multiple-autotags-options-concat-list, .multiple-autotags-options-separator-list', function(e, items){
        $(this).trigger('tokenize:search', "");
    });

    $(document.body).on('tokenize:tokens:add', '.api-field', function(e, value, text) {
        var activeField = $(document.body).find('.tokenize.active');

        updateFieldDatApiOption (activeField, function() {});

        if (!window.apiFieldsOnLoad && !window.apiFieldsOnPagination) {
            maybeUpdateTagsCloud(activeField);
        }
    });

    $(document.body).on('tokenize:dropdown:fill', '.api-field', function(e, items) {
        var activeField = $(document.body).find('.tokenize.active');

        updateFieldDatApiOption (activeField, function() {});
    });

    $(document.body).on('tokenize:tokens:remove', '.api-field', function(e, value){
        var activeField = $(document.body).find('.tokenize.active');

        updateFieldDatApiOption (activeField, function() {});

        if (!window.apiFieldsOnLoad && !window.apiFieldsOnPagination) {
            maybeUpdateTagsCloud(activeField);
        }
    });

    $(document.body).on('tokenize:paste', '.api-field', function(e, items){
        var activeField = $(document.body).find('.tokenize.active');

        updateFieldDatApiOption (activeField, function() {});
    });

    $(document.body).on('tokenize:dropdown:fill', '.options_where', function(e, items){

        if (!window.possibleTagsOnLoad) {
            updateOptinFromCondition(true);
            setTimeout(function() {
                updatePossibleTagsOnChange();
            }, 100);
        }
    });

    $(document.body).on('tokenize:tokens:remove', '.options_where', function(e, items){
        if (!window.possibleTagsOnLoad) {
            updateOptinFromCondition(true);

            setTimeout(function() {
                updatePossibleTagsOnChange();
            }, 100);
        }
    });

    $(document.body).on('tokenize:tokens:add', '.options_where', function(e, items){
        if (!window.possibleTagsOnLoad) {
            updateOptinFromCondition(true);
            setTimeout(function() {
                updatePossibleTagsOnChange();
            }, 100);
        }
    });

    $(document.body).on('tokenize:dropdown:fill', '.options-list, .multiple-autotags-options-list', function(e, items){
        if (!window.possibleTagsOnLoad) {
            setTimeout(function() {
                updatePossibleTagsOnChange();
            }, 100);
        }
    });

    $(document.body).on('tokenize:tokens:remove', '.options-list, .multiple-autotags-options-list', function(e, items){
        if (!window.possibleTagsOnLoad) {
            setTimeout(function() {
                updatePossibleTagsOnChange();
            }, 100);
        }
    });

    $(document.body).on('tokenize:tokens:add', '.options-list, .multiple-autotags-options-list', function(e, items){
        if (!window.possibleTagsOnLoad) {
            setTimeout(function() {
                updatePossibleTagsOnChange();
            }, 100);
        }
    });

    $(document.body).on('tokenize:dropdown:fill', '.options-concat-list, .multiple-autotags-options-concat-list, .multiple-autotags-options-separator-list', function(e, items){
        if (!window.possibleTagsOnLoad) {
            setTimeout(function() {
                updatePossibleTagsOnChange();
            }, 100);
        }
    });

    $(document.body).on('tokenize:tokens:remove', '.options-concat-list, .multiple-autotags-options-concat-list, .multiple-autotags-options-separator-list', function(e, items){
        if (!window.possibleTagsOnLoad) {
            setTimeout(function() {
                updatePossibleTagsOnChange();
            }, 100);
        }
    });

    $(document.body).on('tokenize:tokens:add', '.options-concat-list, .multiple-autotags-options-concat-list', function(e, items){
        if (!window.possibleTagsOnLoad) {
            setTimeout(function() {
                updatePossibleTagsOnChange();
            }, 500);
        }
    });

    $(document.body).on('tokenize:tokens:add', '.multiple-autotags-options-separator-list', function(e, items){
        if (!window.possibleTagsOnLoad) {
            setTimeout(function() {
                updatePossibleTagsOnChange();
            }, 500);
        }
    });

    $(document.body).on('click', '.accordion-header', function() {
        var controller = $(this),
          panel = controller.next('.accordion-body'),
          group = controller.parent('.accordion-group');

        if (controller.hasClass('disabled')) {
          return false;
        }

        group.find('.accordion-body').each(function () {
          // $(this).hide();
            $(this).removeClass('accordion-body-visible');
        });

        if (controller.hasClass('active')) {
          controller.removeClass('active');
          // panel.hide();
          panel.removeClass('accordion-body-visible');
        } else {
          group.find('.accordion-header').each(function () {
            $(this).removeClass('active');
          });

          controller.addClass('active');
          // activepanel.show();
          panel.addClass('accordion-body-visible');
        }
        stickyTagsHolder();
    });

    $(document.body).on('click', '#updateApiFieldsOptions', function(event, silent) {
        var data = {
            action: 'wp2l_update_api_fields'
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
                        if (decoded.fields) {
                            var selectTag = $('<select class="select_api_field_to">');

                            $.each(decoded.fields, function (field_index, field_name) {
                                selectTag.append($('<option value="' + 'api_' + field_index + '">'  + field_name + '</option>'));
                            });

                            var selectApiFieldToHolder = $('.select_api_field_to__holder');

                            selectApiFieldToHolder.each(function() {
                                update_api_fields($(this), decoded.fields);
                            });

							if (typeof silent === "undefined") {
								alert(decoded.message);
							} else {
								//$('body').trigger('updateApiFieldsOptions');
							}
                        }
                    } else {
                        alert(decoded.message);
                    }
                } else {
                    alert(wp2leads_i18n_get('Something went wrong.'));
                }

				$('body').trigger('updateApiFieldsOptions');
            },
            error: function(xhr, status, error) {

            },
            complete: function(xhr, status) {

            }
        });
    });

    function update_api_fields(container, fields) {
        var fieldRow = container.parents('.select_api_field_item');
        var api_field_title = fieldRow.find('h3 .select_api_field_name').text();
        var maprefix = getMapTagPrefix();


        var selectApiFieldContainer = $('#apiFieldsInitialSettings__container');
        var placeholder = selectApiFieldContainer.data('select-placeholder');

        var selected = container.find('select').val();
        var selectTag = $('<select class="select_api_field_to" multiple>');

        selectTag.append($('<option value=""></option>'));

        $.each(fields, function (field_index, field_name) {
            if (maprefix + api_field_title === field_name) {
                selected = 'api_' + field_index;
            } else if (api_field_title === field_name) {
                selected = 'api_' + field_index;
            }

            selectTag.append($('<option value="' + 'api_' + field_index + '">'  + field_name + '</option>'));
        });

        container.empty().append(selectTag);

        var new_api = container.find('.select_api_field_to');

        if (selected) {
            new_api.val(selected);
        }

        new_api.tokenize2({
            tokensMaxItems: 1,
            placeholder: placeholder,
            dropdownMaxItems: 999,
            searchFromStart: false
        });
    }

    $(document.body).on('click', '#saveInitialSettings, #skipInitialSettings', function() {
        var control = $(this);
        var action = control.data('action');
        var apiFieldTypes = $('#apiFieldsInitialSettings__container .field-type, #apiFieldsInitialSettings__container .select_api_field_to');
        var notExistedApiFieldsItems = $('.select_api_field_item');
        var nonExistedApiFieldsSettings = [];

        if (notExistedApiFieldsItems.length > 0) {
            $.each(notExistedApiFieldsItems, function (index, notExistedApiFieldsItem) {
                var item = $(notExistedApiFieldsItem);
                var oldSlug = item.data('old-slug');
                var oldName = item.find('.select_api_field_name').text();
                var oldColumns = item.find('.select_api_field_columns').data('col');
                var oldType = item.find('.select_api_field_type').val();
                var newField = item.find('.select_api_field_to').val();

                var data = {
                    slug: oldSlug,
                    name: oldName,
                    columns: oldColumns,
                    type: oldType,
                    field: newField
                };

                nonExistedApiFieldsSettings.push(data);
            });
        }

        var data = {
            action: 'wp2l_save_map_to_api_initial_settings',
            save: action,
            nonExisted: nonExistedApiFieldsSettings,
            mapId: $_GET('active_mapping'),
            recomended_tags_prefixes: get_recomended_tags_prefixes(),
			optIn: $('.optins-list').val()
        };

        data.globalPrefix = $('#globalTagPrefix').val();
        data.mapPrefix = $('#mapTagPrefix').val();
        data.start_date_data = $('#startDateData').val();
        data.end_date_data = $('#endDateData').val();
        data.api_field_types = [];

        if (apiFieldTypes.length > 0) {
            $.each(apiFieldTypes, function (index, fieldType) {
                var selectedFieldType = $(fieldType).val();
                var selectedFieldSlug = $(fieldType).data('field-slug');

                var selectedData = {};

                selectedData['api_' + selectedFieldSlug] = selectedFieldType;

                data.api_field_types.push(selectedData);
            });
        }

        $.post(
            ajaxurl,
            data,
            function (response) {
                var decoded;

                try {
                    decoded = $.parseJSON(response);
                } catch(err) {
                    decoded = false;
                }

                if (decoded) {
                    if (decoded.success) {
                        alert(decoded.message);

                        window.location.reload();
                    } else {
                        alert(decoded.message);

                        setTimeout(function() {
                            $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                        }, 300);
                    }
                } else {
                    alert(wp2leads_i18n_get('Something went wrong'));
                }

            }
        );
    });

    $(document.body).on('click', '.settings-change', function() {
        var control = $(this);
        var settingsToChange = $('#' + control.data('change'));

        settingsToChange.removeClass('disabled').attr('disabled', false).focus();
    });

	$(document.body).on('change', 'select.field-type', function() {
		update_gmt_checkbox_visible($(this));
    });

	// $el = select element where the user changed the value
	function update_gmt_checkbox_visible($el) {
		var fg = $el.closest('fieldgroup');
		var checkbox = fg.find('.convert-to-gmt');
		var checkbox_local = fg.find('.convert-to-local');
		var tip = fg.find('.tippy_button');
		var label = fg.find('.convert-to-label');

		if (checkbox.length) {
			// check type
			var type = $el.val();

			if (type == 'datetime' || type == 'date' || type == 'time') {
				// show checkbox
				checkbox.closest('label').show();
                checkbox_local.closest('label').show();
                label.show();
                tip.closest('span').css({'display': "inline-block"});
			} else {
				// hide checkbox
				checkbox.closest('label').hide();
                checkbox_local.closest('label').hide();
                label.hide();
                tip.closest('span').css({'display': "none"});
			}
		}
	}

    $(document.body).on('change', '.api_field_body .convert-to-local, .api_field_body .convert-to-gmt', function(){
        var current_cb = $(this);
        var fg = current_cb.parents('fieldgroup');
        var another_cb = current_cb.hasClass('convert-to-local') ? fg.find('.convert-to-gmt') : fg.find('.convert-to-local');

        another_cb.prop( "checked", false );
    });

	$(document.body).on('change', '.api_field_body .field-type, .api_field_body .convert-to-local, .api_field_body .convert-to-gmt', function(){
		updateFieldDatApiOption ($(this).closest('.api_field_body').find('.api-field'));
	});
    /**
     * ==========================
     *  Optins settings
     * ==========================
     */
    $(document.body).on('blur', '#donot-optins-conditions input[name="string"]', function() {
        setTimeout(function() {
            updateOptinFromCondition(true);
        }, 800);
    });

    $(document.body).on('change', '#donot-optins-conditions select[name="operator"]', function() {
        setTimeout(function() {
            updateOptinFromCondition(true);
        }, 800);
    });

    $(document.body).on('click', '.get-user-input-tags-filter', function() {
        $('.accordion-body.api-processing-holder .api-spinner-holder').addClass('api-processing');
        var control = $(this);
        var tagsContainer = control.parents('.recommended_user_input_tags_cloud-container');
        var filterContainer = tagsContainer.find('.recommended_user_input_tags_filter');
        var mapping = control.data('value');

        filterContainer.empty();

        var data = {
            action: 'wp2l_get_recomended_tags_filter',
            mapping: mapping
        };

        $.ajax({
            url: ajaxurl,
            method: 'post',
            data: data,
            success: function (response) {
                var result;

                try {
                    result = $.parseJSON(response);
                } catch(err) {
                    result = false;
                }

                if (result) {
                    if (result.success) {
                        var template = Handlebars.compile($('#wp2l-map-to-api-recomended-tags-filter')[0].innerHTML);
                        filterContainer.append(template({
                            filter_tags: result.tags.join('||')
                        }));
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                    } else {
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                    }
                } else {
                    // TODO: Add actions for error
                    $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr);
                console.log(status);
                console.log(error);
            },
            complete: function (xhr, status) {}
        });
    });

    $(document.body).on('click', '.get-user-input-tags-results', function() {
        $('.accordion-body.api-processing-holder .api-spinner-holder').addClass('api-processing');
        var control = $(this);
        var tagsContainer = control.parents('.recommended_user_input_tags_cloud-container');

        loadRecomendedUserInputTagsCloud(tagsContainer);
    });

    $(document.body).on('click', '#selectRecommendedTags', function() {
        var tagCloudHolder = $('#recommendedTagsCloud');
        var visibleTags = tagCloudHolder.find('fieldset:visible input[type="checkbox"]');

        $.each(visibleTags, function (index, tag) {
            $(tag).prop('checked', 'checked');
        });
    });

    $(document.body).on('click', '#deselectRecommendedTags', function() {
        var tagCloudHolder = $('#recommendedTagsCloud');
        var visibleTags = tagCloudHolder.find('fieldset:visible input[type="checkbox"]');

        $.each(visibleTags, function (index, tag) {
            $(tag).prop('checked', false);
        });
    });

    $(document.body).on('click', '.select-user-input-tags', function() {
        var control = $(this);
        var tagCloudHolder = control.parents('.recommended_user_input_tags_cloud-container');
        var visibleTags = tagCloudHolder.find('fieldset:visible input[type="checkbox"]');

        $.each(visibleTags, function (index, tag) {
            $(tag).attr('checked', 'checked');
        });
    });

    $(document.body).on('click', '.deselect-user-input-tags', function() {
        var control = $(this);
        var tagCloudHolder = control.parents('.recommended_user_input_tags_cloud-container');
        var visibleTags = tagCloudHolder.find('fieldset:visible input[type="checkbox"]');

        $.each(visibleTags, function (index, tag) {
            $(tag).attr('checked', false);
        });
    });

    $(document.body).on('click', '#createRecommendedTags', function() {
        $('.accordion-body.api-processing-holder .api-spinner-holder').addClass('api-processing');
        var tagCloudHolder = $('#recommendedTagsCloud');
        var selectedTags = tagCloudHolder.find('input:checked');
        var tagsToCreate = [];

        if (selectedTags.length > 0) {
            $.each(selectedTags, function (index, item) {
                tagsToCreate.push($(item).val());
            });
        }

        var data = {
            action: 'wp2l_add_recommended_klick_tip_tags',
            tagsToCreate: tagsToCreate
        };

        $.post(
            ajaxurl,
            data,
            function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    alert(decoded.message);

                    updateExistedTagFieldsetList(function() {
                        tagCloudHolder.empty();
                        loadRecomendedTagsCloud();

                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
						$('body').trigger('createRecommendedTags');
                    });

                } else {
                    alert(decoded.message);
                    $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                }
            }
        );
    });

    $(document.body).on('click', '.reload-kt-tags', function() {
        $('.accordion-body.api-processing-holder .api-spinner-holder').addClass('api-processing');

        updateExistedTagFieldsetList(function() {
            getUsersTagsFromKlickTipp(null, function() {
                loadPossibleTagsCloud(null, function() {
                    $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                });
            });
        });
    });

    $(document.body).on('click', '.create-all-user-input-tags', function() {
        $('.accordion-body.api-processing-holder .api-spinner-holder').addClass('api-processing');
        var control = $(this);
        var tagsContainer = control.parents('.recommended_user_input_tags_cloud-container');
        var tagsCloud = tagsContainer.find('.recommended_user_input_tags_cloud');
        var tagsToCreate = control.data('tags-all');
        var mapId = control.data('map-id');
        var tagsSetId = control.data('set-id');
        var getTagsBtn = tagsContainer.find('.get-user-input-tags-results');
        var createTagsBtn = tagsContainer.find('.create-user-input-tags');
        var createAllTagsBtn = tagsContainer.find('.create-all-user-input-tags');
        var selectTagsBtn = tagsContainer.find('.select-user-input-tags');
        var deselectTagsBtn = tagsContainer.find('.deselect-user-input-tags');
        var terminateTagsBtn = tagsContainer.find('.terminate-all-user-input-tags');
        var reloadTagsBtn = tagsContainer.find('.reload-kt-tags');
        var tagsMessage = tagsContainer.find('.recommended_user_input_tags_message');

        var data = {
            action: 'wp2l_add_all_recommended_klick_tip_tags',
            mapId: mapId,
            tagsSetId: tagsSetId,
            tagsToCreate: tagsToCreate
        };

        $.post(
            ajaxurl,
            data,
            function (response) {
                var decoded;

                try {
                    decoded = $.parseJSON(response);
                } catch(err) {
                    decoded = false;
                }

                if (decoded) {
                    if (decoded.success) {
                        tagsCloud.empty();
                        getTagsBtn.hide();
                        createTagsBtn.hide();
                        createAllTagsBtn.hide();
                        selectTagsBtn.hide();
                        deselectTagsBtn.hide();
                        tagsMessage.hide();

                        reloadTagsBtn.show();
                        terminateTagsBtn.show();

                        alert(decoded.message);
                        var template = Handlebars.compile($('#wp2l-map-to-api-recomended-tags-bg')[0].innerHTML);
                        tagsCloud.append(template);

                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                    } else {
                        alert(decoded.message);
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                    }
                } else {
                    alert(wp2leads_i18n_get('Something went wrong.'));
                    $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                }
            }
        );
    });

    $(document.body).on('click', '.create-user-input-tags', function() {
        $('.accordion-body.api-processing-holder .api-spinner-holder').addClass('api-processing');
        var control = $(this);
        var tagsContainer = control.parents('.recommended_user_input_tags_cloud-container');
        var tagsCloud = tagsContainer.find('.recommended_user_input_tags_cloud');
        var selectedTags = tagsCloud.find('input:checked');
        var tagsToCreate = [];

        if (selectedTags.length > 0) {
            $.each(selectedTags, function (index, item) {
                tagsToCreate.push($(item).val());
            });
        }

        var data = {
            action: 'wp2l_add_recommended_klick_tip_tags',
            tagsToCreate: tagsToCreate
        };

        $.post(
            ajaxurl,
            data,
            function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    alert(decoded.message);

                    updateExistedTagFieldsetList(function() {
                        // tagCloudHolder.empty();
                        // loadRecomendedTagsCloud();

                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                        loadRecomendedUserInputTagsCloud(tagsContainer, true);

                    });
                } else {
                    alert(decoded.message);
                    $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                }

				$('body').trigger('UserTagsCreated');
            }
        );
    });

    $(document.body).on('click', '.options-buttons-wrapper > .prev, .options-buttons-wrapper > .next', nextOptionsPage);

    $(document.body).on('click', '.available_options .available_option', function() {
        var selectAvailableOption = $(this);
        var optionAvailableText = '';
        var activeField = $(document.body).find('.tokenize.active');

        if(activeField.length === 0)
            return 0;
        if (
            activeField.parents('.conditions-list').length ||
            activeField.parents('.connected-options-wrapper').length ||
            activeField.parents('.multiple-autotag-item.autotag-single').length ||
            activeField.parents('.multiple-autotag-item.autotag-concat').length ||
            activeField.parents('.multiple-autotag-item.autotag-separator').length
        ) {
            optionAvailableText = selectAvailableOption.find('label').data('table-column');
        } else {
            optionAvailableText = selectAvailableOption.find('label').text();
        }

        var fieldInput = activeField.find('li.token-search input');
        fieldInput.val(optionAvailableText);

        fieldInput.trigger('click');
        fieldInput.trigger('keyup');
        $('.tokenize-dropdown').filter('.dropdown').find('.dropdown-item').trigger('mousedown');

        fieldInput.val('');
        fieldInput.trigger('click');
        fieldInput.trigger('keyup');

        activeField.find('.tokenize').trigger('focusin');
        activeField.find('.tokenize').trigger('focusout');
    });

    $(document.body).on('click', '.api_field_head', function () {
        // set active
        var $box = $(this).closest('.api_field_box');

        if ($box.hasClass('status_active')) {
            // already active - inactivate it
            $box.removeClass('status_active');
            $box.find('.tokenize.active').removeClass('active');
        } else {
            // inactive

            // first deactivate all the other boxes

            $('.api_fields_container').find('.status_active').removeClass('status_active');

            // activate current box

            $box.addClass('status_active');
            $box.children('.api_field_body').slideDown();
            $box.find('.tokenize').trigger('focusin');
            $box.find('.tokenize').trigger('focusout');
            $box.find('.tokenize .token-search').css('width', '17px');
        }

    });

    $(document.body).on('focusin', '.tokenize', function() {
        var tokens = $(this).find('.tokens-container li.token span');

        $('.tokenize.active').removeClass('active');
        $(this).addClass('active');

        $.each(tokens, function(i, tokenHTML) {
            var token = $(tokenHTML);

            if(token.hasAttr('data-option')) {
                token.text(token.attr('data-option'));
            }
        });
    });

    $(document.body).on('click', '#create-tag', function () {
        var tag_text = $(this).parent().find('input[type="text"]').val();

        if (tag_text) {
            var data = {
                action: 'add_new_klick_tip_tag',
                new_tag: tag_text
            };

            $.post(ajaxurl, data, function (response) {
                displayNotice(response);

                if (1 === response.status) {
                    var tags = [];
                    tags.push({
                        tag_id: response.tag_id,
                        tag_name: tag_text
                    });

                    updateTagsLists('add', tags);
                }
            }, 'json');
        }
    });

    $(document.body).on('click', '#remove-tag', function () {
        var selected_tags = $('.remove-tags-cloud input:checked');

        if (selected_tags.length < 1) {
            alert(wp2leads_i18n_get('Please, select one or more tags from the list.'));
        } else {
            var sure = confirm(wp2leads_i18n_get('Be aware! You delete Tags in Klick-Tipp! Yes, i want to delete selected ') + selected_tags.length + wp2leads_i18n_get(' Tags in Klick-Tipp.'));

            if (sure) {
                var tags_ids = [];

                $.each(selected_tags, function (index, tag) {
                    tags_ids.push($(tag).val());
                });

                var data = {
                    action: 'remove_klick_tip_tag',
                    nonce: wp2leads_ajax_object.nonce,
                    tags_ids: tags_ids
                };

                $.post(ajaxurl, data, function (response) {
                    displayNotice(response);

                    if (1 === response.status) {
                        updateTagsLists('remove', response.deleted_tags);
                    }
                }, 'json');
            }
        }
    });

    $(document.body).on('change keyup paste mouseup', '.create-tag-wrapper .tag-text', function () {
        var value = $.trim($(this).val());
        var tags = $('.tags-cloud input[type="checkbox"]');
        var create_btn = $('#create-tag');

        $.each(tags, function (index, tag) {
            var tag_value = $(tag).val();

            if (value === tag_value) {
                create_btn.prop('disabled', true);
                return false;
            } else {
                create_btn.prop('disabled', false);
            }
        });

        filterAvailableTags(tags, value);
    });

    $(document.body).on('change keyup paste mouseup', '.remove-tags-wrapper .tag-text', function () {
        var value = $.trim($(this).val());
        var tags = $('.remove-tags-cloud input[type="checkbox"]');
        var remove_btn = $('#remove-tag');

        $.each(tags, function (index, tag) {
            var tag_value = $(tag).val();

            if (value === tag_value) {
                remove_btn.prop('disabled', true);
                return false;
            } else {
                remove_btn.prop('disabled', false);
            }
        });

        filterAvailableTags(tags, value);
    });

    $(document.body).on('change keyup paste mouseup', '.detach-tags-wrapper .tag-text', function () {
        var value = $.trim($(this).val());
        var tags = $('.detach-tags-wrapper-selection input[type="checkbox"]');
        var detach_btn = $('#detach-tag');

        $.each(tags, function (index, tag) {
            var tag_value = $(tag).val();

            if (value === tag_value) {
                detach_btn.prop('disabled', true);
                return false;
            } else {
                detach_btn.prop('disabled', false);
            }
        });

        filterAvailableTags(tags, value);
    });

    $(document.body).on('click', '#select-all-for-remove', function () {
        var tags_wrapper = $('.remove-tags-cloud');
        var visible_tags = tags_wrapper.find('fieldset:visible input[type="checkbox"]');

        $.each(visible_tags, function (index, tag) {
            $(tag).attr('checked', 'checked');
        });
    });

    $(document.body).on('click', '#deselect-all-for-remove', function () {
        var tags_wrapper = $('.remove-tags-cloud');
        var visible_tags = tags_wrapper.find('fieldset:visible input[type="checkbox"]');

        $.each(visible_tags, function (index, tag) {
            $(tag).attr('checked', false);
        });
    });

    $(document.body).on('click', '#select-all-for-detach',function () {
        var tags_wrapper = $('.detach-tags-wrapper-selection');
        var visible_tags = tags_wrapper.find('fieldset:visible input[type="checkbox"]');

        $.each(visible_tags, function (index, tag) {
            $(tag).attr('checked', 'checked');
        });

        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('click', '#deselect-all-for-detach', function () {
        var tags_wrapper = $('.detach-tags-wrapper-selection');
        var visible_tags = tags_wrapper.find('fieldset:visible input[type="checkbox"]');

        $.each(visible_tags, function (index, tag) {
            $(tag).attr('checked', false);
        });

        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('change', '.detach-tags-wrapper-selection fieldset input[type="checkbox"]', function() {
        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('change', '.tags-cloud fieldset input[type="checkbox"]', function() {
        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('click', '.tags-cloud > fieldset', function () {
        var input = $(this).find('input');
        var tags_wrapper = $('.selected-tags-wrapper');
        var savedMapInput = $('input.mapping');

        var data = {
            id: input.attr('id'),
            text: input.val()
        };

        var alreadySelected = tags_wrapper.find('.selected-tag[data-tag-id="' + data.id + '"]');

        if ('checked' === input.attr('checked')) {
            if (alreadySelected.length < 1) {
                tags_wrapper.append(createTag(data));
            }
        } else {
            tags_wrapper.find('div[data-tag-id="' + input.attr('id') + '"]').remove();
        }

        if (!window.manuallySelectedTagsOnLoad) {
            var api_object = compileMapToAPIObject();
            var api = JSON.stringify(api_object);

            savedMapInput.data('new_value', api);
        }
    });

    $(document.body).on('click', '#show_empty_fields', function() {
        var data = {
            action: 'wp2l_delete_transient',
            transient_name: 'wp2lead_map_to_api_hide_empty_fields'
        };

        $.post(ajaxurl, data, function(response) {
            var availableOptionsList = $(document.body).find('.available_options .available_option_list');
            availableOptionsList.toggleClass('hide-empty-options');
            $(document.body).find('#hide_empty_fields').show();
            $(document.body).find('#show_empty_fields').hide();
        });
    });

    $(document.body).on('click', '#hide_empty_fields', function() {
        var data = {
            action: 'wp2l_set_transient',
            transient_name: 'wp2lead_map_to_api_hide_empty_fields',
            transient_value: 1
        };

        $.post(ajaxurl, data, function(response) {
            var availableOptionsList = $(document.body).find('.available_options .available_option_list');
            availableOptionsList.toggleClass('hide-empty-options');
            $(document.body).find('#hide_empty_fields').hide();
            $(document.body).find('#show_empty_fields').show();
        });
    });

    $(document.body).on('change', '.api_field_option', function () {
        var $box = $(this).closest('.api_field_box');

        $box.find('.field_value').html($(this).val());
    });

    $(document).on('mousedown', '.tokenize-dropdown .dropdown-item', function() {
        var activeField = $(document.body).find('.api_fields_container .api_field_box').filter('.status_active');
        activeField.find('.tokenize').trigger('focusin');
    });

    $(document.body).on('change keyup', '#inputFiledSearchKT input', function() {
        var searchText = $(this).val();
        var fieldsKT = $(document.body).find('.api_fields_container .api_field_box');

        $.each(fieldsKT, function(index, fieldKTHTML) {
            var fieldKT = $(fieldKTHTML);
            var fieldName = fieldKT.find('.api_field_head .field_label').text();

            if(fieldName.search(new RegExp(searchText, 'i')) !== -1) {
                fieldKT.show();
            } else {
                fieldKT.hide();
            }
        });
    });

    $(document.body).on('change keyup', '#inputFiledSearchOption input', function() {
        var searchText = $(this).val();
        var fieldsOptions = $(document.body).find('.available_options .available_option');

        $.each(fieldsOptions, function(index, fieldOptionHTML) {
            var fieldOption = $(fieldOptionHTML);
            var fieldName = fieldOption.find('label').text();

            if(fieldName.search(new RegExp(searchText, 'i')) !== -1) {
                fieldOption.show();
            } else {
                fieldOption.hide();
            }
        });
    });

    function get_recomended_tags_prefixes() {
        var result = {};
        var recomended_tags_prefixes_input = $('.recommended_user_input_tags_cloud-container .recomended-tags-prefix');
        var recomended_tags_prefixes = [];

        if (recomended_tags_prefixes_input.length) {
            $.each(recomended_tags_prefixes_input, function(index, prefix) {
                var input = $(this);
                recomended_tags_prefixes.push(input.val());
            });
        }

        result.recomended_tags_prefixes = recomended_tags_prefixes;
        result.recomended_tags = $('#recommendedTagsCloud').data('saved-value-standart');

        return result;
    }

    $(document.body).on('click', '#addConditionForDoNotOptin', function() {
        var list = $(this).parent().find('.conditions-list');
        var type = $(this).data('type');
        var template = Handlebars.compile($('#wp2l-api-donot-optins-condition-set')[0].innerHTML);
        var available_options = $('.available_options .available_option > label');
        var options = [];

        $.each(available_options,function (index, label) {
            var optionLabel = $(label).data('table-column');

            if ($(label).data('value')) {
                optionLabel += ': (' + $(label).data('value') + ')';
            } else {
                optionLabel += ': (' + wp2leads_i18n_get('No value') + ')';
            }
            options[options.length] = {
                value: $(label).data('table-column'),
                label: optionLabel,
            };
        });

        list.append(template({
            availableOptions: options
        }));

        list.find('.options_where').last().tokenize2({
            tokensMaxItems: 1,
            dropdownMaxItems: 999,
            searchFromStart: false
        });
    });

    $(document.body).on('click', '#addConditionForAutotags', function() {
        var list = $(this).parent().find('.conditions-list');
        var type = $(this).data('type');
        var template = Handlebars.compile($('#wp2l-api-autotags-' + type + '-condition-set')[0].innerHTML);
        var available_options = $('.available_options .available_option > label');
        var options = [];

        $.each(available_options,function (index, label) {
            options[options.length] = $(label).data('table-column');
            if (type === 'detach' || type === 'add') {
                var optionLabel = $(label).data('table-column');

                if ($(label).data('value')) {
                    optionLabel += ': (' + $(label).data('value') + ')';
                } else {
                    optionLabel += ': (' + wp2leads_i18n_get('No value') + ')';
                }
                options[options.length] = {
                    value: $(label).data('table-column'),
                    label: optionLabel,
                };
            } else {
                options[options.length] = $(label).data('table-column');
            }
        });

        list.append(template({
            availableOptions: options
        }));

        list.find('.options_where').last().tokenize2({
            tokensMaxItems: 1,
            dropdownMaxItems: 999,
            searchFromStart: false
        });
    });

    $(document.body).on('click', '#removeAutotagsCondition', function() {
        $(this).parents('.condition').remove();

        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('click', '#removeConditionForDoNotOptin', function() {
        $(this).parents('.condition').remove();

        setTimeout(function() {
            updateOptinFromCondition(true);
        }, 800);
    });

    $(document.body).on('click', '.add-multiple-autotag-item', function(e, onLoad) {
        var list = $(this).parent().find('.multiple-autotag-list');
        var type = $(this).data('type');
        var valueType = $(this).data('value-type');
        var template = Handlebars.compile($('#wp2l-api-multiple-autotags-' + type + '-set')[0].innerHTML);
        var available_options = $('.available_options .available_option > label');
        var options = [];

        $.each(available_options,function (index, label) {
            var optionLabel = $(label).data('table-column');

            if ($(label).data('value')) {
                optionLabel += ': (' + $(label).data('value') + ')';
            } else {
                optionLabel += ': (' + wp2leads_i18n_get('No value') + ')';
            }
            options[options.length] = {
                value: $(label).data('table-column'),
                label: optionLabel,
            };

            // options[options.length] = $(label).data('table-column');
        });

        list.append(template({
            availableOptions: options,
            valueType: valueType
        }));

        list.find('.multiple-autotags-add-conditions .options_where').last().tokenize2({
            tokensMaxItems: 1,
            dropdownMaxItems: 999,
            searchFromStart: false
        });

        if ('autotag-single' === valueType && !onLoad) {
            list.find('.multiple-autotags-options-list').last().tokenize2({
                searchFromStart: false,
                dropdownMaxItems: 999,
            });
        }

        if ('autotag-concat' === valueType && !onLoad) {
            list.find('.multiple-autotags-options-concat-list').last().tokenize2({
                searchFromStart: false,
                dropdownMaxItems: 999,
            });
        }

        if ('autotag-separator' === valueType && !onLoad) {
            list.find('.multiple-autotags-options-separator-list').last().tokenize2({
                searchFromStart: false,
                dropdownMaxItems: 999,
            });
        }
    });

    $(document.body).on('click', '#klicktippSubmit', function() {
        $('.accordion-body.api-processing-holder .api-spinner-holder').addClass('api-processing');
        var ktUsername = $('#klicktippUsername').val();
        var ktPassword = $('#klicktippPassword').val();

        var data = {
            action: 'wp2l_settings_klick_tip_credentials',
            username: ktUsername,
            password: ktPassword
        };

        $.post(
            ajaxurl,
            data,
            function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    alert(decoded.message);

                    window.location.reload();
                } else {
                    alert(decoded.message);

                    setTimeout(function() {
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                    }, 300);
                }
            }
        );
    });

    $(document.body).on('click', '.remove-multiple-autotag-item', function() {
        $(this).parents('.multiple-autotag-item').remove();

        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('click', '.add-condition-for-multiple-autotags', function() {
        var list = $(this).parent().find('.conditions-list');
        var type = $(this).data('type');
        var template = Handlebars.compile($('#wp2l-api-multiple-autotags-' + type + '-condition-set')[0].innerHTML);
        var available_options = $('.available_options .available_option > label');
        var options = [];

        $.each(available_options,function (index, label) {

            if (type === 'add') {
                var optionLabel = $(label).data('table-column');

                if ($(label).data('value')) {
                    optionLabel += ': (' + $(label).data('value') + ')';
                } else {
                    optionLabel += ': (' + wp2leads_i18n_get('No value') + ')';
                }
                options[options.length] = {
                    value: $(label).data('table-column'),
                    label: optionLabel,
                };
            } else {
                options[options.length] = $(label).data('table-column');
            }
        });

        list.append(template({
            availableOptions: options
        }));

        list.find('.options_where').last().tokenize2({
            tokensMaxItems: 1,
            dropdownMaxItems: 999,
            searchFromStart: false
        });
    });

    $(document.body).on('click', '.remove-multiple-autotags-condition', function() {
        $(this).parents('.condition').remove();

        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('click', '.add_new_condition', function (e) {
        e.preventDefault();
        var list = $(this).parent().find('.conditions-list');
        var type = $(this).data('type');
        var template = Handlebars.compile($('#wp2l-api-' + type + '-condition-set')[0].innerHTML);
        var available_options = $('.available_options .available_option > label');
        var connect_to = null;

        if ('tags' === type) {
            connect_to = $('.' + type + '-cloud input[type="checkbox"]');
        } else {
            connect_to = $('.' + type + '-list option');
        }

        var options = [];
        var connectTo = [];

        $.each(available_options,function (index, label) {
            if (type === 'optins' || type === 'tags') {
                var optionLabel = $(label).data('table-column');

                if ($(label).data('value')) {
                    optionLabel += ': (' + $(label).data('value') + ')';
                } else {
                    optionLabel += ': (' + wp2leads_i18n_get('No value') + ')';
                }
                options[options.length] = {
                    value: $(label).data('table-column'),
                    label: optionLabel,
                };
            } else {
                options[options.length] = $(label).data('table-column');
            }
        });

        $.each(connect_to,function (index, item) {
            var option = $(item);

            if ('tags' === type) {
                connectTo[connectTo.length] = {
                    code: option.attr('id'),
                    value: option.val()
                };
            } else {
                connectTo[connectTo.length] = {
                    code: option.val(),
                    value: option.text()
                };
            }
        });

        list.append(template({
            availableOptions: options,
            connectTo: connectTo
        }));

        list.find('.options_where').last().tokenize2({
            tokensMaxItems: 1,
            dropdownMaxItems: 999,
            searchFromStart: false
        });
    });

    $(document.body).on('click', '.remove-api-separator', function () {
        $(this).parent().remove();

        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('click', '.add_new_separator', function () {
        var list = $(this).parent().find('.separators-list');
        var template = Handlebars.compile($('#wp2l-api-tags-separator-set')[0].innerHTML);
        var available_options = $('.available_options .available_option > label');
        var options = [];

        $.each(available_options,function (index, label) {
            let optionLabel = $(label).data('table-column');

            if ($(label).data('value')) {
                optionLabel += ': (' + $(label).data('value') + ')';
            } else {
                optionLabel += ': (' + wp2leads_i18n_get('No value') + ')';
            }
            options[options.length] = {
                value: $(label).data('table-column'),
                label: optionLabel,
            };
        });

        list.append(template({
            availableOptions: options
        }));

        list.find('.options_where').last().tokenize2(
            {
                dropdownMaxItems: 999,
                searchFromStart: false
            }
        );
    });

    $(document.body).on('click', '.add_new_detach_condition', function () {
        var list = $(this).parent().find('.conditions-list');
        var type = $(this).data('type');
        var template = Handlebars.compile($('#wp2l-api-tags-condition-detach')[0].innerHTML);
        var available_options = $('.available_options .available_option > label');
        var connect_to = $('.tags-cloud input[type="checkbox"]');
        var options = [];
        var connectTo = [];

        $.each(available_options,function (index, label) {
            var optionLabel = $(label).data('table-column');
            if ($(label).data('value')) {
                optionLabel += ': (' + $(label).data('value') + ')';
            } else {
                optionLabel += ': (' + wp2leads_i18n_get('No value') + ')';
            }
            options[options.length] = {
                value: $(label).data('table-column'),
                label: optionLabel,
            };
        });

        $.each(connect_to,function (index, item) {
            var option = $(item);

            connectTo[connectTo.length] = {
                code: option.attr('id'),
                value: option.val()
            };
        });

        list.append(template({
            availableOptions: options,
            connectTo: connectTo
        }));

        list.find('.options_where').last().tokenize2({
            tokensMaxItems: 1,
            dropdownMaxItems: 999,
            searchFromStart: false
        });
    });

    $(document.body).on('change', '.tags-detach-conditions-wrapper select[name="tags-detach"], .tags-detach-conditions-wrapper select[name="operator"], .tags-detach-conditions-wrapper select[name="option"]', function () {
        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('change', '.tags-conditions-wrapper select[name="tags-add"], .tags-conditions-wrapper select[name="operator"], .tags-conditions-wrapper select[name="option"], .separator-wrapper input[name="separator-filter-type"], input[name="multiple-autotags-add-separators-filter-type"], input[name="multiple-autotags-concat-filter-type"]', function () {
        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('input', '.tags-detach-conditions-wrapper input[name="string"], .tags-conditions-wrapper input[name="string"], .separator-wrapper input[name="separator"], .separator-wrapper input[name="separator-prefix"], .separator-wrapper input[name="separator-filter"]', function() {
        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('input', 'input[name="multiple-autotags-single-prefix"], input[name="multiple-autotags-concat-prefix"], input[name="multiple-autotags-concat-filter"], input[name="multiple-autotags-add-separators-prefix"], input[name="multiple-autotags-add-separators-filter"]', function() {
        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('blur', '#autotags-detach-conditions input[name="string"], #autotags-add-conditions input[name="string"], .multiple-autotags-add-conditions input[name="string"]', function() {
        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('change', '#autotags-detach-conditions select[name="operator"], #autotags-add-conditions select[name="operator"], .multiple-autotags-add-conditions select[name="operator"]', function() {
        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('input', '.recommended_user_input_tags_cloud-container .recomended-tags-prefix', function() {
        var input = $(this);
        var container = input.parents('.recommended_user_input_tags_cloud-container');
        var tagsCloud = container.find('.recommended_user_input_tags_cloud');
        var template = Handlebars.compile($('#wp2l-map-to-api-recomended-tags-get')[0].innerHTML);
        tagsCloud.empty().append(template);
    });

    $(document.body).on('blur', '#globalTagPrefix, #mapTagPrefix', function() {

        var holder = $(this);
        var userInputTagsCloud = $('.recommended_user_input_tags_cloud');

        if (userInputTagsCloud.length > 0) {
            userInputTagsCloud.each(function() {
                var tagsCloud = $(this);
                var template = Handlebars.compile($('#wp2l-map-to-api-recomended-tags-get')[0].innerHTML);
                tagsCloud.empty().append(template);
            });
        }

        loadRecomendedTagsCloud();

        updatePrefixesSection();

        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('mousedown', '.tags-detach-conditions-wrapper .tokenize-dropdown .dropdown-item', function() {
        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    $(document.body).on('click', '.remove-api-connection', function() {
        $(this).parents('.condition').remove();

        setTimeout(function() {
            updatePossibleTagsOnChange();
        }, 100);
    });

    function disableSaveButtons() {
        $('#btnSaveMapToApi').attr('disabled', true);
        $('#btnSaveMapToApiSticky').attr('disabled', true);
        $('#btnUpdateMapToApi').attr('disabled', true);
        $('#btnUpdateMapToApiSticky').attr('disabled', true);
        $('#exitMap').attr('disabled', true);
        $('#exitMapSticky').attr('disabled', true);
    }

    function enableSaveButtons() {
        setTimeout(function() {
            $('#btnSaveMapToApi').removeClass('btn-loading').attr('disabled', false);
            $('#btnSaveMapToApiSticky').removeClass('btn-loading').attr('disabled', false);
            $('#btnUpdateMapToApi').removeClass('btn-loading').attr('disabled', false);
            $('#btnUpdateMapToApiSticky').removeClass('btn-loading').attr('disabled', false);
            $('#exitMap').removeClass('btn-loading').attr('disabled', false);
            $('#exitMapSticky').removeClass('btn-loading').attr('disabled', false);
        }, 500);
    }

    $(document.body).on('click', '#btnSaveMapToApi, #btnSaveMapToApiSticky', function () {
        $(this).attr('disabled', true).addClass('btn-loading');
        disableSaveButtons();
        let btn = $(this);
        // let btnHtml = btn.html();
        // btn.html('<div class="loading-spinner"></div>');
        var currentMap = JSON.stringify(compileMapToAPIObject()),
            mapId = $_GET('active_mapping'),
            global_tag_prefix = $('#globalTagPrefix').val();

        setTimeout(function() {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                // async: false,
                data: {
                    action: 'wp2l_save_map_before_transfer',
                    mapId: mapId,
                    global_tag_prefix: global_tag_prefix,
                    recomended_tags_prefixes: get_recomended_tags_prefixes(),
                    map: currentMap
                },
                success: function(response) {
                    var decoded = $.parseJSON(response);


                    setTimeout(() => {
                        if (decoded.success) {
                            alert(decoded.message);
                        } else {
                            alert(decoded.message);
                        }
                    }, 1000);

                },
                error: function(xhr, status, error) {

                },
                complete: function(xhr, status) {
                    setTimeout(() => {
                        enableSaveButtons();
                        btn.trigger('focusout');
                    }, 1500);
                }
            })
        }, 100);
    });

    $(document.body).on('click','#btnUpdateMapToApi, #btnUpdateMapToApiSticky',function(e) {
        $(this).attr('disabled', true);
        let btn = $(this);
        disableSaveButtons();
        window.spinner.show();
        e.preventDefault();
        var global_tag_prefix = $('#globalTagPrefix').val();

        var data = {
            action: 'wp2l_save_new_map',
            nonce: wp2leads_ajax_object.nonce,
            map_id: $_GET('active_mapping'),
            global_tag_prefix: global_tag_prefix,
            recomended_tags_prefixes: get_recomended_tags_prefixes(),
            api: JSON.stringify(compileMapToAPIObject())
        };

        // shoot off the ajax request
        $.post(ajaxurl, data, function (response) {
            var decoded = $.parseJSON(response);
            enableSaveButtons();

            if (decoded.success) {
                if (decoded.mapping) {
                    $('.mapping').val(decoded.mapping);
                }

                alert(wp2leads_i18n_get('Map successfully saved!'));
                window.location.href = '?page=wp2l-admin&tab=map_to_api';
            } else {
                alert(wp2leads_i18n_get('Something went wrong.'));
            }

            window.spinner.hide();

        });
    });

    $(document.body).on('click', '#transferAllBg', function () {
        var modal = $('.transfer-data-modal'),
            noticeHolder = modal.find('.notice_holder');

        noticeHolder.empty();

        var resultInProgress = Handlebars.compile($('#wp2l-map-to-api-bg-in-progress')[0].innerHTML);
        noticeHolder.append(resultInProgress({}));

        $('.transfer-data-modal .api-processing-holder .api-spinner-holder').addClass('api-processing');


        var mapId = $_GET('active_mapping');

        var data = {
            action: 'wp2l_check_limit',
            mapId: mapId
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
                        if (decoded.limit) {
                            var totalToTransferHolder = $('.transfer-data-modal .available-data .total');
                            var noticeHolder = $('.transfer-data-modal .notice_holder');
                            var ktLimitNoticeHolder = $('#kt_limit_notice_holder');
                            var btnTransfer = $('#btnTransferData'),
                                transfer_current_btn = $('#transferCurrent'),
                                transfer_all_bg_btn = $('#transferAllBg');

                            noticeHolder.empty();
                            ktLimitNoticeHolder.empty();
                            totalToTransferHolder.text('0');

                            var noDataForTransferTemplate = Handlebars.compile($('#wp2l-no-limit-for-transfer')[0].innerHTML);
                            noticeHolder.append(noDataForTransferTemplate({}));
                            ktLimitNoticeHolder.append(noDataForTransferTemplate({}));
                            btnTransfer.removeClass('button-primary').prop('disabled', true);
                            transfer_current_btn.removeClass('button-primary').prop('disabled', true);
                            transfer_all_bg_btn.removeClass('button-primary').prop('disabled', true);

                            $('.transfer-data-modal .api-processing-holder .api-spinner-holder').removeClass('api-processing');
                            $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                        } else {
                            setTimeout(function() {
                                transferDataToKlickTipPromise()
                                    .then(function(response) {
                                        alert(wp2leads_i18n_get('Users started transfered in background'));
                                        var modal = $('.transfer-data-modal'),
                                            noticeHolder = modal.find('.notice_holder');
                                        noticeHolder.empty();

                                        var bgStatisticHolder = $('#map-to-api-bg-running-inner');

                                        if (bgStatisticHolder.length > 0) {
                                            refreshAllBgMapToApi();
                                        } else {
                                            var data = {
                                                action: 'wp2l_get_map_to_api_statistics'
                                            };

                                            $.post(
                                                ajaxurl,
                                                data,
                                                function (response) {
                                                    var decoded = $.parseJSON(response);

                                                    if (decoded.success) {
                                                        $( "#wpbody-content .wrap > h1" ).after(decoded.html);
                                                    } else {
                                                        alert(decoded.message);
                                                    }
                                                }
                                            );

                                        }

                                        $('.transfer-data-modal .api-processing-holder .api-spinner-holder').removeClass('api-processing');
                                    })
                            }, 1000)
                        }
                    } else {
                        alert(decoded.message);
                        $('.transfer-data-modal .api-processing-holder .api-spinner-holder').removeClass('api-processing');
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                    }
                } else {
                    alert(wp2leads_i18n_get('Something went wrong.'));
                    $('.transfer-data-modal .api-processing-holder .api-spinner-holder').removeClass('api-processing');
                    $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                }

            },
            error: function(xhr, status, error) {
                alert(wp2leads_i18n_get('Something went wrong.'));
                $('.transfer-data-modal .api-processing-holder .api-spinner-holder').removeClass('api-processing');
                $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
            },
            complete: function(xhr, status) {

            }
        });
    });

    $(document.body).on('click', '.gray-back, .transfer-data-modal .close', function() {
        $('.transfer-data-modal').remove();
        $('.gray-back').remove();
    });

    $(document.body).on('click', '#btnTransferDataCalculate', function () {
        $('.accordion-body.api-processing-holder .api-spinner-holder').addClass('api-processing');
        $('.available_options .api-processing-holder .api-spinner-holder').addClass('api-processing');

        setTimeout(function() {
            getMapRowsCountPromise()
                .then(function(response) {
                    var decoded = $.parseJSON(response);

                    return getMapResultsPromise(decoded.message);
                })
                .then(function(mapResults) {
                    setTimeout(function () {
                        var apiTransferModalTemplate = Handlebars.compile($('#wp2l-api-transfer-modal')[0].innerHTML),
                            activeMap = $(this).data('active-map');

                        $('body').append(apiTransferModalTemplate({active_map: activeMap}));
                        $(".transfer-data-modal").center();
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                        $('.available_options .api-processing-holder .api-spinner-holder').removeClass('api-processing');

                        var modal = $('.transfer-data-modal'),
                            noticeHolder = modal.find('.notice_holder'),
                            resultInProgress = Handlebars.compile($('#wp2l-map-to-api-prepare-message-in-progress')[0].innerHTML);

                        noticeHolder.append(resultInProgress({}));

                        setTimeout(function () {
                            prepareDataForKlickTipPromise()
                                .then(function(results) {
                                    return getTransferModalDataInfoPromise();
                                })
                                .then(function (transferModalInfo) {
                                    var decodedTransferModalInfo = $.parseJSON(transferModalInfo),
                                        transfer_all_bg_btn = $('#transferAllBg');

                                    if (decodedTransferModalInfo.success) {
                                        var preparedForTransferCount = window.preparedForTransferCount;
                                        var preparedForTransferKtLimit = window.preparedForTransferKtLimit;

                                        if (false === preparedForTransferKtLimit) {
                                            modal.find('.available-data .total').text(preparedForTransferCount);
                                        } else {
                                            if (preparedForTransferCount < preparedForTransferKtLimit) {
                                                modal.find('.available-data .total').text(preparedForTransferCount);
                                            } else {
                                                modal.find('.available-data .total').text(preparedForTransferKtLimit);
                                            }
                                        }

                                        setTimeout(function () {
                                            noticeHolder.empty();

                                            if (0 === window.preparedForTransferCount) {
                                                var noDataForTransferTemplate = Handlebars.compile($('#wp2l-no-users-for-transfer')[0].innerHTML);
                                                noticeHolder.append(noDataForTransferTemplate({}));
                                            } else if (0 === preparedForTransferKtLimit || 0 > preparedForTransferKtLimit) {
                                                var ktLimitNoticeHolder = $('#kt_limit_notice_holder');
                                                ktLimitNoticeHolder.empty();
                                                var btnTransfer = $('#btnTransferData');
                                                var noDataForTransferTemplate = Handlebars.compile($('#wp2l-no-limit-for-transfer')[0].innerHTML);
                                                noticeHolder.append(noDataForTransferTemplate({}));
                                                ktLimitNoticeHolder.append(noDataForTransferTemplate({}));
                                                btnTransfer.removeClass('button-primary').prop('disabled', true);
                                            } else {
                                                transfer_all_bg_btn.addClass('button-primary').prop('disabled', false);
                                            }

                                            $('.transfer-data-modal .api-processing-holder .api-spinner-holder').removeClass('api-processing');
                                        }, 100);
                                    }
                                });
                        }, 100);
                    }, 100);
                });
        }, 100);
    });

    $(document.body).on('click', '#btnTransferDataCurrent', function () {
        var currentMap = JSON.stringify(compileMapToAPIObject()),
            mapId = $_GET('active_mapping'),
            global_tag_prefix = $('#globalTagPrefix').val();
        $('.accordion-body.api-processing-holder .api-spinner-holder').addClass('api-processing');
        $('.available_options .api-processing-holder .api-spinner-holder').addClass('api-processing');

        if (window.btnTransferDataCurrentInitial) {
            window.btnTransferDataCurrentInitial = false;
            currentMap = $('input.mapping').val();
        }

        setTimeout(function() {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                async: false,
                data: {
                    action: 'wp2l_save_map_before_transfer',
                    mapId: mapId,
                    global_tag_prefix: global_tag_prefix,
                    recomended_tags_prefixes: get_recomended_tags_prefixes(),
                    map: currentMap
                },
                success: function(response) {
                    var decoded = $.parseJSON(response);

                    if (decoded.success) {
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                        $('.available_options .api-processing-holder .api-spinner-holder').removeClass('api-processing');
                        var apiTransferModalTemplate = Handlebars.compile($('#wp2l-api-transfer-modal-current')[0].innerHTML),
                            email = $('.api-fields-wrapper #api_email .token > span').data('value'),
                            tags = $('#selected-tags-holder .selected-tags-cloud-wrapper').html();

                        $('body').append(apiTransferModalTemplate({
                            active_map: mapId,
                            current_email: email,
                            tags: tags
                        }));

                        $(".transfer-data-modal").center();

                        setTimeout(function() {
                            $('.transfer-data-modal .api-processing-holder .api-spinner-holder').removeClass('api-processing');

                        }, 300);
                    } else {
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                        $('.available_options .api-processing-holder .api-spinner-holder').removeClass('api-processing');
                        alert(decoded.message);
                    }
                },
                error: function(xhr, status, error) {

                },
                complete: function(xhr, status) {

                }
            })
        }, 100);
    });

    $(document.body).on('click', '#btnTransferDataImmediately', function () {
        //transferDataImmediately
		$(this).removeClass('active-wz');
		if ( $('#transferDataRangeButtons').length ) {
			$('#transferDataRangeButtons').show();
			$([document.documentElement, document.body]).stop().animate({
				scrollTop: $('#transferDataRangeButtons').closest('.accordion-subbody').prev().offset().top - 70
			}, 500);
		} else {
			transferDataImmediately();
		}
    });

	$(document.body).on('click', '#btnTransferDataCurrentWithoutRange', function () {
        //transferDataImmediately
		$(this).closest('.accordion-subbody').find('input').each(function(){
			$(this).val('');
		});
		transferDataImmediately();
    });

	$(document.body).on('click', '#btnTransferDataCurrentWithRange', function () {
		transferDataImmediately();
    });

	function transferDataImmediately() {
		var currentMap = JSON.stringify(compileMapToAPIObject()),
            mapId = $_GET('active_mapping'),
            global_tag_prefix = $('#globalTagPrefix').val();

        $('.accordion-body.api-processing-holder .api-spinner-holder').addClass('api-processing');
        $('.available_options .api-processing-holder .api-spinner-holder').addClass('api-processing');

        setTimeout(function() {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                async: false,
                data: {
                    action: 'wp2l_save_map_before_transfer',
                    mapId: mapId,
                    global_tag_prefix: global_tag_prefix,
                    recomended_tags_prefixes: get_recomended_tags_prefixes(),
                    map: currentMap
                },
                success: function(response) {
                    var decoded;

                    try {
                        decoded = $.parseJSON(response);
                    } catch(err) {
                        decoded = false;
                    }

                    if (decoded) {
                        if (decoded.success) {
                            transferDataImmediatelyPromise().then(function (response) {
                                setTimeout(function() {
                                    var bgStatisticHolder = $('#map-to-api-bg-running-inner');

                                    if (bgStatisticHolder.length > 0) {
                                        $('#wp2lead-map-to-api-bg-notice').remove();
                                    }

                                    var data = {
                                        action: 'wp2l_get_map_to_api_statistics'
                                    };

                                    $.post(
                                        ajaxurl,
                                        data,
                                        function (response) {
                                            var decoded = $.parseJSON(response);

                                            if (decoded.success) {
                                                $( "#wpbody-content .wrap > h1" ).after(decoded.html);
												$([document.documentElement, document.body]).stop().animate({
													scrollTop: $( "#wpbody-content .wrap > h1" ).offset().top - 70
												}, 500);
                                                var data = {
                                                    mapId: mapId,
                                                    action: 'wp2l_is_map_transfer_in_bg'
                                                };

                                                $.post(
                                                    ajaxurl,
                                                    data,
                                                    function (response) {
                                                        var decoded = $.parseJSON(response);

                                                        if (decoded.success && decoded.message) {
                                                            var calculateBtn = $('#btnTransferDataCalculate');
                                                            var immediatelyBtn = $('#btnTransferDataImmediately');
                                                            var mapTransferInBgMessageTemplate = Handlebars.compile($('#wp2l-api-map-transfer-in-bg')[0].innerHTML);

                                                            calculateBtn.removeClass('button-primary').addClass('disabled').prop('disabled', true);
                                                            immediatelyBtn.removeClass('button-primary').addClass('disabled').prop('disabled', true);
                                                            immediatelyBtn.after(mapTransferInBgMessageTemplate({}));
                                                        }

                                                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                                                        $('.available_options .api-processing-holder .api-spinner-holder').removeClass('api-processing');
														$('body').trigger('transferDataImmediately_finish');
                                                    }
                                                );
                                            } else {
                                                alert(decoded.message);

                                                $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                                                $('.available_options .api-processing-holder .api-spinner-holder').removeClass('api-processing');
												$('body').trigger('transferDataImmediately_finish');
                                            }
                                        }
                                    );
                                }, 100);
                            });
                        } else {
                            $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                            $('.available_options .api-processing-holder .api-spinner-holder').removeClass('api-processing');
                            alert(decoded.message);
							$('body').trigger('transferDataImmediately_finish');
                        }
                    } else {
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                        $('.available_options .api-processing-holder .api-spinner-holder').removeClass('api-processing');
						$('body').trigger('transferDataImmediately_finish');
                    }
                },
                error: function(xhr, status, error) {
					$('body').trigger('transferDataImmediately_finish');
				},
                complete: function(xhr, status) {}
            });
        }, 1000);
	}

    function transferDataImmediatelyPromise() {
        var mapId = $_GET('active_mapping');

        return $.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'wp2l_transfer_data_immediately',
                mapId: mapId
            },
            success: function(response) {
                var decoded;

                try {
                    decoded = $.parseJSON(response);
                } catch(err) {
                    decoded = false;
                }

                if (decoded) {
                    if (decoded.success) {
                        alert(decoded.message);
                    } else {
                        alert(decoded.message);
                    }
                } else {

                }
            },
            error: function(xhr, status, error) {},
            complete: function(xhr, status) {}
        });
    }

    $(document.body).on('click', '#btnTransferData', function () {
        var apiTransferModalTemplate = Handlebars.compile($('#wp2l-api-transfer-modal')[0].innerHTML),
            activeMap = $(this).data('active-map'),
            cronChecked = $(this).data('cron-checked'),
            cronSelected = $(this).data('cron-selected');

        $('body').append(apiTransferModalTemplate({
            active_map: activeMap
        }));

        $(".transfer-data-modal").center();

        var date_base_for_cron_selection = $('.date-base-for-cron'),
            date_base_for_cron_selected = date_base_for_cron_selection.data('selected');

        $.each(cronSelected, function( index, value ) {
            date_base_for_cron_selection.find('option[value="' + value + '"]').prop('selected', true);
        });

        var modal = $('.transfer-data-modal'),
            noticeHolder = modal.find('.notice_holder');

        noticeHolder.empty();

        var resultInProgress = Handlebars.compile($('#wp2l-map-to-api-prepare-message-in-progress')[0].innerHTML);
        noticeHolder.append(resultInProgress({}));

        setTimeout(function() {
            prepareDataForKlickTipPromise()
                .then(function(results) {
                    return getTransferModalDataInfoPromise();
                })
                .then(function(transferModalInfo) {
                    var decodedTransferModalInfo = $.parseJSON(transferModalInfo),
                        transfer_current_btn = $('#transferCurrent'),
                        transfer_all_bg_btn = $('#transferAllBg');

                    if (decodedTransferModalInfo.success) {
                        var preparedForTransferCount = window.preparedForTransferCount;
                        var preparedForTransferKtLimit = window.preparedForTransferKtLimit;

                        if (false === preparedForTransferKtLimit) {
                            modal.find('.available-data .total').text(preparedForTransferCount);
                        } else {
                            if (preparedForTransferCount < preparedForTransferKtLimit) {
                                modal.find('.available-data .total').text(preparedForTransferCount);
                            } else {
                                modal.find('.available-data .total').text(preparedForTransferKtLimit);
                            }
                        }



                        // Statistics
                        modal.find('.last-transferred .total').text(decodedTransferModalInfo.last_transfered);
                        modal.find('.all-transferred .total').text(decodedTransferModalInfo.totally_all);
                        modal.find('.unique-transferred .total').text(decodedTransferModalInfo.totally_unique);
                        modal.find('.cron-local .total').text(decodedTransferModalInfo.last_transfered_cron);
                        modal.find('.cron-unix .total').text(decodedTransferModalInfo.last_transfered_cron_unix);

                        setTimeout(function () {
                            noticeHolder.empty();

                            if (0 === window.preparedForTransferCount) {
                                var noDataForTransferTemplate = Handlebars.compile($('#wp2l-no-users-for-transfer')[0].innerHTML);
                                noticeHolder.append(noDataForTransferTemplate({}));
                            } else if (0 === preparedForTransferKtLimit || 0 > preparedForTransferKtLimit) {
                                var ktLimitNoticeHolder = $('#kt_limit_notice_holder');
                                ktLimitNoticeHolder.empty();
                                var btnTransfer = $('#btnTransferData');
                                var noDataForTransferTemplate = Handlebars.compile($('#wp2l-no-limit-for-transfer')[0].innerHTML);
                                noticeHolder.append(noDataForTransferTemplate({}));
                                ktLimitNoticeHolder.append(noDataForTransferTemplate({}));
                                btnTransfer.removeClass('button-primary').prop('disabled', true);
                            } else {
                                var currentOptinStatus = $('.active-optin .active-optin-wrapper').data('optin');

                                if ('allowed' === currentOptinStatus) {
                                    transfer_current_btn.addClass('button-primary').prop('disabled', false);
                                }

                                transfer_all_bg_btn.addClass('button-primary').prop('disabled', false);
                            }

                            $('.transfer-data-modal .api-processing-holder .api-spinner-holder').removeClass('api-processing');
                        }, 1000);
                    }
                });

        }, 300);
    });

    $(document.body).on('click', '#transferCurrent', function () {
        $('.transfer-data-modal .api-processing-holder .api-spinner-holder').addClass('api-processing');

        setTimeout(function() {
            transferCurrentToKlickTipp();
        }, 1000);
    });

	$(document.body).on('click', '#transferCurrentClose', function () {
       $(this).closest('.transfer-data-modal').find('.close').click();
    });

    $(document.body).on('click', '#saveModuleSettings', function () {
        var mapId, moduleKey, moduleStatus, cron_status;

        var btn = $(this);

        mapId = btn.data('map-id');
        moduleKey = btn.data('module-key');
        moduleStatus = $('#moduleStatus').prop('checked');

        var data = {
            action: 'wp2l_save_module_settings',
            mapId: mapId,
            moduleKey: moduleKey,
            moduleStatus: moduleStatus
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
                        if (decoded.cron) {
                            var cron_status_text_holder = $('.cron-status-text');
                            var cron_status_icon_holder = $('.cron-status-icon');
                            var title_cron_status_icon_holder = $('h3.title .dashicons-clock');
                            var cron_status_list_icon_holder = $('#map-' + mapId + '-row .dashicons-clock');

                            cron_status_text_holder.text(decoded.cron.status_text);
                            cron_status_icon_holder.removeClass('disabled').removeClass('active');
                            cron_status_list_icon_holder.removeClass('disabled').removeClass('active');
                            title_cron_status_icon_holder.removeClass('disabled').removeClass('active');

                            $('#cronStatus').prop('checked', false);

                            if ('' !== decoded.cron.status) {
                                cron_status_icon_holder.addClass(decoded.cron.status);
                                cron_status_list_icon_holder.addClass(decoded.cron.status);
                                title_cron_status_icon_holder.addClass(decoded.cron.status);
                            }
                        }

                        alert(decoded.message);
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

    $(document.body).on('click', '#saveCronSettings', function () {
        var cron_options = $('#cron-columns-options');

        var map_id = $_GET('active_mapping'),
            cron_status = $('#cronStatus').prop('checked'),
            date_columns_selected = cron_options.find('.cron-option:checked');

        var cron_status_text_holder = $('.cron-status-text');
        var cron_status_icon_holder = $('.cron-status-icon');
        var title_cron_status_icon_holder = $('h3.title .dashicons-clock');
        var cron_status_list_icon_holder = $('#map-' + map_id + '-row .dashicons-clock');

        var date_base_for_cron = [];

        if (date_columns_selected.length > 0) {
            $.each(date_columns_selected, function (index, option) {
                date_base_for_cron.push($(option).val());
            });
        }

        var data = {
            action: 'wp2l_save_cron_settings',
            map_id: map_id,
            cron_status: cron_status,
            date_base_for_cron: date_base_for_cron
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
                        cron_status_text_holder.text(decoded.status_text);
                        cron_status_icon_holder.removeClass('disabled').removeClass('active');
                        cron_status_list_icon_holder.removeClass('disabled').removeClass('active');
                        title_cron_status_icon_holder.removeClass('disabled').removeClass('active');

                        if ('' !== decoded.status) {
                            cron_status_icon_holder.addClass(decoded.status);
                            cron_status_list_icon_holder.addClass(decoded.status);
                            title_cron_status_icon_holder.addClass(decoded.status);
                        }

                        if (decoded.module) {
                            $('#moduleStatus').prop('checked', false);
                        }

                        alert(decoded.message);
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

    $(document.body).on('click', '#saveCronSeettings', function () {
        var map_id = $(this).data('active-map'),
            cron_status = $('#cron-status').attr('checked'),
            date_base = $('.date-base-for-cron').val(),
            data = {
                action: 'wp2l_save_cron_settings',
                map_id: map_id,
                cron_status: cron_status,
                date_base_for_cron: date_base
            };

        if ( 'checked' === cron_status && null === date_base ) {
            alert(wp2leads_i18n_get('Please, choose one or more trigger from the list.'));

            return;
        }

        $.post(
            ajaxurl,
            data,
            function (response) {
                var decoded = $.parseJSON(response),
                    btnTransferData = $('#btnTransferData'),
                    titleCronStatus = $('h3.title .dashicons-clock'),
                    listCronStatus = $('.available-maps__table #map-' + map_id + '-row .dashicons-clock');

                if (decoded.success) {
                    btnTransferData.data('cron-checked', decoded.cronChecked);
                    btnTransferData.data('cron-selected', $.parseJSON(decoded.cronSelected));
                    titleCronStatus.removeClass('disabled').removeClass('active');
                    listCronStatus.removeClass('disabled').removeClass('active')

                    if ( '' === decoded.cronChecked ) {
                        titleCronStatus.addClass('disabled');
                        listCronStatus.addClass('disabled');
                    } else {
                        titleCronStatus.addClass('active');
                        listCronStatus.addClass('active');
                    }

                    alert(decoded.message);
                } else {
                    alert(wp2leads_i18n_get('Something went wrong.'));
                }
            }
        );
    });

    $(document.body).on('blur', '#optins-conditions input[name="string"]', function() {
        updateOptinFromCondition(true);
    });

    $(document.body).on('blur', '#optins-conditions input[name="string"]', function() {
        updateOptinFromCondition(true);
    });

    $(document.body).on(
        'change',
        '.api-optins-wrapper .optins-list, #optins-conditions select[name="operator"], #optins-conditions select[name="optins"]',
        function () {
        updateOptinFromCondition(true);
    });

    window.apiFieldsOnLoad = true;
    window.manuallySelectedTagsOnLoad = true;
    var iterationLimit = ktAPIObject.iteration_limit;

    function setContainerPagePadding() {
        const stickyTagsHolder = $('#stickyTagsHolder');
        const mapToApiPage = $('#mapToApiPage');

        if (stickyTagsHolder.hasClass('tagsHolderActive')) {
            var height = stickyTagsHolder.height();
            mapToApiPage.css('padding-bottom', `${height}px`);
        } else {
            mapToApiPage.css('padding-bottom', '0px');
        }
    }

    function stickyElements() {
        if (stickyTimeout) {
            clearTimeout(stickyTimeout);
        }

        stickyTimeout = setTimeout(function () {
            var mapToApiHeader = $('#map-to-api__header');
            if (!mapToApiHeader || !mapToApiHeader[0]) {
                return;
            }
            var mapToApiControl = $('#mapToIpiControlSticky');

            if (isElementOutOfViewport(mapToApiHeader[0])) {
                if (!mapToApiControl.hasClass('stickyBottom')) {
                    mapToApiControl.addClass('stickyBottom');
                }
            } else {
                if (mapToApiControl.hasClass('stickyBottom')) {
                    mapToApiControl.removeClass('stickyBottom');
                }
            }
        }, 500);
    }

    function isElementOutOfViewport(el) {
        if (!el) return;
        var rect = el.getBoundingClientRect();

        return (
            rect.top >= window.innerHeight || rect.bottom <= 0 ||
            rect.left >= window.innerWidth || rect.right <= 0
        );
    }

    $(document.body).on('click', '#tagsHolderControl button, #closeStickyTagsHolder', function () {
        const stickyTagsHolder = $('#stickyTagsHolder');
        stickyTagsHolder.toggleClass('tagsHolderActive');

        setContainerPagePadding();
    });

    function setTagsLoadingState() {
        const tagsCounter = $('#tagsCounter');
        const tagsHolderLoader = $('#tagsHolderLoader');

        if (!tagsCounter.hasClass('tagsLoading')) {
            tagsCounter.addClass('tagsLoading')
        }
        if (!tagsHolderLoader.hasClass('tagsLoading')) {
            tagsHolderLoader.addClass('tagsLoading')
        }
    }

    function generateStickyTagsHolderContent() {
        const tagsWrapper = $('.selected-tags-cloud-wrapper');
        const tagsCounter = $('#tagsCounter');
        const counterHolder = $('#counterHolder');
        const tags = tagsWrapper.find('.selected-tag');
        const stickyTagsHolder = $('#stickyTagsHolderInner');
        const tagsHolderLoader = $('#tagsHolderLoader');
        let tagsCount = 0;

        if (tags) {
            tagsCount = tags.length;
        }

        const selectedTags = $('#selected-tags-holder .selected-tags-cloud-wrapper').html();
        stickyTagsHolder.empty();
        stickyTagsHolder.append(selectedTags);

        counterHolder.text(tagsCount);

        if (tagsCounter.hasClass('tagsLoading')) {
            tagsCounter.removeClass('tagsLoading')
        }

        if (tagsHolderLoader.hasClass('tagsLoading')) {
            tagsHolderLoader.removeClass('tagsLoading')
        }

        setContainerPagePadding();
    }

    function isTagsSectionVisible() {
        const tagHolder = $('#selectedTagsCloudHolder');
        if (!tagHolder) {
            return;
        }
        if (isElementOutOfViewport(tagHolder[0])) {
            return false;
        }
        return tagHolder.closest('.accordion-body').css('visibility') === 'visible';
    }

    function stickyTagsHolder() {
        if (stickyTagsTimeout) {
            clearTimeout(stickyTagsTimeout);
        }

        stickyTagsTimeout = setTimeout(function() {
            const tagsHolderControl = $('#tagsHolderControl');
            const stickyTagsHolder = $('#stickyTagsHolder');

            if (!isTagsSectionVisible()) {
                if (!tagsHolderControl.hasClass('visibleSticky')) {
                    tagsHolderControl.addClass('visibleSticky');
                }
            } else {
                if (tagsHolderControl.hasClass('visibleSticky')) {
                    tagsHolderControl.removeClass('visibleSticky');
                }
                if (stickyTagsHolder.hasClass('tagsHolderActive')) {
                    stickyTagsHolder.removeClass('tagsHolderActive');
                }
            }
            setContainerPagePadding();
        }, 500)
    }

    $(document).ready(async function () {
        stickyElements();
        stickyTagsHolder();

        $('.wp2lead-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            onSelect: dateChanged
        });

        var selectApiFieldContainer = $('#apiFieldsInitialSettings__container');
        var selectApiFieldTo = $('.select_api_field_to');

        if (selectApiFieldTo.length > 0) {
            var placeholder = selectApiFieldContainer.data('select-placeholder');

            selectApiFieldTo.tokenize2({
                tokensMaxItems: 1,
                dropdownMaxItems: 999,
                placeholder: placeholder,
                searchFromStart: false
            });
        }

        var page = $_GET('tab');

        if ('map_to_api' !== page) {
            return false;
        }

        window.possibleTagsOnLoad = true;
        var currentMapId = $_GET('active_mapping');

        window.spinner = $('.api-spinner');
        window.mapResults = [];
        window.firstMapResultLoaded = false;

        loadRecomendedTagsCloud();

        if (!currentMapId || $('.map2api_body .available_options').length === 0) {
            window.initialLoadComplete = true;
            return false;
        }

        if (!$('.map2api_side .api_fields_container').hasClass('no-data-load')) {
            $('.api-field').tokenize2({
                searchFromStart: false,
                dropdownMaxItems: 999,
            });

            var data = [];
            var types = ['optins', 'tags-add', 'tags-detach'];
            var autotag_types = ['add', 'detach'];

            // console.log('00');
            // const mapRowsCount = await getMapRowsCountPromise();
            // console.log('01');
            // var decodedMapRowsCount = $.parseJSON(mapRowsCount);
            // console.log({decodedMapRowsCount});
            // console.log('02');
            // const mapResultsInitial = await getMapResultsInitial(decodedMapRowsCount.message);
            // console.log({mapResultsInitial});
            // var decodedMapResultsInitial = $.parseJSON(mapResultsInitial);
            // console.log('03');
            // console.log({decodedMapResultsInitial});

            getMapRowsCountPromise()
                .then(function(response) {
                    console.log('04');
                    var decoded = $.parseJSON(response);

                    return getMapResultsInitial(decoded.message);
                })
                .then(function(response) {
                    console.log('05');
                    var decoded = $.parseJSON(response);

                    if (decoded.success) {
                        window.firstMapResultLoaded = true;

                        var options_wrapper = $('.available_options');
                        options_wrapper.html(decoded.availableOptions);
                        return true;
                    }
                })
                .then(function() {
                    console.log('06');
                    var new_options = $('.available_options .available_option > label');
                    var field_options = [];

                    $.each(new_options, function (index, label) {
                        field_options[field_options.length] = $(label).text();
                    });

                    updateApiFields(field_options, true);
                    updateOptinFromCondition();
                    loadConditions(types);
                    loadAutotagsConditions(autotag_types);
                    loadSeparators();
                    loadConnectedOptions();
                    loadMultipleAutotags();
                    getUsersTagsFromKlickTipp(null);
                    selectTagsFromConnectedOptions();
                    selectTags();
                    updateManuallySelectedTags();
                    loadPossibleTagsCloud(null);
                    checkPossibleIssues();

                    if (window.wp2leadsStep9) {
                        window.wp2leadsStep9();
                    }

                    if (window.innerWidth >= 961) {
                        $(".wp2leads-sticky").sticky({topSpacing:35});
                    }

                    setTimeout(() => {
                        $('#map-to-api-message-in-progress').remove();
                        $('#map-to-api__right-column.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                    }, 10);

                    window.initialLoadComplete = true;
                });
        }
    });

    function getMapResultsInitial(count) {
        var limit = iterationLimit;
        var offset = 0;
        var iterations = Math.ceil(count / limit);
        var mapId = $_GET('active_mapping');
        var results = [];
        var dataLoaded = false;
        var counter = 0;

        window.mapResultsIterations = iterations;

        while (!dataLoaded) {
            offset = limit * counter;

            $.ajax({
                type: 'post',
                url: ajaxurl,
                async: false,
                data: {
                    action: 'wp2l_get_map_query_results_by_map_id',
                    nocache: 1,
                    mapId: mapId,
                    limit: limit,
                    offset: offset
                },
                success: function(response) {
                    var decoded = {};

                    try {
                        decoded = $.parseJSON(response);
                    } catch(err) {
                        decoded = false;
                    }

                    if (decoded) {
                        if (decoded.success) {
                            $.merge(results, decoded.result);
                            $.merge(window.mapResults, decoded.result);
                        }
                    }
                },
                error: function(xhr, status, error) {

                },
                complete: function(xhr, status) {

                }
            });

            counter++;

            var dataLength = results.length;

            if (dataLength > 20 || counter === iterations) {
                dataLoaded = true;

                var data = {
                    action: 'wp2l_get_available_options_data',
                    mapId: mapId,
                    count: count,
                    mapResult: JSON.stringify(window.mapResults[0])
                };

                return $.ajax({
                    type: 'post',
                    contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                    url: ajaxurl,
                    async: false,
                    data: data,
                    success: function(response) {},
                    error: function(xhr, status, error) {},
                    complete: function(xhr, status) {}
                });
            }
        }
    }

    function dateChanged(date, inst) {
        var dateTimestamp = Date.parse(date);
    }

    function isOutdated() {
        var start = $('#startDateData').val();
        var end = $('#endDateData').val();

        if ('' === start && '' === end) {
            return false;
        }

        var startTimestamp = '';
        var endTimestamp = '';

        if ('' !== start) {
            startTimestamp = Date.parse(start);
        }

        if ('' !== end) {
            endTimestamp = Date.parse(start);
        }
    }

    function checkPossibleIssues() {
        var tags_conditions = $('.tags-conditions-wrapper .conditions-list .condition');
        var tags_detach_conditions = $('#tags-detach-conditions .conditions-list .condition');

        var no_tags_condition_option = false;
        var no_tags_condition_operator = false;
        var no_tags_condition_connectTo = false;

        $.each(tags_conditions, function (index, item) {
            var condition = $(item);

            var data = {
                option: condition.find('select[name="option"] option:selected').val(),
                operator: condition.find('select[name="operator"]').val(),
                connectTo: condition.find('select[name="tags-add"]').val()
            };

            if (!data.option) {
                no_tags_condition_option = true;
            }

            if (!data.operator) {
                no_tags_condition_operator = true;
            }

            if (!data.connectTo) {
                no_tags_condition_connectTo = true;
            }
        });
    }

    window.onbeforeunload = function() {
        if (preventLoosingData()) {
            return;
        }

        return true;
    };

    if (window.addEventListener) {
        window.addEventListener('resize', function () {
            // resizer();
            stickyElements();
            stickyTagsHolder();
        }, false);
        window.addEventListener('scroll', function () {
            stickyElements();
            stickyTagsHolder();
        }, false);
        window.addEventListener('update', function () {
            stickyElements();
            stickyTagsHolder();
        }, false);
    } else if (window.attachEvent) {
        window.attachEvent('onresize', function () {
            // resizer();
            stickyElements();
            stickyTagsHolder();
        })
    }

    function updatePrefixesSection() {
        var tagsPrefix = '';
        var mapGlobalTagsPrefixInput = $('#globalTagPrefix');
        var mapTagsPrefixInput = $('#mapTagPrefix');

        var mapGlobalTagsPrefix = $.trim(mapGlobalTagsPrefixInput.val());
        var mapTagsPrefix = $.trim(mapTagsPrefixInput.val());

        var mapGlobalTagsPrefixOld = $.trim(mapGlobalTagsPrefixInput.data('selected-value'));
        var mapTagsPrefixOld = $.trim(mapTagsPrefixInput.data('selected-value'));

        var warningNoPrefix = $('.tagPrefixWarning__noprefix');
        var warningGlobalChanged = $('.tagPrefixWarning__globalchange');
        var mapTagPrefixHolder = $('#prefixInfo');

        var warningNoPrefixTemplate = Handlebars.compile($('#wp2l-tag-prefix-warning-noprefix')[0].innerHTML);
        var warningGlobalChangedTemplate = Handlebars.compile($('#wp2l-tag-prefix-warning-globalchange')[0].innerHTML);

        if (mapGlobalTagsPrefix) {
            tagsPrefix = mapGlobalTagsPrefix + ' ';
        }

        if (mapTagsPrefix) {
            tagsPrefix = mapTagsPrefix + ' ';
        }

        // Check if Tags is empty
        if ('' === tagsPrefix) {
            if (warningNoPrefix.length === 0) {
                mapTagPrefixHolder.after(warningNoPrefixTemplate({}));
            }
        } else {
            if (warningNoPrefix.length > 0) {
                warningNoPrefix.remove();
            }
        }

        // Check if Global Changed
        if (
            ('' !== mapGlobalTagsPrefixOld && mapGlobalTagsPrefix !== mapGlobalTagsPrefixOld) ||
            ('' !== mapTagsPrefixOld && mapTagsPrefix !== mapTagsPrefixOld)
        ) {
            if (warningGlobalChanged.length === 0) {
                mapTagPrefixHolder.after(warningGlobalChangedTemplate({}));
            }
        } else {
            if (warningGlobalChanged.length > 0) {
                warningGlobalChanged.remove();
            }
        }
    }

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

    function saveNewPrefixesValues() {
        var tagsPrefix = '';
        var mapGlobalTagsPrefixInput = $('#globalTagPrefix');
        var mapTagsPrefixInput = $('#mapTagPrefix');

        var mapGlobalTagsPrefix = $.trim(mapGlobalTagsPrefixInput.val());
        var mapTagsPrefix = $.trim(mapTagsPrefixInput.val());

        var mapGlobalTagsPrefixOld = $.trim(mapGlobalTagsPrefixInput.data('selected-value'));
        var mapTagsPrefixOld = $.trim(mapTagsPrefixInput.data('selected-value'));

        var warningNoPrefix = $('.tagPrefixWarning__noprefix');
        var warningGlobalChanged = $('.tagPrefixWarning__globalchange');
        var mapTagPrefixHolder = $('#mapTagPrefix__holder');

        mapGlobalTagsPrefixInput.data('selected-value', mapGlobalTagsPrefix);
        mapTagsPrefixInput.data('selected-value', mapTagsPrefix);

        if (warningGlobalChanged.length > 0) {
            warningGlobalChanged.remove();
        }
    }

    function resizer() {
        if (resizerTimeout) {
            clearTimeout(resizerTimeout);
        }

        resizerTimeout = setTimeout(function () {
            var stickyContent = $('.map2api_body .sticky-wrapper');

            if (window.innerWidth >= 961) {
                if (stickyContent.length === 0) {
                    $(".wp2leads-sticky").sticky({topSpacing:35});
                }
            } else {
                if (stickyContent.length > 0) {
                    $(".wp2leads-sticky").unstick();
                }
            }
        }, 1000);
    }

    function maybeUpdateTagsCloud(activeField) {
        var activeFieldBody = activeField.parents('.api_field_body');
        var activeFieldSelect = activeFieldBody.find('select.api-field');
        var activeFieldName = activeFieldSelect.attr('name');
        var currentMap = compileMapToAPIObject();

        if('api_email' === activeFieldName) {
            $('.accordion-body.api-processing-holder .api-spinner-holder').addClass('api-processing');

            var tableColumns = currentMap.fields.api_email.table_columns;

            var email = '';
            var emailArray = [];

            if (tableColumns.length > 0) {

                $.each(tableColumns, function(index, column) {
                    var emailOption = $('.available_option label[data-table-column="' + column + '"]');

                    if (emailOption.length > 0) {
                        $.each(emailOption, function (index, opt) {
                            email = $(opt).data('value');
                            emailArray.push($(opt).data('value'));
                        });
                    }
                });
            }

            setTimeout(function() {
                getUsersTagsFromKlickTipp(emailArray, function() {
                    loadPossibleTagsCloud(emailArray, function() {
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                    });
                });
            }, 800);

        }
    }

    function updatePossibleTagsOnChange() {
        setTagsLoadingState();

        if (tagsCloudTimeout) {
            clearTimeout(tagsCloudTimeout);
        }

        tagsCloudTimeout = setTimeout(function () {
            var currentMap = compileMapToAPIObject();
            var tagsCloud = $('.tags-cloud', document.body);
            var existedTags = $('#tags-cloud-options').data('tags-cloud');
            var currentUserKTTag = window.currentUserKTTag;
            var manuallySelectedTags = currentMap.manually_selected_tags.tag_ids;
            var tagsToAdd = currentMap.manually_selected_tags.tag_ids;
            var manuallyDetachTags = currentMap.detach_tags.tag_ids;
            var connectedTags = currentMap.connected_for_tags.tags;
            var connectedTagsSeparator = currentMap.connected_for_tags.separators;
            var connectedTagsConcat = currentMap.connected_for_tags.tags_concat;
            var connectedTagsSeparators = currentMap.connected_for_tags.separators;
            var conditionsTags = currentMap.conditions.tags;
            var conditionsDetachTags = currentMap.conditions.detach_tags;
            var conditionsAutoTags = currentMap.conditions.autotags;
            var conditionsDetachAutoTags = currentMap.conditions.detach_autotags;
            var multipleAutoTagsList = currentMap.multiple_autotags.autotag_items;
            var newTagsToAdd = [];
            var tagsPrefix = getMapTagPrefix();

            var autoTags = [];
            var multipleAutoTags = [];
            var autotagsToAdd = [];
            var newTagsToAddArray = [];
            var autoTagsAllowed = false;
            var autoTagsDetach = false;

            var selectedTagsCloudWrapper = $('.selected-tags-cloud-wrapper', document.body);

            selectedTagsCloudWrapper.empty();

            if (conditionsTags) {
                $.each(conditionsTags, function(conditionIndex, condition) {
                    if (condition.option && condition.operator && condition.connectTo && condition.string) {
                        var option = $('.available_option label[data-table-column="' + condition.option + '"]');

                        if (option.length > 0) {
                            $.each(option, function (index, opt) {

                                var tag_text = $(opt).data('value');

                                if (prepareCondition(tag_text, condition)) {
                                    manuallySelectedTags.push(condition.connectTo);
                                }
                            });
                        }
                    }
                });
            }

            if (conditionsDetachTags) {
                $.each(conditionsDetachTags, function(conditionIndex, condition) {
                    if (condition.option && condition.operator && condition.connectTo && condition.string) {
                        var option = $('.available_option label[data-table-column="' + condition.option + '"]');

                        if (option.length > 0) {
                            $.each(option, function (index, opt) {

                                var tag_text = $(opt).data('value');

                                if (prepareCondition(tag_text, condition)) {
                                    manuallyDetachTags.push(condition.connectTo);
                                }
                            });
                        }
                    }
                });
            }

            // Autotags
            if (connectedTags) {
                $.each(connectedTags, function(tagIndex, connected_column) {
                    var option = $('.available_option label[data-table-column="' + connected_column + '"]');

                    if (option.length > 0) {
                        $.each(option, function (index, opt) {
                            if ('' !== $.trim($(opt).data('value'))) {
                                var tag_text = tagsPrefix + $.trim($(opt).data('value'));
                                var tag_type = typeof tag_text;

                                if ('number' === tag_type) {
                                    var tagToCheck = tag_text.toString();
                                } else {
                                    var tagToCheck = tag_text;
                                }

                                autoTags.push(tagToCheck);
                            }
                        });
                    }
                });
            }

            if (connectedTagsConcat) {
                $.each(connectedTagsConcat, function(index, table_column) {
                    var option = $('.available_option label[data-table-column="' + table_column + '"]');

                    if (option.length > 0) {
                        $.each(option, function (index, opt) {
                            var tag_text = $(opt).data('value');
                            var tag_text_type = typeof tag_text;

                            if ('number' === tag_text_type) {
                                tag_text = tag_text.toString();
                            }

                            var tags_names = tag_text.split(',');

                            $.each(tags_names, function (tagIndex, item) {
                                if ('' !== $.trim(item)) {
                                    var tag_name = tagsPrefix + $.trim(item);

                                    var tag_type = typeof tag_name;

                                    if ('number' === tag_type) {
                                        var tagToCheck = tag_name.toString();
                                    } else {
                                        var tagToCheck = tag_name;
                                    }

                                    autoTags.push(tagToCheck);
                                }
                            });

                        });
                    }
                });
            }

            if (connectedTagsSeparator) {
                $.each(connectedTagsSeparator, function(index, separator) {
                    if (separator.separator && separator.option.length > 0) {
                        var separator_prefix = separator.prefix ? separator.prefix + ' ' : '';
                        var separator_filter = separator.filter ? separator.filter.split('||') : [];
                        var separator_filter_type = separator.filter_action ? 1 : null;

                        if (separator_filter.length) {
                            separator_filter = separator_filter.map(function(filter) {
                                return $.trim(filter);
                            });
                        }

                        $.each(separator.option, function(index, table_column) {
                            var option = $('.available_option label[data-table-column="' + table_column + '"]');

                            if (option.length > 0) {
                                $.each(option, function (index, opt) {
                                    var tag_text = $(opt).data('value');
                                    var tag_text_type = typeof tag_text;

                                    if ('number' === tag_text_type) {
                                        tag_text = tag_text.toString();
                                    }

                                    var tags_names = tag_text.split(separator.separator);

                                    $.each(tags_names, function (tagIndex, item) {
                                        var item_trim = 'number' === typeof $.trim(item) ? $.trim(item).toString() : $.trim(item);

                                        if (separator_filter_type) {
                                            if (separator_filter.length && !separator_filter.includes(item_trim)) {
                                                item_trim = '';
                                            }
                                        } else {
                                            if (separator_filter.length && separator_filter.includes(item_trim)) {
                                                item_trim = '';
                                            }
                                        }

                                        if ('' !== item_trim) {
                                            var tag_text = tagsPrefix + separator_prefix + item_trim;

                                            autoTags.push(tag_text);
                                        }
                                    });
                                });
                            }
                        });
                    }
                });
            }

            if (conditionsAutoTags.length > 0) {
                $.each(conditionsAutoTags, function(index, condition) {
                    var option = $('.available_option label[data-table-column="' + condition.option + '"]');

                    if (option.length > 0) {
                        $.each(option, function (index, opt) {
                            var tag_text = $(opt).data('value');

                            if (prepareCondition(tag_text, condition)) {
                                autoTagsAllowed = true;
                            }
                        });
                    }
                });
            } else {
                autoTagsAllowed = true;
            }

            if (conditionsDetachAutoTags.length > 0) {
                $.each(conditionsDetachAutoTags, function(index, condition) {
                    var option = $('.available_option label[data-table-column="' + condition.option + '"]');

                    if (option.length > 0) {
                        $.each(option, function (index, opt) {
                            var tag_text = $(opt).data('value');

                            if (prepareCondition(tag_text, condition)) {
                                autoTagsAllowed = false;
                                autoTagsDetach = true;
                            }
                        });
                    }
                });
            }

            autoTags = $.unique( autoTags );

            if (autoTagsAllowed && autoTags.length > 0) {
                $.each(autoTags, function (i, tag) {

                    var existedTagData = getExistedTagByName(tag);

                    if (existedTagData) {
                        manuallySelectedTags.push(existedTagData.id);
                    } else {
                        autotagsToAdd.push(tag);
                    }
                });
            }

            if (autoTagsDetach && autoTags.length > 0) {
                $.each(autoTags, function (i, tag) {
                    var existedTagData = getExistedTagByName(tag);

                    if (existedTagData) {
                        manuallyDetachTags.push(existedTagData.id);
                    }
                });
            }

            if (multipleAutoTagsList.length > 0) {
                $.each(multipleAutoTagsList, function(index, multipleAutoTag) {
                    var multipleAutoTagsAllowed = false;

                    if (multipleAutoTag.conditions.length > 0) {
                        $.each(multipleAutoTag.conditions, function(index, condition) {
                            var option = $('.available_option label[data-table-column="' + condition.option + '"]');

                            if (option.length > 0) {
                                $.each(option, function (index, opt) {
                                    var tag_text = $(opt).data('value');

                                    if (prepareCondition(tag_text, condition)) {
                                        multipleAutoTagsAllowed = true;
                                    }
                                });
                            }
                        });
                    } else {
                        multipleAutoTagsAllowed = true;
                    }

                    if (multipleAutoTagsAllowed) {
                        if (!jQuery.isEmptyObject(multipleAutoTag.single_tags)) {
                            var single_tags_prefix = multipleAutoTag.single_tags_prefix ? multipleAutoTag.single_tags_prefix + ' ' : '';

                            $.each(multipleAutoTag.single_tags, function(tagIndex, connected_column) {
                                var option = $('.available_option label[data-table-column="' + connected_column + '"]');

                                if (option.length > 0) {
                                    $.each(option, function (index, opt) {
                                        var item_trim = 'number' === typeof $.trim($(opt).data('value')) ? $.trim($(opt).data('value')).toString() : $.trim($(opt).data('value'));

                                        if ('' !== item_trim) {
                                            var tag_text = tagsPrefix + single_tags_prefix + item_trim;
                                            multipleAutoTags.push(tag_text);
                                        }
                                    });
                                }
                            });
                        } else if (!jQuery.isEmptyObject(multipleAutoTag.concat_tags)) {
                            var concat_tags_prefix = multipleAutoTag.concat_tags_prefix ? multipleAutoTag.concat_tags_prefix + ' ' : '';
                            var concat_tags_filter = multipleAutoTag.concat_tags_filter ? multipleAutoTag.concat_tags_filter.split('||') : [];
                            var concat_tags_filter_type = multipleAutoTag.concat_tags_filter_type ? 1 : null;
                            if (concat_tags_filter.length) {
                                concat_tags_filter = concat_tags_filter.map(function(filter) {
                                    return $.trim(filter);
                                });
                            }

                            $.each(multipleAutoTag.concat_tags, function(index, table_column) {
                                var option = $('.available_option label[data-table-column="' + table_column + '"]');

                                if (option.length > 0) {
                                    $.each(option, function (index, opt) {
                                        var tag_text = $(opt).data('value');
                                        var tag_text_type = typeof tag_text;

                                        if ('number' === tag_text_type) {
                                            tag_text = tag_text.toString();
                                        }

                                        var tags_names = tag_text.split(',');

                                        $.each(tags_names, function (tagIndex, item) {
                                            var item_trim = 'number' === typeof $.trim(item) ? $.trim(item).toString() : $.trim(item);

                                            if (concat_tags_filter_type) {
                                                if (concat_tags_filter.length && !concat_tags_filter.includes(item_trim)) {
                                                    item_trim = '';
                                                }
                                            } else {
                                                if (concat_tags_filter.length && concat_tags_filter.includes(item_trim)) {
                                                    item_trim = '';
                                                }
                                            }

                                            if ('' !== item_trim) {
                                                var tag_text = tagsPrefix + concat_tags_prefix + item_trim;
                                                multipleAutoTags.push(tag_text);
                                            }
                                        });
                                    });
                                }
                            });
                        } else if (!jQuery.isEmptyObject(multipleAutoTag.separator_tags)) {
                            var separator_tags_prefix = multipleAutoTag.separator_tags_prefix ? multipleAutoTag.separator_tags_prefix + ' ' : '';
                            var separator_tags_filter = multipleAutoTag.separator_tags_filter ? multipleAutoTag.separator_tags_filter.split('||') : [];
                            var separator_tags_filter_type = multipleAutoTag.separator_tags_filter_type ? 1 : null;

                            if (separator_tags_filter.length) {
                                separator_tags_filter = separator_tags_filter.map(function(filter) {
                                    return $.trim(filter);
                                });
                            }

                            if (multipleAutoTag.separator_tags.separator && multipleAutoTag.separator_tags.option.length > 0) {
                                $.each(multipleAutoTag.separator_tags.option, function(index, table_column) {
                                    var option = $('.available_option label[data-table-column="' + table_column + '"]');

                                    if (option.length > 0) {
                                        $.each(option, function (index, opt) {
                                            var tag_text = $(opt).data('value');
                                            var tag_text_type = typeof tag_text;

                                            if ('number' === tag_text_type) {
                                                tag_text = tag_text.toString();
                                            }

                                            var tags_names = tag_text.split(multipleAutoTag.separator_tags.separator);

                                            $.each(tags_names, function (tagIndex, item) {
                                                var item_trim = 'number' === typeof $.trim(item) ? $.trim(item).toString() : $.trim(item);

                                                if (separator_tags_filter_type) {
                                                    if (separator_tags_filter.length && !separator_tags_filter.includes(item_trim)) {
                                                        item_trim = '';
                                                    }
                                                } else {
                                                    if (separator_tags_filter.length && separator_tags_filter.includes(item_trim)) {
                                                        item_trim = '';
                                                    }
                                                }

                                                if ('' !== item_trim) {
                                                    var tag_text = tagsPrefix + separator_tags_prefix + item_trim;
                                                    multipleAutoTags.push(tag_text);
                                                }
                                            });
                                        });
                                    }
                                });
                            }
                        }
                    }
                });
            }

            multipleAutoTags = $.unique( multipleAutoTags );

            if (multipleAutoTags.length > 0) {
                $.each(multipleAutoTags, function (i, tag) {
                    var existedTagData = getExistedTagByName(tag);

                    if (existedTagData) {
                        manuallySelectedTags.push(existedTagData.id);
                    } else {
                        autotagsToAdd.push(tag);
                    }
                });
            }

            autotagsToAdd = $.unique( autotagsToAdd );
            manuallySelectedTags = $.unique( manuallySelectedTags );
            manuallyDetachTags = $.unique( manuallyDetachTags );

            if (autotagsToAdd.length > 0) {
                $.each(autotagsToAdd, function (i, tag) {
                    var newTagData = {
                        id: '',
                        text: tag,
                        tagClass: 'selected-tag selected-tag-new'
                    };

                    newTagsToAddArray.push(newTagData);
                });
            }

            if (currentUserKTTag) {
                $.each(currentUserKTTag, function (index, tag_id) {
                    var isManualSelected = $.inArray( tag_id, manuallySelectedTags );
                    var isDetachSelected = $.inArray( tag_id, manuallyDetachTags );
                    var tagClass = 'selected-tag selected-tag-kt';

                    if (-1 !== isManualSelected) {
                        manuallySelectedTags.splice(isManualSelected, 1);

                        tagClass += ' selected-tag-manual';
                    }

                    if (-1 !== isDetachSelected) {
                        tagClass += ' selected-tag-detach';
                    }

                    if (tag_id) {
                        var checkbox = tagsCloud.find('input[id="' + tag_id + '"]');

                        var data = {
                            id: tag_id,
                            text: checkbox.data('name'),
                            tagClass: tagClass
                        };

                        selectedTagsCloudWrapper.append(createPossibleTag(data));
                    }
                })
            }

            if (manuallySelectedTags) {
                $.each(manuallySelectedTags, function (index, tag_id) {
                    if (tag_id) {
                        var isDetachSelected = $.inArray( tag_id, manuallyDetachTags );

                        if (-1 === isDetachSelected) {
                            var checkbox = tagsCloud.find('input[id="' + tag_id + '"]');

                            var data = {
                                id: tag_id,
                                text: checkbox.data('name'),
                                tagClass: 'selected-tag selected-tag-added'
                            };

                            selectedTagsCloudWrapper.append(createPossibleTag(data));
                        }
                    }
                })
            }

            if (newTagsToAddArray.length > 0) {
                $.each(newTagsToAddArray, function (index, tagData) {
                    selectedTagsCloudWrapper.append(createPossibleTag(tagData));
                });
            }

            generateStickyTagsHolderContent();
        }, 1500);
    }

    function loadPossibleTagsCloud(email, cb) {
        setTagsLoadingState();

        var mapId = $_GET('active_mapping');
        var currentMap = JSON.stringify(compileMapToAPIObject());

        if (null === email || undefined === email) {
            var emailField = $('.api-fields-wrapper #api_email .token > span', document.body);

            if (emailField.length === 1) {
                var email = $('.api-fields-wrapper #api_email .token > span', document.body).data('value');
            } else if (emailField.length > 1) {
                var email = [];

                emailField.each(function() {
                    email.push($(this).data('value'));
                });
            }
        }

        var offset = $('.options-buttons-wrapper .prev.button').data('page');
        var currentUserData = JSON.stringify(window.mapResults[offset]);
        var tagsCloud = JSON.stringify($('#tags-cloud-options').data('tags-cloud'));
        var tagsPrefix = '';
        var mapGlobalTagsPrefix = $.trim($('#globalTagPrefix').val());
        var mapTagsPrefix = $.trim($('#mapTagPrefix').val());

        if (mapGlobalTagsPrefix) {
            tagsPrefix = mapGlobalTagsPrefix;
        }

        if (mapTagsPrefix) {
            tagsPrefix = mapTagsPrefix;
        }

        $.ajax({
            type: 'post',
            url: ajaxurl,
            async: false,
            data: {
                action: 'wp2l_load_possible_tags_cloud',
                mapId: mapId,
                map: currentMap,
                email: email,
                tagsCloud: tagsCloud,
                tagsPrefix: tagsPrefix,
                userData: currentUserData
            },
            success: function(response) {
                var decoded = $.parseJSON(response);

                var selectedTagsCloudWrapper = $('.selected-tags-cloud-wrapper');

                if (decoded.success) {
                    selectedTagsCloudWrapper.html(decoded.possible_tags_cloud);

                    if (typeof cb === 'function') {
                        cb();
                    }
                } else {
                    selectedTagsCloudWrapper.html(decoded.message);

                    if (typeof cb === 'function') {
                        cb();
                    }
                }

                generateStickyTagsHolderContent();

                window.possibleTagsOnLoad = false;
            },
            error: function(xhr, status, error) {

            },
            complete: function(xhr, status) {

            }
        })
    }

    function getMapRowsCountPromise() {
        var mapId = $_GET('active_mapping');
        var resultInProgress = Handlebars.compile($('#wp2l-map-to-api-message-in-progress')[0].innerHTML);
        $('#map-to-api-message-in-progress__holder').append(resultInProgress({}));

        return $.ajax({
            url: ajaxurl,
            method: 'post',
            data: {
                mapId: mapId,
                action: 'wp2l_get_map_rows_count'
            },
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
        // var limit = 2000;
        var limit = iterationLimit;
        var offset = 0;
        var iterations = Math.ceil(count / limit);
        var mapId = $_GET('active_mapping');
        var promises = [];
        var results = [];

        window.mapResultsIterations = iterations;

        for (var i = 0; i < iterations; i++) {
            offset = limit * i;

            promises.push(
                $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    async: false,
                    data: {
                        action: 'wp2l_get_map_query_results_by_map_id',
                        mapId: mapId,
                        limit: limit,
                        nocache: 1,
                        offset: offset
                    },
                    success: function(response) {
                        var decoded;

                        try {
                            decoded = $.parseJSON(response);
                        } catch(err) {
                            decoded = false;
                        }

                        var countingResultsHolder = $('.rows_count_progress');

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

    function nextOptionsPage() {
        window.apiFieldsOnPagination = true;
        window.possibleTagsOnLoad = true;
        window.spinner.show();

        $('.accordion-body.api-processing-holder .api-spinner-holder').addClass('api-processing');
        $('.available_options .api-processing-holder .api-spinner-holder').addClass('api-processing');

        var mapId = $_GET('active_mapping');

        var rows_count = $('.rows_count').text();
        var search_value = $('#inputFiledSearchOption').find('input[type="text"]').val();
        var pageNumber = $(this).data('page');
        var offset = pageNumber - 1;
        var direction = $(this).data('direction');

        var count = window.mapResults.length;

        var data = {
            action: 'wp2l_get_available_options_data',
            mapId: mapId,
            count: count,
            pageNumber: pageNumber,
            mapResult: JSON.stringify(window.mapResults[offset])
        };

        $.post(ajaxurl, data, function (response) {
            var decoded = $.parseJSON(response);
            var options_wrapper = $('.available_options');
            options_wrapper.html(decoded.availableOptions);

            var prevBtn = options_wrapper.find('.prev');
            var nextBtn = options_wrapper.find('.next');

            if (prevBtn.data('page') >= 1) {
                prevBtn.attr('disabled', false);
            } else {
                prevBtn.attr('disabled', true);
            }

            if (nextBtn.data('page') > rows_count) {
                nextBtn.attr('disabled', true);
            } else {
                nextBtn.attr('disabled', false);
            }

            options_wrapper.find('.rows_count').text(rows_count);

            if (search_value) {
                var search_field = options_wrapper.find('#inputFiledSearchOption input[type="text"]');
                search_field.val(search_value);
                search_field.change();
            }

            var new_options = options_wrapper.find('.available_option > label');
            var data = [];

            $.each(new_options,function (index, label) {
                data[data.length] = $(label).text();
            });

            updateApiFields(data, true);

            updateOptinFromCondition(true);

            getUsersTagsFromKlickTipp(null);

            updatePossibleTags();

            updateManuallySelectedTags();

            loadPossibleTagsCloud(null);

            window.apiFieldsOnPagination = false;

            $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
            $('.available_options .api-processing-holder .api-spinner-holder').removeClass('api-processing');

            window.spinner.hide();
        }, 'html');
    }

    function updateApiFields(data, empty_api_field = false) {
        var api_fields = $('.api-field');
        var new_select_list = document.createDocumentFragment();

        $.each(data, function (key, value) {
            var option = $('<option>');
            option.val(value);
            option.text(value);

            new_select_list.appendChild(option[0]);
        });

        $.each(api_fields, function (index, item) {
            var select = $(item);
            var api_options = select.data('api-option');

            select.empty();
            select.append(new_select_list.cloneNode(true));

            if (undefined !== api_options['table_columns']) {
                if (empty_api_field) {
                    select.closest('.api_field_box').find('.token').remove();
                }

                $.each(api_options['table_columns'], function (index, val) {
                    selectApiFieldValue(select, val);

                    var activeField = $(document.body).find('.tokenize.active');
                    activeField.removeClass('active');
                });
            }
        });

        window.apiFieldsOnLoad = false;
    }

    function loadConditions(types) {
        $.each(types, function (index, type) {
            var saved_conditions = $('#' + type + '-conditions .conditions-list').data('saved-value');
            var add_new_condition_btn = $('#' + type + '-conditions .add_new_condition');

            if ('tags-detach' === type) {
                add_new_condition_btn = $('#' + type + '-conditions .add_new_detach_condition');
            }

            $.each(saved_conditions, function (index, condition_data) {
                add_new_condition_btn.click();

                var last_added = $('#' + type + '-conditions .conditions-list .condition').last();
                var select = last_added.find('.options_where');
                selectApiFieldValue(select, condition_data.option);

                var activeField = $(document.body).find('.tokenize.active');
                activeField.removeClass('active');
                var prefix = condition_data.prefix ? condition_data.prefix : '';
                var prefix_input = last_added.find('input[name="' + type + '-prefix"]');

                if (prefix_input.length) {
                    prefix_input.val(prefix);
                }

                last_added.find('.field_value').text(condition_data.option);
                last_added.find('select[name="operator"]').val(condition_data.operator);
                last_added.find('select[name="' + type + '"]').val(condition_data.connectTo);
                last_added.find('input[name="string"]').val(condition_data.string);

                if ('tags' === type) {
                    // selectTagsFromConnectedOptions(condition_data);
                }
            });
        });

        $('.options_where').tokenize2({
            tokensMaxItems: 1,
            dropdownMaxItems: 999,
            searchFromStart: false
        });
    }

    function loadAutotagsConditions(types) {
        $.each(types, function (index, type) {
            var saved_conditions = $('#autotags-' + type + '-conditions .conditions-list').data('saved-value');
            var add_new_condition_btn = $('#autotags-' + type + '-conditions #addConditionForAutotags');

            $.each(saved_conditions, function (index, condition_data) {
                add_new_condition_btn.click();

                var last_added = $('#autotags-' + type + '-conditions .conditions-list .condition').last();
                var select = last_added.find('.options_where');

                selectApiFieldValue(select, condition_data.option);

                var activeField = $(document.body).find('.tokenize.active');
                activeField.removeClass('active');

                last_added.find('.field_value').text(condition_data.option);
                last_added.find('select[name="operator"]').val(condition_data.operator);
                last_added.find('input[name="string"]').val(condition_data.string);
            });
        });

        var donot_optins_saved_conditions = $('#donot-optins-conditions .conditions-list').data('saved-value');
        var add_new_donot_optins_condition_btn = $('#addConditionForDoNotOptin');

        $.each(donot_optins_saved_conditions, function (index, condition_data) {
            add_new_donot_optins_condition_btn.click();

            var last_added = $('#donot-optins-conditions .conditions-list .condition').last();
            var select = last_added.find('.options_where');

            selectApiFieldValue(select, condition_data.option);

            var activeField = $(document.body).find('.tokenize.active');
            activeField.removeClass('active');

            last_added.find('.field_value').text(condition_data.option);
            last_added.find('select[name="operator"]').val(condition_data.operator);
            last_added.find('input[name="string"]').val(condition_data.string);
        });

        $('.options_where').tokenize2({
            tokensMaxItems: 1,
            dropdownMaxItems: 999,
            searchFromStart: false
        });
    }

    function loadSeparators() {
        var saved_separators = $('.separators-list').data('saved-value');
        var add_new_separator_btn = $('.add_new_separator');

        $.each(saved_separators, function (index, separator_data) {
            add_new_separator_btn.click();

            var last_added = $('.separators-list .separator-wrapper').last();
            var prefix = separator_data.prefix ? separator_data.prefix : '';
            var filter = separator_data.filter ? separator_data.filter : '';

            last_added.find('input[name="separator"]').val(separator_data.separator);
            last_added.find('input[name="separator-prefix"]').val(prefix);
            last_added.find('input[name="separator-filter"]').val(filter);

            if (separator_data.filter_action) {
                last_added.find('input[name="separator-filter-type"]').prop( "checked", true );
            }

            var select = last_added.find('.options_where');

            $.each(separator_data.option, function (index, item) {
                selectApiFieldValue(select, item);

                var activeField = $(document.body).find('.tokenize.active');
                activeField.removeClass('active');
            });

            last_added.find('.options_where').tokenize2({
                dropdownMaxItems: 999,
                searchFromStart: false
            });
        });
    }

    function loadConnectedOptions() {
        var tags_option = $('.connected-options-wrapper .options-list');

        var tags_concat_option = $('.connected-options-wrapper .options-concat-list');

        var saved_options = tags_option.data('saved-value');

        var saved_concat_options = tags_concat_option.data('saved-value');

        var template = Handlebars.compile($('#wp2l-api-tag-options')[0].innerHTML);
        var available_options = $('.available_options .available_option > label');
        var options = [];

        $.each(available_options,function (index, label) {
            let optionLabel = $(label).data('table-column');

            if ($(label).data('value')) {
                optionLabel += ': (' + $(label).data('value') + ')';
            } else {
                optionLabel += ': (' + wp2leads_i18n_get('No value') + ')';
            }
            options[options.length] = {
                value: $(label).data('table-column'),
                label: optionLabel,
            };
        });

        tags_option.append(template({
            availableOptions: options
        }));

        tags_concat_option.append(template({
            availableOptions: options
        }));

        if (typeof saved_options == 'object') {
            $.each(saved_options, function (index, option) {
                tags_option.find('option[value="' + option + '"]').attr('selected', true);
            });
        }


        if (typeof saved_concat_options == 'object') {
            $.each(saved_concat_options, function (index, option) {
                tags_concat_option.find('option[value="' + option + '"]').attr('selected', true);
            });
        }

        $('.options-list').tokenize2({
            searchFromStart: false,
            dropdownMaxItems: 999,
        });
        $('.options-concat-list').tokenize2({
            searchFromStart: false,
            dropdownMaxItems: 999,
        });

        $('.connected-options-wrapper .simple-auto-tags .tokenize').each(function(i, obj) {
            $(this).trigger('focusout');
        });
    }

    function loadRecomendedUserInputTagsCloud(tagsContainer, notrigger) {
		if ( typeof notrigger == 'undefined' ) notrigger = false;
        var tagsCloud = tagsContainer.find('.recommended_user_input_tags_cloud');
        var messageContainer = tagsContainer.find('.recommended_user_input_tags_message');
        var allMessageTags = messageContainer.find('.all-tags');
        var limitMessageTags = messageContainer.find('.limit-tags');
        var container_index = tagsContainer.data('container');
        var createTagsBtn = tagsContainer.find('.create-user-input-tags');
        var createAllTagsBtn = tagsContainer.find('.create-all-user-input-tags');
        var selectTagsBtn = tagsContainer.find('.select-user-input-tags');
        var deselectTagsBtn = tagsContainer.find('.deselect-user-input-tags');
        var control = tagsContainer.find('.get-user-input-tags-results');
        var mapping = control.data('value');
        var mapPrefix = getMapTagPrefix();
        var prefix = tagsContainer.find('.recomended-tags-prefix').val();

        var data = {
            action: 'wp2l_get_recomended_tags_result',
            mapping: mapping
        };

        if (mapPrefix) {
            data.mapPrefix = mapPrefix;
        }

        if (prefix) {
            data.prefix = prefix;
        } else {
            data.prefix = '';
        }

        $.ajax({
            url: ajaxurl,
            method: 'post',
            data: data,
            success: function (response) {
                var result;

                try {
                    result = $.parseJSON(response);
                } catch(err) {
                    result = false;
                }

                if (result) {
                    if (result.success) {
                        tagsCloud.empty();

                        if (result.tags.length === 0) {
                            var template = Handlebars.compile($('#wp2l-map-to-api-no-new-recomended-tags')[0].innerHTML);
                            tagsCloud.append(template);
                        } else {
                            var isNew = false;
                            var countLimit = 100;
                            var countAll = 0;
                            var tagsNames = [];

                            $.each(result.tags, function (index, item) {
                                var tagName = getMapTagPrefix() + item;

                                if (!getExistedTag (tagName)) {
                                    tagsNames.push(tagName);

                                    if (countAll <= countLimit) {
                                        tagsCloud.append(createRecomendedTag(tagName, '_container_' + container_index + '_' + index));
                                    }

                                    isNew = true;
                                    countAll++;
                                }
                            });

                            if (!isNew) {
                                var template = Handlebars.compile($('#wp2l-map-to-api-recomended-tags-created')[0].innerHTML);
                                tagsCloud.append(template);
                            } else {
                                createTagsBtn.show();
                                selectTagsBtn.show();
                                deselectTagsBtn.show();

                                if (countAll > countLimit) {
                                    createAllTagsBtn.data('tags-all', JSON.stringify(tagsNames));
                                    createAllTagsBtn.show();
                                    allMessageTags.text(countAll);
                                    limitMessageTags.text(countLimit);
                                    messageContainer.show();
                                }
                            }

                        }

                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                    } else {
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                    }
                } else {
                    // TODO: Add actions for error
                    $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                }

				if ( !notrigger ) $('body').trigger('getUserInputTagsResults');
				$('body').trigger('checkScroll5');
            },
            error: function (xhr, status, error) {
                console.log(xhr);
                console.log(status);
                console.log(error);
            },
            complete: function (xhr, status) {}
        });
    }

    function createRecomendedTag(tagName, index) {
        var tag = $('<fieldset>');
        var tag_name_input = $('<input id="recommendedTag_'+index+'" type="checkbox" value="'+tagName+'" data-name="'+tagName+'">');
        var tag_label = $('<label for="recommendedTag_'+index+'">');

        tag_label.text(tagName);

        tag.append(tag_name_input);
        tag.append(tag_label);

        return tag;
    }

    function loadRecomendedTagsCloud() {
        var isNew = false;
        var tagCloudHolder = $('#recommendedTagsCloud');
        var standartTags = tagCloudHolder.data('saved-value-standart');

        tagCloudHolder.empty();

        $.each(standartTags, function (index, standartTag) {
            var tag = standartTag;
            var prefix = '';
            var tagArray = standartTag.split('||');

            if (tagArray.length === 2) {
                prefix = tagArray[0];
                tag = tagArray[1];
            }

            if (prefix && '' !== prefix) {
                prefix = prefix + ' ';
            }

            tag = tag.replace('&amp;', '&').replace('&gt;', '>').replace('&lt;', '<').replace('&quot;', '"').replace('&#039;', "'");

            var tagName = getMapTagPrefix() + prefix + tag;

            if (!getExistedTagByName(tagName)) {
                // tagCloudHolder.append(createRecomendedTag(tagName, 'standart_' + index));
                tagCloudHolder.append(createRecomendedStandartTag(getMapTagPrefix(), tag, prefix, 'standart_' + index));
                isNew = true;
            } else {
                tagCloudHolder.append(createExistedRecomendedStandartTag(getMapTagPrefix(), tag, prefix, 'standart_' + index));
            }
        });

        if (isNew) {
            $('#selectRecommendedTags').attr('disabled', false);
            $('#deselectRecommendedTags').attr('disabled', false);
            $('#createRecommendedTags').attr('disabled', false);
        } else {
            $('#selectRecommendedTags').attr('disabled', true);
            $('#deselectRecommendedTags').attr('disabled', true);
            $('#createRecommendedTags').attr('disabled', true);
        }
    }

    function createRecomendedStandartTag(map_prefix, tag_name, prefix, index) {
        var placeholder = wp2leads_i18n_get('Set prefix');
        var tag = $('<fieldset style="margin: 2px 0;" data-tag="'+tag_name+'" data-prefix="'+$.trim(prefix)+'">');
        var tag_prefix_input = $('<input placeholder="'+placeholder+'" type="text" style="width:90px;min-height:22px;line-height:1.2;margin-right: 5px;font-size:12px;" value="'+$.trim(prefix)+'">');
        var tag_name_input = $('<input id="recommendedTag_'+index+'" data-id="recommendedTag_'+index+'" type="checkbox" value="'+map_prefix + prefix + tag_name+'">');
        var tag_label = $('<label for="recommendedTag_'+index+'">');

        tag_label.text(map_prefix + prefix + tag_name);

        tag.append(tag_prefix_input);
        tag.append(tag_name_input);
        tag.append(tag_label);

        return tag;
    }

    function createExistedRecomendedStandartTag(map_prefix, tag_name, prefix, index) {
        var placeholder = wp2leads_i18n_get('Set prefix');
        var tag = $('<fieldset style="margin: 2px 0;" data-tag="'+tag_name+'" data-prefix="'+$.trim(prefix)+'">');
        var tag_prefix_input = $('<input placeholder="'+placeholder+'" type="text" style="width:90px;min-height:22px;line-height:1.2;margin-right: 5px;font-size:12px;" value="'+$.trim(prefix)+'">');
        var tag_name_input = $('<input data-id="recommendedTag_'+index+'" type="checkbox" value="'+map_prefix + prefix + tag_name+'" disabled>');
        var tag_label = $('<label for="recommendedTag_'+index+'">');

        tag_label.text(map_prefix + prefix + tag_name);

        tag.append(tag_prefix_input);
        tag.append(tag_name_input);
        tag.append(tag_label);

        return tag;
    }

    $(document.body).on('input', '#recommendedTagsCloud fieldset input[type="text"]', function() {
        var prefix_input = $(this);
        var fieldset = prefix_input.parents('fieldset');
        var mapPrefix = getMapTagPrefix();
        var prefix = $.trim(prefix_input.val());
        var tag = fieldset.data('tag');
        var tag_input = fieldset.find('input[type="checkbox"]');
        var tag_label = fieldset.find('label');
        var text = mapPrefix;
        text += prefix && '' !== prefix ? prefix + ' ' + tag : tag;

        tag_input.val(text);
        tag_label.text(text);

        fieldset.data('prefix', prefix);

        var fieldsets = $('#recommendedTagsCloud fieldset');

        var isNew = false;
        var updated_tags = {};

        $.each(fieldsets, function(i, row) {
            var $row = $(row);
            var row_prefix = $row.find('input[type="text"]').val();
            var row_tag_checkbox = $row.find('input[type="checkbox"]');
            var row_tag_checkbox_id = row_tag_checkbox.data('id');
            var row_tag = $row.data('tag');
            var row_label = $row.find('label');
            var updated_tag = row_prefix && '' !== $.trim(row_prefix) ? row_prefix + '||' + row_tag : row_tag;
            var updated_tag_check = row_prefix && '' !== $.trim(row_prefix) ? mapPrefix + row_prefix + ' ' + row_tag : mapPrefix + row_tag;
            updated_tags[i] = updated_tag;

            if (!getExistedTagByName(updated_tag_check)) {
                isNew = true;
                row_tag_checkbox.attr('disabled', false);
                row_tag_checkbox.attr('id', row_tag_checkbox_id);
            } else {
                row_tag_checkbox.attr('disabled', true);
                row_tag_checkbox.attr('id', '');
            }
        });

        $('#recommendedTagsCloud').data('saved-value-standart', updated_tags);

        if (isNew) {
            $('#selectRecommendedTags').attr('disabled', false);
            $('#deselectRecommendedTags').attr('disabled', false);
            $('#createRecommendedTags').attr('disabled', false);
        } else {
            $('#selectRecommendedTags').attr('disabled', true);
            $('#deselectRecommendedTags').attr('disabled', true);
            $('#createRecommendedTags').attr('disabled', true);
        }
    });

    function loadMultipleAutotags() {
        var multipleAutotagsContainer = $('#multiple-autotags-add-conditions');
        var multipleAutotagsList = multipleAutotagsContainer.find('.multiple-autotag-list');
        var savedMultipleAutotagsList = multipleAutotagsList.data('saved-value');
        var addSingleBtn = $('#addConditionForSingleAutotags');
        var addConcatBtn = $('#addConditionForConcatAutotags');
        var addSeparatorBtn = $('#addConditionForSeparatorAutotags');

        $.each(savedMultipleAutotagsList, function (index, autotagsData) {

            if (!jQuery.isEmptyObject(autotagsData.single_tags)) {
                addSingleBtn.trigger( "click", [true] );
            } else if (!jQuery.isEmptyObject(autotagsData.separator_tags)) {
                addSeparatorBtn.trigger( "click", [true] );
            }  else if (!jQuery.isEmptyObject(autotagsData.concat_tags)) {
                addConcatBtn.trigger( "click", [true] );
            }

            var last_added = $('#multiple-autotags-add-conditions .multiple-autotag-list .multiple-autotag-item').last();

            if (!jQuery.isEmptyObject(autotagsData.conditions)) {
                var addNewConditionBtn = $(last_added).find('.add-condition-for-multiple-autotags');

                $.each(autotagsData.conditions, function(k, condition_data) {
                    addNewConditionBtn.click();

                    var lastAddedCondition = $(last_added).find('.multiple-autotags-add-conditions .conditions-list .condition').last();
                    var select = lastAddedCondition.find('.options_where');

                    selectApiFieldValue(select, condition_data.option);

                    var activeField = $(document.body).find('.tokenize.active');
                    activeField.removeClass('active');

                    lastAddedCondition.find('.field_value').text(condition_data.option);
                    lastAddedCondition.find('select[name="operator"]').val(condition_data.operator);
                    lastAddedCondition.find('input[name="string"]').val(condition_data.string);
                });
            }

            if (!jQuery.isEmptyObject(autotagsData.single_tags)) {
                var single_tags_prefix = autotagsData.single_tags_prefix ? $.trim(autotagsData.single_tags_prefix) : '';

                $.each(autotagsData.single_tags, function (k, single_tag) {
                    $(last_added).find('.multiple-autotags-options-list option[value="' + single_tag + '"]').attr('selected', true);
                });

                $(last_added).find('input[name="multiple-autotags-single-prefix"]').val(single_tags_prefix);
                $(last_added).find('.multiple-autotags-options-list').tokenize2({
                    searchFromStart: false,
                    dropdownMaxItems: 999,
                    test: true,
                });

                $(last_added).find('.tokenize').trigger('focusout');
            } else if (!jQuery.isEmptyObject(autotagsData.separator_tags)) {
                var separator_tags_prefix = autotagsData.separator_tags_prefix ? $.trim(autotagsData.separator_tags_prefix) : '';
                var separator_tags_filter = autotagsData.separator_tags_filter ? $.trim(autotagsData.separator_tags_filter) : '';

                $.each(autotagsData.separator_tags.option, function (index, item) {
                    $(last_added).find('.multiple-autotags-options-separator-list option[value="' + item + '"]').attr('selected', true);
                });

                $(last_added).find('.multiple-autotags-add-separators input[name="separator"]').val(autotagsData.separator_tags.separator);
                $(last_added).find('.multiple-autotags-add-separators input[name="multiple-autotags-add-separators-prefix"]').val(separator_tags_prefix);
                $(last_added).find('.multiple-autotags-add-separators input[name="multiple-autotags-add-separators-filter"]').val(separator_tags_filter);

                if (autotagsData.separator_tags_filter_type) {
                    $(last_added).find('input[name="multiple-autotags-add-separators-filter-type"]').prop( "checked", true );
                }

                $(last_added).find('.multiple-autotags-options-separator-list').tokenize2({
                    searchFromStart: false,
                    dropdownMaxItems: 999,
                    test: true,
                });

                $(last_added).find('.tokenize').trigger('focusout');
            } else if (!jQuery.isEmptyObject(autotagsData.concat_tags)) {
                var concat_tags_prefix = autotagsData.concat_tags_prefix ? $.trim(autotagsData.concat_tags_prefix) : '';
                var concat_tags_filter = autotagsData.concat_tags_filter ? $.trim(autotagsData.concat_tags_filter) : '';
                $.each(autotagsData.concat_tags, function (k, concat_tag) {
                    $(last_added).find('.multiple-autotags-options-concat-list option[value="' + concat_tag + '"]').attr('selected', true);
                });

                $(last_added).find('input[name="multiple-autotags-concat-prefix"]').val(concat_tags_prefix);
                $(last_added).find('input[name="multiple-autotags-concat-filter"]').val(concat_tags_filter);

                if (autotagsData.concat_tags_filter_type) {
                    $(last_added).find('input[name="multiple-autotags-concat-filter-type"]').prop( "checked", true );
                }

                $(last_added).find('.multiple-autotags-options-concat-list').tokenize2({
                    searchFromStart: false,
                    dropdownMaxItems: 999,
                    test: true,
                });

                $(last_added).find('.tokenize').trigger('focusout');
            }
        });
    }

    function updateManuallySelectedTags() {
        var savedMapInput = $('input.mapping');
        var savedMapOnLoad = $.parseJSON(savedMapInput.val());
        var savedMapNewData;
        try {
            savedMapNewData = $.parseJSON(savedMapInput.data('new_value'));
        } catch(err) {
            savedMapNewData = false;
        }

        // var savedMapNewData = $.parseJSON(savedMapInput.data('new_value'));
        var saved_map = savedMapOnLoad;

        if (!window.manuallySelectedTagsOnLoad && savedMapNewData) {
            saved_map = savedMapNewData;
        }

        if (saved_map) {
            var tags_cloud = $('.tags-cloud');

            if (saved_map.manually_selected_tags) {
                $.each(saved_map.manually_selected_tags.tag_ids, function (index, tag_id) {
                    var checkbox = tags_cloud.find('input[id="' + tag_id + '"]');
                })
            }
        }
    }

    function getUsersTagsFromKlickTipp(email, cb) {
        if (null === email) {
            var emailField = $('.api-fields-wrapper #api_email .token > span', document.body);

            if (emailField.length === 1) {
                var email = $('.api-fields-wrapper #api_email .token > span', document.body).data('value');
            } else if (emailField.length > 1) {
                var email = [];

                emailField.each(function() {
                    email.push($(this).data('value'));
                });
            }
        }

        var data = {
            action: 'wp2l_get_subscriber_tags_from_klicktipp',
            email: email
        };

        $.post(
            ajaxurl,
            data,
            function (response) {
                var decoded = $.parseJSON(response);
                var existed_tags_wrapper = $('.existed-tags-wrapper');

                window.currentUserKTTag = [];

                if (decoded.success) {

                    existed_tags_wrapper.empty().addClass('flex');

                    $.each(decoded.tags, function (index, tag_id) {
                        var tag_data = {
                            id: tag_id,
                            text: $('.tags-cloud input[id="' + tag_id + '"]').val()
                        };

                        window.currentUserKTTag.push(tag_id);

                        existed_tags_wrapper.append(createTag(tag_data));
                    });

                    if (typeof cb === 'function') {
                        cb();
                    }

                } else {
                    existed_tags_wrapper.removeClass('flex').html( $.parseHTML( decoded.message ) );
                    if (typeof cb === 'function') {
                        cb();
                    }
                }

				$(document.body).trigger('wp2l_get_subscriber_tags_from_klicktipp');
            }
        );
    }

    /**
     *
     *
     * @param condition_data
     */
    function selectTagsFromConnectedOptions(condition_data) {
        if (condition_data) {
            var tags_wrapper = $('.selected-tags-wrapper');
            var option_value = $('.available_option label[data-table-column="' + condition_data.option + '"]').data('value');
            var tag_name = $('.tags-cloud input[type="checkbox"][id="' + condition_data.connectTo + '"]').val();

            var tag_data = {
                id: condition_data.connectTo,
                text: tag_name
            };

            var alreadySelected = tags_wrapper.find('.selected-tag[data-tag-id="' + tag_data.id + '"]');

            if (option_value && alreadySelected.length < 1 && tag_data.id > 0 && undefined !== tag_data.text) {
                if (prepareCondition(option_value, condition_data)) {
                    tags_wrapper.append(createTag(tag_data));
                }
            }
        }
    }

    function selectTags() {
        var connected_for_tags = $('.connected-options-wrapper .options-list option:selected');

        $.each(connected_for_tags, function(i, field) {
            var table_column = $(field).val();
            var option = $('.available_option label[data-table-column="' + table_column + '"]');
            var separators = getSeparartors(table_column);

            if (option.length > 0) {
                $.each(option, function (index, opt) {

                    var tag_text = $(opt).data('value');
                    var tags_names = [];

                    if (typeof separators !== 'undefined' && separators.length > 0) {
                        tags_names = multiSplit(tag_text, separators);
                    } else {
                        tags_names.push(tag_text);
                    }
                });
            }
        });
    }

    function prepareDataForKlickTipPromise() {
        window.preparedForTransfer = [];
        window.preparedForTransferCount = 0;
        window.preparedForTransferKtLimit = false;

        var currentMap = JSON.stringify(compileMapToAPIObject());
        var iterations = window.mapResultsIterations;
        var offset = 0;
        var limit = iterationLimit;
        var mapId = $_GET('active_mapping');
        var promises = [];
        var results = [];

        saveNewPrefixesValues();

        var global_tag_prefix = $('#globalTagPrefix').val();

        for (var i = 0; i < iterations; i++) {
            offset = limit * i;

            promises.push(
                $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    async: false,
                    data: {
                        action: 'wp2l_prepare_data_for_klicktipp_bg',
                        mapId: mapId,
                        limit: limit,
                        offset: offset,
                        global_tag_prefix: global_tag_prefix,
                        recomended_tags_prefixes: get_recomended_tags_prefixes(),
                        map: currentMap
                    },
                    success: function(response) {
                        var decoded = $.parseJSON(response);

                        if (decoded.success) {
                            $.merge(window.preparedForTransfer, decoded.result);
                            window.preparedForTransferCount = window.preparedForTransferCount + decoded.count;
                            window.preparedForTransferKtLimit = decoded.kt_limited;
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

    function transferDataToKlickTipPromise() {
        window.transferedData = [];
        window.transferedDataCount = 0;

        var currentMap = JSON.stringify(compileMapToAPIObject());
        var iterations = window.mapResultsIterations;
        var offset = 0;
        // var limit = 2000;
        var limit = iterationLimit;
        var mapId = $_GET('active_mapping');
        var promises = [];
        var results = [];

        for (var i = 0; i < iterations; i++) {
            offset = limit * i;

            promises.push(
                $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    async: false,
                    data: {
                        action: 'wp2l_transfer_all_data_to_klicktip_bg',
                        mapId: mapId,
                        limit: limit,
                        offset: offset,
                        map: currentMap
                    },
                    success: function(response) {
                        var decoded = $.parseJSON(response);

                        if (decoded.success) {
                            $.merge(window.transferedData, decoded.result);
                            window.transferedDataCount = window.transferedDataCount + decoded.count;
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

    function transferCurrentToKlickTipp() {

        $('.accordion-body.api-processing-holder .api-spinner-holder').addClass('api-processing');
        var mapId = $_GET('active_mapping');

        var data = {
            action: 'wp2l_check_limit',
            mapId: mapId
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
                        if (decoded.limit) {
                            var totalToTransferHolder = $('.transfer-data-modal .available-data .total');
                            var noticeHolder = $('.transfer-data-modal .notice_holder');
                            var ktLimitNoticeHolder = $('#kt_limit_notice_holder');
                            var btnTransfer = $('#btnTransferData');
                            var transfer_current_btn = $('#transferCurrent');
                            var transfer_all_bg_btn = $('#transferAllBg');

                            noticeHolder.empty();
                            ktLimitNoticeHolder.empty();
                            totalToTransferHolder.text('0');

                            var noDataForTransferTemplate = Handlebars.compile($('#wp2l-no-limit-for-transfer')[0].innerHTML);
                            noticeHolder.append(noDataForTransferTemplate({}));
                            ktLimitNoticeHolder.append(noDataForTransferTemplate({}));
                            btnTransfer.removeClass('button-primary').prop('disabled', true);
                            transfer_current_btn.removeClass('button-primary').prop('disabled', true);
                            transfer_all_bg_btn.removeClass('button-primary').prop('disabled', true);

                            $('.transfer-data-modal .api-processing-holder .api-spinner-holder').removeClass('api-processing');
                            $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
							$('body').trigger('transferCurrentToKlickTipp_finished');
                        } else {
                            var currentMap = JSON.stringify(compileMapToAPIObject());
                            var offset = $('.options-buttons-wrapper .prev.button').data('page');
                            var email = $('.api-fields-wrapper #api_email .token > span').data('value');
                            var currentUserData = JSON.stringify(window.mapResults[offset]);

                            var global_tag_prefix = $('#globalTagPrefix').val();

                            var data = {
                                action: 'wp2l_transfer_current_data_to_klicktip_bg',
                                mapId: mapId,
                                email: email,
                                mapResult: currentUserData,
                                global_tag_prefix: global_tag_prefix,
                                recomended_tags_prefixes: get_recomended_tags_prefixes(),
                                map: currentMap
                            };

                            $.post(
                                ajaxurl,
                                data,
                                function (response) {
                                    var decoded;

                                    try {
                                        decoded = $.parseJSON(response);
                                    } catch(err) {
                                        decoded = false;
                                    }

                                    if (decoded) {
                                        if (decoded.success) {
                                            var modal = $('.transfer-data-modal');

                                            var statisticSection = $('#map-statistic-holder'),
                                                allTransferAmount = $('#totalTransferInfo .all-transferred .total'),
                                                uniqueTransferAmount = $('#totalTransferInfo .unique-transferred .total'),
                                                failedTransferAmount = $('#totalTransferInfo .failed-transferred .total'),
                                                cronTransferDate = $('#lastTransferInfo .cron-transfer-date .total'),
                                                manualTransferDate = $('#lastTransferInfo .manual-transfer-date .total');

                                            modal.find('.total-transferred-data .total').text(decoded.total_transferred);
                                            modal.find('.transfered-data .total').text(decoded.added_subscribers);
                                            modal.find('.updated-data .total').text(decoded.existed_subscribers);
                                            modal.find('.failed-data .total').text(decoded.failed_subscribers);

                                            if (decoded.info_message) {
                                                modal.find('.info-message p').text(decoded.info_message);
                                            }

                                            $('#btnTransferData').data('last-transfer', decoded.last_transferred_time);

                                            // Statistics
                                            allTransferAmount.text(decoded.totally_all);
                                            uniqueTransferAmount.text(decoded.totally_unique);
                                            failedTransferAmount.text(decoded.totally_failed);
                                            manualTransferDate.text(decoded.last_transferred_time);

                                            updateExistedTagFieldsetList(function() {
                                                getUsersTagsFromKlickTipp(null, function() {
                                                    loadPossibleTagsCloud(null, function() {
                                                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                                                    })
                                                });
                                            });

                                            // loadPossibleTagsCloud();
                                        } else {
                                            alert(decoded.message);
                                            $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                                        }

                                        setTimeout(function() {
                                            $('.transfer-data-modal .api-processing-holder .api-spinner-holder').removeClass('api-processing');
											$('body').trigger('transferCurrentToKlickTipp_finished');
                                        }, 1000);
                                    } else {
                                        alert(wp2leads_i18n_get('Something went wrong.'));

                                        setTimeout(function() {
                                            $('.transfer-data-modal .api-processing-holder .api-spinner-holder').removeClass('api-processing');
											$('body').trigger('transferCurrentToKlickTipp_finished');
                                        }, 1000);
                                    }
                                }
                            );
                        }
                    } else {
                        alert(decoded.message);
                        $('.transfer-data-modal .api-processing-holder .api-spinner-holder').removeClass('api-processing');
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
						$('body').trigger('transferCurrentToKlickTipp_finished');
                    }
                } else {
                    alert(wp2leads_i18n_get('Something went wrong.'));
                    $('.transfer-data-modal .api-processing-holder .api-spinner-holder').removeClass('api-processing');
                    $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
					$('body').trigger('transferCurrentToKlickTipp_finished');
                }

            },
            error: function(xhr, status, error) {
                alert(wp2leads_i18n_get('Something went wrong.'));
                $('.transfer-data-modal .api-processing-holder .api-spinner-holder').removeClass('api-processing');
                $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
				$('body').trigger('transferCurrentToKlickTipp_finished');
            },
            complete: function(xhr, status) {

            }
        });
    }

    function getTransferModalDataInfoPromise() {
        var mapId = $_GET('active_mapping');

        return $.ajax({
            url: ajaxurl,
            method: 'post',
            data: {
                mapId: mapId,
                action: 'wp2l_get_transfer_modal_data_info'
            },
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

    function updateFieldDatApiOption (activeField, cb) {

        var activeFieldBody = activeField.parents('.api_field_body');
        var activeFieldSelect = activeFieldBody.find('select.api-field');
        var activeFieldPrev = activeFieldSelect.data('api-option');
        var activeFieldName = activeFieldSelect.attr('name');

        if (!window.apiFieldsOnLoad && activeFieldName) {
            var options = activeFieldBody.find('.api-field option:selected');

            var fieldsSelected = {
                table_columns: {}
            };

            var selected_table_columns = [];

            $.each(options, function(i, option) {
                selected_table_columns.push($(option).val().split(' ')[0]);
            });

            if (selected_table_columns.length > 0) {
                for (var j = 0; j < selected_table_columns.length; j++) {
                    fieldsSelected.table_columns[j] = selected_table_columns[j];
                }
            }

            if (activeFieldBody.find('.field-type').val()) {
                fieldsSelected.type = activeFieldBody.find('.field-type').val();

				if (fieldsSelected.type == 'time' || fieldsSelected.type == 'date' || fieldsSelected.type == 'datetime') {
					// here we have convert to gmt option
					fieldsSelected.gmt = activeFieldBody.find('.convert-to-gmt').prop('checked');
					fieldsSelected.gmt_to_local = activeFieldBody.find('.convert-to-local').prop('checked');
				} else {
					fieldsSelected.gmt = false;
					fieldsSelected.gmt_to_local = false;
				}
            }

            activeFieldSelect.data('api-option', fieldsSelected);
        }

        if (typeof cb === 'function') {
            cb();
        }
    }

    function compileMapToAPIObject() {
        var fields = $('.api-fields-wrapper select.api-field');

        var connected_for_tags = $('.connected-options-wrapper .options-list option:selected');
        var connected_for_tags_concat = $('.connected-options-wrapper .options-concat-list option:selected');
        var manually_selected_tags = $('.tags-cloud input:checked');
        var separators = $('.separators-list .separator-wrapper');
        var optins_conditions = $('.optins-conditions-wrapper .conditions-list .condition');
        var donot_optins_conditions = $('#donot-optins-conditions .conditions-list .condition');
        var tags_conditions = $('.tags-conditions-wrapper .conditions-list .condition');
        var auto_tags_conditions = $('#autotags-add-conditions .conditions-list .condition');
        var auto_tags_detach_conditions = $('#autotags-detach-conditions .conditions-list .condition');
        var tags_detach_conditions = $('#tags-detach-conditions .conditions-list .condition');
        var detach_tags = $('.detach-tags-wrapper-selection input[type="checkbox"]:checked');
        var tags_prefix = getMapTagPrefix();
        var map_tags_prefix = $.trim($('#mapTagPrefix').val());
        var start_date_data = $.trim($('#startDateData').val());
        var end_date_data = $.trim($('#endDateData').val());
        var tags_prefix_length = tags_prefix.length;
        var multiple_autotags = $('#multiple-autotags-add-conditions .multiple-autotag-item');
		var remove_notice = $('#remove_notice');

        var api = {
            default_optin: $('.api-optins-wrapper .optins-list').val()
        };

		if (remove_notice.length) {
			api.remove_notice = remove_notice.val();
		}

        api.fields = {};

        $.each(fields, function(i, field) {
            var options = $(field).find('option:selected');
            var attr_name = $(field).attr('name');
            var field_name = $(field).parents('.api_field_box').find('.api_field_head .field_label').text();

            if (undefined === api.fields[attr_name]) {
				var gmt = false;
				var gmt_to_local = false;
				var ttype = $(field).parent().find('.field-type').val();

				if (ttype == 'time' || ttype == 'date' || ttype == 'datetime') {
					gmt = $(field).parent().find('.convert-to-gmt').prop('checked');
                    gmt_to_local = $(field).parent().find('.convert-to-local').prop('checked');
				}

                var add_to_lead = false;

                if (field_name == 'LeadValue') {
                    add_to_lead = $(field).parent().find('.add-to-lead-value').prop('checked');
                }

                api.fields[attr_name] = {
                    name: field_name,
                    table_columns: [],
                    type: ttype,
					gmt: gmt,
                    gmt_to_local: gmt_to_local,
                    add_to_lead: add_to_lead
                };
            }

            $.each(options, function(i, option) {
                var table_column_array =  $(option).val().split(' (');
                table_column_array.pop();
                api.fields[attr_name].table_columns[api.fields[attr_name].table_columns.length] = table_column_array.join(' (');
            });
        });

        api.conditions = {
            global: [],
            optins: [],
            donot_optins: [],
            tags: [],
            detach_tags: [],
            autotags: [],
            detach_autotags: []
        };

        $.each(auto_tags_conditions, function (index, item) {
            var condition = $(item);

            var data = {
                option: condition.find('select[name="option"] option:selected').val(),
                operator: condition.find('select[name="operator"]').val(),
                string: condition.find('input[name="string"]').val(),
            };

            api.conditions.autotags.push(data);
        });

        $.each(auto_tags_detach_conditions, function (index, item) {
            var condition = $(item);

            var data = {
                option: condition.find('select[name="option"] option:selected').val(),
                operator: condition.find('select[name="operator"]').val(),
                string: condition.find('input[name="string"]').val(),
            };

            api.conditions.detach_autotags.push(data);
        });

        $.each(optins_conditions, function (index, item) {
            var condition = $(item);
            var data = {
                option: condition.find('select[name="option"] option:selected').val(),
                operator: condition.find('select[name="operator"]').val(),
                connectTo: condition.find('select[name="optins"]').val(),
                string: condition.find('input[name="string"]').val(),
            };

            api.conditions.optins.push(data);
        });

        $.each(donot_optins_conditions, function (index, item) {
            var condition = $(item);

            var data = {
                option: condition.find('select[name="option"] option:selected').val(),
                operator: condition.find('select[name="operator"]').val(),
                string: condition.find('input[name="string"]').val()
            };

            api.conditions.donot_optins.push(data);
        });

        $.each(tags_conditions, function (index, item) {
            var condition = $(item);
            var tagName = condition.find('select[name="tags-add"] option:selected').text();
            var isPrefixed = false;

            if (tags_prefix_length > 0) {
                if( tagName.indexOf(tags_prefix) === 0){
                    isPrefixed = true;
                }
            }

            if (isPrefixed) {
                var connectToName = tagName.slice(tags_prefix_length);
            } else {
                connectToName = tagName;
            }

            var data = {
                option: condition.find('select[name="option"] option:selected').val(),
                operator: condition.find('select[name="operator"]').val(),
                connectTo: condition.find('select[name="tags-add"]').val(),
                connectToName: connectToName,
                string: condition.find('input[name="string"]').val()
            };

            api.conditions.tags.push(data);
        });

        api.manually_selected_tags = {
            tag_ids: []
        };

        $.each(manually_selected_tags, function (index, item) {
            api.manually_selected_tags.tag_ids.push($(item).attr('id'));
        });

        $.each(tags_detach_conditions, function (index, item) {
            var condition = $(item);
            var tagName = condition.find('select[name="tags-detach"] option:selected').text();
            var isPrefixed = false;
            var tags_detach_prefix = $.trim(condition.find('input[name="tags-detach-prefix"]').val());
            var is_tags_detach_prefix = tags_prefix;

            if (tags_detach_prefix && '' !== tags_detach_prefix) {
                is_tags_detach_prefix = is_tags_detach_prefix + tags_detach_prefix + ' ';
            }

            if (is_tags_detach_prefix.length > 0) {
                if( tagName.indexOf(is_tags_detach_prefix) === 0){
                    isPrefixed = true;
                }
            }

            if (isPrefixed) {
                var connectToName = tagName.slice(is_tags_detach_prefix.length);
            } else {
                connectToName = tagName;
            }

            var data = {
                option: condition.find('select[name="option"] option:selected').val(),
                operator: condition.find('select[name="operator"]').val(),
                connectTo: condition.find('select[name="tags-detach"]').val(),
                prefix: tags_detach_prefix,
                connectToName: connectToName,
                string: condition.find('input[name="string"]').val()
            };

            api.conditions.detach_tags.push(data);
        });

        api.connected_for_tags = {
            tags: [],
            tags_concat: [],
            separators: []
        };

        $.each(connected_for_tags, function(i, field) {
            api.connected_for_tags.tags.push($(field).val());
        });

        $.each(connected_for_tags_concat, function(i, field) {
            api.connected_for_tags.tags_concat.push($(field).val());
        });

        $.each(separators, function(i, field) {
            var separator_wrapper = $(field);
            var selected_options = separator_wrapper.find('.options_where option:selected');
            var data = {
                separator: separator_wrapper.find('input[name="separator"]').val(),
                option: [],
                prefix: separator_wrapper.find('input[name="separator-prefix"]').val(),
                filter: separator_wrapper.find('input[name="separator-filter"]').val()
            };

            var filter_action = separator_wrapper.find('input[name="separator-filter-type"]');
            if (filter_action.length && filter_action.is(':checked')) {
                data.filter_action = 1;
            }

            $.each(selected_options, function (index, option) {
                data.option.push($(option).val());
            });

            api.connected_for_tags.separators.push(data);
        });

        api.multiple_autotags = {
            autotag_items: []
        };

        if (multiple_autotags.length > 0) {
            $.each(multiple_autotags, function(i, item) {
                var data = {};
                var $item = $(item);

                var multiple_conditions = $item.find('.multiple-autotags-add-conditions .conditions-list .condition');
                var simple_tags_selected = $item.find('.multiple-autotags-options-list option:selected');
                var simple_tags_prefix = $item.find('input[name="multiple-autotags-single-prefix"]');
                var concat_tags_selected = $item.find('.multiple-autotags-options-concat-list option:selected');
                var concat_tags_prefix = $item.find('input[name="multiple-autotags-concat-prefix"]');
                var concat_tags_filter = $item.find('input[name="multiple-autotags-concat-filter"]');
                var concat_tags_filter_type = $item.find('input[name="multiple-autotags-concat-filter-type"]');
                var separator_wrapper = $item.find('.multiple-autotags-add-separators .separator-wrapper');
                // var separator_selected_options = separator_wrapper.find('.options_where option:selected');
                var separator_selected_options = separator_wrapper.find('.multiple-autotags-options-separator-list option:selected');
                var separator_tags_prefix = separator_wrapper.find('input[name="multiple-autotags-add-separators-prefix"]');
                var separator_tags_filter = separator_wrapper.find('input[name="multiple-autotags-add-separators-filter"]');
                var separator_tags_filter_type = separator_wrapper.find('input[name="multiple-autotags-add-separators-filter-type"]');

                data.conditions = [];

                $.each(multiple_conditions, function (k, item) {
                    var condition = $(item);
                    var condition_data = {
                        option: condition.find('select[name="option"] option:selected').val(),
                        operator: condition.find('select[name="operator"]').val(),
                        connectTo: condition.find('select[name="tags"]').val(),
                        string: condition.find('input[name="string"]').val(),
                    };

                    data.conditions.push(condition_data);
                });

                data.single_tags = [];
                data.single_tags_prefix = '';

                if ($item.hasClass('autotag-single')) {
                    $.each(simple_tags_selected, function(k, selected) {
                        data.single_tags.push($(selected).val());
                        //
                    });

                    data.single_tags_prefix = $.trim(simple_tags_prefix.val());
                }

                data.concat_tags = [];
                data.concat_tags_prefix = '';
                data.concat_tags_filter = '';

                if ($item.hasClass('autotag-concat')) {
                    $.each(concat_tags_selected, function(k, selected) {
                        data.concat_tags.push($(selected).val());
                    });

                    data.concat_tags_prefix = $.trim(concat_tags_prefix.val());
                    data.concat_tags_filter = $.trim(concat_tags_filter.val());

                    if (concat_tags_filter_type && concat_tags_filter_type.is(':checked')) {
                        data.concat_tags_filter_type = 1;
                    }
                }

                data.separator_tags = [];
                data.separator_tags_prefix = '';
                data.separator_tags_filter = '';

                if ($item.hasClass('autotag-separator')) {
                    data.separator_tags = {
                        separator: separator_wrapper.find('input[name="separator"]').val(),
                        option: []
                    };

                    $.each(separator_selected_options, function (index, option) {
                        data.separator_tags.option.push($(option).val());
                    });

                    data.separator_tags_prefix = $.trim(separator_tags_prefix.val());
                    data.separator_tags_filter = $.trim(separator_tags_filter.val());

                    if (separator_tags_filter_type && separator_tags_filter_type.is(':checked')) {
                        data.separator_tags_filter_type = 1;
                    }
                }

                api.multiple_autotags.autotag_items.push(data);
            });
        }

        api.detach_tags = {
            tag_ids: []
        };

        $.each(detach_tags, function (index, item) {
            api.detach_tags.tag_ids.push($(item).val());
        });

        api.tags_prefix = map_tags_prefix;
        api.start_date_data = start_date_data;
        api.end_date_data = end_date_data;

		// check name tag
		if ($('.losted_name').length > 0 && $('.losted_name').val()) {
			api.losted_name = $('.losted_name').val();
		}

        return api;
    }

    function updateExistedTagFieldsetList(cb) {
        var mapId = $_GET('active_mapping');
        var currentMap = JSON.stringify(compileMapToAPIObject());

        var data = {
            action: 'wp2l_update_existed_tag_fieldset_list',
            mapId: mapId,
            map: currentMap
        };

        $.post(
            ajaxurl,
            data,
            function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    var tagsList = $('.tags-wrapper');
                    var detachTagsList = $('.detach-tags-wrapper-selection');

                    tagsList.html(decoded.tags_list);
                    detachTagsList.html(decoded.detach_tags_list);

                    if (typeof cb === 'function') {
                        cb();
                    }


                } else {
                    alert(decoded.message);

                    if (typeof cb === 'function') {
                        cb();
                    }
                }
            }
        );
    }

    function transferInformationToKlickTipp(map_id, email = null, offset = null ) {

        var data = {
            action: 'wp2l_transfer_to_klicktipp',
            map_id: map_id,
            email: email,
            offset: offset
        };

        $.post(
            ajaxurl,
            data,
            function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    var modal = $('.transfer-data-modal');

                    modal.find('.transfered-data .total').text(decoded.added_subscribers);
                    modal.find('.updated-data .total').text(decoded.existed_subscribers);
                    modal.find('.total-transferred-data .total').text(decoded.total_transferred);

                    $('#btnTransferData').data('last-transfer', decoded.last_transferred_time);

                    // Statistics
                    modal.find('.last-transferred .total').text(decoded.last_transferred_time);
                    modal.find('.all-transferred .total').text(decoded.totally_all);
                    modal.find('.unique-transferred .total').text(decoded.totally_unique);

                    getUsersTagsFromKlickTipp(null);
                    unblockTransferModal();
                } else {
                    alert(decoded.message);
                    unblockTransferModal();
                }
            }
        );
    }

    function getMapTagPrefix() {
        var tagsPrefix = '';
        var mapGlobalTagsPrefix = $.trim($('#globalTagPrefix').val());
        var mapTagsPrefix = $.trim($('#mapTagPrefix').val());

        if (mapGlobalTagsPrefix) {
            tagsPrefix = mapGlobalTagsPrefix + ' ';
        }

        if (mapTagsPrefix) {
            tagsPrefix = mapTagsPrefix + ' ';
        }

        return tagsPrefix;
    }

    function filterAvailableTags(cloud, value) {
        value = value.toLowerCase();

        $.each(cloud, function (index, item) {
            var parent = $(item).parent();
            var tag_name = $(item).data('name') + '';

            if (tag_name.search(new RegExp(value, 'i')) !== -1) {
                parent.show();
            } else {
                parent.hide();
            }
        });
    }

    function updateTagsLists(type, tags) {
        var tags_list = $('.tags-cloud');
        var detach_tags_list = $('.detach-tags-wrapper-selection');
        var existedTags = tags_list.data('tags-cloud');

        if ('add' === type) {
            $.each(tags, function (index, tag) {
                var fieldset = $('<fieldset>');
                var input = $('<input id="' + tag.tag_id + '" type="checkbox" value="' + tag.tag_name + '" data-name="'+tag.tag_name+'">');
                var label = $('<label for="' + tag.tag_id + '">' + tag.tag_name + '</label>');

                existedTags[tag.tag_id] = tag.tag_name;
                tags_list.data('tags-cloud',  existedTags);

                fieldset.append(input, label);

                tags_list.prepend(fieldset);

                var detachFieldset = $('<fieldset>');
                var detachInput = $('<input id="detach_' + tag.tag_id + '" type="checkbox" value="' + tag.tag_id + '" data-name="'+tag.tag_name+'">');
                var detachLabel = $('<label for="detach_' + tag.tag_id + '">' + tag.tag_name + '</label>');

                detachFieldset.append(detachInput, detachLabel);

                detach_tags_list.prepend(detachFieldset);
            });
        } else if ('remove' === type) {
            var removed_tags = $('.remove-tags-cloud');
            var selected_tags = $('.selected-tags-wrapper');
            var selected_tags_cloud = $('.selected-tags-cloud-wrapper');

            $.each(tags, function (index, tag_id) {
                tags_list.find('input[id="' + tag_id + '"]').parent().remove();
                detach_tags_list.find('input[id="detach_' + tag_id + '"]').parent().remove();
                removed_tags.find('input[value="' + tag_id + '"]').parent().remove();
                selected_tags.find('.selected-tag[data-tag-id="' + tag_id + '"]').remove();
                selected_tags_cloud.find('.selected-tag[data-tag-id="' + tag_id + '"]').remove();
            });

            generateStickyTagsHolderContent();
        }
    }

    function displayNotice(response) {
        var body = $('#wpbody-content');
        var responseBlock = $('<div id="setting-error-settings_updated" class="response-message notice is-dismissible"><p><strong></strong></p></div>');
        var dissmis_btn = $('<button type="button" class="notice-dismiss"><span class="screen-reader-text">'+wp2leads_i18n_get('Dismiss this notice.')+'</span></button>');

        responseBlock.append(dissmis_btn);
        body.parent().prepend(responseBlock);

        if (1 === response.status) {
            responseBlock.addClass('notice-success');
        } else {
            responseBlock.addClass('notice-error');
        }

        responseBlock.find('p strong').text(response.message);

        $('.notice-dismiss').on('click', function() {
            $(this).parent().remove();
        });
    }

    function createPossibleTag(data) {
        var tag = $('<div class="selected-tag">');
        tag.text(data.text);
        tag.attr('data-tag-id', data.id);

        if (data.tagClass) {
            tag.attr('class', data.tagClass);
        }

        return tag;
    }

    function createTag(data) {
        var tag = $('<div class="selected-tag">');
        var close_icon = $('<span class="tag-close-btn">');

        tag.text(data.text);
        tag.attr('data-tag-id', data.id);

        if (data.tagClass) {
            tag.attr('class', data.tagClass);
        }

        tag.append(close_icon);

        close_icon.on('click', function () {
            var parent = $(this).parent();
            var tag_id = parent.attr('data-tag-id');
            var existed_wrapper = $(this).closest('.existed-tags-wrapper');

            if (existed_wrapper.length <= 0) {
                var tags_wrapper = $('.tags-cloud');
                tags_wrapper.find('input[id="' + tag_id + '"]').prop('checked', false);
                parent.remove();
            } else {
                detachSubscriberFromTag(tag_id, parent);
            }
        });

        return tag;
    }

    function selectApiFieldValue(select, value) {
        var parent_wrapper = select.closest('.api_field_box');
        var fieldInput = parent_wrapper.find('li.token-search input');

        fieldInput.val(value);
        fieldInput.trigger('click');
        fieldInput.trigger('keyup');
        $('.tokenize-dropdown').filter('.dropdown').find('.dropdown-item').trigger('mousedown');

        fieldInput.val('');
        fieldInput.trigger('click');
        fieldInput.trigger('keyup');

        parent_wrapper.find('.tokenize').trigger('focusin');
        parent_wrapper.find('.tokenize').trigger('focusout');
    }

    function updateOptinFromCondition(onChange) {
        var saved_conditions, donot_optins_saved_conditions;

        if (!onChange) {
            donot_optins_saved_conditions = $('#donot-optins-conditions .conditions-list').data('saved-value');
            saved_conditions = $('.optins-conditions-wrapper .conditions-list').data('saved-value');
        } else {
            var optins_conditions = $('.optins-conditions-wrapper .conditions-list .condition');
            var donot_optins_conditions = $('#donot-optins-conditions .conditions-list .condition');

            saved_conditions = [];
            donot_optins_saved_conditions = [];

            $.each(optins_conditions, function (index, item) {
                var condition = $(item);

                var data = {
                    option: condition.find('select[name="option"] option:selected').val(),
                    operator: condition.find('select[name="operator"]').val(),
                    connectTo: condition.find('select[name="optins"]').val(),
                    string: condition.find('input[name="string"]').val(),
                };

                saved_conditions.push(data);
            });

            $.each(donot_optins_conditions, function (index, item) {
                var condition = $(item);

                var data = {
                    option: condition.find('select[name="option"] option:selected').val(),
                    operator: condition.find('select[name="operator"]').val(),
                    string: condition.find('input[name="string"]').val()
                };

                donot_optins_saved_conditions.push(data);
            });
        }

        var donot_optins = false;
        var optins = $('.optins-list');
        var optin_selected = optins.find('option:selected').text();
        var active_optin = $('.active-optin .active-optin-wrapper');

        $.each(donot_optins_saved_conditions, function (index, condition_data) {
            var option_value = $('.available_option label[data-table-column="' + condition_data.option + '"]').data('value');

            if (prepareCondition(option_value, condition_data)) {
                donot_optins = true;
            }
        });

        if (donot_optins) {
            var message = Handlebars.compile($('#wp2l-api-message-current-user-donot-optins')[0].innerHTML);
            active_optin.text(message);
            active_optin.data('optin', 'disabled');
        } else {
            active_optin.text(optin_selected);
            active_optin.data('optin', 'allowed');

            $.each(saved_conditions, function (index, condition_data) {
                var option_value = $('.available_option label[data-table-column="' + condition_data.option + '"]').data('value');

                if (prepareCondition(option_value, condition_data)) {
                    active_optin.text(optins.find('option[value="' + condition_data.connectTo + '"]').text());
                    //optins.val(condition_data.connectTo);
                    return false;
                } else {
                    if (index == Object.entries(saved_conditions).length - 1) {
                        //optins.find('option').first().prop('selected', true);
                        active_optin.text(optins.find('option:selected').text());
                    }
                }
            });
        }

    }

    function prepareCondition(option_value, condition_data) {
        var condition = false;

        var valueType = $.type(option_value);

        if ('number' === valueType) {
            option_value = option_value.toString();
        }

        if ('string' !== $.type(option_value)) {
            return false;
        }

        switch (condition_data.operator) {
            case 'like':
                if (option_value === condition_data.string) {
                    condition = true;
                }
                break;
            case 'not-like':
                if (option_value !== condition_data.string) {
                    condition = true;
                }
                break;
            case 'contains':
                if (option_value.indexOf(condition_data.string) !== -1) {
                    condition = true;
                }
                break;
            case 'not contains':
                if (option_value.indexOf(condition_data.string) < 0) {
                    condition = true;
                }
                break;
            case 'bigger as':
                if (parseFloat(option_value) > parseFloat(condition_data.string)) {
                    condition = true;
                }
                break;
            case 'smaller as':
                if (parseFloat(option_value) < parseFloat(condition_data.string)) {
                    condition = true;
                }
                break;
        }

        return condition;
    }

    function getExistedTagByName(tag) {
        var existedTags = $('#tags-cloud-options').data('tags-cloud');
        var tagLower = tag.toLowerCase();

        for (var tag_id in existedTags) {
            var existedTagLower = existedTags[tag_id].toLowerCase();

            if ($.trim(tagLower) === $.trim(existedTagLower)) {

                var data = {
                    id: tag_id,
                    text: tag
                };

                return data;
            }
        }

        return false;
    }

    function getExistedTag (tag_text) {

        var tag = $('.tags-cloud input[value="' + tag_text + '"]');

        if (tag.length > 0) {
            return tag;
        }

        return false;
    }

    function createNewTag(tag_text) {
        var input = $('.create-tag-wrapper input');

        input.val(tag_text);
        createNotAprovedTag(tag_text);

        return getExistedTag(tag_text);
    }

    function createNotAprovedTag(tag_text) {
        var cloud = $('.tags-cloud');
        var fieldset = $('<fieldset>');
        var input = $('<input type="checkbox" value="' + tag_text + '">');
        var label = $('<label>' + tag_text + '</label>');

        fieldset.append(input, label);
        cloud.append(fieldset);
    }

    function getSeparartors(table_column) {
        var separators = $('.separators-list .separator-wrapper');
        var found_separators = [];

        $.each(separators, function (index, item) {
            var separator = $(item);
            var selected_options = separator.find('.options_where option:selected');

            $.each(selected_options, function (key, option) {
                if ($(option).val() === table_column) {
                    found_separators.push(separator.find('input[name="separator"]').val());
                }
            });
        });

        return found_separators;
    }

    function multiSplit(str, delimiter) {
        var str_type = typeof str;

        if ('number' === str_type) {
            str = str.toString();
        }

        if (!(delimiter instanceof Array)) {
            return str.split(delimiter);
        }

        if (!delimiter || delimiter.length == 0) {
            return [str];
        }

        var hashSet = new Set(delimiter);

        if (hashSet.has("")) {
            return str.split("");
        }

        var lastIndex = 0;
        var result = [];

        for(var i = 0;i<str.length;i++) {
            if (hashSet.has(str[i])){
                result.push(str.substring(lastIndex,i).trim());
                lastIndex = i+1;
            }
        }

        result.push(str.substring(lastIndex).trim());

        return result;
    }

    function prepareDataForTransfer(map_id) {
        var current_map = JSON.stringify(compileMapToAPIObject());

        var data = {
            action: 'wp2l_prepare_data_for_klicktipp',
            map_id: map_id,
            map: current_map
        };

        $.post(
            ajaxurl,
            data,
            function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    var modal = $('.transfer-data-modal'),
                        noticeHolder = modal.find('.notice_holder'),
                        transfer_current_btn = $('#transferCurrent'),
                        transfer_all_bg_btn = $('#transferAllBg');

                    modal.find('.available-data .total').text(decoded.available_users);

                    // Statistics
                    modal.find('.last-transferred .total').text(decoded.last_transfered);
                    modal.find('.all-transferred .total').text(decoded.totally_all);
                    modal.find('.unique-transferred .total').text(decoded.totally_unique);
                    modal.find('.cron-local .total').text(decoded.last_transfered_cron);
                    modal.find('.cron-unix .total').text(decoded.last_transfered_cron_unix);

                    if ( decoded.notice ) {
                        noticeHolder.html(decoded.notice);
                    }

                    if (0 === decoded.available_users) {
                        transfer_current_btn.prop('disabled', true);
                        transfer_all_bg_btn.prop('disabled', true);
                    } else {

                    }

                    unblockTransferModal();
                } else {
                    alert(wp2leads_i18n_get('Something went wrong.'));

                    unblockTransferModal();
                }
            }
        );
    }

    function detachSubscriberFromTag(tag_id, tag_wrapper) {
        $('.accordion-body.api-processing-holder .api-spinner-holder').addClass('api-processing');
        var email = '';
        var emailField = $('.api-fields-wrapper #api_email .token > span', document.body);

        if (emailField.length === 1) {
            email = $('.api-fields-wrapper #api_email .token > span', document.body).data('value');
        } else if (emailField.length > 1) {
            email = [];

            emailField.each(function() {
                email.push($(this).data('value'));
            });
        }

        var data = {
            action: 'wp2l_detach_from_tag',
            tag_id: tag_id,
            email: email
        };

        $.post(
            ajaxurl,
            data,
            function (response) {
                var decoded = $.parseJSON(response);

                if (decoded.success) {
                    var index = window.currentUserKTTag.indexOf(tag_id);

                    if (index > -1) {
                        window.currentUserKTTag.splice(index, 1);
                    }

                    tag_wrapper.remove();

                    loadPossibleTagsCloud(email, function() {
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                    });
                } else {
                    existed_tags_wrapper.text(wp2leads_i18n_get('Something went wrong.'));

                    loadPossibleTagsCloud(email, function() {
                        $('.accordion-body.api-processing-holder .api-spinner-holder').removeClass('api-processing');
                    });
                }
            }
        );
    }

    function updatePossibleTags() {

        return false;

        var saved_conditions = $('.tags-conditions-wrapper .conditions-list').data('saved-value');

        $('.selected-tags-wrapper').empty();
        $(".tags-cloud input:checked").prop("checked", false);

        $.each(saved_conditions, function (index, condition_data) {
            selectTagsFromConnectedOptions(condition_data);
        });

        selectTags();
    }

    function preventLoosingData() {
        var active_tab_name = $.trim($('.nav-tab-active').text());
        var isSameVersion = true;
        var saved_map = $('input.mapping').val();
        var current_map = '';
        var map_builder_form = $('#map-generator');
        var api_settings_form = $('.map2api_body');

        if (api_settings_form.is(':visible') || map_builder_form.is(':visible')) {
            if ('Map to API' === active_tab_name) {
                current_map = JSON.stringify(compileMapToAPIObject());
            } else {
                return true;
            }

            if (saved_map !== current_map) {
                isSameVersion = false;
            }
        }

        return isSameVersion;
    }

    function blockTransferModal() {
        blockElement($('.transfer-data-modal .main-wrapper'));
        blockElement($('.transfer-data-modal .buttons-wrapper'));
    }

    function unblockTransferModal() {
        setTimeout(function () {
            unblockElement($('.transfer-data-modal .main-wrapper'));
            unblockElement($('.transfer-data-modal .buttons-wrapper'));
        }, 200);
    }

    /**
     * Block element
     * @param el
     */
    function blockElement(el) {
        if ( ! isElementBlocked( el ) ) {
            el.addClass( 'processing' ).block( {
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            } );
        }
    }

    function unblockElement(el) {
        el.removeClass( 'processing' ).unblock();
    }

    function isElementBlocked(el) {
        return el.is( '.processing' ) || el.parents( '.processing' ).length;
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
