class Platform {
	constructor() {
		this.html = $('html');
		this.iHeight = window.screen.height;
		this.iWidth = window.screen.width;
	}

	isIE(){
		return this.html.data('isIe') === true;
	}

	isEdge(){
		return this.html.data('isEdge') === true;
	}

	isSafari() {
		return this.html.data('isSafari') === true;
	}

	isDesktop(){
		return this.html.data('device') === 'desktop';
	}

	isTablet(){
		return this.html.data('device') === 'tablet';
	}

	isPhone(){
		return this.html.data('device') === 'phone';
	}

	deviceModel() {
		let oldIphonesCondition = this.iWidth === 375 && this.iHeight === 667 || this.iWidth === 320 && this.iHeight === 568 || this.iWidth === 320 && this.iHeight === 480;
		let oldIpadsCondition = this.iWidth === 768 && this.iHeight === 1024;

		if(oldIphonesCondition || oldIpadsCondition) {
			return true;
		}else{
			return false;
		}
	}

	iOSDevice() {
		let deviceIphone = navigator.userAgent.match(/iPhone;/i) ? true : false;
		let deviceIpad = navigator.userAgent.match(/iPad;/i) ? true : false;

		if(deviceIphone === true || deviceIpad === true) {
			return true;
		}else{
			return false;
		}
	}
	
}
