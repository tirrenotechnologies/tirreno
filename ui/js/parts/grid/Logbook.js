import {BaseGridWithPanel} from './BaseWithPanel.js?v=2';
import {
    renderIp,
    renderTimeMs,
    renderErrorType,
    renderSensorErrorColumn,
} from '../DataRenderers.js?v=2';

export class LogbookGrid extends BaseGridWithPanel {

    get orderConfig() {
        return [[2, 'desc'], [1, 'desc']];
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
                className: 'logbook-status-col',
                targets: 2
            },
            {
                className: 'logbook-message-col',
                targets: 3
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
                data: 'started',
                render: renderTimeMs,
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
