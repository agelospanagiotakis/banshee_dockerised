function set_authenticator_code() {
	$.get('/register', function(data) {
		$('input#secret').val($(data).find('secret').text());
	});
}

function add_delay_warning() {
	$('body').append('<div class="creating"><div>Creating your account. This may take a few seconds. Please wait...</div></div>');

	$('input.submit').on('click', function() {
		$('div.creating').css('display', 'block');
	});
}
