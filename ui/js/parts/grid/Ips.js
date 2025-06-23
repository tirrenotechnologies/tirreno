import {BaseGrid} from './Base.js?v=2';
import {
    renderIpType,
    renderUserCounter,
    renderNetName,
    renderFullCountry,
    renderAsn,
    renderClickableIpWithCountry,
} from '../DataRenderers.js?v=2';


export class IpsGrid extends BaseGrid {
    get orderConfig() {
        return [[this.config.orderByLastseen ? 7 : 6, 'desc']];
    }

    get columnDefs() {
        const columnDefs = [
            {
                className: 'ip-col',
                targets: 0
            },
            {
                className: 'country-full-name-col',
                targets: 1
            },
            {
                className: 'asn-col',
                targets: 2
            },
            {
                className: 'ip-netname-col',
                targets: 3
            },
            {
                className: 'ip-type-col',
                targets: 4
            },
            {
                className: 'ip-total-counters-col',
                targets: 5
            },
            {
                className: 'ip-total-counters-col',
                targets: 6
            },
            {
                visible: false,
                targets: 7
            },
            {
                visible: false,
                targets: 8
            }
            //  TODO: return alert_list back in next release
            //{
            //    className: 'yes-no-col',
            //    targets: 9
            //}
        ];

        return columnDefs;
    }

    get columns() {
        const columns = [
            {
                data: 'ip',
                name: 'ip',
                render: (data, type, record) => {
                    const rec = {
                        ip: record.ip,
                        ipid: record.id,
                        country: record.country,
                        full_country: record.full_country,
                        isp_name: record.netname,
                    };
                    return renderClickableIpWithCountry(rec);
                }
            },
            {
                data: 'full_country',
                render: renderFullCountry,
            },
            {
                data: 'asn',
                name: 'asn',
                render: (data, type, record) => {
                    return renderAsn(record);
                },
            },
            {
                data: 'netname',
                name: 'netname',
                render: (data, type, record) => {
                    return renderNetName(record, 'short');
                }
            },
            {
                data: 'ip_type',
                name: 'ip_type',
                orderable: false,
                render: (data, type, record) => {
                    return renderIpType(record);
                }
            },
            {
                data: 'total_visit',
                name: 'total_visit',
                render: this.renderTotalsLoader
            },
            {
                data: 'total_account',
                name: 'total_account',
                render: (data, type, record) => {
                    return renderUserCounter(data, 2);
                }
            },
            {
                data: 'lastseen',
                name: 'lastseen',
            },
            {
                data: 'id',
                name: 'id',
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
