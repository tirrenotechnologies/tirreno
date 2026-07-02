import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {DatesFilter} from '../parts/DatesFilter.js?v=0.10.0';
import {SearchFilter} from '../parts/SearchFilter.js?v=0.10.0';
import {IpTypeFilter} from '../parts/choices/IpTypeFilter.js?v=0.10.0';
import {IpsChart} from '../parts/chart/Ips.js?v=0.10.0';
import {IpsGrid} from '../parts/grid/Ips.js?v=0.10.0';

export class IpsPage extends BasePage {
    constructor() {
        super('ips');
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
            url:        `${window.app_base}/loadIps`,
            tileId:     'totalIps',
            tableId:    'ips-table',

            dateRangeGrid:      true,
            timeFrameTotalUrl:  `${window.app_base}/loadIpsTimeFrameTotal`,
            totals: {
                type: 'ip',
                columns: ['total_visit'],
            },

            isSortable:         true,
            orderByLastseen:    false,

            choicesFilterEvents: [ipTypeFilter.getEventType()],

            getParams: this.getParamsSection,
        };

        const chartParams = {
            url:        `${window.app_base}/loadIpsChart`,
            getParams:  this.getParamsSection,
        };

        const elements = [
            [IpsChart,    chartParams],
            [IpsGrid,     gridParams],
        ];

        new SequentialLoad(elements);
    }
}
