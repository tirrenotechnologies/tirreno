import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {LogbookPanel} from '../parts/panel/LogbookPanel.js?v=2';
import {LogbookGrid} from '../parts/grid/Logbook.js?v=2';
import {LogbookChart} from '../parts/chart/Logbook.js?v=2';

export class LogbookPage extends BasePage {

    constructor() {
        super('logbook');

        this.initUi();
    }

    initUi() {
        const datesFilter   = new DatesFilter();
        const searchFilter  = new SearchFilter();

        const chartParams = this.getChartParams(datesFilter, searchFilter);

        this.setBaseFilters(datesFilter, searchFilter);

        const gridParams = {
            url:            `${window.app_base}/admin/loadLogbook`,
            tileId:         'totalLogbook',
            tableId:        'logbook-table',
            panelType:      'logbook',
            dateRangeGrid:  true,

            sessionGroup:   false,
            singleUser:     false,
            isSortable:     true,

            getParams:      this.getParamsSection,
        };

        new LogbookChart(chartParams);
        new LogbookPanel();
        new LogbookGrid(gridParams);
    }
}
