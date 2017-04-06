$(function () {
	// Submit the selection form when input values are changed
	$('form#checkout-selection-form').on('change.ms_checkout', 'input', function () {
		var self = $(this);

		if (0 === self.val()) {
			self.closest('tr').find('td.remove a').click();
		} else {
			self.parents('form').submit();
		}
	});

	$('form#checkout-selection-form').on('submit.ms_checkout', function () {
		var self = $(this);

		$('[data-checkout-live-update]').addClass('loading-basket');

		$.ajax({
			url: self.attr('action'),
			data: self.serialize(),
			method: 'POST',
			dataType: 'html',
			success: function (data) {
				$('[data-checkout-live-update]').removeClass('loading-basket');
				checkoutUpdateTotals(data);
			},
		});

		return false;
	});

	$('form#checkout-selection-form td.remove a').on('click.ms_checkout', function () {
		var self = $(this),
			row = self.closest('tr')
			;

		$.get(self.attr('href'), function (data) {
			row.trigger('remove.ms_basket');

			if ($('form#checkout-selection-form table tbody tr').length == 0) {
				window.location.href = '/checkout/empty';

				return false;
			}

			if (row.is('visible')) {
				row.fadeOut();
			}

			checkoutUpdateTotals(data);
		});

		return false;
	});

	if ($('#shipping_option option').size() > 1) {
		$('form#delivery-method-form button[type=submit]').hide();
	}

	$('form#delivery-method-form select').on('change', function () {
		var form = $('form#delivery-method-form'),
			submitBtn = form.children('button[type=submit]'),
			continueBtn = $('button.continue')
			;

		form.removeClass('error').addClass('loading-delivery');
		continueBtn.attr('disabled', 'disabled');

		$.ajax({
			url: form.attr('action'),
			data: form.serialize(),
			method: 'POST',
			dataType: 'html',
			success: function (data) {
				form.removeClass('loading-delivery');
				checkoutUpdateTotals(data);
				continueBtn.removeAttr('disabled');
			},
			error: function (data) {
				form.addClass('error').removeClass('loading-delivery');
				continueBtn.removeAttr('disabled');
			}
		});

	});
});

function checkoutUpdateTotals(data) {
	$('[data-checkout-live-update]').each(function () {
		if ($(this).is('input')) {
			$(this).val($($(this).getPath(), data).attr('value'));
		}

		$(this).html($($(this).getPath(), data).html()).trigger('change.ms_basket');
	});

	$('form#checkout-selection-form .price').each(function() {
		$(this).html($($(this).getPath(), data).html()).trigger('change.ms_basket');
	});
}