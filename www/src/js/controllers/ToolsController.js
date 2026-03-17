class ToolsController extends PageController {

	constructor(){

		super();

		let fixedCover = new FixedCover('fixedCover', 'staticCover-1');
		let fixedCover2 = new FixedCover('fixedCover-2', 'staticCover-2');

		if(this.platform.isPhone() || this.platform.isTablet()) {
			this.initAnchorScroll(40);
		}else{
			this.initAnchorScroll(100);
		}
   }
}
