import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {ResourcesChart} from '../parts/chart/Resources.js?v=2';
import {ResourcesGrid} from '../parts/grid/Resources.js?v=2';

export class ResourcesPage extends BasePage {

    constructor() {
        super('resources');

        this.initUi();
    }

    initUi() {
        const datesFilter   = new DatesFilter();
        const searchFilter  = new SearchFilter();

        const gridParams = {
            url:            '/admin/loadResources',
            tileId:         'totalResources',
            tableId:        'resources-table',

            dateRangeGrid:      true,
            calculateTotals:    true,
            totals: {
                type: 'resource',
                columns: ['total_visit', 'total_account', 'total_ip', 'total_country'],
            },

            getParams: function() {
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {dateRange, searchValue};
            }
        };

        const chartParams = this.getChartParams(datesFilter, searchFilter);

        new ResourcesChart(chartParams);
        new ResourcesGrid(gridParams);
    }
}
