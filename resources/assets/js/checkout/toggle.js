$(function() {
	$('input[type=checkbox][data-toggle]').on('change.toggle', function() {
		var self     = $(this),
			target   = $(self.attr('data-toggle')),
			inverted = "true" == self.attr('data-toggle-inverted'),
			checked;

		checked = self.is(':checked');

		if ((checked && ! inverted) || (! checked && inverted)) {
			target.slideUp();
		} else {
			target.slideDown();
		}
	}).trigger('change.toggle');

	// Trigger the change a second time on inverted toggle inputs to ensure it
	// is run and run twice.
	$('input[type=checkbox][data-toggle-inverted]').trigger('change.toggle');

	// On the register delivery toggle, add and remove the required attributes
	// of the related inputs
	$('#register_deliver_to_different').on('change.toggle', function() {
		var self     = $(this),
			target   = $('.checkout form .delivery :input'),
			inverted = "true" == self.attr('data-toggle-inverted'),
			checked;

		checked = self.is(':checked');

		if ((checked && ! inverted) || (! checked && inverted)) {
			target.removeAttr('required').attr('data-toggle-required', true);
		} else {
			target.removeAttr('data-toggle-required').attr('required', true);
		}
	});
});