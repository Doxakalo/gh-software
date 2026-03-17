class SupportController extends PageController {

	constructor() {
		super();

		this.activeTabButton = null;
		this.tabSection = $('section.support-tabs');

		// init animated arrows
		this.arrows = new SectionArrows(this.tabSection, true, true);
		
		this.initButtons();
		this.initButtonsMobile();
		if(this.platform.isPhone() || this.platform.isTablet()) {
			this.initAnchorScroll(40);
		}else{
			this.initAnchorScroll(-10);
		}

		let storeInit = new Store();

		$('.product[data-product-id="300914624"] .selectProductChk').prop('checked', true).trigger("change").hide();

	}


	/*
	 * Init tab buttons
	 */
	initButtons() {
		let buttons = $('a.item', this.tabSection);

		$(buttons, this.tabs).click((e) => {
			e.preventDefault();
			this.activeTabButton = $(e.currentTarget);
			if(this.activeTabButton.hasClass('active')) {
				buttons.removeClass('active');
				this.showDetailContent('default');
				this.centerTabButtonArrow();
			} else {
				buttons.removeClass('active');
				this.activeTabButton.addClass('active');
				let id = this.activeTabButton.data('id');
				this.updateTabButtonArrow();
				this.showDetailContent(id);
				/*setTimeout(() => {
					NavigationUtils.scrollToElement(this.activeTabButton, -this.getHeaderHeight());
				}, 400);*/
			}
		});

		$(".item-custom-link").click(function(){
			let dataId = $(this).attr("data-id");
			$('.item[data-id="'+dataId+'"]').click();
		});

	}
	
	initButtonsMobile() {
		let buttons = $('a.item', $('section.support-tabs-mobile'));

		$(buttons, this.tabs).click((e) => {
			e.preventDefault();
			this.activeTabButton = $(e.currentTarget);
			if(this.activeTabButton.hasClass('active')) {
				buttons.removeClass('active');
				this.hideDetailContent();
			}else{
				buttons.removeClass('active');
				this.activeTabButton.addClass('active');
				let id = this.activeTabButton.data('id');
				this.showDetailContent(id);
				setTimeout(() => {
					NavigationUtils.scrollToElement(this.activeTabButton, -this.getHeaderHeight());
				}, 400);
			}
		});
	}
	
	updateTabButtonArrow(){
		if(this.activeTabButton) {
			this.arrows.setPositionToElement(this.activeTabButton);
		}
	}

	centerTabButtonArrow() {
		this.arrows.center();
	}
	

	/*
	 * Handle display of detail content
	 */
	showDetailContent(id) {
		let detailSections = $('section.detail');
		let activeDetail = detailSections.filter('[data-id="' + id + '"]');
		detailSections.slideUp(300);
		activeDetail.slideDown(300);
	}

	hideDetailContent() {
		let detailSections = $('section.detail');
		detailSections.slideUp(300);
	}

}
