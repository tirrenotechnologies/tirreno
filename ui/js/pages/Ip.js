import {BasePage} from './Base.js';

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
        super();

        this.initUi();
    }

    initUi() {
        const IP_ID = parseInt(window.location.pathname.replace('/ip/', ''), 10);

        const getParams = () => {
            return {ipId: IP_ID};
        };

        const usersGridParams = {
            url:        '/admin/loadUsers',
            tileId:     'totalUsers',
            tableId:    'users-table',

            isSortable: false,

            getParams:  getParams,
        };

        const devicesGridParams = {
            url:        '/admin/loadDevices',
            tileId:     'totalDevices',
            tableId:    'devices-table',
            panelType:  'device',

            isSortable: false,

            getParams:  getParams,
        };

        const eventsGridParams = {
            url:        '/admin/loadEvents',
            tileId:     'totalEvents',
            tableId:    'user-events-table',
            panelType:  'event',

            isSortable: false,

            getParams:  getParams,
        };

        const ipDetailsTiles = {
            getParams: getParams,
        };

        const chartParams = {
            getParams: function() {
                const id        = IP_ID;
                const mode      = 'ip';
                const chartType = 'bar';

                return {mode, chartType, id};
            }
        };

        new EventPanel();
        new DevicePanel();
        new IpTiles(ipDetailsTiles);
        new BaseBarChart(chartParams);
        new UsersGrid(usersGridParams);
        new EventsGrid(eventsGridParams);
        new DevicesGrid(devicesGridParams);
        new ReenrichmentButton();
    }
}
