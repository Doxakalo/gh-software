class MobileMenu {

	constructor() {

		this._mobileIcon = $("#menuIcon");
		this._mobileMenu = $("#mobileMenu");
		this._dropDownOpenerIcon = $(".openDropDown");
		this._header = $("header");


		this.clickToMobileIcon();
		this.clickToDropdownIcon();
	}

	clickToMobileIcon() {
		this._mobileIcon.click((e) => {
			this._headerHeight = this.getCurrentHeaderHeight();
			let _this = $(e.currentTarget);

			let isActive = _this.attr("data-active");
			this.setMobileMenuCSSTop(this._headerHeight);

			if (isActive === "true") {
				_this.attr("data-active", "false");
				this.enablePageScrolling();
				this._mobileMenu.slideUp(600);
			} else {
				_this.attr("data-active", "true");
				this.disablePageScrolling();
				this._mobileMenu.slideDown(600);
			}
		});
	}



	clickToDropdownIcon() {
		this._dropDownOpenerIcon.click((e) => {
			let _this = $(e.currentTarget);
			let dropDownMenu = _this.parents("li").next("li");

			let isDropDownMenuActive = dropDownMenu.hasClass("active");

			if(isDropDownMenuActive){
				dropDownMenu.removeClass("active");
				_this.removeClass("active");
			} else {
				dropDownMenu.addClass("active");
				_this.addClass("active");
			}
			dropDownMenu.slideToggle();
		});
	}

	setMobileMenuCSSTop(val) {
		this._mobileMenu.css("top", val);
	}

	getCurrentHeaderHeight() {
		return this._header.outerHeight();
	}

	disablePageScrolling(){
		$("html,body").addClass("stopScroll");

		$("html,body").on("touchmove", (e) => {
			e.preventDefault();
		});

		this._mobileMenu.on("touchmove", (e) => {
			return true;
		});
	}

	enablePageScrolling(){
		$("html,body").removeClass("stopScroll");

		$("html,body").on("touchmove", (e) => {
			return true;
		});
	}
}