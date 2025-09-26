import {BasePage} from './Base.js';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=2';
import {Map} from '../parts/Map.js?v=2';
import {EmailsGrid} from '../parts/grid/Emails.js?v=2';
import {IpsGrid} from '../parts/grid/Ips.js?v=2';
import {EventsGrid} from '../parts/grid/Events.js?v=2';
import {DevicesGrid} from '../parts/grid/Devices.js?v=2';
import {BaseBarChart} from '../parts/chart/BaseBar.js?v=2';
import {BaseSparklineChart} from '../parts/chart/BaseSparkline.js?v=2';
import {UserTiles} from '../parts/details/UserTiles.js?v=2';
import {EventPanel} from '../parts/panel/EventPanel.js?v=2';
import {SingleReviewButton} from '../parts/SingleReviewButton.js?v=2';
import {ScoreDetails} from '../parts/ScoreDetails.js?v=2';
import {PhonesGrid} from '../parts/grid/Phones.js?v=2';
import {FieldAuditTrailGrid} from '../parts/grid/payloads/FieldAuditTrail.js?v=2';
import {IspsGrid} from '../parts/grid/Isps.js?v=2';

import {EmailPanel} from '../parts/panel/EmailPanel.js?v=2';
import {PhonePanel} from '../parts/panel/PhonePanel.js?v=2';
import {DevicePanel} from '../parts/panel/DevicePanel.js?v=2';
import {ReenrichmentButton} from '../parts/ReenrichmentButton.js?v=2';

export class UserPage extends BasePage {

    constructor() {
        super('user', true);

        this.initUi();
    }

    initUi() {
        const userDetailsTiles  = this.getSelfDetails();
        const devicesGridParams = this.getDevicesGridParams();
        const ipsGridParams     = this.getIpsGridParams();
        const ispsGridParams    = this.getIspsGridParams();
        const eventsGridParams  = this.getEventsGridParams();
        eventsGridParams.sessionGroup = true;
        eventsGridParams.singleUser = true;

        const mapParams         = this.getMapParams();
        const chartParams       = this.getBarChartParams();
        const statsChartParams  = {
            getParams: () => ({
                mode:   'stats',
                id:     this.id,
            }),
        };

        ipsGridParams.tileId        = null;
        eventsGridParams.tileId     = null;
        devicesGridParams.tileId    = null;
        mapParams.tileId            = null;

        const emailsGridParams = {
            url:        '/admin/loadEmails',
            tableId:    'emails-table',
            panelType:  'email',

            isSortable: false,

            getParams:  this.getParams,
        };

        const phonesGridParams = {
            url:        '/admin/loadPhones',
            tableId:    'phones-table',
            panelType:  'phone',

            isSortable: false,

            getParams:  this.getParams,
        };

        const fieldAuditTrailGridParams = {
            url:        '/admin/loadFieldAuditTrail',
            tableId:    'field-audit-trail-table',

            isSortable: false,

            getParams:  this.getParams,
        };

        const userScoreDetails = {
            userId:     this.id,
        };

        new ScoreDetails(userScoreDetails);

        new SingleReviewButton(this.id);
        new EventPanel();
        new DevicePanel();
        new ReenrichmentButton();

        const isEmailPhone = !!document.getElementById('email-card');

        if (isEmailPhone) {
            new EmailPanel();
            new PhonePanel();
        }

        const elements = [
            [UserTiles,             userDetailsTiles],
            [BaseSparklineChart,    statsChartParams],
            [Map,                   mapParams],
            [IpsGrid,               ipsGridParams],
            [IspsGrid,              ispsGridParams],
            [DevicesGrid,           devicesGridParams],
        ];

        if (isEmailPhone) {
            elements.push([EmailsGrid, emailsGridParams]);
            elements.push([PhonesGrid, phonesGridParams]);
        }

        elements.push([FieldAuditTrailGrid, fieldAuditTrailGridParams]);
        elements.push([BaseBarChart,        chartParams]);
        elements.push([EventsGrid,          eventsGridParams]);

        new SequentialLoad(elements);
    }
}
