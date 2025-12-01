import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {RulesFilter} from '../parts/choices/RulesFilter.js?v=2';
import {UserGridActionButtons} from '../parts/UserGridActionButtons.js?v=2';
import {ReviewQueueGrid} from '../parts/grid/ReviewQueue.js?v=2';
import {ReviewQueueChart} from '../parts/chart/ReviewQueue.js?v=2';

export class ReviewQueuePage extends BasePage {

    constructor() {
        super('review-queue');
        this.tableId = 'review-queue-table';
        this.initUi();
    }

    initUi() {
        const datesFilter   = new DatesFilter();
        const searchFilter  = new SearchFilter();
        const rulesFilter   = new RulesFilter();

        const chartParams = this.getChartParams(datesFilter, searchFilter);

        this.filters = {
            dateRange:      datesFilter,
            searchValue:    searchFilter,
            ruleUids:       rulesFilter,
        };

        const gridParams = {
            url:            `${window.app_base}/admin/loadReviewQueue`,
            tileId:         'totalUsers',
            tableId:        'review-queue-table',
            dateRangeGrid:  true,

            choicesFilterEvents: [rulesFilter.getEventType()],

            getParams: this.getParamsSection,
        };

        new ReviewQueueChart(chartParams);
        new ReviewQueueGrid(gridParams);
        new UserGridActionButtons(this.tableId);
    }
}
