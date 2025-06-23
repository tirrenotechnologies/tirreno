import {BaseGridWithPanel} from './BaseWithPanel.js?v=2';
import {
    renderIp,
    renderRawTime,
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
                className: 'ip-col',
                targets: 0
            },
            {
                className: 'timestamp-col',
                targets: 1
            },
            {
                className: 'error-type-col',
                targets: 2
            },
            {
                className: 'error-text-col',
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
                data: 'raw_time',
                render: (data, type, record) => {
                    return renderRawTime(record);
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
