import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {RulesFilter} from '../parts/choices/RulesFilter.js?v=2';
import {ScoresRangeFilter} from '../parts/choices/ScoresRangeFilter.js?v=2';
import {UsersGrid} from '../parts/grid/Users.js?v=2';
import {UsersChart} from '../parts/chart/Users.js?v=2';

export class UsersPage extends BasePage {

    constructor() {
        super('users');

        this.initUi();
    }

    initUi() {
        const datesFilter       = new DatesFilter();
        const searchFilter      = new SearchFilter();
        const rulesFilter       = new RulesFilter();
        const scoresRangeFilter = new ScoresRangeFilter();

        const chartParams = this.getChartParams(datesFilter, searchFilter);

        this.filters = {
            dateRange:      datesFilter,
            searchValue:    searchFilter,
            ruleUids:       rulesFilter,
            scoresRange:    scoresRangeFilter,
        };

        const gridParams = {
            url:            `${window.app_base}/admin/loadUsers`,
            tileId:         'totalUsers',
            tableId:        'users-table',

            dateRangeGrid:      true,

            choicesFilterEvents: [rulesFilter.getEventType(), scoresRangeFilter.getEventType()],

            getParams: this.getParamsSection,
        };

        new UsersGrid(gridParams);
        new UsersChart(chartParams);
    }
}
