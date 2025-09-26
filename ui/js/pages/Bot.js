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
        super('bot', true);

        this.initUi();
    }

    initUi() {
        const usersGridParams   = this.getUsersGridParams();
        const eventsGridParams  = this.getEventsGridParams();
        const ipsGridParams     = this.getIpsGridParams();
        const mapParams         = this.getMapParams();

        const botDetailsTiles   = this.getSelfDetails();

        const chartParams       = this.getBarChartParams();

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
