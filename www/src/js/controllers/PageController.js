class PageController {
    constructor() {
        this.body = document.getElementsByTagName("body")[0];
        this.footer = document.getElementById("global-footer");
        this.content = document.getElementById("content");
        this.template = $('body').data('template');
        let activeVideoSection = null;

        Date.prototype.addDays = function(days) {
            var date = new Date(this.valueOf());
            date.setDate(date.getDate() + days);
            return date;
        };

        this.scrollTop = $(document).scrollTop();
        this.footerHeight = this.getFooterHeight();



        // detect platform
        this.platform = new Platform();

        // header
        this.header = new Header(this);
        this.mobileMenu = new MobileMenu();

        this.footerLayout = new FooterComponent(this.header.container[0], this.footer, this.content);


        this.initHighlightTapLinks();

        // init all default sliders
        this.mp4init();
        this.initSliders();
        this.initTestimonialSliderSingle();
        this.initTestimonialSliderMultiple();
        this.initTestimonialSliderCompilation();


        this.initAdvertisementWindow();

        this.initVideoplayerPopup();

        // init parallax
        // except on page support with browser Safari
        if (!(this.template === 'support' && this.platform.isSafari())) {
            this.initCoverParallax();
            this.initStickyParallax();
            if (this.platform.isDesktop()) {
                this.initTileParallax();
            }
        }

        // init video (youtube) videos
        this.initVideoTestimonials();

        // animated number counters
        this.initNumberCounters();

        // highlight tiles
        this.initTileHighlightOnScroll();

        this.initLazyLoadImage();


        this.showPrivacyInformation();

        this.setTelPrefixToDefault();

        let clientIpInfo = this.getClientIPInfo();
        if (clientIpInfo !== null) {
            this.setTelPrefixContactForm(clientIpInfo);
        }

        // SECURE FORM TO BUY PRODUCT
        let c1_buy = $("#frm-buy-c1").val();
        let c2_buy = $("#frm-buy-c2").val();
        let spamSecureField_buy = $(".frm-buy-spamSecureSum");
        setTimeout(function(){spamSecureField_buy.val(parseInt(c1_buy) + parseInt(c2_buy))}, 3000);
        spamSecureField_buy.hide();

        // SECURE FORM TO ZENDESK TICKET
        let c1 = $("#frm-c1").val();
        let c2 = $("#frm-c2").val();
        let spamSecureField = $(".frm-spamSecureSum");
        setTimeout(function(){spamSecureField.val(parseInt(c1) + parseInt(c2))}, 10000);
        spamSecureField.hide();

        // SECURE FORM TO FOOTER QUESTION
        let c1_ft = $("#frm-ft-c1").val();
        let c2_ft = $("#frm-ft-c2").val();
        let spamSecureField_ft = $(".frm-ft-spamSecureSum");
        setTimeout(function(){spamSecureField_ft.val(parseInt(c1_ft) + parseInt(c2_ft))}, 10000);
        spamSecureField_ft.hide();

        this.clickToAnchorScroll();
        this.hoverToPartnerLogo();
        this.logGAtracking();
        this.logDownloadProduct();
        this.mp4videosTapInit();
        this.initKeystrokeCounterOnForms();
    }
    initKeystrokeCounterOnForms(){
        let keystrokeCounter = '.contactForm-KeystrokeCount';
        $("form").keyup(function(){
            let counter = $(keystrokeCounter, this);
            if(counter.length !== 0){
                let currentCountString = counter.val();
                let currentCount = parseInt(currentCountString);
                counter.val(currentCount+=1);
            }
        });
    }
    mp4init(){
        $(".vjs-tech").addClass("swiper-no-swiping");
    }
    mp4videosTapInit(){
        $(document).ready( function() {
            $( ".video-js" ).each(function() {
                let myID =   $( this ).attr('id')
                var myPlayer = videojs(myID);
                myPlayer.on('touchstart', function (e) {
                    if (e.target.nodeName === 'VIDEO') {
                        if (myPlayer.paused()) {
                            this.play();
                        } else {
                            this.pause();
                        }
                    }
                });
            });
        });
    }

    mp4autoplay(){
        let activeSlide =  $('.testimonial-slider-compilation').find('.swiper-slide-active');
        //console.log(activeSlide);
        $(activeSlide).each((index, slide) => {
            //$("video", slide).trigger('click');
            console.log($("video", slide));
            //$("video", slide).css('border','1px solid red');
            //console.log(index);
            //console.log(item);
        });
        // $("video", activeSlide).trigger('play');
    }

    initVideoplayerPopup(){
        let _this = this;
        let activeVideoSection = null;
        let activeVideoElement = null;

        $(window).resize(function (){
            if(activeVideoSection != null && activeVideoSection.hasClass('showBig')){
                _this.videoplayerRecalculateDimensions(activeVideoElement, true);
            }
        });

        $(".mp4-videoplayer-resolution img").click(function (){
            activeVideoSection = $(this).parents("section");
            activeVideoElement = activeVideoSection.find(".mp4-videoplayer-element");
            if(activeVideoSection.hasClass('showBig')){
                _this.videoplayerRecalculateDimensions(activeVideoElement, false);
            }
            _this.videoplayerToggleClasses(activeVideoElement, activeVideoSection);
        });

        $(".videoplayer-resolution img").click(function (){
            activeVideoSection = $(this).parents(".videoplayer-parent-element");
            activeVideoElement = activeVideoSection.find(".videoplayer-element");
            if(activeVideoSection.hasClass('showBig')){
                _this.videoplayerRecalculateDimensions(activeVideoElement, false);
            }
            _this.videoplayerToggleClasses(activeVideoElement, activeVideoSection);
        });

        $(".videoplayer-overlay").click(function (){
            _this.videoplayerRecalculateDimensions(activeVideoElement, false);
            _this.videoplayerToggleClasses(activeVideoElement, activeVideoSection);
        });
        $(".videoplayer-testimonial-overlay").click(function (){
            _this.videoplayerRecalculateDimensions(activeVideoElement, false);
            _this.videoplayerToggleClasses(activeVideoElement, activeVideoSection);
        });
    }

    videoplayerToggleClasses(activeVideoElement, activeVideoSection){
        let testimonialSwiperElement = activeVideoElement.parents(".testimonial-swiper-video")
        activeVideoElement.toggleClass("video-fullsize");
        activeVideoSection.toggleClass("showBig");
        if (testimonialSwiperElement.length > 0) {
            if(testimonialSwiperElement.hasClass('video-switch-arrows')){
                testimonialSwiperElement.toggleClass("arrow");
            }
            testimonialSwiperElement.toggleClass("video-fullsize");
            $(".videoplayer-testimonial-overlay", testimonialSwiperElement).toggleClass("show");
        }else{
            $(".videoplayer-overlay").toggleClass("show");
        }
        $(window).resize();
    }
    videoplayerRecalculateDimensions(activeVideoElement, switch2Fullsize){
        if(switch2Fullsize == false){
            activeVideoElement.css("height", 'inherit');
            activeVideoElement.css("width", 'inherit');
            activeVideoElement.css("margin", 'inherit');
        } else {
            let windowWidth = $(window).width();
            let windowHeight = $(window).height();
            let calculatedWidth = (windowHeight/9)*16;
            let calculatedHeight = (windowWidth/16)*9;

            let videoHeight =  calculatedHeight*0.9;
            let videoWidth =  windowWidth*0.9;

            if( calculatedHeight > windowHeight){
                videoHeight =  windowHeight*0.9;
                videoWidth =  calculatedWidth*0.9;
            }

            let videoVerticalMargin = (windowHeight - videoHeight) / 2;
            let videoHorizontalMargin = (windowWidth - videoWidth) / 2;

            activeVideoElement.css("height", videoHeight);
            activeVideoElement.css("width", videoWidth);
            activeVideoElement.css("margin", videoVerticalMargin+'px '+videoHorizontalMargin+'px');
        }
    }


    hoverToPartnerLogo() {
        let _this = this;
        let defaultLogo = $(".defaultLogo");
        let partnerLogo = $(".partnerLogo");
        let dropDownLogo = $(".dropdown-menu-logo");
        partnerLogo.mouseenter(function () {
            dropDownLogo.addClass("deactive");
            partnerLogo.addClass("active");
            defaultLogo.removeClass("active");
        }).mouseleave(function () {
            dropDownLogo.removeClass("deactive");
            partnerLogo.removeClass("active");
            defaultLogo.addClass("active");
        });
    }

    setTelPrefixContactForm(clientIpInfo) {
        if ($("select[name='prefix_number']").length > 0) {
            $("select[name='prefix_number']").val(clientIpInfo.country).trigger('change');
        }
    }



    initAdvertisementWindow() {
        let advertisement = $("#advertisement");

        if (advertisement.length >= 1) {

            let name = advertisement.attr("data-ad-name");
            let reactTimeAd = localStorage.getItem(name);
            var currentDateTime = new Date();
            if (reactTimeAd === "null" || reactTimeAd < currentDateTime){
                this.advertisementShow(advertisement);
            }

            $(".confirm-advertisment", advertisement).click(() => {
                let currentDateTimePlusAdDuration = currentDateTime.addDays(adDuration);
                localStorage.setItem(name, currentDateTimePlusAdDuration);

                if(advertisement.hasClass("ad-type-button")){
                    this.advertisementHide(advertisement);
                }
            });

            $(".close-advertisment", advertisement).click(() => {
                this.advertisementHide(advertisement);
            });
        }
    };

    advertisementShow(ad){
        ad.addClass('show');
    }

    advertisementHide(ad){
        ad.removeClass('show');
    }



    setTelPrefixToDefault() {
        $("select[name='prefix_number']").change(function () {
            var s = $("select[name='prefix_number'] option:selected").text();
            var x = $("select[name='prefix_number']").parent().find(".placeholder-text").html(s.split(' (')[0]);
        });
    }


    getClientIPInfo() {
        let result = null;
        $.ajax({
            url: "https://ipinfo.io",
            dataType: "json",
            async: false,
            success: function (data) {
                result = data;
            }
        });

        return result;
    }

    showPrivacyInformation() {
        $(document).on('click', '.showPrivacyInformation', function(e) {
            $('#privacyInformationPopover').modal({  closeExisting: false});
        });
    }

    initTestimonialSliderMultiple() {
        let multipleTestimonialSlider = new Swiper('.testimonial-slider-multiple', {
            direction: 'horizontal',
            loop: true,
            speed: 500,
            observer: true,
            observeParents: true,
            freeMode: false,
            watchSlidesVisibility: true,
            watchSlidesProgress: true,
            watchActiveIndex: true,
            //initialSlide: Math.floor(Math.random() * 5),
            autoplay: false,
            slidesPerView: "auto",
            centeredSlides: true,
            //autoHeight: true,
            simulateTouch: false,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            }
        });

        $(".swiper-button-next").click((e) => {
            let _this = $(e.currentTarget);
            let slider = _this.parents(".swiper-container");

            this.stopVideosInSlider(slider)
        });

        $(".swiper-button-prev").click((e) => {
            let _this = $(e.currentTarget);
            let slider = _this.parents(".swiper-container");

            this.stopVideosInSlider(slider)
        });

        $(".testimonial-slider-multiple .play-icon").click((e) => {
            let _this = $(e.currentTarget);

            multipleTestimonialSlider.autoplay.stop();
        });

        $(".playVideoTestimonial-mobile", ".testimonial-slider-multiple").each((index, videoWrapper) => {
            let video = $(".video", videoWrapper);
            let swiper = video.parents('.swiper-slide');
            if(swiper.hasClass('swiper-slide-active')){

                let src = video.attr('data-src');
                video.attr('src', src);
            } else {
                video.attr('src', '');
            }
        });
    }

    initTestimonialSliderCompilation() {
        let compilationTestimonialSlider = new Swiper('.testimonial-slider-compilation', {
            direction: 'horizontal',
            loop: false,
            speed: 500,
            observer: true,
            observeParents: true,
            freeMode: false,
            watchSlidesVisibility: true,
            watchSlidesProgress: true,
            watchActiveIndex: true,
            initialSlide: 0,
            autoplay: false,
            slidesPerView: "auto",
            centeredSlides: true,
            //autoHeight: true,
            simulateTouch: false,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            }
        });

        $(".swiper-button-next").click((e) => {
            let _this = $(e.currentTarget);
            let slider = _this.parents(".swiper-container");

            this.stopVideosInSlider(slider)
        });

        $(".swiper-button-prev").click((e) => {
            let _this = $(e.currentTarget);
            let slider = _this.parents(".swiper-container");

            this.stopVideosInSlider(slider)
        });

        $(".testimonial-slider-compilation .play-icon").click((e) => {
            let _this = $(e.currentTarget);

            compilationTestimonialSlider.autoplay.stop();
        });

        setTimeout(() => {
            $("video", '.testimonial-slider-compilation').each(function() {
                let slide = $(this).parents(".swiper-slide-active");
                if(slide.length === 0){
                    $( this ).trigger('pause');
                }
            });


        }, 1000);


    }

    stopVideosInSlider(slider) {
        let videoplayerResolutionSwitch = slider.find('.videoplayer-resolution');
        let prevButton = slider.find('.swiper-button-prev');
        let nextButton = slider.find('.swiper-button-next');
        let activeSlide = slider.find('.swiper-slide-active');


        videoplayerResolutionSwitch.removeClass('active');
        prevButton.addClass('active');
        nextButton.addClass('active');

        $(".playVideoTestimonial-mobile", slider).each((index, videoWrapper) => {
            let video = $(".video", videoWrapper);
            let swiper = video.parents('.swiper-slide');
            if(swiper.hasClass('swiper-slide-active')){
                let src = video.attr('data-src');
                //alert(src);
                video.attr('src', src);
            } else {
                video.attr('src', '');
            }
        });

        $(".playVideoTestimonial", slider).each((index, videoWrapper) => {
            let videoExists = false;
            if ($(".video", videoWrapper).length > 0) {
                videoExists = true;
            }

            if (videoExists === true) {
                let video = $(".video", videoWrapper);
                video.remove();
                $(videoWrapper).removeClass("loading");
                $(videoWrapper).removeClass("hidePlayIcon");
            }
        });

        if($("video", activeSlide).length > 0){
            $("video", activeSlide).trigger('pause');
        }
    }

    initLazyLoadImage() {
        let lazyLoadInstance = new LazyLoad({
            elements_selector: ".lazy"
            // ... more custom settings?
        });
    }


    getScrollHeaderDifference() {
        return 30; //TODO: Difference between expanded/collapse header height on scroll
    }

    getHeaderHeight() {
        return this.header.getHeight();
    }

    getFooterHeight() {
        return $(this.footer).outerHeight(true);
    }

    initVideoTestimonials() {
        $(document).on('click', '.playVideoTestimonial', (e) => {
            let _this = $(e.currentTarget);
            let src = _this.attr("data-yt");
            let img = _this.find("img");
            let videoplayerParentElement = _this.parents('.videoplayer-parent-element');
            let videoplayerResolutionSwitch = videoplayerParentElement.find('.videoplayer-resolution');
            let prevButton = videoplayerParentElement.find('.swiper-button-prev');
            let nextButton = videoplayerParentElement.find('.swiper-button-next');
            videoplayerResolutionSwitch.addClass('active');
            prevButton.removeClass('active');
            nextButton.removeClass('active');

            _this.addClass("loading");

            $(_this).append($('<iframe>'));

            let videoFrame = _this.find("iframe");
            videoFrame.addClass("video");
            videoFrame.attr("allow", "autoplay");
            videoFrame.attr("allowfullscreen", "allowfullscreen");
            videoFrame.attr("src", "https://www.youtube.com/embed/" + src + "?autoplay=1&rel=0&fs=1&wmode=transparent");
            
            videoFrame.on("load", (e) => {
                let _this = $(e.currentTarget);
                _this.parents(".playVideoTestimonial").addClass("hidePlayIcon");
            });
        });

        this.fullScreenMode = document.fullScreen || document.mozFullScreen || document.webkitIsFullScreen; // This will return true or false depending on if it's full screen or not.


        // WORKAROUND FOR SAFARI - Because if section with class arrows contains YT video, video fullscreen show incorect
        let sectionWithArrowClass = [];
        $(document).on('mozfullscreenchange webkitfullscreenchange fullscreenchange', function () {
            this.fullScreenMode = !this.fullScreenMode;

            if (this.fullScreenMode === false) {
                sectionWithArrowClass.forEach(function (item) {
                    $(item).addClass("arrow");
                });

                sectionWithArrowClass = [];
            } else {
                $("section").each(function (i, el) {
                    if ($(el).hasClass("arrow")) {
                        sectionWithArrowClass.push(el);
                        $(el).removeClass("arrow");
                    }
                });
            }
        });


    }

    initSliders() {
        let slider = new Swiper('.default-slider', {
            direction: 'horizontal',
            loop: true,
            speed: 500,
            autoHeight: true,
            autoplay: false,
            shortSwipes: false,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            },
            observer: true,
            observeParents: true,
            freeMode: false,
            watchSlidesVisibility: true,
            watchSlidesProgress: true,
            watchActiveIndex: true,
            initialSlide: Math.floor(Math.random() * 5),
            slidesPerView: "auto",
            centeredSlides: true,
            simulateTouch: false,
            //autoHeight: true,

        });
    }
    initTestimonialSliderSingle() {
        let slider = new Swiper('.testimonial-slider-single', {
            direction: 'horizontal',
            loop: true,
            speed: 500,
            autoHeight: true,
            autoplay: false,
            shortSwipes: false,
            observer: true,
            observeParents: true,
            freeMode: false,
            watchSlidesVisibility: true,
            watchSlidesProgress: true,
            watchActiveIndex: true,
            initialSlide: Math.floor(Math.random() * 5),
            slidesPerView: "auto",
            centeredSlides: true,
            simulateTouch: false,
            //autoHeight: true,

        });
    }

    initCoverParallax() {
        let speedCover;
        let speedInner;

        if (this.platform.iOSDevice() && this.platform.deviceModel()) {
            speedCover = -1;
            speedInner = -1;
        } else {
            speedCover = -4;
            speedInner = -5;
        }

        if ($('.cover-parallax').length) {
            let bgParallax = new Rellax('.cover-parallax', {
                speed: speedCover,
                center: false,
                wrapper: null,
                round: false,
                vertical: true,
                horizontal: false
            });
        }
        if ($('.cover-parallax-inner').length) {
            let contentParallax = new Rellax('.cover-parallax-inner', {
                speed: speedInner,
                center: false,
                wrapper: null,
                round: false,
                vertical: true,
                horizontal: false
            });
        }
    }


    initTileParallax() {
        let speedTile = -1.1;
        let speedInner = -1.2;

        if ($('.tile-parallax').length) {
            let bgParallax = new Rellax('.tile-parallax', {
                speed: speedTile,
                center: true,
                wrapper: null,
                round: false,
                vertical: true,
                horizontal: false
            });
        }
        if ($('.tile-parallax-inner').length) {
            let contentParallax = new Rellax('.tile-parallax-inner', {
                speed: speedInner,
                center: true,
                wrapper: null,
                round: false,
                vertical: true,
                horizontal: false
            });
        }
    }

    initStickyParallax() {
        let speedTile = 0;
        let speedInner = -0.8;

        if ($('.sticky-parallax').length) {
            let bgParallax = new Rellax('.sticky-parallax', {
                speed: speedTile,
                center: true,
                wrapper: null,
                round: false,
                vertical: true,
                horizontal: false
            });
        }
        if ($('.sticky-parallax-inner').length) {
            let contentParallax = new Rellax('.sticky-parallax-inner', {
                speed: speedInner,
                center: true,
                wrapper: null,
                round: false,
                vertical: true,
                horizontal: false
            });
        }
    }


    initNumberCounters() {
        $('[data-number-counter="true"]').each(function (index) {
            let counter = new NumberCounter($(this));
        });
    }

    initTileHighlightOnScroll() {
        let _this = this;
        if (!this.platform.isDesktop()) {
            $('[data-hightlight="true"]').each(function (index) {
                let tile = new TileHighlightOnScroll($(this), $(this).data('hightlightIsSecond'), $(this).data('highlightFullWidth'));
            });
        }
    }

    /* 
        Delayed tap/click for link/tile highlight on iOS
    */
    initHighlightTapLinks() {
        $('#content a, .tools-items .item').on('touchstart touchend', () => {
            void (0);
        });
        $('.tools-items .item, .tools-items .item a').click((e) => {
            e.preventDefault();
            let link = $(e.currentTarget);
            let url;
            if (link.prop('tagName') === 'A') {
                url = link.attr('href');
            } else {
                url = link.data('href');
            }
            if (url) {
                if (this.platform.isPhone() || this.platform.isTablet()) {
                    setTimeout(() => {
                        window.location.href = url;
                    }, 300);
                } else {
                    window.location.href = url;
                }
            }
            return false;
        });
    }

    initAnchorScroll(offsetCover) {
        console.log(window.location.hash)
        if (window.location.hash) {
            let hash = (window.location.hash).substring(1);
            // find the anchored element
            let el = $('[data-anchor="' + hash + '"]').first();
            setTimeout(() => {
                let durationMin = 0.5;
                let durationMax = 1.5;
                let durationFactor = 700;
                let offset = -(this.getHeaderHeight() + offsetCover);

                let elementTop = Math.round(el.offset().top + (!isNaN(offset) ? offset : 0));
                let currentPageTop = $(document).scrollTop();
                let distance = elementTop - currentPageTop;
                let duration = distance / durationFactor;

                // set duration bounds
                duration = Math.max(duration, durationMin);
                duration = Math.min(duration, durationMax);

                $('html, body').stop().animate({scrollTop: elementTop}, duration * 300, 'swing');
            }, 300);
        }
    }
    clickToAnchorScroll() {
        let _this = this;
        $(".link-to-scroll").click(function () {
            //consoleF.log('clickToAnchorScroll function');
            let button = $(this);
            let anchor = $(button).attr('data-scroll-anchor')
            //$(_this.mobileMenu._mobileIcon).click();
            NavigationUtils.scrollToElement($("[data-anchor='" + anchor + "']"), (-175));
        });
    };

    logGAtracking() {
        $(document).on('click', '.GA_TimeTrade_log', function(e) {
            gtag('event', 'click',{'event_category': 'external-links', 'event_label': 'timetrade'});
        });
    }

    logDownloadProduct() {
        let _this = this;
        $(document).on('click', '.ga-track-download-file', function(e) {
            let link = $(this);
            let filename = _this.fileNameFromUrl(link.attr("href"));
            gtag('event', 'download',{'event_category': 'product-file', 'event_label': filename});
        });
    }
     fileNameFromUrl(url) {
        var matches = url.match(/\/([^\/?#]+)[^\/]*$/);
        if (matches.length > 1) {
            return matches[1];
        }
        return null;
    }
    initShortlinkClipboardCopy() {
        $(document).on('click', '.shortlink-Copy2Clipboard', function(e) {
            let url = $("input[name='url']", this);
            let button = $(this).parents(".shortlink2copy");
            let msg = button.find('.shortlink-Copy2Clipboard-msg');

            url.toggleClass('hide').select();
            document.execCommand("copy");
            url.toggleClass('hide');
            $(msg).fadeIn();
            setTimeout($(msg).fadeOut(3000), 10000);
            return false;
        });
    }
}
