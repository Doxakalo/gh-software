class FooterSubscribeForm {
	constructor(container) {
		this.container = $(container);

		this._fieldsVisible = false;

		this.initShowFieldsHandler();
	}
	
	initShowFieldsHandler(){
		let email = "#frm-subscribeNewsForm-email";


		$(".form-container").on('focus', email, () => {
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
