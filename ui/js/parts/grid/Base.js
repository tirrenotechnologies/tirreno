import {Loader} from '../Loader.js?v=2';
import {Tooltip} from '../Tooltip.js?v=2';
import {fireEvent} from '../utils/Event.js?v=2';
import {getQueryParams} from '../utils/DataSource.js?v=2';
import {handleAjaxError} from '../utils/ErrorHandler.js?v=2';
import {TotalTile} from '../TotalTile.js?v=2';
import {renderTotalFrame} from '../DataRenderers.js?v=2';
import {
    MIDLINE_HELLIP,
} from '../utils/Constants.js?v=2';

export class BaseGrid {
    constructor(gridParams) {
        this.config = gridParams;

        this.loader    = new Loader();
        this.tooltip   = new Tooltip();
        this.totalTile = new TotalTile();

        this.firstload = true;

        this.renderTotalsLoader = this.renderTotalsLoader.bind(this);

        this.initLoad();

        if (this.config.dateRangeGrid && !this.config.sequential) {
            const onDateFilterChanged = this.onDateFilterChanged.bind(this);
            window.addEventListener('dateFilterChanged', onDateFilterChanged, false);
        }

        if (gridParams.choicesFilterEvents) {
            const onChoicesFilterChanged = this.onChoicesFilterChanged.bind(this);
            for (let i = 0; i < gridParams.choicesFilterEvents.length; i++) {
                window.addEventListener(gridParams.choicesFilterEvents[i], onChoicesFilterChanged, false);
            }
        }

        if (!this.config.sequential) {
            const onSearchFilterChanged = this.onSearchFilterChanged.bind(this);
            window.addEventListener('searchFilterChanged', onSearchFilterChanged, false);
        }
    }

    initLoad() {
        const me      = this;
        const tableId = this.config.tableId;

        $(document).ready(() => {
            $.extend($.fn.dataTable.ext.classes, {
                sStripeEven: '', sStripeOdd: ''
            });

            $.fn.dataTable.ext.pager.numbers_length = 9;

            $.fn.dataTable.ext.errMode = function() {};
            $(`#${tableId}`).on('error.dt', me.onError);

            const onBeforeLoad = me.onBeforeLoad.bind(me);
            $(`#${tableId}`).on('preXhr.dt', onBeforeLoad);

            const onBeforePageChange = me.onBeforePageChange.bind(me);
            $(`#${tableId}`).on('page.dt', onBeforePageChange);

            const config = me.getDataTableConfig();
            $(`#${tableId}`).DataTable(config);

            const onTableRowClick = me.onTableRowClick.bind(me);
            $(`#${tableId} tbody`).on('click', 'tr', onTableRowClick);

            const onDraw = me.onDraw.bind(me);
            $(`#${tableId}`).on('draw.dt', onDraw);

            $(`#${tableId}`).closest('.dt-container').find('nav').empty();

            document.getElementById(tableId).classList.add('hide-body');

            if (!me.config.sequential) {
                me.loadData();
            }
        });
    }

    getDataTableConfig() {
        const me         = this;
        const url        = this.config.url;
        const columns    = this.columns;
        const columnDefs = this.columnDefs;
        const isSortable = this.getConfigParam('isSortable');
        const order      = this.orderConfig;

        const config = {
            ajax: function(data, callback, settings) {
                $.ajax({
                    url: url,
                    method: 'GET',
                    data: data,
                    dataType: 'json',
                    success: function(response, textStatus, jqXHR) {
                        callback(response);
                        me.performAdditional(response, me.config);
                        me.stopAnimation();
                    },
                    error: handleAjaxError,
                });
            },
            processing: true,
            serverSide: true,
            deferRender: true,
            deferLoading: 0,
            pageLength: 25,
            autoWidth: false,
            lengthChange: false,
            searching: true,
            ordering: isSortable,
            info: false,
            pagingType: 'simple_numbers',
            language: {
                paginate: {
                    previous: '&lt;',
                    next: '&gt;',
                },
            },

            layout: {
                topEnd: null,
                bottomEnd: {
                    paging: {
                        boundaryNumbers: false,
                        type: 'simple_numbers',
                    },
                },
            },

            createdRow: function(row, data, dataIndex) {
                $(row).attr('data-item-id', data.id);
            },

            drawCallback: function(settings) {
                me.drawCallback(settings);
                me.updateTableFooter(this);
            },

            columnDefs: columnDefs,
            columns: columns,
            order: order
        };

        return config;
    }

    performAdditional(response, config) {
        if (!config.calculateTotals) {
            fireEvent('dateFilterChangedCompleted');

            return;
        }

        const ids = response.data.map(item => item.id);
        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;
        const dateRange = response.dateRange;

        if (dateRange && ids.length) {
            const requestData = {
                token: token,
                ids: ids,
                type: config.totals.type,
                startDate: dateRange.startDate,
                endDate: dateRange.endDate,
            };
            let preparedBase = {};
            response.data.forEach(rec => {
                preparedBase[rec.id] = rec;
            });
            $.ajax({
                type: 'GET',
                url: '/admin/timeFrameTotal',
                data: requestData,
                success: (data) => this.onTotalsSuccess(data, config, preparedBase),
                error: handleAjaxError,
                complete: function() {
                    fireEvent('dateFilterChangedCompleted');
                },
            });
        } else {
            fireEvent('dateFilterChangedCompleted');
        }

        if (!dateRange && ids.length) {
            // actualy making fake response from server
            let data = {totals: {}};
            let preparedBase = {};
            const cols = config.totals.columns;
            response.data.forEach(item => {
                data.totals[item.id] = {};
                preparedBase[item.id] = {};
                cols.forEach(col => {
                    data.totals[item.id][col] = item[col];
                    preparedBase[item.id][col] = item[col];
                });

            });
            this.onTotalsSuccess(data, config, preparedBase);
        }
    }

    onTotalsSuccess(data, config, base) {
        const table = $(`#${config.tableId}`).DataTable();
        const columns = config.totals.columns;

        let idxs = {};

        for (let i = 0; i < columns.length; i++) {
            idxs[columns[i]] = -1;
        }

        table.settings().init().columns.forEach((col, index) => {
            columns.forEach(colName => {
                if (col.data === colName || col.name === colName) {
                    idxs[colName] = index;
                }
            });

        });

        if (Object.values(idxs).includes(-1)) return;

        let rowData;
        let id;

        table.rows().every(function() {
            rowData = this.data();
            id = String(rowData.id);
            if (id in data.totals) {
                for (const col in idxs) {
                    $(table.cell(this, idxs[col]).node()).html(renderTotalFrame(base[id][col], data.totals[id][col]));
                }
            }
        });
    }

    drawCallback(settings) {
        const me    = this;

        this.initTooltips();

        //this.stopAnimation();

        const params = {
            tableId: me.config.tableId
        };

        if (settings && settings.iDraw > 1) {
            const total = settings.json.recordsTotal;
            const tileId  = this.config.tileId;
            const tableId = this.config.tableId;

            this.totalTile.update(tableId, tileId, total);
            this.updateTableTitle(total);

            fireEvent('tableLoaded', params);
        }
    }

    updateTableTitle(value) {
        const tableId = this.config.tableId;
        const wrapper = document.getElementById(tableId).closest('.card');

        if (wrapper) {
            const span = wrapper.querySelector('header span');

            span.textContent = value;
        }
    }

    stopAnimation() {
        this.loader.stop();
        const el = document.getElementById(`${this.config.tableId}_loader`);
        if (el) el.remove();

        const table = document.getElementById(this.config.tableId);
        table.classList.remove('dim-table');
    }

    updateTableFooter(dataTable) {
        const tableId = this.config.tableId;
        const pagerSelector = `#${tableId}_wrapper .dt-paging`;

        const api = dataTable.api();
        if (api.ajax && typeof api.ajax.json === 'function' && api.ajax.json() === undefined) {
            return;
        }

        $(`#${tableId}`).closest('.dt-container').find('nav').show();
        if (api.page.info().pages <= 1) {
            $(pagerSelector).hide();
        } else {
            $(pagerSelector).show();
        }
    }

    initTooltips() {
        const tableId = this.config.tableId;
        Tooltip.addTooltipsToGridRecords(tableId);
        Tooltip.addTooltipToSpans();
        Tooltip.addTooltipToParagraphs();
    }

    onBeforeLoad(e, settings, data) {
        if (!this.config.sequential) {
            this.startLoader();
        }
        this.updateTableTitle(MIDLINE_HELLIP);

        fireEvent('dateFilterChangedCaught');

        //TODO: move to events grid? Or not?
        const params = this.config.getParams();
        const queryParams = getQueryParams(params);

        for (let key in queryParams) {
            data[key] = queryParams[key];
        }

        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;
        data.token = token;
    }

    onDraw(e, settings) {
        if (this.firstload) {
            document.getElementById(this.config.tableId).classList.remove('hide-body');
            this.firstload = false;
        }
    }

    onBeforePageChange(e, settings) {
        const tableId   = this.config.tableId;
        const pagesPath = `#${tableId}_paginate a`;

        [...document.querySelectorAll(pagesPath)].forEach(a => {
            a.outerHTML =
            a.outerHTML
                .replace(/<a/g, '<span')
                .replace(/<\/a>/g, '</span>');
        });
    }

    onTableRowClick(event) {
        const selection = window.getSelection();
        if ('Range' === selection.type) {
            return;
        }

        const row  = event.target.closest('tr');
        const link = row.querySelector('a');

        if (link) {
            event.preventDefault();
            if (event.ctrlKey || event.metaKey) {
                window.open(link.href, '_blank');
            } else {
                window.location.href = link.href;
            }
        }
    }

    //TODO: leave blank and move to the events table
    addTableRowsEvents() {
        const tableId    = this.config.tableId;
        const onRowClick = this.onRowClick.bind(this);

        if ($(this.table).DataTable().data().any()) {
            const rows = document.querySelectorAll(`#${tableId} tbody tr`);
            rows.forEach(row => row.addEventListener('click', onRowClick, false));
        }
    }

    startLoader() {
        const tableId    = this.config.tableId;
        const loaderPath = `${tableId}_processing`;

        const loaderWrapper = document.getElementById(loaderPath);
        const el = document.createElement('p');
        el.className = 'text-loader';
        loaderWrapper.replaceChildren(el);

        this.loader.start(el);

        loaderWrapper.style.display = null;

        const table = document.getElementById(this.config.tableId);
        table.classList.add('dim-table');
    }

    onRowClick(e) {
        const selection = window.getSelection();
        if ('Range' === selection.type) {
            return;
        }

        e.preventDefault();

        const row    = e.target.closest('tr');
        const itemId = row.dataset.itemId;
        const data   = {itemId: itemId};

        fireEvent('tableRowClicked', data);
    }

    onError(e, settings, techNote, message) {
        if (settings.jqXHR !== undefined && 403 === settings.jqXHR.status) {
            window.location.href = escape('/');
        }

        //console.warn('An error has been reported by DataTables: ', message);
    }

    loadData() {
        //TODO: create getter for table el: $(me.table).DataTable().ajax.reload()
        const me = this;
        $(me.table).DataTable().ajax.reload();
    }

    onDateFilterChanged() {
        this.loadData();
    }

    onSearchFilterChanged() {
        this.loadData();
    }

    onChoicesFilterChanged() {
        this.loadData();
    }

    getConfigParam(key) {
        const cfg   = this.config;
        const value = ('undefined' !== typeof cfg[key]) ? cfg[key] : true;

        return value;
    }

    get orderConfig() {
        return this.getConfigParam('isSortable') ? [[1, 'desc']] : [];
    }

    get table() {
        const tableId = this.config.tableId;
        const tableEl = document.getElementById(tableId);

        return tableEl;
    }

    renderTotalsLoader(data, type, record, meta) {
        const span = document.createElement('span');

        const col_name = meta.settings.aoColumns[meta.col].name;
        if (this.config.calculateTotals && this.config.totals.columns.includes(col_name)) {
            span.className = 'loading-table-total';
            span.textContent = MIDLINE_HELLIP;
        } else {
            span.textContent = data;
        }

        return span;
    }
}
