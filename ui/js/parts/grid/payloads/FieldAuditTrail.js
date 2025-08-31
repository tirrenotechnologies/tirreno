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
                className: 'date-col',
                targets: 0
            },
            {
                className: 'audit-id-col',
                targets: 1
            },
            {
                className: 'audit-value-col',
                targets: 2
            },
            {
                className: 'audit-value-col',
                targets: 3
            },
            {
                className: 'audit-id-col',
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
