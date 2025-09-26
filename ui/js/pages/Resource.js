import {BasePage} from './Base.js';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=2';
import {Map} from '../parts/Map.js?v=2';
import {IpsGrid} from '../parts/grid/Ips.js';
import {IspsGrid} from '../parts/grid/Isps.js?v=2';
import {UsersGrid} from '../parts/grid/Users.js?v=2';
import {EventsGrid} from '../parts/grid/Events.js?v=2';
import {DevicesGrid} from '../parts/grid/Devices.js?v=2';
import {BaseBarChart} from '../parts/chart/BaseBar.js?v=2';
import {StaticTiles} from '../parts/StaticTiles.js?v=2';
import {EventPanel} from '../parts/panel/EventPanel.js?v=2';
import {DevicePanel} from '../parts/panel/DevicePanel.js?v=2';

export class ResourcePage extends BasePage {

    constructor() {
        super('resource', true);

        this.initUi();
    }

    initUi() {
        const devicesGridParams = this.getDevicesGridParams();
        const eventsGridParams  = this.getEventsGridParams();
        const ipsGridParams     = this.getIpsGridParams();
        const usersGridParams   = this.getUsersGridParams();
        const ispsGridParams    = this.getIspsGridParams();
        const mapParams         = this.getMapParams();
        const chartParams       = this.getBarChartParams();

        const tilesParams = {
            elems: ['totalUsers', 'totalCountries', 'totalIps', 'totalEvents']
        };

        new StaticTiles(tilesParams);
        new EventPanel();
        new DevicePanel();

        const elements = [
            [UsersGrid,     usersGridParams],
            [Map,           mapParams],
            [IpsGrid,       ipsGridParams],
            [IspsGrid,      ispsGridParams],
            [DevicesGrid,   devicesGridParams],
            [BaseBarChart,  chartParams],
            [EventsGrid,    eventsGridParams],
        ];

        new SequentialLoad(elements);
    }
}
