import {BaseGrid} from './Base.js?v=2';
import {
    renderDate,
    renderTime,
    renderAuditFieldName,
    renderClickableAuditFieldId,
} from '../DataRenderers.js?v=2';

export class FieldAuditsGrid extends BaseGrid {
    get orderConfig() {
        return [2, 'desc'];
    }

    get columnDefs() {
        return [
            {
                className: 'field-audit-trail-field-col',
                targets: 0
            },
            {
                className: 'field-audit-trail-field-col',
                targets: 1
            },
            {
                className: 'field-audit-trail-date-col',
                targets: 2
            },
            {
                className: 'field-audit-trail-date-col',
                targets: 3
            },
            {
                className: 'field-audit-trail-cnt-col',
                targets: 4
            },
            {
                className: 'field-audit-trail-cnt-col',
                targets: 5
            },
        ];
    }

    get columns() {
        return [
            {
                data: 'field_id',
                render: (data, type, record) => {
                    return renderClickableAuditFieldId(record);
                },
            },
            {
                data: 'field_name',
                render: (data, type, record) => {
                    return renderAuditFieldName(record);
                },
            },
            {
                data: 'created',
                render: (data, type, record) => {
                    return renderDate(data);
                },
            },
            {
                data: 'lastseen',
                render: (data, type, record) => {
                    return renderTime(data);
                },
            },
            {
                data: 'total_edit',
                name: 'total_edit',
                render: this.renderTotalsLoader
            },
            {
                data: 'total_account',
                name: 'total_account',
                render: this.renderTotalsLoader
            },
        ];
    }
}
