jQuery(document).ready(function ($) {
	var url = $('#ltp-admin-settings-form').data('admin-url');
	var nonce = $('#ltp-admin-settings-form').data('nonce');
	var types = {
		class: 'Class',
		id: 'ID',
		selector: 'Selector'
	};

	$('#ltp-admin-settings-form').submit(function (e) {
		e.preventDefault();
		$('#ltp-admin-settings-form-save').attr('disabled', 'disabled').text('Saving...');

		var selector = $('#ltp-selector').val();
		var type = $('#ltp-type').val();
		$.ajax({
			type: 'POST',
			url: url,
			data: {
				action: 'link_to_popup_save_selector',
				nonce: nonce,
				selector: selector,
				type: type
			},
			dataType: 'json',
			success: function (response) {
				$('#ltp-admin-settings-form-save').removeAttr('disabled').text('Save');
				$('#ltp-admin-message').text(response.message);
				if (response.status == 'error') {
					$('#ltp-admin-message').removeClass('ltp-admin-message-success').addClass('ltp-admin-message-error').show();
				} else if (response.status == 'success') {
					$('#ltp-admin-message').removeClass('ltp-admin-message-error').addClass('ltp-admin-message-success').show();

					if ($('#ltp-admin-table tbody tr .ltp-text-center').length > 0) {
						$('#ltp-admin-table tbody tr').remove();
					}

					$('#ltp-admin-table tbody').append([
						'<tr>',
							'<td>' + selector + '</td>',
							'<td>' + types[type] + '</td>',
							'<td><button class="button ltp-delete" data-id="' + response.data.id + '">Delete</button></td>',
						'</tr>'
					].join(''));
					$('#ltp-selector').val('');
				}
			}
		});
	});

	$('body').on('click', '.ltp-delete', function () {
		var self = this;
		$(this).attr('disabled', 'disabled').text('Deleting...');
		var id = $(this).data('id');
		$.ajax({
			type: 'POST',
			url: url,
			data: {
				action: 'link_to_popup_remove_selector',
				nonce: nonce,
				id: id
			},
			dataType: 'json',
			success: function (response) {
				$('#ltp-admin-message').text(response.message);
				if (response.status == 'error') {
					$('#ltp-admin-message').removeClass('ltp-admin-message-success').addClass('ltp-admin-message-error').show();
				} else if (response.status == 'success') {
					$('#ltp-admin-message').removeClass('ltp-admin-message-error').addClass('ltp-admin-message-success').show();
					$(self).closest('tr').remove();

					if ($('#ltp-admin-table tbody tr').length == 0) {
						$('#ltp-admin-table tbody').append('<tr><td colspan="3" class="ltp-text-center">No data</td></tr>');
					}
				}
			}
		});
	});
});