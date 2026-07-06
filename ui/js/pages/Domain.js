import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {Map} from '../parts/Map.js?v=0.10.0';
import {IpsGrid} from '../parts/grid/Ips.js?v=0.10.0';
import {UsersGrid} from '../parts/grid/Users.js?v=0.10.0';
import {IspsGrid} from '../parts/grid/Isps.js?v=0.10.0';
import {EventsGrid} from '../parts/grid/Events.js?v=0.10.0';
import {DomainsGrid} from '../parts/grid/Domains.js?v=0.10.0';
import {BaseBarChart} from '../parts/chart/BaseBar.js?v=0.10.0';
import {EventPanel} from '../parts/panel/EventPanel.js?v=0.10.0';
import {DomainTiles} from '../parts/details/DomainTiles.js?v=0.10.0';
import {ReenrichmentButton} from '../parts/button/ReenrichmentButton.js?v=0.10.0';

export class DomainPage extends BasePage {
    constructor() {
        super('domain', true);
    }

    initUi() {
        const usersGridParams       = this.getUsersGridParams();
        const eventsGridParams      = this.getEventsGridParams();
        const ipsGridParams         = this.getIpsGridParams();
        const ispsGridParams        = this.getIspsGridParams();
        const mapParams             = this.getMapParams();
        const domainDetailsTiles    = this.getSelfDetails();
        const chartParams           = this.getBarChartParams();

        const domainsGridParams = {
            url:        `${window.app_base}/loadDomains`,
            tileId:     'totalDomains',
            tableId:    'domains-table',

            isSortable: false,

            getParams: this.getParams,
        };

        new EventPanel();
        new ReenrichmentButton();

        const elements = [
            [DomainTiles,   domainDetailsTiles],
            [UsersGrid,     usersGridParams],
            [DomainsGrid,   domainsGridParams],
            [Map,           mapParams],
            [IpsGrid,       ipsGridParams],
            [IspsGrid,      ispsGridParams],
            [BaseBarChart,  chartParams],
            [EventsGrid,    eventsGridParams],
        ];

        new SequentialLoad(elements);
    }
}
