class Store {
    constructor() {
        this.stringUtils = new StringUtils();
        this.numberUtils = new NumberUtils();

        this.limitForExtraOrder = 5000; // USD


        this.initStore();
        this._storeFormEl = "#frm-storeForm";
        this._customFieldBundleIdWrapper = "#customfield-bundleId";
        this._customFieldBundleIdRequired = 'input[name="bundleId_required"]';
        this._processSubmitButtonEl = 'input[name="process"]';
        this._detailOrderWrapper = '.detailOrderWrapper';
        this._extraOrderEl = '.extra-order';
        this._nonExtraOrderEl = '.non-extra-order';
        this._inputPersonalInformationRequired = 'input[name="personal_info_required"]';

        this._storeForm = "_storeForm";
        this.trackSendingDuration();


        // AJAX FORM SENT
        $.nette.ext(this._storeForm, {
            before: (jqXHR, settings) => {
                if (settings.nette && settings.nette.el) {
                    if (this.isInCorrespondingSection(settings.nette.form)) {
                        $(settings.nette.el).addClass("sending");
                        $(settings.nette.el).attr("data-clicked", Date.now());

                        let selectedItems = 0;
                        $(".quantity:enabled", "." + this._storeForm).each((index, item) => {
                            let quantity = parseInt($(item).val());

                            if (quantity > 0) {
                                selectedItems += quantity;
                            }
                        });

                        if (selectedItems > 0) {
                            $(".store-error-selected-items").hide();
                        } else {
                            $(".store-error-selected-items").show();
                            jqXHR.abort();
                            $(settings.nette.el).removeClass("sending");
                        }
                    }
                }
            },
            complete: (jqXHR, status, settings) => {
                if (settings.nette && settings.nette.el) {
                    if (this.isInCorrespondingSection(settings.nette.form)) {
                        NavigationUtils.scrollToElement($("#buy-headline"), -this.getHeaderHeight());
                        $(settings.nette.el).removeClass("data-clicked");
                        $(settings.nette.el).removeClass("sending");
                        let totalQuantity = 0;

                        $(".quantity:enabled", "." + this._storeForm).each((index, item) => {
                            let quantity = parseInt($(item).val());
                            if (quantity > 0) {
                                totalQuantity += quantity;
                            }
                        });
                        gtag('event', 'form', {
                            'event_category': "send",
                            'event_label': "not-confirmed-order",
                            'event_value': totalQuantity
                        });
                    }
                }
            }
        });
    }


    isInCorrespondingSection(el) {
        return $(el).hasClass(this._storeForm);
    }

    trackSendingDuration(){
        let _this = this;
        let submitButton = $(this._processSubmitButtonEl, this._storeFormEl);
        setInterval(function(){
            var timestampClickedString = submitButton.attr("data-clicked");
            if(typeof timestampClickedString !== "undefined"){
                let currentDatTime = new Date();
                var timestampClicked = new Date(parseInt(timestampClickedString));

                var seconds = (currentDatTime.getTime() - timestampClicked.getTime()) / 1000;

                if(seconds > 8){
                    submitButton.removeAttr("data-clicked");
                    _this.removeSendingClassFromSubmitFormButton(submitButton);
                }
            } else {
                _this.removeSendingClassFromSubmitFormButton(submitButton);
            }

        }, 1000);
    }

    removeSendingClassFromSubmitFormButton(el){
        el.removeClass("sending");
    }

    initStore() {
        this._quantity = $("input.quantity");
        this._selectProductRadioInput = ".selectProduct";
        this._minusQuantityButton = $("button.minusQuantity");
        this._plusQuantityButton = $("button.plusQuantity");
        this._premiumSupportChk = $("input[type='checkbox'].selectGlobalPremiumSupportChk");
        this._buyBlock = $(".buy-block");

        this.setSectionType();

        this.initWatchClickPlusQuantityButton();
        this.initWatchClickMinusQuantityButton();
        this.initWatchClickQuantityInput();
        this.initWatchManualChangeQuantity();
        this.initWatchProductQuantityChange();

        this.initWatchClickSelectProduct();
        this.initWatchClickSelectProductChk();
        this.initWatchClickGlobalPremiumSupportChk();

        $(this._storeFormEl).trigger("reset");
        $(this._processSubmitButtonEl, this._storeFormEl).removeClass("sending");
        $(this._processSubmitButtonEl, this._storeFormEl).removeAttr("data-clicked");

        //$(this._selectProductRadioInput).trigger('change',{'isTriggeredBySystem':true})
        //$(".selectProductChk").trigger('change',{'isTriggeredBySystem':true});
        let _this = this;
        setTimeout(function () {
            let firstInit = true;
            $("input.quantity").each((index, item) => {
                let quantity = _this.getProductQuantity($(item));
                let product = _this.getElementParentProduct($(item));

                if (quantity > 0) {
                    firstInit = false;
                    $(item).trigger("click", {"force": true});
                    $(item).val(quantity);

                    if ($(".selectProductChk", product).length > 1) {
                        $(".selectProductChk", product).prop('checked', true);
                    }
                }
                $(item).change();
            });

            if(firstInit) {
                $("input.quantity").each((index, item) => {
                    let quantity = _this.getProductQuantity($(item));
                    let product = _this.getElementParentProduct($(item));

                    if (product.attr("data-select-default") === "true") {
                        $(".selectProduct", product).prop('checked', true).attr("data-active", 1).trigger('change', {'isTriggeredBySystem': true});
                    }
                });
            }

            if(_this._premiumSupportChk.length > 0) {
                _this._premiumSupportChk.change();
            }

        }, 100);

    }

    initWatchClickSelectProduct() {
        let _this = this;
        $(document).on("click", _this._selectProductRadioInput, function (e) {
            let radioInput = $(this);

            let radioGroup = radioInput.attr("name");
            if (radioInput.attr("data-active") === "1") {
                $(_this._selectProductRadioInput + '[name="' + radioGroup + '"]').attr("data-active", 0);
                radioInput.prop("checked", false);
                radioInput.trigger('change', {'isTriggeredBySystem': true})
            } else {
                $(_this._selectProductRadioInput + '[name="' + radioGroup + '"]').attr("data-active", 0);
                radioInput.attr("data-active", 1);
                radioInput.prop("checked", true);
                radioInput.trigger('change', {'isTriggeredBySystem': true})
            }
        });

        $(this._selectProductRadioInput).change((e, o = {}) => {
            if ("isTriggeredBySystem" in o) {
                let _this = $(e.currentTarget);

                let product = this.getElementParentProduct(_this);
                let buyBlock = this.getProductParentBuyBlock(product);
                let input = this.getProductNumberInput(product);


                if (_this.is(':checked')) {
                    this.setProductAsActive(product);
                    this.setProductAsEnabled(input);
                    input.attr("min", input.attr("data-min"));
                    this.setProductQuantity(product, input.attr("data-min"));
                } else {
                    this.setProductAsInactive(product);
                    this.setProductQuantity(product, 0);
                    input.attr("min", 0);

                }
                input.change();

                $(".product", buyBlock).not(product).each((index, item) => {
                    // SET PRODUCT AS INACTIVE - SET QUANTITY TO 0
                    this.setProductAsInactive(item);
                    this.setProductQuantity(item, 0);
                    let input = this.getProductNumberInput(item);
                    input.attr("min", 0);
                    //this.setProductAsDisabled(input);
                    input.change();
                });
            }
        });

    }

    initWatchClickSelectProductChk() {
        $(".selectProductChk").change((e) => {
            let _this = $(e.currentTarget);

            let product = this.getElementParentProduct(_this);
            let input = this.getProductNumberInput(product);


            if (_this.prop('checked')) {
                this.setProductAsActive(product);
                if (product.attr("data-disabled-change-quantity") === "false") {
                    input.attr("min", input.attr("data-min"));
                    this.setProductQuantity(product, input.attr("data-min"));
                }
                //this.setProductAsEnabled(input);
            } else {
                this.setProductAsInactive(product);
                //this.setProductAsDisabled(input);
                if (product.attr("data-disabled-change-quantity") === "false") {
                    this.setProductQuantity(product, 0);
                    input.attr("min", 0);
                }
            }


            input.change();
        });
    }

    initWatchClickGlobalPremiumSupportChk() {
        this._premiumSupportChk.change((e) => {

            let _this = this;
            let premiumChk = $(e.currentTarget);
            let ps_checkboxes = premiumChk.parents(this._buyBlock).find(".product_ps .selectProductChk");

            ps_checkboxes.each(function () {
                let product_quantity = $(this).parents(".product").find("input.quantity");
                let productNumberInput = product_quantity;
                let quantity = _this.getProductQuantity(productNumberInput);

                if (quantity != 0) {
                    $(this).prop("checked", premiumChk.prop("checked"));
                    $(this).change();
                } else {
                    $(this).prop("checked", false);
                    $(this).change();
                }

                if ($(this).is(':checked')) {
                    _this.setProductAsEnabled(productNumberInput);
                } else {
                    _this.setProductAsDisabled(productNumberInput);
                }

            });

        });
    }


    setSectionType() {
        this._buyBlock.each((index, item) => {
            let blockGroupSection = index;
            let sectionType = $(item).attr("data-store-type");

            if (sectionType === "sli") {
                $(".product", item).each((index, product) => {
                    let productNameEle = $(".name", product);

                    // SET PRODUCT AS INACTIVE - SET QUANTITY TO 0
                    this.setProductAsInactive(product);
                    this.setProductQuantity(product, 0);
                    let input = this.getProductNumberInput(product);
                    input.change();


                    // ADD CHECKBOX BEFORE PRODUCT NAME
                    productNameEle.prepend("<input type='radio' name='selectProduct" + blockGroupSection + "[]' class='selectProduct'>");
                });
            } else if (sectionType === "chk") {
                $(".product", item).each((index, product) => {
                    let productNameEle = $(".name", product);
                    let input = this.getProductNumberInput(product);

                    this.setProductAsInactive(product);
                    //this.setProductAsDisabled(input);
                    //input.attr("min", input.attr("data-min"));
                    //this.setProductQuantity(product, input.attr("data-min"));

                    input.change();


                    // ADD CHECKBOX BEFORE PRODUCT NAME
                    productNameEle.prepend("<input type='checkbox' name='selectProductChk' class='selectProductChk'>");
                });
            }
        });
    }

    initWatchClickQuantityInput() {
        let _this = this;
        $(document).on("click", "input.quantity", function (e, o) {
            let product = _this.getElementParentProduct($(this));
            let input = _this.getProductNumberInput(product);
            let productEnabledByClick = _this.enableProductThroughtCheckboxOrRadionButton(product, o);

            /*if(!productEnabledByClick) {
                this.plusOneQuantityToInput(product, input);
                input.change();
            }*/
        });
    }

    initWatchClickPlusQuantityButton() {
        let _this = this;
        this._plusQuantityButton.click((e) => {
            let _this = $(e.currentTarget);
            let product = this.getElementParentProduct(_this);
            let input = this.getProductNumberInput(product);
            let productEnabledByClick = this.enableProductThroughtCheckboxOrRadionButton(product);


            if (!productEnabledByClick) {
                this.plusOneQuantityToInput(product, input);
                input.change();
            }
        });
    }

    initWatchManualChangeQuantity() {
        this._quantity.keyup((e) => {
            let _this = $(e.currentTarget);

            var min = parseInt(_this.attr('min')) || 0;
            var max = parseInt(_this.attr('max')) || "";
            var val = parseInt(_this.val()) || 0;
            if (val < min) {
                _this.val(min);
            } else if (max !== "" && val > max) {
                _this.val(max);
            } else {
                _this.val(val);
            }

        });
    }

    plusOneQuantityToInput(product, input) {

        let min = parseInt(input.attr("min"));
        let max = parseInt(input.attr("max"));
        let quantity = this.getProductQuantity(input);
        let newQuantity = quantity + 1;
        if (newQuantity > max) {
            this.setProductQuantity(product, quantity);
        } else {
            if (newQuantity < min) {
                this.setProductQuantity(product, min);
            } else {
                this.setProductQuantity(product, newQuantity);
            }
        }
    }

    initWatchClickMinusQuantityButton() {
        this._minusQuantityButton.click((e) => {
            let _this = $(e.currentTarget);
            let product = this.getElementParentProduct(_this);
            let input = this.getProductNumberInput(product);

            this.minusOneQuantityToInput(product, input);

            input.change();
        });
    }

    minusOneQuantityToInput(product, input) {

        let min = parseInt(input.attr("min"));
        let max = parseInt(input.attr("max"));
        let quantity = this.getProductQuantity(input);
        let newQuantity = quantity - 1;
        if (newQuantity < min) {
            let enabled = this.disableProductThroughtCheckboxOrRadionButton(product);
            //if(!enabled) {
            this.setProductQuantity(product, 0);
            //}
        } else {
            this.setProductQuantity(product, newQuantity);
        }
    }

    enableProductThroughtCheckboxOrRadionButton(product, config = {}) {
        let _this = this;

        if ($(".selectProductChk", product).length > 0) {
            if (!$(".selectProductChk", product).is(':checked') || "force" in config) {
                $(".selectProductChk", product).prop('checked', true).trigger('change', {'isTriggeredBySystem': true})
                return true;
            }

            //$(".selectProductChk", product).trigger('change', {'isTriggeredBySystem': true})
        }

        if ($(_this._selectProductRadioInput, product).length > 0) {
            let radioInput = $(_this._selectProductRadioInput, product);
            let radioGroup = radioInput.attr("name");

            if (radioInput.attr("data-active") !== "1") {
                $(_this._selectProductRadioInput + '[name="' + radioGroup + '"]').attr("data-active", 0);
                radioInput.prop("checked", true);
                radioInput.attr("data-active", 1);
                radioInput.trigger('change', {'isTriggeredBySystem': true})
                return true;
            }
        }

        return false;
    }

    disableProductThroughtCheckboxOrRadionButton(product) {
        let _this = this;
        if ($(".selectProductChk", product).length > 0) {
            if ($(".selectProductChk", product).is(':checked')) {
                $(".selectProductChk", product).prop('checked', false).change();
                return true;
            }
        }

        if ($(_this._selectProductRadioInput, product).length > 0) {
            let radioInput = $(_this._selectProductRadioInput, product);
            let radioGroup = radioInput.attr("name");

            if (radioInput.attr("data-active") === "1") {
                $(_this._selectProductRadioInput + '[name="' + radioGroup + '"]').attr("data-active", 0);
                radioInput.prop("checked", false);
                radioInput.trigger('change', {'isTriggeredBySystem': true})
                return true;
            }
        }

        return false;
    }

    initWatchProductQuantityChange() {
        this._quantity.change((e) => {
            let productNumberInput = $(e.currentTarget);
            let form = this.getForm(productNumberInput);
            let formSubmitButton = this.getSubmitButton(form);
            let productNumberInputName = productNumberInput.attr("name");
            let product = this.getElementParentProduct(productNumberInput);
            let productProperties = this.getProductProperties(product);
            let quantity = this.getProductQuantity(productNumberInput);

            let setSameQuantityFor = product.attr("data-same-quantity-for");
            let setPairedWith = product.attr("data-paired-with");
            let setPairedMinQuantity = product.attr("data-paired-min-quantity");
            let dataPairedMinCache = "data-paired-min-cache";

            if (setSameQuantityFor !== "") {
                let productMinor = $(".product[data-product-id='" + setSameQuantityFor + "']");
                this.setProductQuantity(productMinor, quantity);
                this.getProductNumberInput(productMinor).change();
                if (productMinor.hasClass('product_ps')) {
                    this._premiumSupportChk.change();
                }
            }
            if (setPairedWith !== "") {
                let productPairMinor = $(".product[data-product-id='" + setPairedWith + "']");
                let input = this.getProductNumberInput(productPairMinor);

                if (setPairedMinQuantity !== "") {
                    if (quantity > 0) {
                        if (this.getProductQuantity(input) < setPairedMinQuantity) {
                            this.setProductQuantity(productPairMinor, setPairedMinQuantity);
                        }
                        if (input.attr(dataPairedMinCache) == "") {
                            input.attr(dataPairedMinCache, input.attr("min"));
                            input.attr("data-min", setPairedMinQuantity);
                            input.attr("min", setPairedMinQuantity);
                        }
                        this.getProductNumberInput(productPairMinor).change();
                    } else {
                        if (input.attr(dataPairedMinCache) !== "") {
                            input.attr("data-min", input.attr(dataPairedMinCache));
                            input.attr("min", input.attr(dataPairedMinCache));
                            input.attr(dataPairedMinCache, "");
                        }
                        this.getProductNumberInput(productPairMinor).change();
                    }
                }
                if (productPairMinor.hasClass('product_ps')) {
                    this._premiumSupportChk.change();
                }
            }

            if (productProperties.discounts === "false") {
                let totalPrice = quantity * Number(productProperties.piece_price);

                this.setProductTotalPrice(product, totalPrice);
                this.setProductPiecePrice(product, Number(productProperties.piece_price));
            } else {
                let discounts = JSON.parse(productProperties.discounts);

                let piecePrice = this.calculatePiecePrice(productProperties.piece_price, quantity, discounts);

                let totalPrice = quantity * piecePrice;

                this.setProductTotalPrice(product, totalPrice);
                this.setProductPiecePrice(product, piecePrice);
            }

            this.openDetailOrderDetail();

            this.sumTotalPrice();
        });
    }

    // Funkce pro výpočet ceny za kus
    calculatePiecePrice(basePrice, quantity, discounts) {
        let pricePerUnit = Number(basePrice);

        // Určení typu slevy
        const isPercentageDiscount = typeof Object.values(discounts)[0] === "number";

        if (isPercentageDiscount) {
            // Procentuální sleva
            const applicableDiscount = Object.keys(discounts)
                .map(Number)
                .filter(key => key <= quantity)
                .sort((a, b) => b - a)[0];

            if (applicableDiscount) {
                const discountPercentage = discounts[applicableDiscount];
                pricePerUnit = basePrice * (1 - discountPercentage / 100);
            }
        } else {
            // Pevná sleva
            const applicableDiscount = Object.keys(discounts)
                .map(Number)
                .filter(key => key <= quantity)
                .sort((a, b) => b - a)[0];

            if (applicableDiscount) {
                const discountValue = discounts[applicableDiscount].USD;
                pricePerUnit = (basePrice - discountValue );
            }
        }

        return pricePerUnit;
    }

    openDetailOrderDetail() {
        let totalQuantity = 0;
        let _this = this;

        let requiredCustomFieldBundleId = 0;

        this._quantity.each((index, item) => {
            let name = $(item).attr("name");
            let quantity = parseInt($(item).val());
            let product = _this.getElementParentProduct($(item));
            let requiredCustomFieldsRaw = product.attr("data-customfield");

            if(quantity > 0) {
                if (requiredCustomFieldsRaw.length > 0) {
                    let requiredCustomFields = JSON.parse(requiredCustomFieldsRaw);

                    if (requiredCustomFields.includes("bundleId")) {
                        requiredCustomFieldBundleId = 1;
                    }
                }
            }

            totalQuantity += quantity;
        });

        if (totalQuantity > 0) {
            $(_this._detailOrderWrapper).slideDown();
        } else {
            $(_this._detailOrderWrapper).slideUp();
        }

        // BUNDLE ID - CUSTOM FIELD
        if(requiredCustomFieldBundleId){
            $(_this._customFieldBundleIdWrapper).show();
            $(_this._customFieldBundleIdRequired).val(1).change();
        } else {
            $(_this._customFieldBundleIdRequired).val(0).change();
            $(_this._customFieldBundleIdWrapper).hide();
        }
    }

    getForm(element) {
        return element.parents("form");
    }

    getSubmitButton(form) {
        return form.find("input[type='submit'");
    }

    getProductParentBuyBlock(product) {
        return product.parents(".buy-block");
    }

    setProductAsEnabled(input) {
        $(input).prop('disabled', false);

    }

    setProductAsDisabled(input) {
        $(input).prop('disabled', true);
    }


    setProductAsInactive(product) {
        $(product).attr("data-active", "false");
        //$(".quantity", product).attr("readonly", "true");
        //this.setProductQuantity(product, 0);
    }

    setProductAsActive(product) {
        $(product).attr("data-active", "true");
        //$(".quantity", product).removeAttr("readonly");
        //this.setProductQuantity(product, 1);
    }

    setProductQuantity(product, quantity) {
        $(".quantity", product).val(quantity);
    }

    getPiecePriceDiscountByQuantity(rprice, qty) {
        var prev = -1;
        var i;

        let lastDiscount = Object.keys(rprice)[Object.keys(rprice).length - 1];

        if (qty >= lastDiscount) {
            return rprice[lastDiscount]["USD"];
        } else {
            for (i in rprice) {
                var n = parseInt(i);
                if ((prev != -1) && (qty < n)) {
                    return rprice[prev]["USD"];
                } else {
                    prev = n;
                }
            }
        }
    }

    getProductQuantity(input) {
        return parseInt(input.val());
    }

    getProductNumberInput(product) {
        return $("input.quantity", product);
    }

    getProductProperties(product) {
        return {
            total_price: product.attr("data-total-price"),
            piece_price: product.attr("data-piece-price"),
            discounts: product.attr("data-discounts")
        }
    }

    getElementParentProduct(ele) {
        return ele.parents(".product");
    }

    sumTotalPrice() {
        let totalPrice = 0;

        $(".total-price").each((index, item) => {
            let product = this.getElementParentProduct($(item));
            let productActive = product.attr('data-active');

            if (typeof productActive !== typeof undefined && productActive !== false) {
                if (productActive === "true") {
                    let priceString = $(item).attr("data-price");
                    totalPrice += this.numberUtils.roundNumber(priceString);
                }
            } else {
                let priceString = $(item).attr("data-price");
                totalPrice += this.numberUtils.roundNumber(priceString);
            }
        });

        let formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2
        });

        this.evaluationToExternFormDueExtraOrder(totalPrice)
        /*if(totalPrice > 5000){
            $(".extra-order").show();
            $(".non-extra-order").hide();
            $('input[name="personal_info_required"]').val(1).trigger("change");
            $(this._processSubmitButtonEl, this._storeFormEl).val($(this._processSubmitButtonEl, this._storeFormEl).attr("data-extra-order-label"));

        } else {
            $(".extra-order").hide();
            $(".non-extra-order").show();
            $('input[name="personal_info_required"]').val(0).trigger("change");
            $(this._processSubmitButtonEl, this._storeFormEl).val($(this._processSubmitButtonEl, this._storeFormEl).attr("data-non-extra-order"));

        }*/

        $(".total-sum-price span").html(formatter.format(totalPrice));
    }

    /**
     * In case that order is higer then some limit the form with order will expand about new fields and insted of using FastSpring the e-mail will be send
     * @param totalPrice
     */
    evaluationToExternFormDueExtraOrder(totalPrice){
        let _this = this;

        if(totalPrice >= _this.limitForExtraOrder){
            $(_this._extraOrderEl).show();
            $(_this._nonExtraOrderEl).hide();
            $(_this._inputPersonalInformationRequired).val(1).trigger("change");
            $(this._processSubmitButtonEl, this._storeFormEl).val($(this._processSubmitButtonEl, this._storeFormEl).attr("data-extra-order-label"));
        } else {
            $(_this._extraOrderEl).hide();
            $(_this._nonExtraOrderEl).show();
            $(_this._inputPersonalInformationRequired).val(0).trigger("change");
            $(this._processSubmitButtonEl, this._storeFormEl).val($(this._processSubmitButtonEl, this._storeFormEl).attr("data-non-extra-order"));

        }
    }

    setProductTotalPrice(product, price) {
        let priceFinal = this.numberUtils.roundNumber(price);

        let formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2
        });
        $(".total-price", product).attr("data-price", priceFinal);
        this.setPremiumSupportTotalPrice(product);
        return $(".total-price span", product).html(formatter.format(priceFinal));
    }

    setPremiumSupportTotalPrice(product) {
        let _this = this;

        if (product.hasClass('product_ps')) {
            let priceFinal = 0;
            let section = product.parents(_this._buyBlock);
            let section_ps_items = section.find(".product_ps");
            let premiumSupportChk = section.find(_this._premiumSupportChk);
            section_ps_items.each(function () {
                priceFinal = priceFinal + _this.numberUtils.roundNumber($(".total-price", this).attr("data-price"));
            });
            let formatter = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2
            });
            return $(".total-price-ps span", section).html(formatter.format(priceFinal));
        }
    }

    setProductPiecePrice(product, price) {
        let priceFinal = this.numberUtils.roundNumber(price);

        let formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2
        });

        return $(".price-per-piece span", product).html(formatter.format(priceFinal));
    }
}