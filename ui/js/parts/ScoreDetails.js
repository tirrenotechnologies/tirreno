import {renderScoreDetails} from './DataRenderers.js?v=2';
import {handleAjaxError} from './utils/ErrorHandler.js?v=2';
import {Tooltip} from './Tooltip.js?v=2';


export class ScoreDetails {

    constructor(scoreParams) {
        this.config = scoreParams;
        this.loadData();
    }

    loadData() {
        const onScoreDetailsLoaded = this.onScoreDetailsLoaded.bind(this);
        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;

        $.ajax({
            url: '/admin/scoreDetails',
            type: 'get',
            data: {userId: this.config.userId, token: token},
            success: onScoreDetailsLoaded,
            error: handleAjaxError,
        });
    }

    onScoreDetailsLoaded(data, status) {
        if ('success' !== status || 0 === data.length) {
            return;
        }

        let el = this.contentDiv;

        if (el) {
            el.replaceChildren(renderScoreDetails(data));
        }

        this.initTooltips();
    }

    get card() {
        return document.querySelector('.score-details');
    }

    get contentDiv() {
        return this.card.querySelector('div.score-details-content');
    }

    initTooltips() {
        Tooltip.addTooltipsToScoreDetails();
    }
}
