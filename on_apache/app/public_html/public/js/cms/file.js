function click_anchor(obj) {
	document.location = $(obj).find('a').attr('href');
}

function rename_file(filename_current) {
	if ((filename_new = prompt('Rename ' + filename_current + ' to:', filename_current)) != null) {
		$.post('', {
			submit_button: 'Rename',
			filename_current: filename_current,
			filename_new: filename_new
		}).done(function(data) {
			$.each($('table.files tr td:nth-child(2) a'), function() {
				if ($(this).text() == filename_current) {
					$(this).text(filename_new);
					var parts = $(this).prop('href').split('/');
					var pos = (parts[parts.length - 1] == '') ? 2 : 1;
					parts[parts.length - pos] = encodeURIComponent(filename_new);
					$(this).prop('href', parts.join('/'));
				}
			});
		}).fail(function(data) {
			alert($(data.responseXML).find('result').text());
		});
	}
}

function delete_file(filename) {
	if (confirm('Delete ' + filename + '?')) {
		$.post('', {
			submit_button: 'Delete',
			filename: filename,
		}).done(function() {
			document.location = document.location;
		}).fail(function(data) {
			alert($(data.responseXML).find('result').text());
		});
	}
}

$(document).ready(function() {
	$.contextMenu({
		selector: 'table.files tr.alter',
		callback: function(key, options) {
			var file = $(this).find('td:nth-child(2)').text();

			switch (key) {
				case 'rename':
					rename_file(file);
					break;
				case 'delete':
					delete_file(file);
					break;
			}
		},
		items: {
			'rename': {name:'Rename', icon:'edit'},
			'delete': {name:'Delete', icon:'delete'}
		}
	});

	$('table.files tr').find('a').on('click', function(e) {
		e.stopPropagation();
	});
});
