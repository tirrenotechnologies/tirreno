import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {UsersGrid} from '../parts/grid/Users.js?v=0.10.0';
import {EventsGrid} from '../parts/grid/Events.js?v=0.10.0';
import {DevicesGrid} from '../parts/grid/Devices.js?v=0.10.0';
import {BaseBarChart} from '../parts/chart/BaseBar.js?v=0.10.0';
import {EventPanel} from '../parts/panel/EventPanel.js?v=0.10.0';
import {DevicePanel} from '../parts/panel/DevicePanel.js?v=0.10.0';
import {IpTiles} from '../parts/details/IpTiles.js?v=0.10.0';
import {ReenrichmentButton} from '../parts/button/ReenrichmentButton.js?v=0.10.0';

export class IpPage extends BasePage {
    constructor() {
        super('ip', true);
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
