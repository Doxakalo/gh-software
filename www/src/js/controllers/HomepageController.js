class HomepageController extends PageController {

	constructor(){
		super();

		this.initCoverSlider();

		if(this.platform.isPhone() || this.platform.isTablet()) {
			this.initAnchorScroll(40);
		}else{
			this.initAnchorScroll(-10);
		}
   }

	initCoverSlider() {
		let slider = new Swiper('#cover-slider', {
			direction: 'horizontal',
			loop: true,
			speed: 500,
			autoplay: {
			  delay: 6000
			},
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev'
			},
			pagination: {
				el: '.swiper-pagination',
				clickable: true
			}
		});
	}
}
