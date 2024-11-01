(function ($) {
	$(document).ready(function () {

		// init steps
		var currentStep = 0;
		var localMapId = $('#mapTagPrefix').data('id');
		var startup = false;

		if (localStorage.getItem('wizard-'+localMapId)) {
			currentStep = localStorage.getItem('wizard-'+localMapId);
		} else if ( $('#start_step').length ) {
			currentStep = parseInt($('#start_step').val());
		}

		// current step looks like: {step : 1, data : {} }
		if (currentStep < 50) {
			$('#disableWizard, #skipStep').show();
		}

		function runWizardStep( stop ) {
			if ( typeof stop == 'undefined' ) stop = false;

			$('.active-wz').removeClass('active-wz');
			$('.skip-btn').hide();
			$autorun = false;

			if (currentStep == 0 || currentStep == 1) {  // check tag prefix, next step on blur on
				currentStep = 1;

				if ($('#noInitSettings').length) {
					// first fire of the wizard
					if ($('#mapTagPrefix').val()) {
						currentStep++;
						$autorun = true;
					} else {
						$('#mapTagPrefix').addClass('active-wz');
						scrollToEl($('#map-to-api__body'));
						$('#mapTagPrefix').tooltip();
						$('#mapTagPrefix').tooltip( 'open' );
					}
				}
			}

			if (currentStep == 2) { // hightlight recommendedTagsCloud, tags will be added automatically, select all
				// recommend tags
				if ($('#recommendedTagsCloud').find('fieldset').length) {
					setTimeout(function() { scrollToEl($('#recommendedTagsCloud').parent()); }, 100);
					$('#selectRecommendedTags').click();
					$('#createRecommendedTags').addClass('active-wz');
					$('#skipRecommendedTags').show();
				} else {
					currentStep = 2.5;
					$autorun = true;
				}
			}

			if (currentStep == 2.5) {
				// recommend tags
				if ($('.change-magic-replacements').length) {
					setTimeout(function() { scrollToEl($('.change-magic-replacements').parent()); }, 100);
					$('.change-magic-replacements').click();
					$('.change-magic-replacements').addClass('active-wz');
				} else {
					currentStep = 3;
					$autorun = true;
				}
			}

			if (currentStep == 3) {
				// recommend tags
				var curr_tag = $('.map2api_side').find('.recommended_user_input_tags_cloud-container.wz');

				if (curr_tag.length < 1) {
					curr_tag = $('.map2api_side').find('.recommended_user_input_tags_cloud-container');
				}

				if (curr_tag.length < 1) {
					$('.wz').removeClass('wz');
					currentStep = 5;
					$autorun = true;
				} else {
					curr_tag = curr_tag.eq(0);
					curr_tag.addClass('wz');
					curr_tag.find('.skip-btn').show();

					scrollToEl(curr_tag);

					if (curr_tag.find('fieldset').length) {
						if (curr_tag.find('.select-user-input-tags').length) curr_tag.find('.select-user-input-tags').click();
						if (curr_tag.find('.select-user-input-tags').length) curr_tag.find('.create-user-input-tags').addClass('active-wz');
					} else {
						if (curr_tag.find('.get-user-input-tags-results').length) curr_tag.find('.get-user-input-tags-results').addClass('active-wz');
					}
				}
			}

			if (currentStep == 4) {
				// check recommend tags and go step earlier if there are more recommend tags clouds
				var curr_tag = $('.map2api_side').find('.recommended_user_input_tags_cloud-container.wz');

				if (curr_tag.length > 0) {
					if (curr_tag.next().next().length > 0) {
						curr_tag.removeClass('wz');
						curr_tag.next().next().addClass('wz');
						currentStep = 3;
					} else {
						currentStep = 5;
					}
				} else {
					currentStep = 3;
				}

				$autorun = true;
			}

			if (currentStep == 5) {
				if ($('.map-to-api-kt-link').length) {
					$('.map-to-api-kt-link').addClass('active-wz');
					scrollToEl($('.map-to-api-kt-link').closest('.accordion-subbody').prev());

				} else {
					currentStep++;
					$autorun = true;
				}
			}

			if (currentStep == 6) {
				if ($('#apiFieldsInitialSettings__container').length) {
					scrollToEl($('#apiFieldsInitialSettings__container').parent().prev());
					// preselect fields
					$('#apiFieldsInitialSettings__container').find('.select_api_field_item').each(function(){
						var name = $(this).find('.select_api_field_name').text().trim();
						// delete tags from the name: get half of the string but not more than 10 symbols
						if (name.length > 20) {
							name = name.slice(-10);
						} else {
							var halflength = 0 - parseInt(name.length/2);
							name = name.slice(halflength);
						}

						var text = '';
						var val = '';
						var one = true;

						$(this).find('.select_api_field_to').find('option').each(function(){
							if (~$(this).text().indexOf(name)) {
								if (text) {
									one = false;
								} else {
									text = $(this).text();
									val = $(this).val()
								}
							}
						});

						if (one && text && val) {
							$(this).find('.select_api_field_to').tokenize2().trigger('tokenize:tokens:add', [val, text, true]);
						}
					});

					// find Datum + Zeit) not filled and made it green

					var timer = setInterval(function(){
						var all_filled = true;
						$('#apiFieldsInitialSettings__container').find('.select_api_field_to').each(function(){
							if ($(this).val()) {
								$(this).next().find('.tokens-container').removeClass('active-wz');
							} else {
								$(this).next().find('.tokens-container').addClass('active-wz');
								all_filled = false;
							}
						});

						if (all_filled) {
							// go to next step
							currentStep = 7;
							runWizardStep();
							clearInterval(timer);
						}
					}, 1000);

					setTimeout(function(){
						$('#apiFieldsInitialSettings__container').animate({
							scrollTop: $('#apiFieldsInitialSettings__container').find('.active-wz').closest('.select_api_field_item').position().top
						}, 100);
					}, 1200 );
				} else {
					currentStep++;
					$autorun = true;
				}
			}

			if (currentStep == 7) {
				if ($('.api-optins-wrapper').length) {
					scrollToEl($('.api-optins-wrapper').closest('.accordion-subbody').prev());

					$('.api-optins-wrapper').find('select').addClass('active-wz');
				} else {
					currentStep++;
					$autorun = true;
				}
			}

			if (currentStep == 8) {
				$('.wizard-complete-text').show();
				$('#saveInitialSettings').addClass('active-wz');
			}

			if (currentStep == 9) {
				const apiContainer = $('.api_fields_container');

				if (!apiContainer || apiContainer.hasClass('no-data-load')) {
					currentStep++;
					runWizardStep();
				} else {
					var emailField = $('.api-fields-wrapper #api_email .token > span', document.body);
					var mapping = null;
					var isEmailSetUp = false;
					var mappingInput = $('.map-to-api > .mapping');

					if (mappingInput) {
						mapping = $.parseJSON(mappingInput.val());

						if (
							mapping &&
							mapping.fields &&
							mapping.fields.api_email &&
							mapping.fields.api_email.table_columns &&
							mapping.fields.api_email.table_columns.length
						) {
							isEmailSetUp = true;
						}
					}

					if ( $('#btnTransferDataCurrent').length && isEmailSetUp) {
						window.wp2leadsStep9 = () => {
							console.log('Run Step 9 Script');

							scrollToEl($('#transfer-btn-holder').prev());
							setTimeout( function(){
								window.btnTransferDataCurrentInitial = true;
								$('#btnTransferDataCurrent').click();
								setTimeout( function(){
									$('#transferCurrent').addClass('active-wz');
								}, 1000 );
							}, 100 );
						}
					} else {
						currentStep++;
						runWizardStep();
					}
				}
			}

			// After reload the page
			if (currentStep == 10) {
				if ( $('#moduleStatus').length && $('#moduleStatus:visible').length ) {
					$('#moduleStatus').prop('checked', 'checked');
					$('#saveModuleSettings').addClass('active-wz');
					scrollToEl($('#saveModuleSettings'));
				} else {
					currentStep++;
					runWizardStep();
				}
			}

			// Here we try to show big Edit Form link
			if (currentStep == 11) {
				if ( $('#globalMapsList').find('tr.current a.wp2l-map-edit.button.button-small.button-small').length && $('#come_from').length == 0 ) {
					$('#globalMapsList').addClass('active');
					scrollToEl($('#globalMapsList'));

					setTimeout(function(){
						$('#globalMapsList').find('tr.current a.wp2l-map-edit.button.button-small.button-small').css({'transform' : 'scale(1.5)'});
					}, 300);
				} else {
					currentStep++;
				}
			}

			if (currentStep == 12) {
				if ( $('.map2api_body .rows_count').length && parseInt($('.map2api_body .rows_count').text()) > 1 && $('#btnTransferDataImmediately').length ) {
					$('#btnTransferDataImmediately').addClass('active-wz');
					scrollToEl($('#transfer-btn-holder'));
				} else {
					$('#btnUpdateMapToApi').addClass('active-wz');
					scrollToEl($('#btnUpdateMapToApi'));
					currentStep = 99;
				}
			}

			// end of the wizard
			if (currentStep > 50) {
				$('#disableWizard, #skipStep').remove();
			}

			localStorage.setItem('wizard-'+localMapId, currentStep);
			if ($autorun) {
				runWizardStep(stop);
			} else if ( ! startup ) {


				if ( $('.wp2leads-global-notice').length && $('.active-wz').length &&  $('.active-wz').closest('.accordion-body') .is(":visible") ) {
					startup = false;
					$('.active-wz').closest('.accordion-subbody').before( $('.wp2leads-global-notice') );
					scrollToEl($('.wp2leads-global-notice'));
				}
			}
		}

		runWizardStep();

		$('#disableWizard, .map-to-api-kt-imported-no').click(function(){
			currentStep = 99;
			runWizardStep();
		});

		$('#skipStep, .skip-btn').click(function(){
			$('.active-wz').removeClass('active-wz');

			if ( currentStep == 2 ) {
				currentStep = 2.5;
			} else if ( currentStep == 2.5 )  {
				currentStep = 3;
			} else {
				currentStep++;
			}
			runWizardStep();
		});

		// go out of the 1 step
		$('#mapTagPrefix').on('blur', function(){
			runWizardStep(); // here will be checked and turned on the next step
		});

		// go out of the 2 step
		$('body').on('createRecommendedTags', function(){
			if (currentStep >= 2 && currentStep < 50) {
				currentStep = 2.5;
				runWizardStep();
			}
		});

		$('body').on('magicSaved', function(){
			if (currentStep == 2.5) {
				currentStep = 3;
				runWizardStep();
			}
		});

		$('body').on('magicClosed', function(){
			if (currentStep == 2.5) {
				currentStep = 3;
				runWizardStep();
			}
		});

		$('body').on('magicShowed', function(){
			if (currentStep == 2.5) {
				$('#magicSave').addClass('active-wz');
			}
		});

		$('body').on('getUserInputTagsResults', function(){
			console.log('getUserInputTagsResults');
			if (currentStep == 3) {
				var button = $('.active-wz');
				if (button.parent().find('.button-primary').css('display') !== 'none') {

					button.removeClass('active-wz');
					button.parent().find('.button-primary').addClass('active-wz');
				} else {
					currentStep++;
					runWizardStep();
				}
			}
		});

		$('body').on('UserTagsCreated', function(){
			console.log('UserTagsCreated');
			if (currentStep > 1 && currentStep < 50) {
				currentStep++;
				runWizardStep( true );
			}
		});

		$('body').on('checkScroll5', function(){
			if (currentStep == 5 ) {
				scrollToEl($('.active-wz').closest('.accordion-subbody').prev());
			}
		});

		$('body').on('updateApiFieldsOptions', function(){
			if (currentStep == 5) {
				currentStep++;
				runWizardStep();
			}
		});

		$('.optins-list').click(function(){
			if (currentStep == 7) {
				currentStep++;
				runWizardStep();
			}
		});

		$('#saveInitialSettings').click(function(){
			if (currentStep <= 8) {
				currentStep = 9;
				localStorage.setItem('wizard-'+localMapId, currentStep);
			}
		});

		$('#saveModuleSettings').click(function(){
			$('.active-wz').removeClass('active-wz');
			currentStep++;
			runWizardStep();
		});

		$('body').on('transferCurrentToKlickTipp_finished', function(){
			if ( currentStep == 9 ) {
				$('.active-wz').removeClass('active-wz');
				currentStep = 9.5;
				$('.close, #transferCurrentClose').addClass('active-wz');
				$('#transferCurrentClose').show();
			}
		});

		$('body').on('click', '.close', function(){
			if ( currentStep == 9.5 || currentStep == 9 ) {
				$('.active-wz').removeClass('active-wz');
				currentStep = 10;
				runWizardStep();
			}
		});

		$('body').on('click', '#saveModuleSettings', function(){
			if ( currentStep == 10 ) {
				$('.active-wz').removeClass('active-wz');
				currentStep++;
				runWizardStep();
			}
		});


		$('body').on('transferDataImmediately_finish', function(){
			if ( currentStep == 12 ) {
				$('.active-wz').removeClass('active-wz');
				currentStep = 99;
				$('#btnUpdateMapToApi').addClass('active-wz');
				scrollToEl($('#btnUpdateMapToApi'));
			}
		});


		function scrollToEl($el) {
			$([document.documentElement, document.body]).stop().animate({
				scrollTop: $el.offset().top - 70
			}, 500);
		}

		function enablePanel() {
			$('#map-to-api__left-column').removeClass('panel-hidden');
			$('#map-to-api__left-column').addClass('panel-active');
		}

		function disablePanel() {
			$('#map-to-api__left-column').addClass('panel-hidden');
			$('#map-to-api__left-column').removeClass('panel-active');
		}

		$('.map-to-api-toggle-panel').click(function(){
			$('#map-to-api__left-column').toggleClass('panel-hidden');
			$('#map-to-api__left-column').toggleClass('panel-active');
		});

		$('body').on('shortly_show_db_panel', function(){
			if ($('#map-to-api__left-column').hasClass('panel-hidden')) {
				$('#map-to-api__left-column').toggleClass('panel-hidden');
				$('#map-to-api__left-column').toggleClass('panel-active');

				var timer = setTimeout(function(){
					$('#map-to-api__left-column').toggleClass('panel-hidden');
					$('#map-to-api__left-column').toggleClass('panel-active');
				}, 2000);

				$('.map-to-api-toggle-panel').one('click', function(){
					clearTimeout(timer);
				});
			}
		});



		// list of the elements where will be triggered showing panel

		$('body').on('click', '.api_field_body', function(){
			// $('body').trigger('shortly_show_db_panel');
		});
		window.onbeforeunload = null;
	});

})(jQuery);
