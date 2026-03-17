class QualityAssuranceController extends PageController{
	constructor() {
		super();
		this.clickableCardBox = '.showMore';

		if(this.platform.isPhone() || this.platform.isTablet()) {
			this.initAnchorScroll(40);
		}else{
			this.initAnchorScroll(-10);
		}
		this.toggleCardBoxText();
	}
	toggleCardBoxText(){
		let _this = this;
		console.log('click');
		$(_this.clickableCardBox).click(function() {
			$(this).toggleClass('colapsed');
			$('.detail', this).slideToggle(500);
			if ($('.detail', this).height()>1){
				$('.read_more', this).fadeToggle("slow", "");
			} else {
				$('.read_more', this).fadeToggle("fast", "linear");
			}
		});
	}
}