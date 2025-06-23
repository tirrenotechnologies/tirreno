import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {IpTypeFilter} from '../parts/choices/IpTypeFilter.js?v=2';
import {IpsChart} from '../parts/chart/Ips.js?v=2';
import {IpsGrid} from '../parts/grid/Ips.js?v=2';

export class IpsPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        const datesFilter  = new DatesFilter();
        const searchFilter = new SearchFilter();
        const ipTypeFilter = new IpTypeFilter();

        const gridParams = {
            url:        '/admin/loadIps',
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

            getParams: function() {
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();
                const ipTypeIds   = ipTypeFilter.getValues();

                return {dateRange, searchValue, ipTypeIds};
            }
        };

        const chartParams = {
            getParams: function() {
                const mode        = 'ips';
                const chartType   = 'line';
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {mode, chartType, dateRange, searchValue};
            }
        };

        new IpsChart(chartParams);
        new IpsGrid(gridParams);
    }
}
