import {BasePage} from './Base.js';

import {Map} from '../parts/Map.js?v=2';
import {IpsGrid} from '../parts/grid/Ips.js?v=2';
import {UsersGrid} from '../parts/grid/Users.js?v=2';
import {IspsGrid} from '../parts/grid/Isps.js?v=2';
import {EventsGrid} from '../parts/grid/Events.js?v=2';
import {DomainsGrid} from '../parts/grid/Domains.js?v=2';
import {BaseBarChart} from '../parts/chart/BaseBar.js?v=2';
import {EventPanel} from '../parts/panel/EventPanel.js?v=2';
import {DomainTiles} from '../parts/details/DomainTiles.js?v=2';
import {ReenrichmentButton} from '../parts/ReenrichmentButton.js?v=2';

export class DomainPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        const DOMAIN_ID = parseInt(window.location.pathname.replace('/domain/', ''), 10);

        const getParams = () => {
            return {domainId: DOMAIN_ID};
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

        const ipsGridParams = {
            url:        '/admin/loadIps',
            tileId:     'totalIps',
            tableId:    'ips-table',

            isSortable:         false,
            orderByLastseen:    true,

            getParams: getParams,
        };

        const ispsGridParams = {
            url:        '/admin/loadIsps',
            tableId:    'isps-table',

            isSortable: false,

            getParams:  getParams,
        };

        const domainsGridParams = {
            url:        '/admin/loadDomains',
            tileId:     'totalDomains',
            tableId:    'domains-table',

            isSortable: false,

            getParams: getParams,
        };


        const mapParams = {
            getParams:      getParams,
            tooltipString:  'event',
            tooltipField:   'total_visit',
        };

        const domainDetailsTiles = {
            getParams: getParams,
        };

        const chartParams = {
            getParams: function() {
                const id        = DOMAIN_ID;
                const mode      = 'domain';
                const chartType = 'bar';

                return {mode, chartType, id};
            }
        };

        new Map(mapParams);
        new EventPanel();
        new DomainTiles(domainDetailsTiles);
        new BaseBarChart(chartParams);
        new IpsGrid(ipsGridParams);
        new IspsGrid(ispsGridParams);
        new UsersGrid(usersGridParams);
        new EventsGrid(eventsGridParams);
        new DomainsGrid(domainsGridParams);
        new ReenrichmentButton();
    }
}
