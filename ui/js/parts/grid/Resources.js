import {BaseGrid} from './Base.js?v=2';
import {
    renderClickableResourceWithoutQuery,
    renderHttpCode,
    renderBoolean,
} from '../DataRenderers.js?v=2';

export class ResourcesGrid extends BaseGrid {
    get orderConfig() {
        return [[7, 'desc']];
    }

    get columnDefs() {
        const columnDefs = [
            {
                className: 'url-col',
                targets: 0
            },
            {
                className: 'http-response-code-col',
                targets: 1
            },
            {
                className: 'resources-total-counters-col',
                targets: 2
            },
            {
                className: 'resources-total-counters-col',
                targets: 3
            },
            {
                className: 'resources-total-counters-col',
                targets: 4
            },
            {
                className: 'resources-total-counters-col',
                targets: 5
            },
            {
                className: 'yes-no-col',
                targets: 6
            },
            {
                visible: false,
                targets: 7
            }
        ];

        return columnDefs;
    }

    get columns() {
        const columns = [
            {
                data: 'title',
                render: (data, type, record) => {
                    return renderClickableResourceWithoutQuery(record);
                }
            },
            {
                data: 'http_code',
                render: (data, type, record) => {
                    return renderHttpCode(record);
                }
            },
            {
                data: 'total_account',
                name: 'total_account',
                render: this.renderTotalsLoader
            },
            {
                data: 'total_country',
                name: 'total_country',
                render: this.renderTotalsLoader
            },
            {
                data: 'total_ip',
                name: 'total_ip',
                render: this.renderTotalsLoader
            },
            {
                data: 'total_visit',
                name: 'total_visit',
                render: this.renderTotalsLoader
            },
            {
                data: 'suspicious',
                render: renderBoolean,
                orderable: false
            },
            {
                data: 'id',
                name: 'id',
            }
        ];

        return columns;
    }
}
