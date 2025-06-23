import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {IspsChart} from '../parts/chart/Isps.js?v=2';
import {IspsGrid} from '../parts/grid/Isps.js?v=2';

export class IspsPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        const datesFilter  = new DatesFilter();
        const searchFilter = new SearchFilter();

        const gridParams = {
            url:        '/admin/loadIsps',
            tileId:     'totalIsps',
            tableId:    'isps-table',

            dateRangeGrid:      true,
            calculateTotals:    true,
            totals: {
                type: 'isp',
                columns: ['total_visit', 'total_account', 'total_ip'],
            },

            getParams: function() {
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {dateRange, searchValue};
            }
        };

        const chartParams = {
            getParams: function() {
                const mode        = 'isps';
                const chartType   = 'line';
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {mode, chartType, dateRange, searchValue};
            }
        };

        new IspsChart(chartParams);
        new IspsGrid(gridParams);
    }
}
