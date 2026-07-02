import {BasePanel} from './BasePanel.js?v=0.10.0';
import {
    renderDeviceWithOs,
    renderBrowser,
    renderLanguage,
    renderDate,
    renderBoolean,
    renderUserAgent,
} from '../DataRenderers.js?v=0.10.0';
import {defined} from '../utils/Functions.js?v=0.10.0';

export class DevicePanel extends BasePanel {
    constructor() {
        let eventParams = {
            enrichemntUrl: false,
            type: 'device',
            url: `${window.app_base}/deviceDetails`,
            cardId: 'device-card',
            panelClosed: 'devicePanelClosed',
            closePanel: 'closeDevicePanel',
            rowClicked: 'deviceTableRowClicked',
        };
        super(eventParams);
    }

    proceedData(data) {
        let browser_name    = data.browser_name;
        let browser_version = data.browser_version;
        browser_name        = defined(browser_name) ? browser_name : '';
        browser_version     = defined(browser_version) ? browser_version : '';

        const device_record   = {
            ua:             data.ua,
            os_name:        data.os_name,
            os_version:     data.os_version,
            device_name:    data.device,
            browser:        `${browser_name} ${browser_version}`,
            lang:           data.lang
        };
        data.device               = renderDeviceWithOs(device_record);
        data.browser              = renderBrowser(device_record);
        data.lang                 = renderLanguage(device_record);
        data.device_created       = renderDate(data.created);

        data.ua_modified          = renderBoolean(data.modified);
        data.ua                   = renderUserAgent(data);

        return data;
    }
}
