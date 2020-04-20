(function($) {
	"use strict";

	$('.products').on('click', '.add-to-cart', function () {
		$.post('/order/add/', {product_id: $(this).data('product_id')}, function (resp) {
			let result = JSON.parse(resp);
			if (!result.error) {
				modalInformation(
					'Товар добавлен',
					'<p>Товар успешно добавлен</p>'
				)
			} else {
				modalInformation(
					'Прозошла ошибка.',
					'<p>К сожалению при отправке, произошла ошибка.</p>'
				)
			}

			modalToggle('modal-information');
		});
	});

	function modalInformation(title, body) {
		$('.modal-card-title').html(title);
		$('.modal-card-body').html(body);
	}

	function modalToggle(type) {
		$('.' + type).toggleClass('is-active');
		$('html').toggleClass('is-clipped');
	}

	$('.modal').on('click', '.delete', function (e) {
		let elem = $(e.target).data('target');
		$('.' + elem).toggleClass('is-active');
		$('html').toggleClass('is-clipped');
	});

})(jQuery);