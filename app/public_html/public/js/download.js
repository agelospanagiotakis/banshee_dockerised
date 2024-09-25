function toggle_trunk(arrow) {
	var src = $(arrow).attr('src');
	$(arrow).attr('src', src.substring(0, 13) + (1 - parseInt(src.substring(13, 14))) + src.substring(14));

	var trunk = $(arrow).parent().parent().next();
	if ($(trunk).hasClass('trunk')) {
		$(trunk).toggle();
	}
}

function click_anchor(obj) {
	document.location = $(obj).find('a').attr('href');
}
