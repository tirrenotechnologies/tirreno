import {BaseGrid} from './Base.js?v=2';
import {fireEvent} from '../utils/Event.js?v=2';
import {renderClickableCountry} from '../DataRenderers.js?v=2';

export class CountriesGrid extends BaseGrid {
    get orderConfig() {
        return [[0, 'asc']];
    }

    drawCallback(settings) {
        super.drawCallback(settings);

        if (settings && settings.iDraw > 1) {
            const data = settings.json.data;
            fireEvent('countriesGridLoaded', {data: data});
        }
    }

    get columnDefs() {
        const columnDefs = [
            {
                className: 'country-country-col',
                targets: 0
            },
            {
                className: 'country-iso-col',
                targets: 1
            },
            {
                className: 'country-cnt-col',
                targets: 2
            },
            {
                className: 'country-cnt-col',
                targets: 3
            },
            {
                className: 'country-cnt-col',
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

        const api = dataTable.api();
        if (api.ajax && typeof api.ajax.json === 'function' && api.ajax.json() === undefined) {
            $(`${pagerSelector} nav`).empty();

            return;
        }

        $(pagerSelector).hide();
    }
}
