jQuery(document).ready(function($) {
	if ($('#link-to-popup-ajax').length > 0) {
		var adminUrl = $('#link-to-popup-ajax').data('admin-url');
		var nonce = $('#link-to-popup-ajax').data('nonce');
		$.ajax({
			type: 'GET',
			url: adminUrl,
			data: {
				action: 'link_to_popup_get_selectors',
				nonce: nonce
			},
			dataType: 'json',
			success: function (response) {
				if (response.data.selectors) {
					var selectors = response.data.selectors.join(', ');
					$(selectors).magnificPopup({
						type: 'iframe'
					});
				}
			}
		});
	}
});