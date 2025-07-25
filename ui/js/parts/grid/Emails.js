import {BaseGridWithPanel} from './BaseWithPanel.js?v=2';
import {
    renderBoolean,
    renderReputation,
    renderEmail,
    renderDefaultIfEmptyElement,
} from '../DataRenderers.js?v=2';

export class EmailsGrid extends BaseGridWithPanel {

    get orderConfig() {
        return [];
    }

    get columnDefs() {
        const columnDefs = [
            {
                className: 'email-col',
                targets: 0
            },
            {
                className: 'email-reputation-col',
                targets: 1
            },
            {
                className: 'yes-no-col',
                targets: 2
            },
            {
                className: 'medium-yes-no-col',
                targets: 3
            },
            {
                className: 'medium-yes-no-col',
                targets: 4
            },
            {
                className: 'medium-yes-no-col',
                targets: 5
            },
            {
                className: 'medium-yes-no-col',
                targets: 6
            },
            {
                className: 'short-yes-no-col',
                targets: 7
            },
            //  TODO: return alert_list back in next release
            //{
            //    className: 'medium-yes-no-col',
            //    targets: 8
            //}
        ];

        return columnDefs;
    }

    get columns() {
        const columns = [
            {
                data: 'email',
                render: (data, type, record) => {
                    return renderEmail(record);
                }
            },
            {
                data: 'reputation',
                render: (data, type, record) => {
                    return renderReputation(record);
                }
            },
            {
                data: 'free_email_provider',
                render: renderBoolean
            },
            //{
            //    data: 'profiles',
            //    orderable: false,
            //    render: (data, type, record) => {
            //        // revert profiles to `no profiles`
            //        return renderBoolean(data === null ? null : !data);
            //    }
            //},
            {
                data: 'data_breach',
                orderable: false,
                render: (data, type, record) => {
                    // revert data breach to `no breach`
                    return renderBoolean(data === null ? null : !data);
                }
            },
            {
                data: 'data_breaches',
                render: renderDefaultIfEmptyElement
            },
            {
                data: 'disposable_domains',
                render: renderBoolean
            },
            {
                data: 'blockemails',
                render: renderBoolean
            },
            {
                data: 'fraud_detected',
                render: renderBoolean
            },
            //  TODO: return alert_list back in next release
            //{
            //    data: 'alert_list',
            //    render: renderBoolean
            //}
        ];

        return columns;
    }
}
