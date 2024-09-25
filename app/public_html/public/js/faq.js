function toggle_item(id) {
	$('div.panel-body').slideUp('normal');

	if ($('div#faq'+id).is(':visible') == false) {
		$('div#faq'+id).slideToggle('normal');
	}
}
