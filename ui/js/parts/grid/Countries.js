import {BaseGrid} from './Base.js?v=2';
import {fireEvent} from '../utils/Event.js?v=2';
import {renderClickableCountry} from '../DataRenderers.js?v=2';

export class CountriesGrid extends BaseGrid {
    get orderConfig() {
        return [[0, 'asc']];
    }

    drawCallback(settings) {
        super.drawCallback(settings);

        const data = settings.json.data;
        fireEvent('countriesGridLoaded', {data: data});
    }

    get columnDefs() {
        const columnDefs = [
            {
                className: 'country-full-name-flag-col',
                targets: 0
            },
            {
                className: 'country-code-col',
                targets: 1
            },
            {
                className: 'country-total-counters-col',
                targets: 2
            },
            {
                className: 'country-total-counters-col',
                targets: 3
            },
            {
                className: 'country-total-counters-col',
                targets: 4
            },
            {
                visible: false,
                targets: 5
            }
        ];

        return columnDefs;
    }

    get columns() {
        const columns = [
            {
                data: 'full_country',
                name: 'full_country',
                render: (data, type, record) => {
                    return renderClickableCountry(record, false);
                }
            },
            {
                data: 'country_iso',
                name: 'country_iso'
            },
            {
                data: 'total_account',
                name: 'total_account',
                render: this.renderTotalsLoader,
            },
            {
                data: 'total_visit',
                name: 'total_visit',
                render: this.renderTotalsLoader,
            },
            {
                data: 'total_ip',
                name: 'total_ip',
                render: this.renderTotalsLoader,
            },
            {
                data: 'id',
                name: 'id',
            },
        ];

        return columns;
    }

    updateTableFooter(dataTable) {
        const tableId = this.config.tableId;
        const pagerSelector = `#${tableId}_wrapper .dt-paging`;

        $(pagerSelector).hide();
    }
}
