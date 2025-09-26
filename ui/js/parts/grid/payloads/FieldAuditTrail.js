import {BaseGrid} from '../Base.js?v=2';
import {
    renderDate,
    renderAuditField,
    renderAuditValue,
    renderAuditParent,
} from '../../DataRenderers.js?v=2';

export class FieldAuditTrailGrid extends BaseGrid {
    get orderConfig() {
        return [];
    }

    get columnDefs() {
        const columnDefs = [
            {
                className: 'field-audit-trail-date-col',
                targets: 0
            },
            {
                className: 'field-audit-trail-field-col',
                targets: 1
            },
            {
                className: 'field-audit-trail-value-col',
                targets: 2
            },
            {
                className: 'field-audit-trail-value-col',
                targets: 3
            },
            {
                className: 'field-audit-trail-parent-col',
                targets: 4
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
                data: 'field_id',
                render: (data, type, record) => {
                    return renderAuditField(record);
                },
            },
            {
                data: 'old_value',
                render: renderAuditValue,
            },
            {
                data: 'new_value',
                render: renderAuditValue,
            },
            {
                data: 'parent_id',
                render: (data, type, record) => {
                    return renderAuditParent(record);
                },
            },
        ];

        return columns;
    }
}
