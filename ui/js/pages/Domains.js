import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {DatesFilter} from '../parts/DatesFilter.js?v=0.10.0';
import {SearchFilter} from '../parts/SearchFilter.js?v=0.10.0';
import {DomainsChart} from '../parts/chart/Domains.js?v=0.10.0';
import {DomainsGrid} from '../parts/grid/Domains.js?v=0.10.0';

export class DomainsPage extends BasePage {
    constructor() {
        super('domains');
    }

    initUi() {
        const datesFilter  = new DatesFilter();
        const searchFilter = new SearchFilter();

        this.setBaseFilters(datesFilter, searchFilter);

        const gridParams = {
            url:        `${window.app_base}/loadDomains`,
            tileId:     'totalDomains',
            tableId:    'domains-table',

            dateRangeGrid:      true,
            timeFrameTotalUrl:  `${window.app_base}/loadDomainsTimeFrameTotal`,
            totals: {
                type: 'domain',
                columns: ['total_account'],
            },

            getParams: this.getParamsSection,
        };

        const chartParams = {
            url:        `${window.app_base}/loadDomainsChart`,
            getParams: this.getParamsSection,
        };

        const elements = [
            [DomainsChart,    chartParams],
            [DomainsGrid,     gridParams],
        ];

        new SequentialLoad(elements);
    }
}
