function fill_flags_pulldown(data) {
	$(data).find("flags flag").each(function() {
		var flag = $(this).text();

		$("form select#flag").append("<option>" + flag + "</option>");
	});
}

function get_flags() {
	$("form select#flag").empty();

	var module = $("form select#module option").filter(":selected").text();
	$.get("/cms/flag", "module=" + module, fill_flags_pulldown);
}

$(document).ready(function() {
	$("form select#module").change(get_flags);
	if ($("form select#flag option").length == 0) {
		get_flags();
	}
});
