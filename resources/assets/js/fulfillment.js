$(function() {
	$('a.dispatch-automatically').click(function() {
		var self = $(this);

		$.ajax({
			url: self.attr('href'),
			dataType: 'json',
			beforeSend: function(data) {
				$('html').addClass('loading');
			},
			complete: function(data) {
				$('html').removeClass('loading');
				console.log(data);
			}
		});

		return false;
	});
});