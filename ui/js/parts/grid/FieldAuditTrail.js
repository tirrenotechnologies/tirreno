import {BaseGridWithPanel} from './BaseWithPanel.js?v=2';
import {
    renderTime,
    renderAuditField,
    renderAuditValue,
    renderAuditParent,
    renderUserWithScore,
} from '../DataRenderers.js?v=2';

export class FieldAuditTrailGrid extends BaseGridWithPanel {
    onTableRowClicked(e) {
        if (document.activeElement instanceof HTMLTextAreaElement) {
            e.preventDefault();
            return;
        }

        super.onTableRowClicked(e);
    }

    onRowClick(e) {
        if (document.activeElement instanceof HTMLTextAreaElement) {
            e.preventDefault();
            return;
        }

        super.onRowClick(e);
    }


    get orderConfig() {
        return [0, 'desc'];
    }

    get columnDefs() {
        return [
            {
                className: 'field-audit-trail-user-date-col',
                targets: 0
            },
            {
                className: 'field-audit-trail-user-field-col',
                targets: 1
            },
            {
                className: 'field-audit-trail-user-value-col',
                targets: 2
            },
            {
                className: 'field-audit-trail-user-value-col',
                targets: 3
            },
            {
                className: 'field-audit-trail-user-parent-col',
                targets: 4
            },
        ];
    }

    get columns() {
        let columns = [
            {
                data: 'field_id',
                render: (data, type, record) => {
                    return renderAuditField(record);
                },
            },
            {
                data: 'created',
                render: (data, type, record) => {
                    return renderTime(data);
                }
            },
            {
                data: 'new_value',
                render: renderAuditValue,
            },
            {
                data: 'old_value',
                render: renderAuditValue,
            },
            {
                data: 'parent_id',
                render: (data, type, record) => {
                    return renderAuditParent(record);
                },
            },
        ];

        if (!this.config.singleUser) {
            columns[0] = {
                data: 'userid',
                render: (data, type, record) => {
                    return renderUserWithScore(record);
                },
                orderable: false
            };
        }

        return columns;
    }
}
