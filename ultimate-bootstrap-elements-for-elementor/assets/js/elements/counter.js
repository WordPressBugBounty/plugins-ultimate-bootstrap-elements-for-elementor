(function ($) {
	'use strict';
	var UbeCounterHandler = {
		init: function ($scope, $) {
			if (!window.IntersectionObserver) {
				return;
			}
			var $counters = $scope.find('.ube-counter-number');
			const observer = UbeCounterHandler.createObserver();
			observer.observe($counters[0]);
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
						var options_setting = $(entry.target).data('counter-options');
						var start = options_setting.start;
						var end = options_setting.end;
						var decimals = options_setting.decimals;
						var duration = options_setting.duration;
						var separator = options_setting.separator;
						var usegrouping = false;
						if (separator !== '') {
							usegrouping = true
						}
						var decimal = options_setting.decimal;
						var prefix = '';
						var suffix = '';
						var options = {
							useEasing: true,
							useGrouping: usegrouping,
							separator: separator,
							decimal: decimal,
							prefix: prefix,
							suffix: suffix
						};
						var counterup = new CountUp(entry.target, start, end, decimals, duration, options);
						counterup.start();
					}
				});
			}, options);
		}
	};
	 window.addEventListener( 'elementor/frontend/init', () => {
		 elementorFrontend.hooks.addAction('frontend/element_ready/ube-counter.default', UbeCounterHandler.init);
	});
})(jQuery);