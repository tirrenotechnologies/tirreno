import {BasePage} from './Base.js';

import {IpsGrid} from '../parts/grid/Ips.js?v=2';
import {IspsGrid} from '../parts/grid/Isps.js?v=2';
import {UsersGrid} from '../parts/grid/Users.js?v=2';
import {EventsGrid} from '../parts/grid/Events.js?v=2';
import {BaseBarChart} from '../parts/chart/BaseBar.js?v=2';
import {StaticTiles} from '../parts/StaticTiles.js?v=2';
import {EventPanel} from '../parts/panel/EventPanel.js?v=2';

export class CountryPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        const COUNTRY_ID = parseInt(window.location.pathname.replace('/country/', ''), 10);

        const getParams = () => {
            return {countryId: COUNTRY_ID};
        };

        const usersGridParams = {
            url:        '/admin/loadUsers',
            tileId:     'totalUsers',
            tableId:    'users-table',

            isSortable: false,

            getParams:  getParams,
        };

        const eventsGridParams = {
            url:        '/admin/loadEvents',
            tileId:     'totalEvents',
            tableId:    'user-events-table',
            panelType:  'event',

            isSortable: false,

            getParams: getParams,
        };

        const ispsGridParams = {
            url:        '/admin/loadIsps',
            tableId:    'isps-table',

            isSortable: false,

            getParams:  getParams,
        };

        const ipsGridParams = {
            url:        '/admin/loadIps',
            tileId:     'totalIps',
            tableId:    'ips-table',

            isSortable:         false,
            orderByLastseen:    true,

            getParams: getParams
        };

        const chartParams = {
            getParams: function() {
                const id        = COUNTRY_ID;
                const mode      = 'country';
                const chartType = 'bar';

                return {mode, chartType, id};
            }
        };

        const tilesParams = {
            elems: ['totalUsers', 'totalIps', 'totalEvents']
        };

        new StaticTiles(tilesParams);
        new EventPanel();
        new BaseBarChart(chartParams);
        new IpsGrid(ipsGridParams);
        new IspsGrid(ispsGridParams);
        new UsersGrid(usersGridParams);
        new EventsGrid(eventsGridParams);
    }
}
