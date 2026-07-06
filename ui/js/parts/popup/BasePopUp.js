import {
    fireEvent,
    handleEscape,
} from '../utils/Event.js?v=0.10.0';
import {mapKeys} from '../utils/Functions.js?v=0.10.0';

export class BasePopUp {
    constructor(formParams) {
        this.mainButtonId = formParams.mainButtonId;
        this.confirmButtonId = formParams.confirmButtonId;
        this.formId = formParams.formId;
        this.popupId = formParams.popupId;

        this.allPopups = {
            'enrich-all-popup':         'enrichAllPopUpClosed',
            'close-account-popup':      'closeAccountPopUpClosed',
            'apply-rules-preset-popup': 'applyRulesPresetPopUpClosed',
            'reset-api-key-popup':      'ResetApiKeyPopUpClosed',
        };

        const onMainButtonClick = this.onMainButtonClick.bind(this);
        this.mainButton.addEventListener('click', onMainButtonClick, false);

        const onConfirmButtonClick = this.onConfirmButtonClick.bind(this);
        this.confirmButton.addEventListener('click', onConfirmButtonClick, false);

        const onKeydown = this.onKeydown.bind(this);
        window.addEventListener('keydown', onKeydown, false);

        const onCloseButtonClick = this.onCloseButtonClick.bind(this);
        this.closePopUpButton.addEventListener('click', onCloseButtonClick, false);
    }

    onKeydown(e) {
        handleEscape(e, () => this.close(), false);
    }

    onConfirmButtonClick(e) {
        e.preventDefault();
        this.form.submit();

        this.card.classList.add('is-hidden');
        this.contentDiv.classList.add('is-hidden');

    }

    onMainButtonClick(e) {
        e.preventDefault();

        let card = null;

        const popupKeys = mapKeys(this.allPopups);
        for (let i = 0; i < popupKeys.length; i++) {
            const key = popupKeys[i];

            card = document.querySelector(`.details-card#${key}`);
            if (key !== this.popupId && card && !card.classList.contains('is-hidden')) {
                card.classList.add('is-hidden');
                fireEvent(this.allPopups[key]);
            }
        }

        this.card.classList.remove('is-hidden');
        this.contentDiv.classList.remove('is-hidden');
    }

    onCloseButtonClick(e) {
        e.preventDefault();
        this.close();
    }

    close() {
        fireEvent(this.allPopups[this.popupId]);
        this.card.classList.add('is-hidden');

        return false;
    }

    get card() {
        return document.querySelector(`.details-card#${this.popupId}`);
    }

    get closePopUpButton() {
        return this.card.querySelector('.delete');
    }

    get contentDiv() {
        return this.card.querySelector('div.content');
    }

    get form() {
        return document.getElementById(this.formId);
    }

    get confirmButton() {
        return document.getElementById(this.confirmButtonId);
    }

    get mainButton() {
        return document.getElementById(this.mainButtonId);
    }
}
