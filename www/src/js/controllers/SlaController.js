class SlaController extends PageController {

	constructor(){
		super();

		this.initSwitchColumnMobile();
		if(this.platform.isPhone() || this.platform.isTablet()) {
			this.initAnchorScroll(40);
		}else{
			this.initAnchorScroll(-10);
		}
   }

	initSwitchColumnMobile() {
		let tableTabsContent = $('.table-tabs-content');
		let tab = $('a.tab');

		tab.click(function(e) {
			e.preventDefault();
			let _this = $(this);
			tab.removeClass('active');
			_this.addClass('active');
			let selectedTab = _this.data('tab-link');
		
			switch (selectedTab) {
				case 'bronze':
					tableTabsContent.removeClass('silver-tab-content gold-tab-content').addClass('bronze-tab-content');
					break;
					
				case 'silver':
					tableTabsContent.removeClass('bronze-tab-content gold-tab-content').addClass('silver-tab-content');
					break;

				case 'gold':
					tableTabsContent.removeClass('bronze-tab-content silver-tab-content').addClass('gold-tab-content');
					break;
			}
		});
	}
}
