class StickyElement {
	constructor(container, page) {
		this.container = $(container);
		this.page = page;
		this.document = $(document);
		this.window = $(window);
		this.initialWindowHeight = this.window.outerHeight();
		this.resizedNavbarHeight = 55;
		this.resizedOffsetTop;
		this.resizeTimeout;
		this.setStickyPosition;
		this.sticky;
		this.checkResize = 0;
		this.windowScrollTop = this.window.scrollTop();

		this.styleInitial = this.page.platform.isIE() ? 'inherit' : 'initial';
		// tablet address bar height (30px)
		this.tabletCompensation = this.page.platform.isTablet() ? 30 : 0;

		this.saveInitialPosition();

		this.window.scroll(() => {
			this.windowScrollTop = this.window.scrollTop();
		});
		
		this.window.resize(() => {
			if(this.resizeTimeout) {
				clearTimeout(this.resizeTimeout);
			}
			this.resizeTimeout = setTimeout(() => {
				this.checkResize = 1;
				this.saveInitialPosition();
				this.setPosition();
			}, 500);
		});
		this.setPosition();
		
	}

	saveInitialPosition() {
		this.initialOffsetTop = this.container.offset().top - this.page.getHeaderHeight();
		this.initialSidebarHeight = this.container.height();
	}
	setPosition() {
		if(this.sticky) {
			this.sticky.destroy();
		}
		if(this.window.height() < (this.initialSidebarHeight + this.page.getHeaderHeight())) {
			this.sticky = new Waypoint.Sticky({
				element: this.container,
				offset: 'bottom-in-view',
			});
			let triggerPoint;
			if(this.checkResize && this.windowScrollTop > this.initialOffsetTop) {
				triggerPoint = this.sticky.waypoint.triggerPoint;
			}else{
				triggerPoint = this.sticky.waypoint.triggerPoint - this.page.header.HEADER_COLLAPSED_DIFFERENCE;
			}
			
			this.setStickyPosition = () => {
				this.sticky.waypoint.triggerPoint = triggerPoint;

				if((this.document.height() - this.page.getFooterHeight()) <= (this.window.innerHeight() + this.windowScrollTop)) {
					let offset = (this.windowScrollTop + this.window.innerHeight()) - (this.document.height() - this.page.getFooterHeight());
					this.sticky.element.css({bottom: offset, top: this.styleInitial});
				}else{
					this.sticky.element.css({bottom: 0, top: this.styleInitial});
				}
			}
			this.setStickyPosition();
			this.window.off('scroll', this.setStickyPosition);
			this.window.scroll(() => {
				this.setStickyPosition();
			});
		}else{
			this.sticky = new Waypoint.Sticky({
				element: this.container,
				offset: this.resizedNavbarHeight,
			});
			this.resizedOffsetTop = $(this.sticky.wrapper).offset().top - this.page.getHeaderHeight();

			let resizeTimeout;
			this.window.resize(() => {
				if(resizeTimeout) {
					clearTimeout(resizeTimeout);
				}
				resizeTimeout = setTimeout(() => {
					this.sticky.destroy();
					this.sticky = new Waypoint.Sticky({
						element: this.container,
						offset: this.resizedNavbarHeight
					});
					this.resizedOffsetTop = $(this.sticky.wrapper).offset().top - this.page.getHeaderHeight();
					this.initialSidebarHeight = this.container.height();
					if(this.resizedOffsetTop < 0) {
						this.resizedOffsetTop = this.initialOffsetTop;
					}
					this.setStickyPosition();
				}, 500);
			});
			this.sticky.element.css({top: this.resizedNavbarHeight, bottom: this.styleInitial});
			
			this.setStickyPosition = () => {
				this.sticky.waypoint.triggerPoint = this.resizedOffsetTop;

				if((this.document.height() - this.page.getFooterHeight()) <= ((this.initialSidebarHeight + this.page.getHeaderHeight()) + this.windowScrollTop)) {
					let compensation = this.initialWindowHeight < this.window.outerHeight() ? this.tabletCompensation : 0;
					let offset = (this.windowScrollTop + this.window.innerHeight()) - (this.document.height() - this.page.getFooterHeight());
					this.sticky.element.css({bottom: offset + compensation, top: this.styleInitial});
				}else{
					if(this.resizedOffsetTop >= this.initialOffsetTop) {
						if($(window).scrollTop() >= this.resizedOffsetTop){
							this.sticky.element.css({top: this.resizedNavbarHeight, bottom: this.styleInitial});
						}else{
							this.sticky.element.css({top: this.resizedNavbarHeight, bottom: this.styleInitial});
						}
					}else{
						if($(window).scrollTop() >= this.resizedOffsetTop){
							this.sticky.element.css({top: this.resizedNavbarHeight, bottom: this.styleInitial});
						}else{
							this.sticky.element.css({top: this.resizedNavbarHeight, bottom: this.styleInitial});
						}
					}
				}
			}
			this.setStickyPosition();
			this.window.off('scroll', this.setStickyPosition);
			this.window.scroll(() => {
				this.setStickyPosition();
			});
		}
	}
}