$(function() {
	// Submit the selection form when input values are changed
	$('form#checkout-selection-form input').on('change.ms_checkout', function() {
		var self = $(this);

		if (0 == self.val()) {
			self.parents('tr').find('td.remove a').click();
		} else {
			self.parents('form').submit();
		}
	});

	$('form#checkout-selection-form').on('submit.ms_checkout', function() {
		var self = $(this);

		$.ajax({
			url     : self.attr('action'),
			data    : self.serialize(),
			method  : 'POST',
			dataType: 'html',
			success : function(data) {
				$('[data-checkout-live-update]').each(function() {
					$(this).html($($(this).getPath(), data).html()).trigger('change.ms_basket');
				});
			},
			// complete: function(data) {
			// 	console.log(data);
			// 	// why is only complete firing?!?!?!
			// },
		});

		return false;
	});

	$('form#checkout-selection-form td.remove a').on('click.ms_checkout', function() {
		var self = $(this),
			row  = self.parents('tr');

		$.get(self.attr('href'), function() {
			row.trigger('remove.ms_basket');

			if (row.is('visible')) {
				row.fadeOut();
			}
		});

		return false;
	});
});