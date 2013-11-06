$(function() {
	$('input[type=checkbox][data-toggle]').on('change.toggle', function() {
		var self   = $(this),
			target = $(self.attr('data-toggle'));

		if (self.is(':checked')) {
			target.slideUp();
		} else {
			target.slideDown();
		}
	}).trigger('change.toggle');
});