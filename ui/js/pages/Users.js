import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {RulesFilter} from '../parts/choices/RulesFilter.js?v=2';
import {UsersGrid} from '../parts/grid/Users.js?v=2';
import {UsersChart} from '../parts/chart/Users.js?v=2';

export class UsersPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        const datesFilter       = new DatesFilter();
        const searchFilter      = new SearchFilter();
        const rulesFilter       = new RulesFilter();

        const chartParams = {
            getParams: function() {
                const mode        = 'users';
                const chartType   = 'line';
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {mode, chartType, dateRange, searchValue};
            }
        };

        const gridParams = {
            url     : '/admin/loadUsers',
            tileId  : 'totalUsers',
            tableId : 'users-table',
            dateRangeGrid: true,
            calculateTotals: true,
            totals: {
                type: 'user',
                columns: ['total_visit'],
            },

            choicesFilterEvents: [rulesFilter.getEventType()],

            getParams: function() {
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();
                const ruleIds = rulesFilter.getValues();

                return {dateRange, searchValue, ruleIds};
            }
        };

        new UsersGrid(gridParams);
        new UsersChart(chartParams);
    }
}
