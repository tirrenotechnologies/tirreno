import {BaseGridWithPanel} from './BaseWithPanel.js?v=2';
import {
    renderIp,
    renderTimeMsLogbook,
    renderEndpoint,
    renderErrorType,
    renderSensorErrorColumn,
} from '../DataRenderers.js?v=2';

export class LogbookGrid extends BaseGridWithPanel {

    get orderConfig() {
        return [[3, 'desc'], [1, 'desc']];
    }

    get columnDefs() {
        const columnDefs = [
            {
                className: 'logbook-ip-col',
                targets: 0
            },
            {
                className: 'logbook-timestamp-col',
                targets: 1
            },
            {
                className: 'logbook-endpoint-col',
                targets: 2
            },
            {
                className: 'logbook-status-col',
                targets: 3
            },
            {
                className: 'logbook-message-col',
                targets: 4
            },
        ];

        return columnDefs;
    }

    get columns() {
        const columns = [
            {
                data: 'ip',
                render: (data, type, record) => {
                    return renderIp(record);
                }
            },
            {
                data: 'created',
                render: (data, type, record) => {
                    return renderTimeMsLogbook(record);
                }
            },
            {
                data: 'endpoint',
                render: (data, type, record) => {
                    return renderEndpoint(record);
                }
            },
            {
                data: 'error_type',
                render: (data, type, record) => {
                    return renderErrorType(record);
                }
            },
            {
                data: 'error_text',
                render: (data, type, record) => {
                    return renderSensorErrorColumn(record);
                }
            },
        ];

        return columns;
    }
}
