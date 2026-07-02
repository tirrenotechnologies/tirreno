import {BasePanel} from './BasePanel.js?v=0.10.0';
import {
    renderIp,
    renderTimeMsLogbook,
    renderEndpoint,
    renderErrorType,
    renderSensorError,
    renderJsonTextarea,
    renderMailto,
} from '../DataRenderers.js?v=0.10.0';

export class LogbookPanel extends BasePanel {
    constructor() {
        let eventParams = {
            enrichmentUrl: false,
            type: 'logbook',
            url: `${window.app_base}/logbookDetails`,
            cardId: 'logbook-card',
            panelClosed: 'logbookPanelClosed',
            closePanel: 'closeLogbookPanel',
            rowClicked: 'logbookTableRowClicked',
        };
        super(eventParams);
    }

    proceedData(data) {
        // ! render ip after endpoint because endpoint render checks if ip is empty
        data.endpoint   = renderEndpoint(data, true);
        data.ip         = renderIp(data);
        data.created    = renderTimeMsLogbook(data);
        data.error_type = renderErrorType(data, true);
        data.error_text = renderSensorError(data);
        data.request    = renderJsonTextarea(data.raw);

        data.mailto     = renderMailto(data);

        return data;
    }
}
