class FileMakerConsultingController extends PageController{
	constructor() {
		super();
		this.cardBox2scroll = '.go2Form';
		this.formSubject = '#frm-contactForm-topic';
		this.formSubjectText = 'Doc-O-Matic';
		this.formFocusField = '#frm-contactForm-name';

		if(this.platform.isPhone() || this.platform.isTablet()) {
			this.initAnchorScroll(40);
		}else{
			this.initAnchorScroll(-10);
		}
		this.cardbox2contact();
	}
	cardbox2contact(){
		let _this = this;
		$(_this.cardBox2scroll).click(function() {
			let cardbox = $(this);
			let anchor = $(cardbox).attr('data-scroll-anchor');
			$(_this.formSubject).val(_this.formSubjectText)
			$(_this.formFocusField).focus();
			NavigationUtils.scrollToElement($("section[data-anchor='" + anchor + "']"), (-145));
		});
	}

}