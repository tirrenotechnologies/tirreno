import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {DatesFilter} from '../parts/DatesFilter.js?v=0.10.0';
import {SearchFilter} from '../parts/SearchFilter.js?v=0.10.0';
import {RulesFilter} from '../parts/choices/RulesFilter.js?v=0.10.0';
import {ReviewQueueActionButtons} from '../parts/button/ReviewQueueActionButtons.js?v=0.10.0';
import {ReviewQueueGrid} from '../parts/grid/ReviewQueue.js?v=0.10.0';
import {ReviewQueueChart} from '../parts/chart/ReviewQueue.js?v=0.10.0';

export class ReviewQueuePage extends BasePage {
    constructor() {
        super('review-queue');
    }

    initUi() {
        this.tableId = 'review-queue-table';

        const datesFilter   = new DatesFilter();
        const searchFilter  = new SearchFilter();
        const rulesFilter   = new RulesFilter();

        this.filters = {
            dateRange:      datesFilter,
            searchValue:    searchFilter,
            ruleUids:       rulesFilter,
        };

        const gridParams = {
            url:            `${window.app_base}/loadReviewQueue`,
            tileId:         'totalUsers',
            tableId:        'review-queue-table',
            dateRangeGrid:  true,

            choicesFilterEvents: [rulesFilter.getEventType()],

            getParams: this.getParamsSection,
        };

        const chartParams = {
            url:        `${window.app_base}/loadReviewQueueChart`,
            getParams:  this.getParamsSection,
        };

        new ReviewQueueActionButtons(this.tableId);

        const elements = [
            [ReviewQueueChart,    chartParams],
            [ReviewQueueGrid,     gridParams],
        ];

        new SequentialLoad(elements);
    }
}
