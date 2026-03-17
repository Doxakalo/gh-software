class FooterContactForm {
	constructor(container) {
		this.container = $(container);

		this._fieldsVisible = false;

		this.initShowFieldsHandler();
	}
	
	initShowFieldsHandler(){
		let textarea = "#frm-createFooterContactForm-message";


		$(".form-container").on('focus', textarea, () => {
			if (!this._fieldsVisible) {
				this._fieldsVisible = true;
				this.showFields();
			}
		});
	}

	showFields() {
		$('.more-fields', this.container).addClass('visible');
	}
}
