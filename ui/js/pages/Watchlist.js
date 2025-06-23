import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {EventPanel} from '../parts/panel/EventPanel.js?v=2';
import {WatchlistTags} from '../parts/WatchlistTags.js?v=2';
import {EventsGrid} from '../parts/grid/Events.js?v=2';

export class WatchlistPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        const datesFilter   = new DatesFilter();
        const searchFilter  = new SearchFilter();

        const gridParams = {
            url:            '/admin/loadEvents?watchlist=true',
            tileId:         'totalEvents',
            tableId:        'user-events-table',
            panelType:      'event',

            dateRangeGrid:  true,
            isSortable:     false,

            getParams: function() {
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {dateRange, searchValue};
            },
        };

        new EventPanel();
        new WatchlistTags();
        new EventsGrid(gridParams);
    }
}
