(function ($) {
	'use strict';
	var UbeProgressHandler = function ($scope, $) {
		var $progressbar = $scope.find('.progress-bar');
		$.each($progressbar, function () {
			var settings = $(this).data("settings"),
				length = settings.progress_value,
				speed = settings.speed;
			$(this).css({"opacity": "1", "width": length + "%","transition-duration":speed +"ms"});
		});
	};

	var UbeProgressScrollWidgetHandler = {
		init : function ($scope, $) {
			if (!window.IntersectionObserver) {
				return;
			}
			var $progressbar = $scope.find('.progress-bar');
			$.each($progressbar, function () {
				const observer = UbeProgressScrollWidgetHandler.createObserver();
				observer.observe(this);
			});
		},
		createObserver() {
			const options = {
				root: null,
				threshold: 0,
				rootMargin: '0px'
			};
			return new IntersectionObserver(entries => {
				entries.forEach(entry => {
					if (entry.isIntersecting && !$(entry.target).hasClass('loaded')) {
						$(entry.target).addClass('loaded');
						var settings = $(entry.target).data("settings"),
							length = settings.progress_value,
							speed = settings.speed;
						$(entry.target).css({"opacity": "1", "width": length + "%","transition-duration":speed +"ms"});
					}
				});
			}, options);
		}
	};

	 window.addEventListener( 'elementor/frontend/init', () => {
		if (elementorFrontend.isEditMode()) {
			elementorFrontend.hooks.addAction(
				"frontend/element_ready/ube-progress.default", UbeProgressHandler);
		} else {
			elementorFrontend.hooks.addAction(
				"frontend/element_ready/ube-progress.default", UbeProgressScrollWidgetHandler.init);
		}
	});

})(jQuery);
