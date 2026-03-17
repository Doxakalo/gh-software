class StoriesController extends PageController {

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

	initCoverSlider() {
		let slider = new Swiper('#cover-slider', {
			direction: 'horizontal',
			loop: true,
			speed: 500,
			simulateTouch: false,
			allowTouchMove: false,
			effect: 'fade',
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
