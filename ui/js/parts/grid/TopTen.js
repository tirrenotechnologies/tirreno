import {BaseGrid} from './Base.js?v=2';
import {renderDefaultIfEmpty}  from '../DataRenderers.js?v=2';

export class TopTenGrid extends BaseGrid {
    get columnDefs() {
        const columnDefs = [
            {
                className: 'top-ten-aggregating-col',
                targets: 0
            },
            {
                className: 'level-center',
                targets: 1
            },
        ];

        return columnDefs;
    }

    get columns() {
        const columns = [
            {
                data: 'item',
                render: (data, type, record) => {
                    return this.config.renderItemColumn(record);
                },
            },
            {
                data: 'value',
                render: renderDefaultIfEmpty,
            }
        ];

        return columns;
    }

    getDataTableConfig() {
        const me         = this;
        const columns    = this.columns;
        const columnDefs = this.columnDefs;

        const mode  = this.config.mode;
        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;

        const config = {
            ajax: `/admin/loadTopTen?mode=${mode}&token=${token}`,
            processing: true,
            serverSide: true,
            searching: false,
            pageLength: 10,
            bPaginate: false,
            bInfo: false,
            lengthChange: false,
            ordering: false,
            autoWidth: false,
            info: false,

            createdRow: function(row, data, dataIndex) {
                $(row).attr('data-item-id', data.id);
            },

            drawCallback: function(settings) {
                me.drawCallback(settings);
                me.updateTableFooter(this);
            },

            columnDefs: columnDefs,
            columns: columns
        };

        return config;
    }
}
