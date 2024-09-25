var ckeditor = null;
var image_selector = null;

function ckeditor_started(editor) {
	ckeditor = editor;
	ckeditor_add_insert_image_button('select_image');
}

function select_image() {
	$('div.images').empty();

	$.ajax('/cms/page').done(function(data) {
		$(data).find('images').each(function() {
			var dir = $(this).attr('dir');
			$('div.images').append('<h3>Images in /' + dir + ':</h3>');
			$(this).find('image').each(function() {
				var image = '<div class="image" onClick="javascript:insert_image(this)"><img src="/' + dir + '/' + $(this).text() + '" /></div>';
				$('div.images').append(image);
			});
		});

		image_selector.open();
		$('input.btn').blur();
	});
}

function insert_image(image) {
	var image_src = $(image).find('img').attr('src');
	close_image_dialog();

	if ((typeof ckeditor == 'undefined') || (ckeditor == null)) {
		var image_tag = '<img src="' + image_src + '" />';
		var editor = document.getElementById('editor');
		var start = editor.selectionStart;
		var end = editor.selectionEnd;

		var text = $(editor).val();
		$(editor).val(text.substring(0, start) + image_tag + text.substring(end));
	} else {
		ckeditor.execute('imageInsert', { source: image_src } );
	}
}

function close_image_dialog() {
	image_selector.close();
}

$(document).ready(function() {
	if (typeof ClassicEditor == 'undefined') {
		$('div.btn-group').first().find('input:first-of-type').after('<input id="select_image_button" type="button" value="Insert image" class="btn btn-default" onClick="javascript:select_image()" />');
	}

	image_selector = $('<div class="images"></div>').windowframe({
		top: 50,
		width: 830,
		style: 'primary',
		header: 'Select image'
	});
});
