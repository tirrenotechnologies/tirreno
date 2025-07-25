import {BaseGrid} from './Base.js?v=2';
import {fireEvent} from '../utils/Event.js?v=2';
import {handleAjaxError} from '../utils/ErrorHandler.js?v=2';
import {renderDefaultIfEmptyElement}  from '../DataRenderers.js?v=2';

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
                render: renderDefaultIfEmptyElement,
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
            ajax: function(data, callback, settings) {
                $.ajax({
                    url: `/admin/loadTopTen?mode=${mode}&token=${token}`,
                    method: 'GET',
                    data: data,
                    dataType: 'json',
                    success: function(response, textStatus, jqXHR) {
                        callback(response);
                    },
                    error: handleAjaxError,
                    complete: function() {
                        fireEvent('dateFilterChangedCompleted');
                    },
                });
            },

            processing: true,
            serverSide: true,
            searching: false,
            pageLength: 10,
            paging: false,
            info: false,
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
