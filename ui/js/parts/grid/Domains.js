import {BaseGrid} from './Base.js?v=2';
import {
    renderClickableDomain,
    renderBoolean,
    renderDate,
    renderDefaultIfEmptyElement,
    renderUserCounter,
} from '../DataRenderers.js?v=2';

export class DomainsGrid extends BaseGrid {
    get orderConfig() {
        return [[6, 'desc']];
    }

    get columnDefs() {
        const columnDefs = [
            {
                className: 'domain-col',
                targets: 0
            },
            {
                className: 'yes-no-col',
                targets: 1
            },
            {
                className: 'domain-rating-col',
                targets: 2
            },
            {
                className: 'yes-no-col',
                targets: 3
            },
            {
                className: 'yes-no-col',
                targets: 4
            },
            {
                className: 'date-col',
                targets: 5
            },
            {
                className: 'domains-total-counters-col',
                targets: 6
            },
            {
                className: 'domains-total-counters-col',
                targets: 7
            },
            {
                visible: false,
                targets: 8
            }
        ];

        return columnDefs;
    }

    get columns() {
        const columns = [
            {
                data: 'domain',
                render: (data, type, record) => {
                    return renderClickableDomain(record);
                }
            },
            {
                data: 'free_email_provider',
                render: (data, type, record) => {
                    const free_email_provider = record.free_email_provider;
                    return renderBoolean(free_email_provider);
                }
            },
            {
                data: 'tranco_rank',
                name: 'tranco_rank',
                render: (data, type, record) => {
                    let rank = renderDefaultIfEmptyElement(data);
                    if (data) {
                        rank = data;
                    }

                    return rank;
                }
            },
            {
                data: 'disabled',
                render: (data, type, record) => {
                    const unavailable = record.disabled;
                    return renderBoolean(unavailable);
                }
            },
            {
                data: 'disposable_domains',
                render: (data, type, record) => {
                    const disposable = record.disposable_domains;
                    return renderBoolean(disposable);
                }
            },
            {
                data: 'creation_date',
                render: (data, type, record) => {
                    const creation_date = record.creation_date;

                    if (creation_date) {
                        return renderDate(creation_date);
                    } else {
                        return renderDefaultIfEmptyElement(creation_date);
                    }
                }
            },
            {
                data: 'total_account',
                name: 'total_account',
                render: this.renderTotalsLoader,
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
