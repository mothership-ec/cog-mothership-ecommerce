$(function() {
	$('input[type=checkbox][data-toggle]').on('change.toggle', function() {
		var self   = $(this),
			target = $(self.attr('data-toggle')),
			inverted = "true" == self.attr('data-toggle-inverted'),
			checked;

		checked = self.is(':checked');

		if ((checked && ! inverted) || (! checked && inverted)) {
			target.slideUp();
		} else {
			target.slideDown();
		}
	}).trigger('change.toggle');
});