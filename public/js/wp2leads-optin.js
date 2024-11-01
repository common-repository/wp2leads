"use strict";

(function ($) { $(document).ready(function () {
    /** Functions to handle opt-in processes */
	
	$(document).on('wpcf7submit', (event) => {
		console.log(event);
		
		if (event.detail.status == "mail_sent") {
			// check redirect for optin process 
			let data = {
				action: 'wp2leads_cf7_redirects',
				contactFormId: event.detail.contactFormId,
				inputs: event.detail.inputs
			}
			
			$.post(wp2leadsOptinAjax.url, data, function(response) {
				if (response) {
					location.href = response;
				}
			});
		}
	});
}); })(jQuery);
