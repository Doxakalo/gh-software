class SearchController extends PageController {

	constructor(){
		super();

		if(this.platform.isPhone() || this.platform.isTablet()) {
			this.initAnchorScroll(40);
		}else{
			this.initAnchorScroll(-10);
		}

		let fixedCover = new FixedCover('fixedCover');
	}
}
