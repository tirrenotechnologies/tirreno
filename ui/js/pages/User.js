import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {Map} from '../parts/Map.js?v=0.10.0';
import {EmailsGrid} from '../parts/grid/Emails.js?v=0.10.0';
import {IpsGrid} from '../parts/grid/Ips.js?v=0.10.0';
import {EventsGrid} from '../parts/grid/Events.js?v=0.10.0';
import {DevicesGrid} from '../parts/grid/Devices.js?v=0.10.0';
import {BaseBarChart} from '../parts/chart/BaseBar.js?v=0.10.0';
import {BaseSparklineChart} from '../parts/chart/BaseSparkline.js?v=0.10.0';
import {UserTiles} from '../parts/details/UserTiles.js?v=0.10.0';
import {EventPanel} from '../parts/panel/EventPanel.js?v=0.10.0';
import {FieldPanel} from '../parts/panel/FieldPanel.js?v=0.10.0';
import {SingleReviewButton} from '../parts/button/SingleReviewButton.js?v=0.10.0';
import {ScoreDetails} from '../parts/ScoreDetails.js?v=0.10.0';
import {PhonesGrid} from '../parts/grid/Phones.js?v=0.10.0';
import {FieldAuditTrailGrid} from '../parts/grid/FieldAuditTrail.js?v=0.10.0';
import {IspsGrid} from '../parts/grid/Isps.js?v=0.10.0';

import {EmailPanel} from '../parts/panel/EmailPanel.js?v=0.10.0';
import {PhonePanel} from '../parts/panel/PhonePanel.js?v=0.10.0';
import {DevicePanel} from '../parts/panel/DevicePanel.js?v=0.10.0';
import {ReenrichmentButton} from '../parts/button/ReenrichmentButton.js?v=0.10.0';

export class UserPage extends BasePage {
    constructor() {
        super('user', true);
    }

    initUi() {
        const userDetailsTiles  = this.getSelfDetails();
        const devicesGridParams = this.getDevicesGridParams();
        const ipsGridParams     = this.getIpsGridParams();
        const ispsGridParams    = this.getIspsGridParams();
        const eventsGridParams  = this.getEventsGridParams();
        eventsGridParams.sessionGroup = true;
        eventsGridParams.singleUser = true;

        const fieldAuditTrailGridParams = this.getFieldAuditTrailParams();
        fieldAuditTrailGridParams.singleUser = true;

        const mapParams         = this.getMapParams();
        const chartParams       = this.getBarChartParams();
        const statsChartParams  = {
            url:    `${window.app_base}/loadUserSparklinesChart`,
            id:     this.id,
        };

        ipsGridParams.tileId        = null;
        eventsGridParams.tileId     = null;
        devicesGridParams.tileId    = null;
        mapParams.tileId            = null;

        const emailsGridParams = {
            url:        `${window.app_base}/loadEmails`,
            tableId:    'emails-table',
            panelType:  'email',

            isSortable: false,

            getParams:  this.getParams,
        };

        const phonesGridParams = {
            url:        `${window.app_base}/loadPhones`,
            tableId:    'phones-table',
            panelType:  'phone',

            isSortable: false,

            getParams:  this.getParams,
        };

        const userScoreDetails = {
            userId:     this.id,
        };

        new ScoreDetails(userScoreDetails);

        new SingleReviewButton(this.id);
        new EventPanel();
        new FieldPanel();
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
