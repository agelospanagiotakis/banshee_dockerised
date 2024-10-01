$(document).ready(function() {
	$("ul.pagination").quickPagination({ pageSize:"4" });

	var input = document.getElementById("query");
	input.selectionStart = input.selectionEnd = input.value.length;
});
