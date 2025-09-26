import {BasePage} from './Base.js';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=2';
import {UsersGrid} from '../parts/grid/Users.js?v=2';
import {EventsGrid} from '../parts/grid/Events.js?v=2';
import {DevicesGrid} from '../parts/grid/Devices.js?v=2';
import {BaseBarChart} from '../parts/chart/BaseBar.js?v=2';
import {EventPanel} from '../parts/panel/EventPanel.js?v=2';
import {DevicePanel} from '../parts/panel/DevicePanel.js?v=2';
import {IpTiles} from '../parts/details/IpTiles.js?v=2';
import {ReenrichmentButton} from '../parts/ReenrichmentButton.js?v=2';

export class IpPage extends BasePage {

    constructor() {
        super('ip', true);

        this.initUi();
    }

    initUi() {
        const usersGridParams   = this.getUsersGridParams();
        const devicesGridParams = this.getDevicesGridParams();
        const eventsGridParams  = this.getEventsGridParams();
        const ipDetailsTiles    = this.getSelfDetails();
        const chartParams       = this.getBarChartParams();

        new EventPanel();
        new DevicePanel();
        new ReenrichmentButton();

        const elements = [
            [IpTiles,       ipDetailsTiles],
            [UsersGrid,     usersGridParams],
            [DevicesGrid,   devicesGridParams],
            [BaseBarChart,  chartParams],
            [EventsGrid,    eventsGridParams],
        ];

        new SequentialLoad(elements);
    }
}
