import {BaseGrid} from './Base.js?v=0.10.0';
import {
    renderRuleUid,
    renderRuleType,
    renderRuleDescription,
    renderRuleProportion,
    renderRuleWeightSelector,
    renderRuleManageButtons,
} from '../DataRenderers.js?v=0.10.0';

export class RulesGrid extends BaseGrid {
    get orderConfig() {
        return [[0, 'asc']];
    }

    createdRowCallback(row, data, dataIndex) {
        $(row).attr('data-rule-uid', data.uid);
    }

    //createdCellCallback(td, cellData, rowData) {
    //}

    onTableRowClick(event) {}

    onDateFilterChanged() {}

    get columnDefs() {
        const columnDefs = [
            {
                className: 'rules-code-col',
                targets: 0,
                createdCell: function(td, cellData, rowData) {
                    td.style.textAlign = 'right';
                }
            },
            {
                className: 'rules-type-col',
                targets: 1
            },
            {
                className: 'rules-description-col',
                targets: 2
            },
            {
                className: 'rules-proportion-col',
                targets: 3,
                createdCell: function(td, cellData, rowData) {
                    td.style.textAlign = 'center';
                }
            },
            {
                className: 'rules-weight-col',
                targets: 4
            },
            {
                className: 'rules-action-col',
                targets: 5
            },
        ];

        return columnDefs;
    }

    get columns() {
        const columns = [
            {
                data: 'uid',
                orderable: false,
                render: (data, type, record) => {
                    return renderRuleUid(record);
                }
            },
            {
                data: 'type',
                orderable: false,
                render: (data, type, record) => {
                    return renderRuleType(record);
                }
            },
            {
                data: 'name',
                orderable: false,
                render: (data, type, record) => {
                    return renderRuleDescription(record);
                }
            },
            {
                data: 'broken',
                orderable: false,
                render: (data, type, record) => {
                    return renderRuleProportion(record);
                }
            },
            {
                data: 'uid',
                orderable: false,
                render: (data, type, record) => {
                    return renderRuleWeightSelector(record);
                }
            },
            {
                data: 'broken',
                orderable: false,
                render: (data, type, record) => {
                    return renderRuleManageButtons(record);
                }
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
