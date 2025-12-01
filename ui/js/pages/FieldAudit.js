import {BasePage} from './Base.js';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=2';
import {IpsGrid} from '../parts/grid/Ips.js?v=2';
import {IspsGrid} from '../parts/grid/Isps.js?v=2';
import {UsersGrid} from '../parts/grid/Users.js?v=2';
import {StaticTiles} from '../parts/StaticTiles.js?v=2';
import {FieldPanel} from '../parts/panel/FieldPanel.js?v=2';
import {FieldAuditTrailGrid} from '../parts/grid/FieldAuditTrail.js?v=2';

export class FieldAuditPage extends BasePage {

    constructor() {
        super('field', true);

        this.initUi();
    }

    initUi() {
        const usersGridParams   = this.getUsersGridParams();
        const ispsGridParams    = this.getIspsGridParams();
        const ipsGridParams     = this.getIpsGridParams();

        const fieldAuditTrailGridParams = this.getFieldAuditTrailParams();

        const tilesParams = {
            elems: ['totalUsers', 'totalIps', 'totalIsps', 'totalEdits'],
        };

        new StaticTiles(tilesParams);
        new FieldPanel();

        const elements = [
            [UsersGrid,             usersGridParams],
            [IpsGrid,               ipsGridParams],
            [IspsGrid,              ispsGridParams],
            [FieldAuditTrailGrid,   fieldAuditTrailGridParams],
        ];

        new SequentialLoad(elements);
    }
}
