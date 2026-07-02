import {BaseGridWithPanel} from './BaseWithPanel.js?v=0.10.0';
import {
    renderIp,
    renderTimeMsLogbook,
    renderEndpoint,
    renderErrorType,
    renderRawRequestColumn,
} from '../DataRenderers.js?v=0.10.0';

export class LogbookGrid extends BaseGridWithPanel {
    get orderConfig() {
        return [[1, 'desc']];
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
                className: 'logbook-raw-col',
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
                data: 'raw',
                render: (data, type, record) => {
                    return renderRawRequestColumn(record);
                }
            },
        ];

        return columns;
    }
}
