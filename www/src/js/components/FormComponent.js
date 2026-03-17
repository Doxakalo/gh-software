class FormComponent {
    constructor() {
        this.init();

        this.initNetteFormValidation();

        // AJAX FORM SENT
        $.nette.ext("genericFormAjax", {
            before: (jqXHR, settings) => {
                $(settings.nette.el).addClass("sending");
            },
            complete: (jqXHR, status, settings) => {
                this._fieldsVisible = false;
                $(settings.nette.el).removeClass("sending");
            }
        });
    }


    /**
     * Nette Form Validation
     */
    initNetteFormValidation() {
        Nette.showFormErrors = function(form, errors) {
            var validationType = $(form).attr("data-validationtype");

            // REMOVE ALL ERRORS FROM FORM
            $("label.error",form).removeClass("error");
            $(".error-text",form).remove();

            switch (validationType) {
                case "fields":
                    errors.forEach(function(error) {
                        var field = $(error.element, form);
                        var label = field.parent("label");
                        label.addClass("error"); // SET STYLE AS INVALID FIELD

                        // ADD ERROR MESSAGE TO FIELD
                        label.append("<span class='error-text'>"+error.message+"</span>");
                    });
            }
        }
    }

    init(){

    }
}
