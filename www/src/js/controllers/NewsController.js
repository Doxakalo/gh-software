class NewsController extends PageController {

	constructor(){
		super();

		let asideSticky = new StickyElement($('aside.sticky'), this);
		if(this.platform.isPhone() || this.platform.isTablet()) {
			this.initAnchorScroll(40);
		}else{
			this.initAnchorScroll(-10);
		}

		this.initShortlinkClipboardCopy();

	}
}
