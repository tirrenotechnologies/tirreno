import {BasePage} from './Base.js';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=2';
import {Map} from '../parts/Map.js?v=2';
import {IpsGrid} from '../parts/grid/Ips.js?v=2';
import {UsersGrid} from '../parts/grid/Users.js?v=2';
import {EventsGrid} from '../parts/grid/Events.js?v=2';
import {BaseBarChart} from '../parts/chart/BaseBar.js?v=2';
import {EventPanel} from '../parts/panel/EventPanel.js?v=2';
import {BotTiles} from '../parts/details/BotTiles.js?v=2';
import {ReenrichmentButton} from '../parts/ReenrichmentButton.js?v=2';

export class BotPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        const BOT_ID = parseInt(window.location.pathname.replace('/bot/', ''), 10);

        const getParams = () => {
            return {botId: BOT_ID};
        };

        const usersGridParams = {
            url:        '/admin/loadUsers',
            tileId:     'totalUsers',
            tableId:    'users-table',

            isSortable: false,

            getParams: getParams
        };

        const eventsGridParams = {
            url:        '/admin/loadEvents',
            tileId:     'totalEvents',
            tableId:    'user-events-table',
            panelType:  'event',

            isSortable: false,

            getParams: getParams
        };

        const ipsGridParams = {
            url:        '/admin/loadIps',
            tileId:     'totalIps',
            tableId:    'ips-table',

            isSortable: false,
            orderByLastseen: true,

            getParams: getParams
        };

        const botDetailsTiles = {
            getParams: getParams
        };

        const chartParams = {
            getParams: function() {
                const id        = BOT_ID;
                const mode      = 'bot';

                return {mode, id};
            }
        };

        const mapParams = {
            getParams:      getParams,
            tooltipString:  'event',
            tooltipField:   'total_visit'
        };

        new EventPanel();
        new ReenrichmentButton();

        const elements = [
            [BotTiles,              botDetailsTiles],
            [Map,                   mapParams],
            [IpsGrid,               ipsGridParams],
            [UsersGrid,             usersGridParams],
            [BaseBarChart,          chartParams],
            [EventsGrid,            eventsGridParams],
        ];

        new SequentialLoad(elements);
    }
}
