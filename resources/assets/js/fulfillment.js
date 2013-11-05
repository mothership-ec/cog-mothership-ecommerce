$(function() {
	$.jZebraControl.configure({
		path : '/cogules/Message:Mothership:Ecommerce/java/qz-print_jnlp.jnlp',
		error: function(err) {
			alert(err);
		}
	});

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
				if (typeof data.labelData !== 'undefined') {
					if (typeof data.labelData === 'string') {
						$.jZebraControl.append(data.labelData.split("\n"));
					}
					else {
						$.jZebraControl.append(data.labelData);
					}

					$.jZebraControl.print();
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