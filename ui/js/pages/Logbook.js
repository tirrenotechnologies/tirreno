import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {LogbookPanel} from '../parts/panel/LogbookPanel.js?v=2';
import {LogbookGrid} from '../parts/grid/Logbook.js?v=2';
import {LogbookChart} from '../parts/chart/Logbook.js?v=2';

export class LogbookPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        const datesFilter   = new DatesFilter();
        const searchFilter  = new SearchFilter();

        const chartParams = {
            getParams: function() {
                const mode        = 'logbook';
                const chartType   = 'line';
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {mode, chartType, dateRange, searchValue};
            }
        };

        const gridParams = {
            url:            '/admin/loadLogbook',
            tileId:         'totalLogbook',
            tableId:        'logbook-table',
            panelType:      'logbook',
            dateRangeGrid:  true,

            sessionGroup:   false,
            singleUser:     false,
            isSortable:     true,

            getParams: function() {
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {dateRange, searchValue};
            }
        };

        new LogbookChart(chartParams);
        new LogbookPanel();
        new LogbookGrid(gridParams);
    }
}
