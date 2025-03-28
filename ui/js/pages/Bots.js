import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {BotsChart} from '../parts/chart/Bots.js?v=2';
import {BotsGrid} from '../parts/grid/Bots.js?v=2';

export class BotsPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        const gridParams = {
            url     : '/admin/loadBots',
            //tileId  : 'totalDevices',
            tableId : 'bots-table',
            dateRangeGrid: true,

            getParams: function() {
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {dateRange, searchValue};
            }
        };

        const chartParams = {
            getParams: function() {
                const mode        = 'bots';
                const chartType   = 'line';
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {mode, chartType, dateRange, searchValue};
            }
        };

        const datesFilter  = new DatesFilter();
        const searchFilter = new SearchFilter();

        new BotsChart(chartParams);
        new BotsGrid(gridParams);
    }
}
