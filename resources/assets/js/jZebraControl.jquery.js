/**
 * "jZebra Control" jQuery Plugin
 *
 * Provides a simple interface to the jZebra java applet.
 *
 * This plugin is a singleton global plugin, and does not work on elements but
 * rather globally for the entire page.
 *
 * <code>
 * $.jZebraControl.configure({printerName: 'myPrinter'});
 *
 * $.jZebraControl.append('testing');
 *
 * $.jZebraControl.print();
 * </code>
 *
 * This is a *private* plugin, and should only be used by Message Digital Design.
 *
 * @author Message Digital Design <dev@message.co.uk>
 * @author Joe Holdcroft <joe@message.co.uk>
 */
;(function( $ ){
	var settings = {
		path         : '/cogules/Message:Mothership:Ecommerce/jar/jzebra.jar',
		printerName  : null,
		errorCallback: function(error) {
			console.error(error);
		}
	};

	var control;

	var ready = false;

	$.jZebraControl = {

		configure: function(options) {
			settings = $.extend({}, settings, options);
		},

		init: function() {
			// Quit if jZebra is already initialised
			if (typeof control !== 'undefined') {
				return true;
			}

			// Private function for waiting for jZebra to find the printer
			function monitorFinding() {
				try {
					if (!control) {
						throw 'Applet not loaded - please ensure you have allowed the Java applet jZebra to run on this machine.';
					}

					if (!control.isDoneFinding()) {
						window.setTimeout(function() { monitorFinding(); }, 100);
					}
					else {
						if (control.getPrinter() === null) {
							throw 'Thermal printer not found - please check all connections and drivers and then refresh this page.';
						}

						ready = true;
					}
				}
				catch (err) {
					throw 'Thermal printer failed to initialise: "' + err.message + '". Please refresh this page and try again.';
				}
			}

			// Add Java applet to DOM
			$('body').append(
				$('<applet name="jzebra" code="jzebra.PrintApplet.class" width="0" height="0"></applet>')
					.attr('archive', settings.path)
			);

			// Set the applet control
			control = document.jzebra;

			// Tell jZebra to find the printer
			control.findPrinter(settings.printerName);

			// Monitor the finding of the printer & handle errors
			try {
				monitorFinding();
			}
			catch (err) {
				settings.errorCallback(err);

				return false;
			}

			// Return true if we're good to go
			return true;
		},

		append: function(buffer) {
			if (!ready) {
				return false;
			}

			if (typeof buffer === 'object') {
				for (i in buffer) {
					control.append(buffer[i]);
				}
			}
			else {
				control.append(buffer);
			}
		},

		print: function(line) {
			if (!ready) {
				return false;
			}

			control.print();
		}
	};
})(jQuery);