import {BasePanel} from './BasePanel.js?v=2';
import {
    renderIp,
    renderRawTime,
    renderErrorType,
    renderSensorError,
    renderRawRequest,
    renderMailto,
} from '../DataRenderers.js?v=2';

export class LogbookPanel extends BasePanel {

    constructor() {
        let eventParams = {
            enrichment: false,
            type: 'logbook',
            url: '/admin/logbookDetails',
            cardId: 'logbook-card',
            panelClosed: 'logbookPanelClosed',
            closePanel: 'closeLogbookPanel',
            rowClicked: 'logbookTableRowClicked',
        };
        super(eventParams);
    }

    proceedData(data) {
        data.ip         = renderIp(data);
        data.raw_time   = renderRawTime(data);
        data.error_type = renderErrorType(data);
        data.error_text = renderSensorError(data);
        data.request    = renderRawRequest(data);

        data.mailto     = renderMailto(data);

        return data;
    }
}
