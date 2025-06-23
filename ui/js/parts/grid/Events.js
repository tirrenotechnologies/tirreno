import {BaseGridWithPanel} from './BaseWithPanel.js?v=2';
import {
    renderResourceWithQueryAndEventType,
    renderDeviceWithOs,
    renderIpType,
    renderIpWithCountry,
    renderUserForEvent,
    renderTimestampForEvent,
} from '../DataRenderers.js?v=2';

export class EventsGrid extends BaseGridWithPanel {
    // 7, 8 - invisible time and id columns to prevent sorting buttons appearence
    get orderConfig() {
        return this.config.sessionGroup && !this.config.singleUser ? [[6, 'desc'], [7, 'desc'], [8, 'desc']] : [[7, 'desc'], [8, 'desc']];
    }

    get columnDefs() {
        const columnDefs = [
            {
                className: 'email-risk-score-short-col',
                targets: 0
            },
            {
                className: 'timestamp-col',
                targets: 1
            },
            {
                className: 'event-type-col',
                targets: 2
            },
            {
                className: 'ip-col',
                targets: 3
            },
            {
                className: 'ip-type-col',
                targets: 4
            },
            {
                className: 'device-type-col',
                targets: 5
            },
            {
                visible: false,
                targets: 6
            },
            {
                visible: false,
                targets: 7
            },
            {
                visible: false,
                targets: 8
            },
        ];

        return columnDefs;
    }

    get columns() {
        const userIdRender = (record) => renderUserForEvent(record, 'medium', this.config.sessionGroup, this.config.singleUser);
        const timestampRender = (record) => renderTimestampForEvent(record, this.config.sessionGroup, this.config.singleUser);

        const columns = [
            {
                data: 'userid',
                render: (data, type, record) => {
                    return userIdRender(record);
                },
                orderable: false
            },
            {
                data: 'time',
                render: (data, type, record) => {
                    return timestampRender(record);
                },
                orderable: false
            },
            {
                data: 'type',
                render: (data, type, record) => {
                    return renderResourceWithQueryAndEventType(record);
                },
                orderable: false
            },
            {
                data: 'ip',
                render: (data, type, record) => {
                    return renderIpWithCountry(record);
                },
                orderable: false
            },
            {
                data: 'ip_type',
                name: 'ip_type',
                render: (data, type, record) => {
                    return renderIpType(record);
                },
                orderable: false
            },
            {
                data: 'device',
                render: (data, type, record) => {
                    return renderDeviceWithOs(record);
                },
                orderable: false
            },
            {
                data: 'session_id',
                name: 'session_id',
            },
            {
                data: 'time',
                name: 'time',
            },
            {
                data: 'id',
                name: 'id',
            },
        ];

        return columns;
    }
}
