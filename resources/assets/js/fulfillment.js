$(function() {

	$.jZebraControl.configure({
		path : '/cogules/Message:Mothership:Ecommerce/jar/jzebra.jar',
		error: function(err) {
			alert(err);
		}
	});

	$('a.dispatch-automatically').click(function() {
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
});