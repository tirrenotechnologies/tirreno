import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {IpTypeFilter} from '../parts/choices/IpTypeFilter.js?v=2';
import {IpsChart} from '../parts/chart/Ips.js?v=2';
import {IpsGrid} from '../parts/grid/Ips.js?v=2';

export class IpsPage extends BasePage {

    constructor() {
        super('ips');

        this.initUi();
    }

    initUi() {
        const datesFilter  = new DatesFilter();
        const searchFilter = new SearchFilter();
        const ipTypeFilter = new IpTypeFilter();

        this.filters = {
            dateRange:      datesFilter,
            searchValue:    searchFilter,
            ipTypeIds:      ipTypeFilter,
        };

        const gridParams = {
            url:        `${window.app_base}/admin/loadIps`,
            tileId:     'totalIps',
            tableId:    'ips-table',

            dateRangeGrid:      true,
            calculateTotals:    true,
            totals: {
                type: 'ip',
                columns: ['total_visit'],
            },

            isSortable:         true,
            orderByLastseen:    false,

            choicesFilterEvents: [ipTypeFilter.getEventType()],

            getParams: this.getParamsSection,
        };

        const chartParams = this.getChartParams(datesFilter, searchFilter);

        new IpsChart(chartParams);
        new IpsGrid(gridParams);
    }
}
