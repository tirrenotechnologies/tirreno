import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {DomainsChart} from '../parts/chart/Domains.js?v=2';
import {DomainsGrid} from '../parts/grid/Domains.js?v=2';

export class DomainsPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        const datesFilter  = new DatesFilter();
        const searchFilter = new SearchFilter();

        const gridParams = {
            url     : '/admin/loadDomains',
            tileId  : 'totalDomains',
            tableId : 'domains-table',
            dateRangeGrid: true,
            calculateTotals: true,
            totals: {
                type: 'domain',
                columns: ['total_account'],
            },

            getParams: function() {
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {dateRange, searchValue};
            }
        };

        const chartParams = {
            getParams: function() {
                const mode        = 'domains';
                const chartType   = 'line';
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {mode, chartType, dateRange, searchValue};
            }
        };

        new DomainsChart(chartParams);
        new DomainsGrid(gridParams);
    }
}
