import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {Map} from '../parts/Map.js?v=0.10.0';
import {IpsGrid} from '../parts/grid/Ips.js?v=0.10.0';
import {UsersGrid} from '../parts/grid/Users.js?v=0.10.0';
import {EventsGrid} from '../parts/grid/Events.js?v=0.10.0';
import {BaseBarChart} from '../parts/chart/BaseBar.js?v=0.10.0';
import {EventPanel} from '../parts/panel/EventPanel.js?v=0.10.0';
import {UserAgentTiles} from '../parts/details/UserAgentTiles.js?v=0.10.0';
import {ReenrichmentButton} from '../parts/button/ReenrichmentButton.js?v=0.10.0';

export class UserAgentPage extends BasePage {
    constructor() {
        super('userAgent', true);
    }

    initUi() {
        const usersGridParams   = this.getUsersGridParams();
        const eventsGridParams  = this.getEventsGridParams();
        const ipsGridParams     = this.getIpsGridParams();
        const mapParams         = this.getMapParams();

        const userAgentDetailsTiles   = this.getSelfDetails();

        const chartParams       = this.getBarChartParams();

        new EventPanel();
        new ReenrichmentButton();

        const elements = [
            [UserAgentTiles,        userAgentDetailsTiles],
            [Map,                   mapParams],
            [IpsGrid,               ipsGridParams],
            [UsersGrid,             usersGridParams],
            [BaseBarChart,          chartParams],
            [EventsGrid,            eventsGridParams],
        ];

        new SequentialLoad(elements);
    }
}
