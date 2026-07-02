import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {DatesFilter} from '../parts/DatesFilter.js?v=0.10.0';
import {SearchFilter} from '../parts/SearchFilter.js?v=0.10.0';
import {IspsChart} from '../parts/chart/Isps.js?v=0.10.0';
import {IspsGrid} from '../parts/grid/Isps.js?v=0.10.0';

export class IspsPage extends BasePage {
    constructor() {
        super('isps');
    }

    initUi() {
        const datesFilter  = new DatesFilter();
        const searchFilter = new SearchFilter();

        this.setBaseFilters(datesFilter, searchFilter);

        const gridParams = {
            url:        `${window.app_base}/loadIsps`,
            tileId:     'totalIsps',
            tableId:    'isps-table',

            dateRangeGrid:      true,
            timeFrameTotalUrl:  `${window.app_base}/loadIspsTimeFrameTotal`,
            totals: {
                type: 'isp',
                columns: ['total_visit', 'total_account', 'total_ip'],
            },

            getParams: this.getParamsSection,
        };

        const chartParams = {
            url:        `${window.app_base}/loadIspsChart`,
            getParams:  this.getParamsSection,
        };

        const elements = [
            [IspsChart,    chartParams],
            [IspsGrid,     gridParams],
        ];

        new SequentialLoad(elements);
    }
}
