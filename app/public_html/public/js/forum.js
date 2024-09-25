function click_anchor(obj) {
	document.location = $(obj).find('a').attr('href');
}

function show_smiley(smiley) {
	textarea = document.getElementById("content");
	pos = textarea.selectionStart + smiley.length + 1;

	text  = textarea.value.substring(0, textarea.selectionStart);
	text += " " + smiley;
	text += textarea.value.substring(textarea.selectionEnd);
	textarea.value = text;

	textarea.setSelectionRange(pos, pos);
	textarea.focus();
}

function edit_message(button, message_id) {
	$('form.update input.cancel').on('click', );

	$.ajax('/forum/message/' + message_id).done(function(data) {
		var content = $(data).find('content').text();
		var panel = $(button).parent().parent();
		panel.find('img.avatar').hide();
		panel.find('div.message').hide();
		panel.find('div.edit').hide();

		var form =
			'<textarea id="message" name="message" class="form-control">' + content + '</textarea>' +
			'<div class="btn-group">' +
			'<input type="button" value="Save" class="btn btn-default" onClick="javascript:save_message(this, ' + message_id + ')" />' +
			'<input type="button" value="Delete" class="btn btn-default" onClick="javascript:delete_message(this, ' + message_id + ')" />' +
			'<input type="button" value="Cancel" class="btn btn-default cancel" onClick="javascript:hide_edit(this)" />' +
			'</div>';

		var user = panel.parent().find('span.unregistered');
		if (user.length == 1) {
			form = '<input id="username" name="username" value="' + user.text() + '" class="form-control" />' + form;
		}

		panel.prepend('<form class="update">' + form + '</form>');
	}).fail(function(data) {
		alert('Error');
	});
}

function preview_message(button) {
	var form = $(button).parent().parent();

	$.post('/forum', {
		submit_button: $(button).val(),
		message: $(form).find('textarea').val()
	}).done(function(data) {
		var message = $(data).find('message').text();

		var preview = $('div.preview');
		preview.css('display', 'block');
		preview.find('div.panel-body').html(message);
	}).fail(function(data) {
		alert('Error');
	});
}

function save_message(button, message_id) {
	var form = $(button).parent().parent();
	
	$.post('/forum/message', {
		submit_button: $(button).val(),
		username: form.find('input#username').val(),
		message_id: message_id,
		message: form.find('textarea#message').val()
	}).done(function(data) {
		var message = $(data).find('message').text();
		form.parent().find('div.message').html(message);

		var user = form.parent().parent().find('span.unregistered');
		if (user.length == 1) {
			user.text(form.find('input#username').val());
		}

		hide_edit(button);
	}).fail(function(data) {
		alert('Error');
	});
}

function delete_message(button, message_id) {
	if (confirm('DELETE: Are you sure?') == false) {
		return;
	}

	$.post('/forum/message', {
		submit_button: $(button).val(),
		message_id: message_id
	}).done(function(data) {
		var form = $(button).parent().parent();
		form.parent().parent().remove();
	}).fail(function(data) {
		alert('Error');
	});
}

function hide_edit(button) {
	var panel = $(button).parent().parent().parent();
	panel.find('img.avatar').show();
	panel.find('div.message').show();
	panel.find('div.edit').show();
	panel.find('form').remove();
}
