import {BasePage} from './Base.js?v=2';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {UserAgentsChart} from '../parts/chart/UserAgents.js?v=2';
import {UserAgentsGrid} from '../parts/grid/UserAgents.js?v=2';

export class UserAgentsPage extends BasePage {
    constructor() {
        super('userAgents');
    }

    initUi() {
        const datesFilter  = new DatesFilter();
        const searchFilter = new SearchFilter();

        this.setBaseFilters(datesFilter, searchFilter);

        const gridParams = {
            url:        `${window.app_base}/admin/loadUserAgents`,
            // tileId:  'totalDevices',
            tableId:    'user-agents-table',

            dateRangeGrid:      true,
            calculateTotals:    true,
            totals: {
                type: 'userAgent',
                columns: ['total_account'],
            },

            getParams: this.getParamsSection,
        };

        const chartParams = this.getChartParams(datesFilter, searchFilter);

        new UserAgentsChart(chartParams);
        new UserAgentsGrid(gridParams);
    }
}
