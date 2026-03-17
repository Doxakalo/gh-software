/* 
 *  Copyright © Ludvik Michalek, Bigfoot Interactive
 *  http://www.bigfoot.cz/
 *  
 *  Navigation utils
 */

var NavigationUtils = function(){};


/**
 * Static functions
 */


/**
 * Scroll to element by selector
 * @param {string} element
 * @returns {Boolean}
 */
NavigationUtils.scrollToElement = function(element, offset){
	var el = $(element).first();
	if(el){
		var durationMin = 0.5;
		var durationMax = 1.5;
		var durationFactor = 700;

		var elementTop = Math.round(el.offset().top + (!isNaN(offset) ? offset : 0));
		var currentPageTop = $(document).scrollTop();
		var distance = elementTop - currentPageTop;
		var duration = distance / durationFactor;

		// set duration bounds
		duration = Math.max(duration, durationMin);
		duration = Math.min(duration, durationMax);

		$('html, body').stop().animate({scrollTop: elementTop}, duration * 1000, 'swing');
		
		return true;
	}
	return false;
};


/**
 * Bind scrolling to all links with '.scrollToElement' class
 */
NavigationUtils.initScrollToElementLinks = function(offset) {
	var clickLinks = $('.scrollToElement:not(.scrollToElement-inited)');

	if (clickLinks.length > 0) {
		clickLinks.click(function (event) {
			event.preventDefault();
			var link = $(this);
			var elementSelector = link.data('scrollTarget');
			if (elementSelector) {
				NavigationUtils.scrollToElement(elementSelector, offset);
			}
		});

		// set init flag to existing links
		$('.scrollToElement').addClass('scrollToElement-inited');
	}
};


/**
 * Perform scroll to anchored element on page load
 * Usage: 
 * Link: /#contact-scroll
 * NavigationUtils.initScrollToElementOnLoad('-scroll');
 */
NavigationUtils.initScrollToElementOnLoad = function(scrollSuffix) {
	if (window.location.hash) {
		var hashRaw = (window.location.hash).substring(1);
		// check if anchor contains '-scroll' suffix
		if(hashRaw.indexOf(scrollSuffix) === hashRaw.length - scrollSuffix.length){
			// remove scroll suffix from hash
			var hash = hashRaw.substr(0, hashRaw.indexOf(scrollSuffix));
			// find the anchored element
			var el = $('#' + hash);
			if (el.length === 1) {
				setTimeout(function () {
					NavigationUtils.scrollToElement(el);
				}, 500);
			}
		}
	}
};