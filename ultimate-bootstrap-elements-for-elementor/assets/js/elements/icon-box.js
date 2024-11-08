(function ($) {
	'use strict';
	var UbeIconBoxHandler = {
		init: function ($scope, $){
			var $elements = $scope.find('[data-vivus]');
			if ($elements.length === 0) {
				return;
			}
			if (!window.IntersectionObserver) {
				return;
			}
			$elements.each(function ($index, element) {
				const observer = UbeIconBoxHandler.createObserver();
				observer.observe(element);
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
						var settings = $(entry.target).data( 'vivus' );
						var $icon = $(entry.target).find( '.elementor-icon' );
						if ( settings) {
							if ($icon.length > 0) {
								var $svg = $icon.children('svg').not('.svg-defs-gradient');
								if ($svg.length === 0) {
									return;
								}

								var options = {
									type: settings.type,
									duration: settings.duration,
									animTimingFunction: Vivus.EASE_OUT
								};
								if (vivus) {
									vivus.destroy();
								}

								var vivus = new Vivus($svg[0], options);

								if ('yes' === settings.play_on_hover) {
									var $wrap = $(entry.target).closest('.ube-icon-box-wrapper');
									$wrap.hover(function () {
										vivus.stop()
											.reset()
											.play(2);
									}, function () {
										//vivus.finish();
									});
								}
							}
						}
					}
				});
			}, options);
		}
	}
	$( window ).on( 'elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction( 'frontend/element_ready/ube-icon-box.default', UbeIconBoxHandler.init );
		elementorFrontend.hooks.addAction( 'frontend/element_ready/ube-advanced-icon-box.default', UbeIconBoxHandler.init );
	} );
})(jQuery);