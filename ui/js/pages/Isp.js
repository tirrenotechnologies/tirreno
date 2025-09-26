import {BasePage} from './Base.js';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=2';
import {Map} from '../parts/Map.js?v=2';
import {IpsGrid} from '../parts/grid/Ips.js?v=2';
import {UsersGrid} from '../parts/grid/Users.js?v=2';
import {EventsGrid} from '../parts/grid/Events.js?v=2';
import {BaseBarChart} from '../parts/chart/BaseBar.js?v=2';
import {EventPanel} from '../parts/panel/EventPanel.js?v=2';
import {IspTiles} from '../parts/details/IspTiles.js?v=2';

export class IspPage extends BasePage {

    constructor() {
        super('isp', true);

        this.initUi();
    }

    initUi() {
        const ispDetailsTiles   = this.getSelfDetails();
        const usersGridParams   = this.getUsersGridParams();
        const eventsGridParams  = this.getEventsGridParams();
        const ipsGridParams     = this.getIpsGridParams();
        const mapParams         = this.getMapParams();
        const chartParams       = this.getBarChartParams();

        new EventPanel();

        const elements = [
            [IspTiles,      ispDetailsTiles],
            [UsersGrid,     usersGridParams],
            [Map,           mapParams],
            [IpsGrid,       ipsGridParams],
            [BaseBarChart,  chartParams],
            [EventsGrid,    eventsGridParams],
        ];

        new SequentialLoad(elements);
    }
}
