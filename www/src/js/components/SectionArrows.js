class SectionArrows {
	constructor(container, topBottom = false) {
		this.container = $(container);
		this.topBottom = topBottom;
		this.positionElement = null;
		
		const RESIZE_COMPLETE_DELAY = 250;
		
		// config values for responsive
		this.config = {
			arrowBaseWidth: 120,
			arrowBaseHeight: 34,
			arrowScaleDesktop: 0.8,
			arrowScaleTablet: 0.6,
			arrowScaleMobile: 0.4
		};
		
		// redraw on viewport update
		let resizeCompleteTimeout;
		$(window).resize(() => {
			this.container.removeClass('animated');
			this.update();
			// disable animation during resize
			if (resizeCompleteTimeout) {
				clearTimeout(resizeCompleteTimeout);
			}
			resizeCompleteTimeout = setTimeout(() => {
				this.container.addClass('animated');
			}, RESIZE_COMPLETE_DELAY);
		});	
	}
	
	
	/*
	 * Draw the clipping path with arrows
	 */
	setPosition(posLeft) {
		const boxHeight = this.container.height();
		let arrWidth = this.config.arrowBaseWidth;
		let arrHeight = this.config.arrowBaseHeight;
		
		// responsive scale of arrow
		if(Gridle.isActive('desktop')) {
			arrWidth = Math.round(this.config.arrowBaseWidth * this.config.arrowScaleDesktop);
			arrHeight = Math.round(this.config.arrowBaseHeight * this.config.arrowScaleDesktop);
		} else if(Gridle.isActive('tablet')) {
			arrWidth = Math.round(this.config.arrowBaseWidth * this.config.arrowScaleTablet);
			arrHeight = Math.round(this.config.arrowBaseHeight * this.config.arrowScaleTablet);
		} else if(Gridle.isActive('mobile')) {
			arrWidth = Math.round(this.config.arrowBaseWidth * this.config.arrowScaleMobile);
			arrHeight = Math.round(this.config.arrowBaseHeight * this.config.arrowScaleMobile);
		}
		
		// coordinates for top arrow
		let points = [
			['0%', '0%'],
			[(posLeft - (arrWidth/2)) + 'px', '0%'],
			[(posLeft) + 'px', arrHeight + 'px'],
			[(posLeft + (arrWidth/2)) + 'px', '0%'],
			['100%', '0%'],
			['100%', boxHeight + 'px'],
			['0%', boxHeight + 'px']
		];		
		// coordinates for top+bottom arrow
		if(this.topBottom){
			points = [
				['0%', '0%'],
				[(posLeft - (arrWidth/2)) + 'px', '0%'],
				[(posLeft) + 'px', arrHeight + 'px'],
				[(posLeft + (arrWidth/2)) + 'px', '0%'],
				['100%', '0%'],
				['100%', (boxHeight - arrHeight) + 'px'],
				[(posLeft + (arrWidth/2)) + 'px', (boxHeight - arrHeight) + 'px'],
				[(posLeft) + 'px', boxHeight + 'px'],
				[(posLeft - (arrWidth/2)) + 'px', (boxHeight - arrHeight) + 'px'],
				['0%', (boxHeight - arrHeight) + 'px']
			];			
		} 

		// convert to string for CSS
		let pointArrayStr = '';
		points.forEach(function (point, index) {
			pointArrayStr += point[0] + ' ' + point[1] + (index < points.length - 1 ? ',' : '');
		});

		// apply CSS
		this.container.css({
			'clip-path': 'polygon('+pointArrayStr+')',
			'-webkit-clip-path': 'polygon('+pointArrayStr+')'
		});
	}
	
	
	/*
	 * Redraw arrow for positioned element
	 */
	update(){
		if (this.positionElement) {
			let pos = this.positionElement.offset().left + (this.positionElement.width() / 2);
			this.setPosition(pos);
		}
	}
	

	/*
	 * Set element for auto-positioning arrow
	 */
	setPositionToElement(element){
		this.positionElement = element;
		this.update();
	}
	
	
	/*
	 * Center arrow
	 */
	center() {
		let posCenter = this.container.outerWidth()/2;
		this.setPosition(posCenter);
	}
	
	
	
}
