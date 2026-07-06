import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {DatesFilter} from '../parts/DatesFilter.js?v=0.10.0';
import {SearchFilter} from '../parts/SearchFilter.js?v=0.10.0';
import {FileTypeFilter} from '../parts/choices/FileTypeFilter.js?v=0.10.0';
import {ResourcesChart} from '../parts/chart/Resources.js?v=0.10.0';
import {ResourcesGrid} from '../parts/grid/Resources.js?v=0.10.0';

export class ResourcesPage extends BasePage {
    constructor() {
        super('resources');
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
            url:            `${window.app_base}/loadResources`,
            tileId:         'totalResources',
            tableId:        'resources-table',

            dateRangeGrid:      true,
            timeFrameTotalUrl:  `${window.app_base}/loadResourcesTimeFrameTotal`,
            totals: {
                type: 'resource',
                columns: ['total_visit', 'total_account', 'total_ip', 'total_edit'],
            },

            choicesFilterEvents: [fileTypeFilter.getEventType()],

            getParams: this.getParamsSection,
        };

        const chartParams = {
            url:        `${window.app_base}/loadResourcesChart`,
            getParams:  this.getParamsSection,
        };

        const elements = [
            [ResourcesChart,    chartParams],
            [ResourcesGrid,     gridParams],
        ];

        new SequentialLoad(elements);
    }
}
