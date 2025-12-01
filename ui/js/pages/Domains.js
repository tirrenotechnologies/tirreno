import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {DomainsChart} from '../parts/chart/Domains.js?v=2';
import {DomainsGrid} from '../parts/grid/Domains.js?v=2';

export class DomainsPage extends BasePage {

    constructor() {
        super('domains');

        this.initUi();
    }

    initUi() {
        const datesFilter  = new DatesFilter();
        const searchFilter = new SearchFilter();

        this.setBaseFilters(datesFilter, searchFilter);

        const gridParams = {
            url:        `${window.app_base}/admin/loadDomains`,
            tileId:     'totalDomains',
            tableId:    'domains-table',

            dateRangeGrid:      true,
            calculateTotals:    true,
            totals: {
                type: 'domain',
                columns: ['total_account'],
            },

            getParams: this.getParamsSection,
        };

        const chartParams = this.getChartParams(datesFilter, searchFilter);

        new DomainsChart(chartParams);
        new DomainsGrid(gridParams);
    }
}
