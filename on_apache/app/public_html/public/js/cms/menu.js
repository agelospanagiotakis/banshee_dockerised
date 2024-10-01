$(window).ready(function() {
	$("form > ul").menuEditor();

	button = $("form input.insert").detach();
	$("form div.btn-group").append(button);

	$('div.pages > div').each(function() {
		var url = $(this).find('div div:last-child').text();
		if ($('ul.menu-editor li input.form-control:nth-child(2)[value="' + url + '"]').length > 0) {
			$(this).hide();
		}
	});
});

function page_click(page) {
	$('ul.menu-editor li:last-child span input.btn:first-child').trigger('click');
	$('ul.menu-editor li:last-child input.form-control:first-child').val($(page).find('div:first-child').text());
	$('ul.menu-editor li:last-child input.form-control:nth-child(2)').val($(page).find('div:last-child').text());
	$(page).parent().hide();
}
