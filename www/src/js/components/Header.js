class Header {
	constructor(page) {
		this.DEFAULT_HEADER_HEIGHT = page.platform.isPhone() ? 55 : 85;
		this.HEADER_COLLAPSED_DIFFERENCE = page.platform.isPhone() ? 0 : 30;
		this.container = $("body > header");
		this.lngWarning = $("#lang-warning");
		this.innerContainer = $(".inner", this.container);
		this.headerHeight = this.DEFAULT_HEADER_HEIGHT;
		this.window = $(window);
		this.wrapper;
		
		this.setHeight();

		let sticky = new Waypoint.Sticky({
			element: this.container,
			offset: 0,
			stuckClass: 'stuck'
		});

		this.wrapper = $(sticky.wrapper);

		this.window.scroll(() => {
			this.setHeight();
		});
	}

	getHeight() {
		return this.headerHeight;
	}

	setHeight(){
		let difference = Math.min((this.window.scrollTop() * 0.2), this.HEADER_COLLAPSED_DIFFERENCE);
		this.headerHeight = Math.min(this.DEFAULT_HEADER_HEIGHT - difference, 85);
		let styles = {
			height: this.headerHeight
		};
		 this.toggleLngWarning(difference);
		this.container.css(styles);
		this.innerContainer.css(styles);
		if(this.wrapper) {
			$(this.wrapper).css(styles);
		}
	}

	toggleLngWarning(difference){
		if(this.lngWarning.hasClass('active')){
			if(this.HEADER_COLLAPSED_DIFFERENCE == difference){
				this.lngWarning.slideUp(500);
			} else {
				this.lngWarning.slideDown(500);
			}
		}
	}
}