import {BasePanel} from './BasePanel.js?v=2';
import {
    renderIp,
    renderTimeMsLogbook,
    renderEndpoint,
    renderErrorType,
    renderSensorError,
    renderJsonTextarea,
    renderMailto,
} from '../DataRenderers.js?v=2';

export class LogbookPanel extends BasePanel {

    constructor() {
        let eventParams = {
            enrichment: false,
            type: 'logbook',
            url: `${window.app_base}/admin/logbookDetails`,
            cardId: 'logbook-card',
            panelClosed: 'logbookPanelClosed',
            closePanel: 'closeLogbookPanel',
            rowClicked: 'logbookTableRowClicked',
        };
        super(eventParams);
    }

    proceedData(data) {
        data.ip         = renderIp(data);
        data.endpoint   = renderEndpoint(data);
        data.created    = renderTimeMsLogbook(data);
        data.error_type = renderErrorType(data);
        data.error_text = renderSensorError(data);
        data.request    = renderJsonTextarea(data.raw);

        data.mailto     = renderMailto(data);

        return data;
    }
}
