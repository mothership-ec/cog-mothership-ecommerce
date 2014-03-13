$(function() {
	// var addressCheckbox, sameAddress;

	// addressCheckbox = $('#checkout_addresses_deliverToDifferent');
	// sameAddress = true;

	// // Loop each billing input and compare it's value to the equivalent
	// // delivery input.
	// $(':input[name*="checkout_addresses[billing]"]').each(function()
	// {
	// 	var billingInput = $(this), name, deliveryName, deliveryInput;
	// 	name = billingInput.attr('name');
	// 	deliveryName = billingInput.attr('name').replace('billing', 'delivery');
	// 	deliveryInput = $(':input[name="' + deliveryName + '"]');

	// 	if (billingInput.val() != deliveryInput.val()) {
	// 		sameAddress = false;
	// 	}
	// });

	// // If the checkbox is wrong compared to the addresses being the same,
	// // trigger the toggle to fix it.
	// if ((sameAddress && addressCheckbox.is(':checked')) ||
	// 	(! sameAddress && ! addressCheckbox.is(':checked'))
	// ) {
	// 	addressCheckbox.prop('checked', ! addressCheckbox.is(':checked'));
	// }

	// Checkbox toggle switch
	$('input[type=checkbox][data-toggle]').on('change.toggle', function()
	{
		var self     = $(this),
			target   = $(self.attr('data-toggle')),
			inverted = "true" == self.attr('data-toggle-inverted'),
			checked  = self.is(':checked');

		if ((checked && ! inverted) || (! checked && inverted)) {
			target.slideUp();
		} else {
			target.slideDown();
		}

		if ((checked && ! inverted) || (! checked && inverted)) {
			target.find(':input').each(function() {
				var t = $(this);
				if (t.attr('required')) {
					t.removeAttr('required').attr('data-toggle-required', true);
				}
			});
		} else {
			target.find(':input').each(function() {
				var t = $(this);
				if (t.attr('data-toggle-required')) {
					t.removeAttr('data-toggle-required').attr('required', true);
				}
			});
		}
	}).trigger('change.toggle');

	// Trigger the change a second time on inverted toggle inputs to ensure it
	// is run and run twice.
	$('input[type=checkbox][data-toggle-inverted]').trigger('change.toggle');
});