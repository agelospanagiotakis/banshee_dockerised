function show_categories() {
	$('div.toggle').hide();
	$('div.categories').show();
}

function add_to_cart(article_id) {
	$.get('/webshop/cart/add/' + article_id, function(data) {
		if ((result = $(data).find('result').text()) == 'ok') {
			$('div.cart span').text($(data).find('quantity').text());
			$.notify('Added to shopping cart.', 'success');
		} else if (result == '') {
			$.notify('Internal error', 'error');
		} else {
			$.notify(result, 'error');
		}
	});
}
