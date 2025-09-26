import {BaseGridWithPanel} from './BaseWithPanel.js?v=2';
import {
    renderDate,
    renderBoolean,
    renderDevice,
    renderOs,
    renderBrowser,
    renderLanguage,
} from '../DataRenderers.js?v=2';

export class DevicesGrid extends BaseGridWithPanel {
    get columnDefs() {
        const columnDefs = [
            {
                className: 'device-date-col',
                targets: 0
            },
            {
                className: 'device-type-col',
                targets: 1
            },
            {
                className: 'device-os-col',
                targets: 2
            },
            {
                className: 'device-browser-col',
                targets: 3
            },
            {
                className: 'device-language-col',
                targets: 4
            },
            {
                className: 'device-modified-col',
                targets: 5
            },
            {
                visible: false,
                targets: 6
            },
        ];

        return columnDefs;
    }

    get columns() {
        const columns = [
            {
                data: 'created',
                render: (data, type, record) => {
                    return renderDate(data);
                },
            },
            {
                data: 'device',
                render: (data, type, record) => {
                    return renderDevice(record);
                }
            },
            {
                data: 'os_name',
                render: (data, type, record) => {
                    return renderOs(record);
                }
            },
            {
                data: 'browser_name',
                render: (data, type, record) => {
                    return renderBrowser(record);
                }
            },
            {
                data: 'lang',
                render: (data, type, record) => {
                    return renderLanguage(record);
                },
            },
            {
                data: 'modified',
                render: renderBoolean
            },
            {
                data: 'id',
                name: 'id',
            },
        ];

        return columns;
    }
}
