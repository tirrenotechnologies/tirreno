import {BaseGrid} from './Base.js?v=2';
import {
    currentPlanRender,
    currentStatusRender,
    currentUsageRender,
    currentBillingEndRender,
    updateCardButtonRender,
} from '../DataRenderers.js?v=2';


export class UsageStatsGrid extends BaseGrid {

    // do not update counter tile
    updateTableTitle(value) {
    }

    // do not use pagination
    updateTableFooter(dataTable) {
        const tableId = this.config.tableId;
        const pagerSelector = `#${tableId}_wrapper .dt-paging`;

        $(pagerSelector).hide();
    }

    get columnDefs() {
        const columnDefs = [
            {
                className: 'subscription-plan-col',
                targets: 0
            },
            {
                className: 'subscription-status-col',
                targets: 1
            },
            {
                className: 'subscription-usage-col',
                targets: 2
            },
            {
                className: 'billing-date-col',
                targets: 3
            },
            {
                className: 'action-button-col',
                targets: 4
            },
        ];

        return columnDefs;
    }

    get columns() {
        const columns = [
            {
                data: 'sub_plan_api_calls',
                render: currentPlanRender,
                orderable: false,
            },
            {
                data: 'sub_status',
                render: currentStatusRender,
                orderable: false,
            },
            {
                data: 'sub_calls_used',
                render: currentUsageRender,
                orderable: false,
            },
            {
                data: 'sub_next_billed',
                render: currentBillingEndRender,
                orderable: false,
            },
            {
                data: 'sub_update_url',
                render: updateCardButtonRender,
                orderable: false,
            },
        ];

        return columns;
    }
}
