import {BasePage} from './Base.js';

import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {FieldAuditsChart} from '../parts/chart/FieldAudits.js?v=2';
import {FieldAuditsGrid} from '../parts/grid/FieldAudits.js?v=2';

export class FieldAuditsPage extends BasePage {

    constructor() {
        super('fields');

        this.initUi();
    }

    initUi() {
        const datesFilter  = new DatesFilter();
        const searchFilter = new SearchFilter();

        this.setBaseFilters(datesFilter, searchFilter);

        const gridParams = {
            url:        `${window.app_base}/admin/loadFieldAudits`,
            //tileId:     '',
            tableId:    'field-audits-table',

            dateRangeGrid:      true,
            singleUser:         false,
            calculateTotals:    true,
            totals: {
                type: 'field',
                columns: ['total_edit', 'total_account'],
            },

            getParams: this.getParamsSection,
        };

        const chartParams = this.getChartParams(datesFilter, searchFilter);

        new FieldAuditsChart(chartParams);
        new FieldAuditsGrid(gridParams);
    }
}
