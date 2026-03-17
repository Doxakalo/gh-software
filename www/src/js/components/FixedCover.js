class FixedCover {

	constructor(elementId, staticCover) {
		this.staticCover = staticCover ? $('#' + staticCover) : $('section.cover');
		this.fixedCover = $('#' + elementId);
		this.resizeTimeout;
		this.windowScrollTop;
		this.window = $(window);
		this.windowResize = $(window).width();
		this.bgVerticalPosition = this.fixedCover.data('imagePositionHorizontal') ? this.fixedCover.data('imagePositionHorizontal') : 50;
		
		this.initialData();

		this.window.resize(() => {
			if(this.resizeTimeout) {
				clearTimeout(this.resizeTimeout);
			}
			this.resizeTimeout = setTimeout(() => {
				if(this.window.width() != this.windowResize) {
					this.initialData();
					this.setFixedCoverPosition();
				}
			}, 300);
		});
		this.window.scroll(() => {
			this.setFixedCoverPosition();
		})
		this.getDataFromCover();
		this.setFixedCoverPosition();
	}

	initialData() {
		this.navbarHeight = $('header').outerHeight(true);
		this.staticCoverHeight = this.staticCover.outerHeight(true);
		this.fixedCoverPositionFromTop = this.staticCoverHeight + this.staticCover.offset().top - this.navbarHeight;
	}

	getDataFromCover() {
		let titleStaticCover = $('.page-title > strong', this.staticCover).text();
		let imageStaticCover = this.staticCover.css('background-image');
		let titleFixedCover = $('div.title', this.fixedCover);

		titleFixedCover.text(titleStaticCover);
		this.fixedCover.css({'background-image': imageStaticCover, 'backgroundPosition': '50%' + this.bgVerticalPosition + '%'});
	}

	setFixedCoverPosition () {
		this.windowScrollTop = this.window.scrollTop();
		if(this.windowScrollTop > this.fixedCoverPositionFromTop) {
			this.fixedCover.addClass('active');
		}else{
			this.fixedCover.removeClass('active');
		}
	}
}