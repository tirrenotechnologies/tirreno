import {Loader} from './Loader.js?v=2';
import {fireEvent} from './utils/Event.js?v=2';
import {handleAjaxError} from './utils/ErrorHandler.js?v=2';
import {renderEnrichmentCalculation} from './DataRenderers.js?v=2';

export class EnrichAllPopUp {

    constructor() {
        this.loader = new Loader();

        const onEnrichAllButtonClicked = this.onEnrichAllButtonClicked.bind(this);
        this.enrichAllButton.addEventListener('click', onEnrichAllButtonClicked, false);

        const onConfirmEnrichAllButton = this.onConfirmEnrichAllButton.bind(this);
        this.confirmButton.addEventListener('click', onConfirmEnrichAllButton, false);

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

    onConfirmEnrichAllButton(e) {
        e.preventDefault();
        this.accountForm.submit();

        this.card.classList.add('is-hidden');
        this.contentDiv.classList.add('is-hidden');

    }

    onEnrichAllButtonClicked(e) {
        e.preventDefault();

        // close other panels
        const card = document.querySelector('.details-card#close-account-popup');
        if (card && !card.classList.contains('is-hidden')) {
            fireEvent('closeAccountPopUpClosed');
            card.classList.add('is-hidden');
        }

        // call ajax
        this.loadData();
    }

    loadData(id) {
        this.contentDiv.classList.add('is-hidden');
        this.loaderDiv.classList.remove('is-hidden');
        this.card.classList.remove('is-hidden');

        const el = this.loaderDiv;
        this.loader.start(el);

        const onDetailsLoaded = this.onDetailsLoaded.bind(this);
        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;

        $.ajax({
            url: '/admin/enrichmentDetails',
            type: 'get',
            data: {token: token},
            success: onDetailsLoaded,
            error: handleAjaxError,
        });
    }

    onDetailsLoaded(data, status) {
        if ('success' !== status || 0 === data.length) {
            return;
        }

        data = this.proceedData(data);

        this.loader.stop();
        this.contentDiv.classList.remove('is-hidden');
        this.loaderDiv.classList.add('is-hidden');

        let span = null;
        //todo: foreach and arrow fn ?
        for (const key in data) {
            span = this.card.querySelector(`#details_${key}`);
            if (span) {
                span.innerHTML = data[key];
            }
        }
    }

    proceedData(data) {
        data.calculation = renderEnrichmentCalculation(data);

        return data;
    }

    onCloseButtonClick(e) {
        e.preventDefault();
        this.close();
    }

    close() {
        fireEvent('enrichAllPopUpClosed');
        this.card.classList.add('is-hidden');

        return false;
    }

    get loaderDiv() {
        return this.card.querySelector('div.text-loader');
    }

    get contentDiv() {
        return this.card.querySelector('div.content');
    }

    get card() {
        return document.querySelector('.details-card#enrich-all-popup');
    }

    get closePopUpButton() {
        return this.card.querySelector('.delete');
    }

    get accountForm() {
        return document.getElementById('enrich-all-form');
    }

    get confirmButton() {
        return document.getElementById('confirm-enrich-all-button');
    }

    get enrichAllButton() {
        return document.getElementById('enrich-all-btn');
    }
}
