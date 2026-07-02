import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {DatesFilter} from '../parts/DatesFilter.js?v=0.10.0';
import {SearchFilter} from '../parts/SearchFilter.js?v=0.10.0';
import {LogbookPanel} from '../parts/panel/LogbookPanel.js?v=0.10.0';
import {LogbookGrid} from '../parts/grid/Logbook.js?v=0.10.0';
import {LogbookChart} from '../parts/chart/Logbook.js?v=0.10.0';

export class LogbookPage extends BasePage {
    constructor() {
        super('logbook');
    }

    initUi() {
        const datesFilter   = new DatesFilter();
        const searchFilter  = new SearchFilter();

        this.setBaseFilters(datesFilter, searchFilter);

        const gridParams = {
            url:            `${window.app_base}/loadLogbook`,
            tileId:         'totalLogbook',
            tableId:        'logbook-table',
            panelType:      'logbook',
            dateRangeGrid:  true,

            sessionGroup:   false,
            singleUser:     false,
            isSortable:     true,

            getParams:      this.getParamsSection,
        };

        const chartParams = {
            url:        `${window.app_base}/loadLogbookChart`,
            getParams:  this.getParamsSection,
        };

        new LogbookPanel();

        const elements = [
            [LogbookChart,    chartParams],
            [LogbookGrid,     gridParams],
        ];

        new SequentialLoad(elements);
    }
}
