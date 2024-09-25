function implode_filenames(files) {
	var names = [];

	for (i = 0; i < files.length; i++) {
		names.push(files[i].name);
	}

	return names.join(', ');
}

$(document).ready(function() {
	$("#sortable").sortable({
		stop: function(event, ui) {
			var id = ui.item.attr('id').substring(1);
			var pos = ui.item.index();

			$.post("/cms/photo/move", {
				photo_id: id,
				position: pos
			}).always(function(data) {
				if ($(data).find("result").text() != "ok") {
					alert("Repositioning failed.");
				}
			});
		}
	});
});
