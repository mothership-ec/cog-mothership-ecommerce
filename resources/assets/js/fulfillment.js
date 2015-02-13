$(function() {
	$('body').on('click', 'a.dispatch-automatically', function() {
		var self = $(this);

		$.ajax({
			url: self.attr('href'),
			dataType: 'json',
			beforeSend: function(data) {
				$('html').addClass('loading');
			},
			complete: function() {
				$('html').removeClass('loading');
			},
			success: function(data) {
				// Print label data
				if (typeof data.downloadUrl === 'string') {
					window.location.href = data.downloadUrl;
				}
			}
		});

		return false;
	});

	// Registering "select all" checkbox functionality
	bindSelectAllToggles();

	$('[data-live-pane]').on('ms.cp.livePane.change', function() {
		bindSelectAllToggles();
	});
});

function bindSelectAllToggles()
{
	$('div.fulfillment form[data-select-all] table').each(function() {
		$('<button class="toggle button small" />').selectAllToggle({
			inputs: $(this).find('input[type=checkbox]'),
		}).insertAfter($(this));
	});
}