class NewsDetailController extends PageController {

    constructor() {
        super();

        this.imageCompilation = ".mceWrapper-imgCompilation";
        this.imageCompilationItem = ".wrapperImgCompilation-item";
        this.mceImage = ".mceImage";

        if (this.platform.isPhone() || this.platform.isTablet()) {
            this.initAnchorScroll(40);
        } else {
            this.initAnchorScroll(40);
        }

        this.initImageCompilation();
        this.initImagesLink();
        this.showFormPopover();
        this.showShortlinkForm();
        this.initShortlinkClipboardCopy();
    }

    initImagesLink() {
        let _this = this;
        $(_this.mceImage).each(function () {
            if ($(this).parents("a").length == 0) {
                $(this).wrap('<a href="' + $(this).attr('src') + '" data-lightbox="image-1" ></a>');
            }
        });
    }

    showFormPopover() {
        $(document).on('click', '.showFormPopoverContact', function (e) {
            $('#formPopoverContact').modal();
            return false;
        });
        $(document).on('click', '.showFormPopoverMessage', function (e) {
            $('#formPopoverMessage').modal();
            return false;
        });
        $(document).on('click', '.showFormPopoverSubscribe', function (e) {
            var customFormTag = $(this).attr('data-mailchimp-tag');
            var modal = $('#formPopoverSubscribe').modal();

            $("#customFormTag", modal).val(customFormTag);
            return false;
        });
    }

    showShortlinkForm() {
        $(document).on('click', '.shortlink-FormSwitch', function (e) {
            $('.shortlinkFormContainer').toggleClass("hide");
            return false;
        });
    }


    initImageCompilation() {
        let _this = this;
        _this.recalculateImageCompilation()
        $(window).resize(function () {
            $(_this.imageCompilationItem, $(_this.imageCompilation)).attr('style', '');
            $("img", $(_this.imageCompilation)).attr('style', '');
            _this.recalculateImageCompilation()
        });
    }

    recalculateImageCompilation() {
        let _this = this;
        let maxHeight = false;
        if (this.platform.isPhone()) {
            return false;
        }
        //recalculate optimal width
        $(_this.imageCompilation).each(function () {

            $("img", this).each(function () {
                let width = $(this).parents(_this.imageCompilationItem).width();
                $(this).attr('style', 'width: ' + width + 'px !important');
                $(this).parent(_this.imageCompilationItem).css('height', $(this).height());
            });
        });

        //recalculate optimal height
        $(_this.imageCompilation).each(function () {
            maxHeight = false;
            $("img", this).each(function () {
                maxHeight = maxHeight > $(this).height() ? maxHeight : $(this).height();
            });
            $("img", this).each(function () {
                $(this).parents(_this.imageCompilationItem).css('height', maxHeight);
                let styles = 'min-height: ' + maxHeight + 'px !important; max-height: ' + maxHeight + 'px !important;';
                $(this).attr('style', styles);
            });
        });
    }
}
