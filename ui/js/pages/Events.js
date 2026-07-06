import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {EventsChart} from '../parts/chart/Events.js?v=0.10.0';
import {DatesFilter} from '../parts/DatesFilter.js?v=0.10.0';
import {SearchFilter} from '../parts/SearchFilter.js?v=0.10.0';
import {EventTypeFilter} from '../parts/choices/EventTypeFilter.js?v=0.10.0';
import {DeviceTypeFilter} from '../parts/choices/DeviceTypeFilter.js?v=0.10.0';
import {RulesFilter} from '../parts/choices/RulesFilter.js?v=0.10.0';
import {EventPanel} from '../parts/panel/EventPanel.js?v=0.10.0';
import {EventsGrid} from '../parts/grid/Events.js?v=0.10.0';

export class EventsPage extends BasePage {
    constructor() {
        super('events');
    }

    initUi() {
        const datesFilter       = new DatesFilter();
        const searchFilter      = new SearchFilter();
        const eventTypeFilter   = new EventTypeFilter();
        const deviceTypeFilter  = new DeviceTypeFilter();
        const rulesFilter       = new RulesFilter();

        this.filters = {
            dateRange:      datesFilter,
            searchValue:    searchFilter,
            eventTypeIds:   eventTypeFilter,
            ruleUids:       rulesFilter,
            deviceTypes:    deviceTypeFilter,
        };

        const gridParams = {
            url:            `${window.app_base}/loadEvents`,
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

            getParams: this.getParamsSection,
        };

        const chartParams = {
            url:        `${window.app_base}/loadEventsChart`,
            getParams:  this.getParamsSection,
        };

        new EventPanel();

        const elements = [
            [EventsChart,    chartParams],
            [EventsGrid,     gridParams],
        ];

        new SequentialLoad(elements);
    }
}
