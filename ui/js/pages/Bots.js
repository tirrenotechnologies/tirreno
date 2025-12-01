import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {BotsChart} from '../parts/chart/Bots.js?v=2';
import {BotsGrid} from '../parts/grid/Bots.js?v=2';

export class BotsPage extends BasePage {

    constructor() {
        super('bots');

        this.initUi();
    }

    initUi() {
        const datesFilter  = new DatesFilter();
        const searchFilter = new SearchFilter();

        this.setBaseFilters(datesFilter, searchFilter);

        const gridParams = {
            url:        `${window.app_base}/admin/loadBots`,
            // tileId:  'totalDevices',
            tableId:    'bots-table',

            dateRangeGrid:  true,

            getParams: this.getParamsSection,
        };

        const chartParams = this.getChartParams(datesFilter, searchFilter);

        new BotsChart(chartParams);
        new BotsGrid(gridParams);
    }
}
