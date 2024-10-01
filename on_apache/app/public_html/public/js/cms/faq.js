$(document).ready(function() {
	set_section_type();
});

function set_section_type() {
	var type = $('input[name=select]:checked').val();
	if (type == 'new') {
		$('input#input_new').show();
		$('select#input_existing').hide();
	} else if (type == 'existing') {
		$('input#input_new').hide();
		$('select#input_existing').show();
	}
}
