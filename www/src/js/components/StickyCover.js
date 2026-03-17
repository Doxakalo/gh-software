class StickyCover {
	constructor(container, wrapperClass, page, isTop) {

		this.DESCRIPTION_HEIGHT_RATIO = 0.5;

		this.container = $(container);
		this.page = page;
		this.isTop = isTop;
		this.window = $(window);
		this.wrapperClass = wrapperClass;
		this.description = $('div.description', this.container);
		this.image = $('div.toggle-image', this.container);
		this.buttons = $('div.toggle-buttons', this.container);
		this.descriptionInitialHeight = 0;
		this.imageInitialHeight = 0;
		this.imageInitialWidth = 0;
		this.buttonsInitialHeight = 0;
		this.initialContainerHeight = 0;
		this.containerHeight = 0;
		this.initialWrapperHeight = 0;
		this.initialTriggerPoint = 0;
		this.triggerPointDifference = 0;
		this.windowScrollTop = this.window.scrollTop();
		this.resizeTimeout;
		this.helperContainer;
		this.sticky;
		this.resizeHelperEnabled = this.container.data('resizeHelper') === false ? false : true;
		this.windowResize = this.window.width();

		this.scrollPositionChange = this.container.data('positionChange');

		this.window.scroll(() => {
			this.windowScrollTop = this.window.scrollTop();
		});
		this.windowScrollTop = this.window.scrollTop();

		if(this.resizeHelperEnabled) {
			this.window.resize(() => {
				if(this.resizeTimeout) {
					clearTimeout(this.resizeTimeout);
				}
				this.resizeTimeout = setTimeout(() => {
					if(this.window.width() != this.windowResize) {
						this.setInitialSize();
						this.initPosition();
					}
				}, 300);
			});
		}

		this.setInitialSize();
		this.initPosition();
	}

	getCollapsedHeight() {
		return $('.page-title').outerHeight();
	}

	getCoverHelperHeight() {
		if(!this.helperContainer) {
			this.helperContainer = $('<div id="sticky-cover-helper-container"></div>');
			$('body').prepend(this.helperContainer);
			this.helperContainer.append(this.container.clone());
		}
		this.descriptionInitialHeight = this.helperContainer.find('.description').height();
		this.imageInitialHeight = this.helperContainer.find('.toggle-image').height();
		this.imageInitialWidth = this.helperContainer.find('.toggle-image').width();
		this.buttonsInitialHeight = this.helperContainer.find('.toggle-buttons').height();
		let height = this.helperContainer.height();
		return height;
	}

	setInitialSize() {
		if(this.resizeHelperEnabled) {
			this.initialContainerHeight = this.getCoverHelperHeight();
		}else{
			this.initialContainerHeight = this.container.outerHeight(true);
		}
	}

	showDescription(state) {
		if(this.description.length != 0){
			this.description.css({height:this.descriptionInitialHeight});
			if(state) {
				this.description.css({height:this.descriptionInitialHeight});
				this.description.removeClass('fadeOut');
			}else{
				let height = Math.min(Math.max(this.descriptionInitialHeight - (this.windowScrollTop * this.DESCRIPTION_HEIGHT_RATIO), 0), this.descriptionInitialHeight);
				this.description.css({height: height});
				this.description.addClass('fadeOut');
			}
		}
	}

	showImage(state) {
		if(this.image.length != 0){
			this.image.css({height:this.imageInitialHeight, width:this.imageInitialWidth});
			if(state) {
				this.image.css({height:this.imageInitialHeight, width:this.imageInitialWidth});
				this.image.addClass('gr-3');
				this.image.removeClass('fadeOut');
			}else{
				let height = Math.min(Math.max(this.imageInitialHeight - (this.windowScrollTop * this.DESCRIPTION_HEIGHT_RATIO), 0), this.imageInitialHeight);
				let width = Math.min(Math.max(this.imageInitialWidth - (this.windowScrollTop * this.DESCRIPTION_HEIGHT_RATIO), 0), this.imageInitialWidth);
				this.image.css({height: height, width: width});
				this.image.addClass('fadeOut');
				this.image.removeClass('gr-3');
			}
		}
	}

	showButtons(state) {
		if(this.buttons.length != 0){
			this.buttons.css({height:this.buttonsInitialHeight});
			if(state) {
				this.buttons.css({height:this.buttonsInitialHeight});
				this.buttons.removeClass('fadeOut');
			}else{
				let height = Math.min(Math.max(this.buttonsInitialHeight - (this.windowScrollTop * this.DESCRIPTION_HEIGHT_RATIO), 0), this.buttonsInitialHeight);
				this.buttons.css({height: height});
				this.buttons.addClass('fadeOut');
			}
		}
	}

	initPosition() {
		if(this.sticky) {
			this.sticky.destroy();
		}
		if(this.isTop && this.resizeHelperEnabled === true) {
			this.sticky = new Waypoint.Sticky({
				element: this.container,
				offset: this.page.getHeaderHeight(),
				wrapper: '<div class="' + this.wrapperClass + '" />',
			});

			this.containerHeight = Math.min(Math.max(this.initialContainerHeight - this.windowScrollTop, 0), this.initialContainerHeight);
			let backgroundPosition = this.scrollPositionChange ? Math.min(Math.max(50 - (this.windowScrollTop * 0.05), 25), 50) : 50;
			this.initialWrapperHeight = this.initialContainerHeight;

			this.sticky.element.css({top:this.page.getHeaderHeight()});
			$(this.sticky.wrapper).css({height: this.initialWrapperHeight});
			setTimeout(() => {
				this.sticky.element.css({top:this.page.getHeaderHeight()});
				$(this.sticky.wrapper).css({height: this.initialWrapperHeight});
			}, 10);

			if(this.windowScrollTop > 0) {
				this.showDescription(false);
				this.showImage(false);
				this.showButtons(false);
			}else{
				this.showDescription(true);
				this.showImage(true);
				this.showButtons(true);
			}
			this.sticky.element.css({top:this.page.getHeaderHeight()});
			this.sticky.element.css({height: this.containerHeight, 'backgroundPosition':'50% '+backgroundPosition+'%'});

			this.window.scroll(() => {
				this.sticky.waypoint.triggerPoint = 0;
				this.containerHeight = Math.min(Math.max(this.initialContainerHeight - this.windowScrollTop, 0), this.initialContainerHeight);
				if(this.windowScrollTop >= 0) {
					this.sticky.element.css({position: 'fixed', top:this.page.getHeaderHeight()});
					$(this.sticky.wrapper).css({height: this.initialWrapperHeight});
				}else{
					this.sticky.element.css({position: 'static', top:'initial'});
					$(this.sticky.wrapper).css({height: this.initialWrapperHeight});
				}
				let backgroundPosition = this.scrollPositionChange ? Math.min(Math.max(50 - (this.windowScrollTop * 0.05), 25), 50) : 50;
				if(this.windowScrollTop > this.sticky.waypoint.triggerPoint) {
					this.sticky.element.css({height: this.containerHeight, 'backgroundPosition':'50% '+backgroundPosition+'%'});
					this.showDescription(false);
					this.showImage(false);
					this.showButtons(false);
				}else {
					this.sticky.element.css({height: this.initialWrapperHeight, 'backgroundPosition':'50% '+backgroundPosition+'%'});
					this.showDescription(true);
					this.showImage(true);
					this.showButtons(true);
				}
			});
		}else if(this.isTop && this.resizeHelperEnabled === false){
			this.sticky = new Waypoint.Sticky({
				element: this.container,
				offset: this.page.getHeaderHeight(),
				wrapper: '<div class="' + this.wrapperClass + '" />',
			});

			this.initialWrapperHeight = $(this.sticky.wrapper).outerHeight(true);
			let height = Math.min(Math.max(this.initialContainerHeight - this.windowScrollTop, 0), this.initialContainerHeight);

			this.sticky.element.css({top:this.page.getHeaderHeight()});
			$(this.sticky.wrapper).css({height: this.initialWrapperHeight});
			setTimeout(() => {
				this.sticky.element.css({top:this.page.getHeaderHeight()});
				$(this.sticky.wrapper).css({height: this.initialWrapperHeight});
			}, 10);

			if(this.windowScrollTop > 0) {
				this.showDescription(false);
				this.showImage(false);
				this.showButtons(false);
			}else{
				this.showDescription(true);
				this.showImage(true);
				this.showButtons(true);
			}
			this.sticky.element.css({top:this.page.getHeaderHeight()});
			this.sticky.element.css({height: height});

			this.window.scroll(() => {
				this.sticky.waypoint.triggerPoint = 0;
				if(this.windowScrollTop >= 0) {
					this.sticky.element.css({position: 'fixed', top:this.page.getHeaderHeight()});
				}else{
					this.sticky.element.css({position: 'static', top:'initial'});
					$(this.sticky.wrapper).css({height: this.initialWrapperHeight});
				}
				let height = Math.min(Math.max(this.initialContainerHeight - this.windowScrollTop, 0), this.initialContainerHeight);
				let backgroundPosition = this.scrollPositionChange ? Math.min(Math.max(50 - (this.windowScrollTop * 0.05), 25), 50) : 50;
				if(this.windowScrollTop > this.sticky.waypoint.triggerPoint) {
					this.sticky.element.css({height: height, 'backgroundPosition':'50% '+backgroundPosition+'%'});
					this.showDescription(false);
					this.showImage(false);
					this.showButtons(false);
				}else {
					this.sticky.element.css({height: height, 'backgroundPosition':'50% '+backgroundPosition+'%'});
					this.showDescription(true);
					this.showImage(true);
					this.showButtons(true);
				}
			});
		}else{
			this.sticky = new Waypoint.Sticky({
				element: this.container,
				offset: this.page.getHeaderHeight(),
				wrapper: '<div class="' + this.wrapperClass + '" />'
			});
			this.initialWrapperHeight = $(this.sticky.wrapper).outerHeight(true);
			this.initialTriggerPoint = this.sticky.waypoint.triggerPoint;

			this.showDescription(true);
			this.showImage(true);
			this.showButtons(true);
			this.sticky.element.css({height: this.initialContainerHeight});

			this.window.scroll(() => {
				this.triggerPointDifference = this.initialTriggerPoint - this.sticky.waypoint.triggerPoint;
				if(this.triggerPointDifference > 0) {
					this.sticky.waypoint.triggerPoint = this.initialTriggerPoint;
				}

				let height = Math.min(Math.max((this.initialContainerHeight + this.initialTriggerPoint) - this.windowScrollTop, 0), this.initialContainerHeight);
				if(this.windowScrollTop > this.initialTriggerPoint) {
					this.sticky.element.css({height: height});
					this.showDescription(false);
					this.showImage(false);
					this.showButtons(false);
				}else {
					this.sticky.element.css({height: height});
					this.showDescription(true);
					this.showImage(true);
					this.showButtons(true);
				}
			});
		}
	}
}