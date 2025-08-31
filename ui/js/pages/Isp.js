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
        super();

        this.initUi();
    }

    initUi() {
        const ISP_ID = parseInt(window.location.pathname.replace('/isp/', ''), 10);

        const getParams = () => {
            return {ispId: ISP_ID};
        };

        const usersGridParams = {
            url:        '/admin/loadUsers',
            tileId:     'totalUsers',
            tableId:    'users-table',

            isSortable: false,

            getParams:  getParams,
        };

        const ispDetailsTiles = {
            getParams: getParams,
        };

        const eventsGridParams = {
            url:        '/admin/loadEvents',
            tileId:     'totalEvents',
            tableId:    'user-events-table',
            panelType:  'event',

            isSortable: false,

            getParams:  getParams,
        };

        const ipsGridParams = {
            url:        '/admin/loadIps',
            tileId:     'totalIps',
            tableId:    'ips-table',

            isSortable:         false,
            orderByLastseen:    true,

            getParams:          getParams,
        };

        const mapParams = {
            getParams:      getParams,
            tooltipString:  'event',
            tooltipField:   'total_visit',
        };

        const chartParams = {
            getParams: function() {
                const id        = ISP_ID;
                const mode      = 'isp';

                return {mode, id};
            }
        };

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
