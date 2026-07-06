import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {DatesFilter} from '../parts/DatesFilter.js?v=0.10.0';
import {SearchFilter} from '../parts/SearchFilter.js?v=0.10.0';
import {RulesFilter} from '../parts/choices/RulesFilter.js?v=0.10.0';
import {ScoresRangeFilter} from '../parts/choices/ScoresRangeFilter.js?v=0.10.0';
import {UsersGrid} from '../parts/grid/Users.js?v=0.10.0';
import {UsersChart} from '../parts/chart/Users.js?v=0.10.0';

export class UsersPage extends BasePage {
    constructor() {
        super('users');
    }

    initUi() {
        const datesFilter       = new DatesFilter();
        const searchFilter      = new SearchFilter();
        const rulesFilter       = new RulesFilter();
        const scoresRangeFilter = new ScoresRangeFilter();

        this.filters = {
            dateRange:      datesFilter,
            searchValue:    searchFilter,
            ruleUids:       rulesFilter,
            scoresRange:    scoresRangeFilter,
        };

        const gridParams = {
            url:            `${window.app_base}/loadUsers`,
            tileId:         'totalUsers',
            tableId:        'users-table',

            dateRangeGrid:      true,

            choicesFilterEvents: [rulesFilter.getEventType(), scoresRangeFilter.getEventType()],

            getParams: this.getParamsSection,
        };

        const chartParams = {
            url:        `${window.app_base}/loadEventsChart`,
            getParams:  this.getParamsSection,
        };

        const elements = [
            [UsersChart,    chartParams],
            [UsersGrid,     gridParams],
        ];

        new SequentialLoad(elements);
    }
}
