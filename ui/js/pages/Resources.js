import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {FileTypeFilter} from '../parts/choices/FileTypeFilter.js?v=2';
import {ResourcesChart} from '../parts/chart/Resources.js?v=2';
import {ResourcesGrid} from '../parts/grid/Resources.js?v=2';

export class ResourcesPage extends BasePage {

    constructor() {
        super('resources');

        this.initUi();
    }

    initUi() {
        const datesFilter       = new DatesFilter();
        const searchFilter      = new SearchFilter();
        const fileTypeFilter    = new FileTypeFilter();

        this.filters = {
            dateRange:      datesFilter,
            searchValue:    searchFilter,
            fileTypeIds:    fileTypeFilter,
        };

        const gridParams = {
            url:            `${window.app_base}/admin/loadResources`,
            tileId:         'totalResources',
            tableId:        'resources-table',

            dateRangeGrid:      true,
            calculateTotals:    true,
            totals: {
                type: 'resource',
                columns: ['total_visit', 'total_account', 'total_ip', 'total_edit'],
            },

            choicesFilterEvents: [fileTypeFilter.getEventType()],

            getParams: this.getParamsSection,
        };

        const chartParams = this.getChartParams(datesFilter, searchFilter);

        new ResourcesChart(chartParams);
        new ResourcesGrid(gridParams);
    }
}
