class FooterComponent {

	constructor(header, footer, content){
		this._header = header;
		this._footer = footer;
		this._content = content;

		this.init();
	}

	init() {
		let _this = this;

		// init
		new ResizeSensor(_this._content, (e) => {
			_this.updateFooter();
		});
		_this.updateFooter();
	}

	updateFooter() {
		let _this = this;

		var windowHeight = $(window).outerHeight(true);
		var mainHeight = $(_this._content).outerHeight(true);
		var headerHeight = $(_this._header).outerHeight(true);
		var footerHeight = $(_this._footer).outerHeight(true);
		if (mainHeight + headerHeight + footerHeight > windowHeight) {
			$(_this._footer).removeClass('fixed');
		} else {
			$(_this._footer).addClass('fixed');
		}
	}
}