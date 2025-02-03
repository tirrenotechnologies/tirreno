import {BasePage} from './Base.js';

import {EventsChart} from '../parts/chart/Events.js?v=2';
import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {EventPanel} from '../parts/panel/EventPanel.js?v=2';
import {EventsGrid} from '../parts/grid/Events.js?v=2';

export class EventsPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        const chartParams = {
            getParams: function() {
                const mode        = 'events';
                const chartType   = 'line';
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {mode, chartType, dateRange, searchValue};
            }
        };

        const gridParams = {
            url    : '/admin/loadEvents',
            tileId : 'totalEvents',
            tableId: 'user-events-table',
            panelType: 'event',
            dateRangeGrid: true,

            sessionGroup: true,
            singleUser: false,
            isSortable: true,

            getParams: function() {
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {dateRange, searchValue};
            }
        };

        const datesFilter  = new DatesFilter();
        const searchFilter = new SearchFilter();
        const eventPanel   = new EventPanel();
        const lineChart    = new EventsChart(chartParams);
        const eventsGrid   = new EventsGrid(gridParams);
    }
}
