import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {DatesFilter} from '../parts/DatesFilter.js?v=0.10.0';
import {SearchFilter} from '../parts/SearchFilter.js?v=0.10.0';
import {UserAgentsChart} from '../parts/chart/UserAgents.js?v=0.10.0';
import {UserAgentsGrid} from '../parts/grid/UserAgents.js?v=0.10.0';

export class UserAgentsPage extends BasePage {
    constructor() {
        super('userAgents');
    }

    initUi() {
        const datesFilter  = new DatesFilter();
        const searchFilter = new SearchFilter();

        this.setBaseFilters(datesFilter, searchFilter);

        const gridParams = {
            url:        `${window.app_base}/loadUserAgents`,
            // tileId:  'totalDevices',
            tableId:    'user-agents-table',

            dateRangeGrid:      true,
            timeFrameTotalUrl:  `${window.app_base}/loadUserAgentsTimeFrameTotal`,
            totals: {
                type: 'userAgent',
                columns: ['total_account'],
            },

            getParams: this.getParamsSection,
        };

        const chartParams = {
            url:        `${window.app_base}/loadUserAgentsChart`,
            getParams:  this.getParamsSection,
        };

        const elements = [
            [UserAgentsChart,    chartParams],
            [UserAgentsGrid,     gridParams],
        ];

        new SequentialLoad(elements);
    }
}
