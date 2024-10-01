$(document).ready(function() {
	var message = $('div.banshee-internal-error').windowframe({
		width: 800,
		top: '50px',
		style: 'danger',
		header: 'Internal error',
	});

	message.open();
});
