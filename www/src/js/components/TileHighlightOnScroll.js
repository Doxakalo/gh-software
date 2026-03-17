class TileHighlightOnScroll {
	constructor(container, isSecond, fullWidth) {
		this.OPACITY_RATIO = 0.01;
		this.window = $(window);
		this.isSecond = isSecond;
		this.fullWidth = fullWidth;
		this.container = container;
		this.platform = new Platform();
		this.rolloverLayer = $('.rollover', this.container);
		this.containerHeight = null;
		this.tileOffsetTop = null;
		this.windowCenter = null;

		this.initValues();

		this.window.resize(() => {
			this.initValues();
			this.highlightTile();
		});

		this.highlightTile();
		this.window.scroll(() => {
			this.highlightTile();
		});
	}

	initValues() {
		this.containerHeight = this.container.height();
		this.tileOffsetTop = this.rolloverLayer.offset().top;
		this.windowCenter = Math.round(this.window.innerHeight() * 0.65);
	}

	highlightTile() {
		let windowScrollTop = this.window.scrollTop();
		if(this.platform.isPhone()) {
			if ((windowScrollTop + this.windowCenter) >= this.tileOffsetTop 
			&& (windowScrollTop + this.windowCenter) <= this.tileOffsetTop + this.containerHeight) {
				let setOpacity = Math.min(((windowScrollTop + this.windowCenter) - this.tileOffsetTop) * this.OPACITY_RATIO, 1);
				this.rolloverLayer.css({ opacity: setOpacity })
			} else if ((windowScrollTop + this.windowCenter) > (this.tileOffsetTop + this.containerHeight)) {
				let setOpacity = Math.max(1 + ((this.tileOffsetTop + this.containerHeight) - (windowScrollTop + this.windowCenter)) * this.OPACITY_RATIO, 0);
				this.rolloverLayer.css({ opacity: setOpacity })
			} else {
				this.rolloverLayer.css({ opacity: 0 })
			}
		}else{
			if(this.fullWidth) {
				if ((windowScrollTop + this.windowCenter) >= this.tileOffsetTop 
				&& (windowScrollTop + this.windowCenter) <= this.tileOffsetTop + this.containerHeight) {
					let setOpacity = Math.min(((windowScrollTop + this.windowCenter) - this.tileOffsetTop) * this.OPACITY_RATIO, 1);
					this.rolloverLayer.css({ opacity: setOpacity })
				} else if ((windowScrollTop + this.windowCenter) > (this.tileOffsetTop + this.containerHeight)) {
					let setOpacity = Math.max(1 + ( (this.tileOffsetTop + this.containerHeight) - (windowScrollTop + this.windowCenter) ) * this.OPACITY_RATIO, 0);
					this.rolloverLayer.css({ opacity: setOpacity })
				} else {
					this.rolloverLayer.css({ opacity: 0 })
				}
			}else{
				if (this.isSecond) {
					if ((windowScrollTop + this.windowCenter) >= this.tileOffsetTop + (this.containerHeight * 0.5) 
					&& (windowScrollTop + this.windowCenter) <= this.tileOffsetTop + this.containerHeight) {
						let setOpacity = Math.min(((windowScrollTop + this.windowCenter) - (this.tileOffsetTop + this.containerHeight * 0.5)) * this.OPACITY_RATIO, 1);
						this.rolloverLayer.css({ opacity: setOpacity })
					} else if ((windowScrollTop + this.windowCenter) > (this.tileOffsetTop + this.containerHeight)) {
						let setOpacity = Math.max(1 + ( (this.tileOffsetTop + this.containerHeight) - (windowScrollTop + this.windowCenter) ) * this.OPACITY_RATIO, 0);
						this.rolloverLayer.css({ opacity: setOpacity })
					} else {
						this.rolloverLayer.css({ opacity: 0 })
					}
				} else {
					if ((windowScrollTop + this.windowCenter) >= this.tileOffsetTop 
					&& (windowScrollTop + this.windowCenter) <= this.tileOffsetTop + this.containerHeight * 0.5) {
						let setOpacity = Math.min(((windowScrollTop + this.windowCenter) - this.tileOffsetTop) * this.OPACITY_RATIO, 1);
						this.rolloverLayer.css({ opacity: setOpacity })
					} else if ((windowScrollTop + this.windowCenter) > (this.tileOffsetTop + this.containerHeight * 0.5)) {
						let setOpacity = Math.max(1 + ( (this.tileOffsetTop + this.containerHeight * 0.5) - (windowScrollTop + this.windowCenter) ) * this.OPACITY_RATIO, 0);
						this.rolloverLayer.css({ opacity: setOpacity })
					} else {
						this.rolloverLayer.css({ opacity: 0 })
					}
				}
			}
		}
	}
}