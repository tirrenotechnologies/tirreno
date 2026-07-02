import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {Map} from '../parts/Map.js?v=0.10.0';
import {IpsGrid} from '../parts/grid/Ips.js?v=0.10.0';
import {UsersGrid} from '../parts/grid/Users.js?v=0.10.0';
import {EventsGrid} from '../parts/grid/Events.js?v=0.10.0';
import {BaseBarChart} from '../parts/chart/BaseBar.js?v=0.10.0';
import {EventPanel} from '../parts/panel/EventPanel.js?v=0.10.0';
import {IspTiles} from '../parts/details/IspTiles.js?v=0.10.0';

export class IspPage extends BasePage {
    constructor() {
        super('isp', true);
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
