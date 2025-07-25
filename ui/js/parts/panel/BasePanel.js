import {Loader} from '../Loader.js?v=2';
import {Tooltip} from '../Tooltip.js?v=2';
import {fireEvent} from '../utils/Event.js?v=2';
import {handleAjaxError} from '../utils/ErrorHandler.js?v=2';

export class BasePanel {

    constructor(eventParams) {
        this.enrichment = eventParams.enrichment;
        this.type = eventParams.type;
        this.url = eventParams.url;
        this.cardId = eventParams.cardId;
        this.panelClosed = eventParams.panelClosed;
        this.closePanel = eventParams.closePanel;
        this.rowClicked = eventParams.rowClicked;

        this.loader = new Loader();

        this.itemId = null;

        const onCloseDetailsPanel = this.onCloseDetailsPanel.bind(this);
        window.addEventListener(this.closePanel, onCloseDetailsPanel, false);

        const onTableRowClicked = this.onTableRowClicked.bind(this);
        window.addEventListener(this.rowClicked, onTableRowClicked, false);

        const onKeydown = this.onKeydown.bind(this);
        window.addEventListener('keydown', onKeydown, false);

        const onCloseButtonClick = this.onCloseButtonClick.bind(this);
        this.closeButton.addEventListener('click', onCloseButtonClick, false);

        if (this.enrichment) {
            const onEnrichmentButtonClick = this.onEnrichmentButtonClick.bind(this);
            this.reenrichmentButton.addEventListener('click', onEnrichmentButtonClick, false);
        }

        this.allPanels = {
            'event':    {id: 'event-card',  closeEvent: 'eventPanelClosed'},
            'logbook':  {id: 'logbook-card',closeEvent: 'logbookPanelClosed'},
            'email':    {id: 'email-card',  closeEvent: 'emailPanelClosed'},
            'device':   {id: 'device-card', closeEvent: 'devicePanelClosed'},
            'phone':    {id: 'phone-card',  closeEvent: 'phonePanelClosed'},
        };
    }

    //https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/key
    onKeydown(e) {
        // procced keydown even if event was processed

        switch (e.key) {
            case 'Esc': // IE/Edge specific value
            case 'Escape': {
                this.close();

                break;
            }

            default: {
                return;
            }
        }

        // Cancel the default action to avoid it being handled twice
        e.preventDefault();
    }

    onEnrichmentButtonClick(e) {
        this.contentDiv.classList.add('is-hidden');
        this.loaderDiv.classList.remove('is-hidden');
        this.card.classList.remove('is-hidden');

        const el = this.loaderDiv;
        this.loader.start(el);

        this.reenrichmentButton.setAttribute('disabled', '');
        this.reenrichmentButton.classList.add('is-hidden');

        const onEnrichmentLoaded = this.onEnrichmentLoaded.bind(this);
        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;

        $.ajax({
            url: '/admin/reenrichment',
            type: 'post',
            data: {type: this.type, entityId: this.itemId, token: token},
            success: onEnrichmentLoaded,
            error: handleAjaxError,
        });
    }

    onEnrichmentLoaded(data, status) {
        if ('success' !== status || 0 === data.length) {
            return;
        }

        this.loadData(this.itemId);
    }

    onCloseButtonClick(e) {
        e.preventDefault();
        this.close();
    }

    onCloseDetailsPanel() {
        this.close();
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
            url: this.url,
            type: 'get',
            data: {id: id, token: token},
            success: onDetailsLoaded,
            error: handleAjaxError,
        });
    }

    onTableRowClicked({detail}) {
        this.itemId = detail.itemId;
        this.loadData(this.itemId);
    }

    onDetailsLoaded(data, status) {

        if ('success' !== status || 0 === data.length) {
            return;
        }

        data = this.proceedData(data);

        if (this.enrichment && data.hasOwnProperty('checked') && this.reenrichmentButton) {
            if (data.checked === false && data.enrichable) {
                this.reenrichmentButton.removeAttribute('disabled');
                this.reenrichmentButton.classList.remove('is-hidden');
            } else {
                this.reenrichmentButton.setAttribute('disabled', '');
                this.reenrichmentButton.classList.add('is-hidden');
            }
        }

        this.loader.stop();
        this.contentDiv.classList.remove('is-hidden');
        this.loaderDiv.classList.add('is-hidden');

        let span = null;
        //todo: foreach and arrow fn ?
        for (const key in data) {
            span = this.card.querySelector(`#details_${key}`);
            if (span) {
                if (data[key] instanceof Node) {
                    span.replaceChildren(data[key]);
                } else {
                    span.innerHTML = data[key];
                }
            }
        }

        this.initTooltips();
    }

    initTooltips() {
        Tooltip.addTooltipsToEventDetailsPanel();
    }

    proceedData(data) {

        return {};
    }

    close() {
        fireEvent(this.panelClosed);
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
        return document.querySelector(`.details-card#${this.cardId}`);
    }

    get closeButton() {
        return this.card.querySelector('.delete');
    }

    get reenrichmentButton() {
        return this.card.querySelector('.reenrichment-button');
    }
}
