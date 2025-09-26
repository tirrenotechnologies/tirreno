import {BasePage} from './Base.js';

import {EventsChart} from '../parts/chart/Events.js?v=2';
import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {EventTypeFilter} from '../parts/choices/EventTypeFilter.js?v=2';
import {DeviceTypeFilter} from '../parts/choices/DeviceTypeFilter.js?v=2';
import {RulesFilter} from '../parts/choices/RulesFilter.js?v=2';
import {EventPanel} from '../parts/panel/EventPanel.js?v=2';
import {EventsGrid} from '../parts/grid/Events.js?v=2';

export class EventsPage extends BasePage {

    constructor() {
        super('events');

        this.initUi();
    }

    initUi() {
        const datesFilter       = new DatesFilter();
        const searchFilter      = new SearchFilter();
        const eventTypeFilter   = new EventTypeFilter();
        const deviceTypeFilter  = new DeviceTypeFilter();
        const rulesFilter       = new RulesFilter();

        const chartParams = this.getChartParams(datesFilter, searchFilter);

        const gridParams = {
            url:            '/admin/loadEvents',
            tileId:         'totalEvents',
            tableId:        'user-events-table',
            panelType:      'event',
            dateRangeGrid:  true,

            sessionGroup:   true,
            singleUser:     false,
            isSortable:     true,

            choicesFilterEvents: [
                eventTypeFilter.getEventType(),
                rulesFilter.getEventType(),
                deviceTypeFilter.getEventType(),
            ],

            getParams: function() {
                const dateRange     = datesFilter.getValue();
                const searchValue   = searchFilter.getValue();
                const eventTypeIds  = eventTypeFilter.getValues();
                const ruleUids      = rulesFilter.getValues();
                const deviceTypes   = deviceTypeFilter.getValues();

                return {dateRange, searchValue, eventTypeIds, ruleUids, deviceTypes};
            }
        };

        new EventPanel();
        new EventsChart(chartParams);
        new EventsGrid(gridParams);
    }
}
