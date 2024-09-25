$(document).ready(function() {
	var form = $('form').windowframe({
		activator: 'button.form_dialog',
		header: 'My form',
		footer: 'Some footer',
		buttons: {
			'Submit': function() {
				alert('You said: ' + $('input.form-control').val());
				$(this).close();
			},
			'Cancel': function() {
				$(this).close();
			},
			'Reset': function() {
				$(this).reset();
			},
			'Reset & close': function() {
				$(this).close();
				$(this).reset();
			},
			'Message dialog': function () {
				message.open();
			}
		},
		close: function() {
			if (confirm('Close?') == false) {
				return false;
			}

			console.log('Dialog closed');
		}
	});

	var message = $('<p>Hello world!</p>').windowframe({
		activator: 'button.message_dialog',
		width: 400,
		style: 'danger',
		header: 'Message of the day',
		info: 'Some information about this window'
	});

	message.open();
});
