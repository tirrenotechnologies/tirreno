// should be used for POST forms
export class ReenrichmentButton {
    constructor() {
        if (this.reenrichmentButton) {
            const onButtonClick = this.onButtonClick.bind(this);
            this.reenrichmentButton.addEventListener('click', onButtonClick, false);
        }
    }

    onButtonClick(e) {
        e.preventDefault();
        this.reenrichmentButton.setAttribute('disabled', '');
        this.form.submit();
    }

    get reenrichmentButton() {
        return document.querySelector('#reenrichment-form .reenrichment-button');
    }

    get form() {
        return document.querySelector('#reenrichment-form');
    }
}
