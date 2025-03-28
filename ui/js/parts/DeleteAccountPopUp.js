import {fireEvent} from './utils/Event.js?v=2';

export class DeleteAccountPopUp {

    constructor() {

        const onDeleteAccountButtonClicked = this.onDeleteAccountButtonClicked.bind(this);
        this.closeAccountButton.addEventListener('click', onDeleteAccountButtonClicked, false);

        const onConfirmDeleteAccountButton = this.onConfirmDeleteAccountButton.bind(this);
        this.confirmButton.addEventListener('click', onConfirmDeleteAccountButton, false);

        const onKeydown = this.onKeydown.bind(this);
        window.addEventListener('keydown', onKeydown, false);

        const onCloseButtonClick = this.onCloseButtonClick.bind(this);
        this.closePopUpButton.addEventListener('click', onCloseButtonClick, false);
    }

    //https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/key
    onKeydown(e) {
        if (e.defaultPrevented) {
            return; // Do nothing if the event was already processed
        }
        switch (e.key) {
            case 'Esc': // IE/Edge specific value
            case 'Escape':
                this.close();
                break;
            default:
                return;
        }
        // Cancel the default action to avoid it being handled twice
        e.preventDefault();
    }

    onConfirmDeleteAccountButton(e) {
        e.preventDefault();
        this.accountForm.submit();

        this.card.classList.add('is-hidden');
        this.contentDiv.classList.add('is-hidden');

    }

    onDeleteAccountButtonClicked(e) {
        e.preventDefault();

        // close other panels
        const card = document.querySelector('.details-card#enrich-all-popup');
        if (card && !card.classList.contains('is-hidden')) {
            fireEvent('enrichAllPopUpClosed');
            card.classList.add('is-hidden');
        }

        this.card.classList.remove('is-hidden');
        this.contentDiv.classList.remove('is-hidden');
    }

    onCloseButtonClick(e) {
        e.preventDefault();
        this.close();
    }

    close() {
        fireEvent('closeAccountPopUpClosed');
        this.card.classList.add('is-hidden');

        return false;
    }

    get contentDiv() {
        return this.card.querySelector('div.content');
    }

    get card() {
        return document.querySelector('.details-card#close-account-popup');
    }

    get closePopUpButton() {
        return this.card.querySelector('.delete');
    }

    get accountForm() {
        return document.getElementById('close-account-form');
    }

    get confirmButton() {
        return document.getElementById('confirm-close-account-button');
    }

    get closeAccountButton() {
        return document.getElementById('close-account-btn');
    }
}
