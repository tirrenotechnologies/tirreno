import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {IpsGrid} from '../parts/grid/Ips.js?v=0.10.0';
import {IspsGrid} from '../parts/grid/Isps.js?v=0.10.0';
import {UsersGrid} from '../parts/grid/Users.js?v=0.10.0';
import {EventsGrid} from '../parts/grid/Events.js?v=0.10.0';
import {BaseBarChart} from '../parts/chart/BaseBar.js?v=0.10.0';
import {StaticTiles} from '../parts/StaticTiles.js?v=0.10.0';
import {EventPanel} from '../parts/panel/EventPanel.js?v=0.10.0';

export class CountryPage extends BasePage {
    constructor() {
        super('country', true);
    }

    initUi() {
        const usersGridParams   = this.getUsersGridParams();
        const eventsGridParams  = this.getEventsGridParams();
        const ispsGridParams    = this.getIspsGridParams();
        const ipsGridParams     = this.getIpsGridParams();
        const chartParams       = this.getBarChartParams();

        const tilesParams = {
            elems: ['totalUsers', 'totalIps', 'totalEvents']
        };

        new StaticTiles(tilesParams);
        new EventPanel();

        const elements = [
            [UsersGrid,     usersGridParams],
            [IpsGrid,       ipsGridParams],
            [IspsGrid,      ispsGridParams],
            [BaseBarChart,  chartParams],
            [EventsGrid,    eventsGridParams],
        ];

        new SequentialLoad(elements);

    }
}
