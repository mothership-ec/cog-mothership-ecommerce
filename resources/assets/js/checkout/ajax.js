$(function() {
	$('form#checkout-selection-form').on('submit', function() {
		var self = $(this);

		$.ajax({
			url     : self.attr('action'),
			data    : self.serialize(),
			method  : 'POST',
			dataType: 'html',
			success : function(data) {
				console.log(data);
			},
			// complete: function(data) {
			// 	console.log(data);
			// 	// why is only complete firing?!?!?!
			// },
		});

		return false;
	});
});