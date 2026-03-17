class ToolsDetailController extends PageController {

    constructor() {
        super();
        let fixedCover = new FixedCover('fixedCover');

        this.stringUtils = new StringUtils();
        this.numberUtils = new NumberUtils();


        this.init();
        this.initDownload();

        let storeInit = new Store();

        if (this.platform.isPhone()) {
            this.initAnchorScroll(40);
        } else {
            this.initAnchorScroll(100);
        }
        //this.submitCheck();

        // popover plugin
        $('.popover .name-inner').webuiPopover({
            trigger: "hover",
            placement: (this.platform.isPhone() === false) ? 'auto-right' : 'auto',
            title: '',
            style: (this.platform.isPhone() === false) ? 'more-information' : 'more-information-mobile',
            width: (this.platform.isPhone() === false) ? 400 : 250
        });


    }


    // --------------- DOWNLOAD SECTION -----------------

    initDownload() {
        this._downloadVersionBasicInfo = $("#download-content .version .basic-info");
        this._downloadVersion = $("#download-content .version");

        this.openFirstVersion();
        this.initClickToVersion();
    }

    openFirstVersion() {
        let version = this._downloadVersion.first();
        let versionSubmenu = this.getDownloadVersionSubmenu(version);

        version.attr("data-active", "true");
        versionSubmenu.slideDown();
    }

    initClickToVersion() {
        this._downloadVersionBasicInfo.click((e) => {
            let _this = $(e.currentTarget);
            let version = this.getParentDownloadVersion(_this);
            let versionSubmenu = this.getDownloadVersionSubmenu(version);
            let versionIsOpen = version.attr("data-active");

            if (versionIsOpen !== "true") {
                version.attr("data-active", "true");
                versionSubmenu.slideDown();
            } else {
                version.attr("data-active", "false");
                versionSubmenu.slideUp();
            }
        });
    }

    getParentDownloadVersion(ele) {
        return ele.parents(".version");
    }

    getDownloadVersionSubmenu(versionEle) {
        return versionEle.find(".version-submenu");
    }

    // --------------- OTHER -----------------

    init() {


        this.clickToBuyScrollButton();
        this.clickToDownloadScrollButton();


        // this.initTestimonialSliderSingle();
    }

    /*initTestimonialSliderSingle() {
        let singleTestimonialSlider = new Swiper('.testimonial-slider-single', {
            direction: 'horizontal',
            loop: true,
            allowSlideNext: false,
            allowSlidePrev: false,
            allowTouchMove: false,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            }
        });

        $(".testimonial-slider-single .play-icon").click((e) => {
            let _this = $(e.currentTarget);

            singleTestimonialSlider.autoplay.stop();
        });
    }*/

    // BUTTONs IN COVER IMAGE
    clickToBuyScrollButton() {
        $(".buy-button").click(() => {
            NavigationUtils.scrollToElement($("section[data-anchor='buy']"), -this.getHeaderHeight());
        });
    };

    // BUTTONs IN COVER IMAGE
    clickToDownloadScrollButton() {
        $(".download-button").click(() => {
            NavigationUtils.scrollToElement($("section[data-anchor='download']"), -this.getHeaderHeight());
        });
    };


}

