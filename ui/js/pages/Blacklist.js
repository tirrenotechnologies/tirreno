import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {DatesFilter} from '../parts/DatesFilter.js?v=0.10.0';
import {SearchFilter} from '../parts/SearchFilter.js?v=0.10.0';
import {EntityTypeFilter} from '../parts/choices/EntityTypeFilter.js?v=0.10.0';
import {BlacklistGridActionButtons} from '../parts/button/BlacklistGridActionButtons.js?v=0.10.0';
import {BlacklistChart} from '../parts/chart/Blacklist.js?v=0.10.0';
import {BlacklistGrid} from '../parts/grid/Blacklist.js?v=0.10.0';

export class BlacklistPage extends BasePage {
    constructor() {
        super('blacklist');
    }

    initUi() {
        this.tableId = 'blacklist-table';

        const datesFilter       = new DatesFilter();
        const searchFilter      = new SearchFilter();

        this.setBaseFilters(datesFilter, searchFilter);

        const gridParams = {
            url:            `${window.app_base}/loadBlacklist`,
            tileId:         'totalBlacklist',
            tableId:        'blacklist-table',

            dateRangeGrid:  true,

            getParams: this.getParamsSection,
        };

        if (document.getElementById('entity-type-selectors')) {
            const entityTypeFilter  = new EntityTypeFilter();

            gridParams.choicesFilterEvents = [entityTypeFilter.getEventType()];

            this.filters.entityTypeIds = entityTypeFilter;
        }

        const chartParams = {
            url:        `${window.app_base}/loadBlacklistChart`,
            getParams: this.getParamsSection,
        };

        new BlacklistGridActionButtons(this.tableId);

        const elements = [
            [BlacklistChart,    chartParams],
            [BlacklistGrid,     gridParams],
        ];

        new SequentialLoad(elements);
    }
}
