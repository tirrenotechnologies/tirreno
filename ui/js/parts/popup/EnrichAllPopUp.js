import {Loader} from '../Loader.js?v=0.10.0';
import {handleAjaxError} from '../utils/ErrorHandler.js?v=0.10.0';
import {renderEnrichmentCalculation} from '../DataRenderers.js?v=0.10.0';
import {BasePopUp} from './BasePopUp.js?v=0.10.0';
import {replaceChildren} from '../utils/Functions.js?v=0.10.0';

export class EnrichAllPopUp extends BasePopUp {
    constructor() {
        const formParams = {
            mainButtonId:       'enrich-all-btn',
            confirmButtonId:    'confirm-enrich-all-button',
            formId:             'enrich-all-form',
            popupId:            'enrich-all-popup',
        };

        super(formParams);

        this.loader = new Loader();

        const onMainButtonClick = this.onMainButtonClick.bind(this);
        this.mainButton.addEventListener('click', onMainButtonClick, false);
    }

    onMainButtonClick(e) {
        super.onMainButtonClick(e);

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
            url: `${window.app_base}/enrichmentDetails`,
            type: 'GET',
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
            replaceChildren(this.card.querySelector(`#details_${key}`), data[key]);
        }
    }

    proceedData(data) {
        data.calculation = renderEnrichmentCalculation(data);

        return data;
    }

    get loaderDiv() {
        return this.card.querySelector('div.text-loader');
    }
}
