import {BaseGrid} from './Base.js?v=2';
import {
    renderClickableAsn,
    renderNetName,
    renderUserCounter,
} from '../DataRenderers.js?v=2';

export class IspsGrid extends BaseGrid {
    get orderConfig() {
        return [[3, 'desc']];
    }

    get columnDefs() {
        const columnDefs = [
            {
                className: 'asn-col',
                targets: 0
            },
            {
                className: 'isp-netname-col',
                targets: 1
            },
            {
                className: 'total-counters-col',
                targets: 2
            },
            {
                className: 'total-counters-col',
                targets: 3
            },
            {
                className: 'total-counters-col',
                targets: 4
            },
            {
                className: 'total-counters-col',
                targets: 5
            },
            {
                visible: false,
                targets: 6
            }
        ];

        return columnDefs;
    }

    get columns() {
        const columns = [
            {
                data: 'asn',
                name: 'asn',
                render: (data, type, record) => {
                    record['ispid'] = record.id;
                    return renderClickableAsn(record);
                }
            },
            {
                data: 'name',
                name: 'name',
                render: (data, type, record) => {
                    record.netname = data;
                    return renderNetName(record, 'long');
                }
            },
            {
                data: 'total_visit',
                name: 'total_visit',
                render: this.renderTotalsLoader
            },
            {
                data: 'total_ip',
                name: 'total_ip',
                render: this.renderTotalsLoader
            },
            {
                data: 'total_account',
                name: 'total_account',
                render: this.renderTotalsLoader
            },
            {
                data: 'fraud',
                name: 'fraud',
                render: (data, type, record) => {
                    return renderUserCounter(data, 1);
                }
            },
            {
                data: 'id',
                name: 'id',
            },
        ];

        return columns;
    }
}
