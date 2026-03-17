class CompanyController extends PageController {

	constructor(){
		super();

		let fixedCover = new FixedCover('fixedCover');

		if(this.platform.isPhone() || this.platform.isTablet()) {
			this.initAnchorScroll(40);
		}else{
			this.initAnchorScroll(40);
		}
   }
}
