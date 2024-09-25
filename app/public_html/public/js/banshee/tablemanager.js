$(document).ready(function() {
	$('table.tablemanager td.sort').on('click', function(event) {
		event.stopPropagation();
	});

	$('table.tablemanager tbody').sortable({
		handle: 'td.sort',
		helper: 'clone',
		update: function(event, ui) {
			var order = [];
			$('table.tablemanager tbody tr').each(function() {
				var number = $(this).attr('number');
				order.push(number);
			});

			$.post('', {order: order});
		}
	});
});
