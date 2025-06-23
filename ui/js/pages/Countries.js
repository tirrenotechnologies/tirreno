import {BasePage} from './Base.js';

import {Map} from '../parts/Map.js?v=2';
import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {SearchFilter} from '../parts/SearchFilter.js?v=2';
import {CountriesGrid} from '../parts/grid/Countries.js?v=2';

export class CountriesPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        const datesFilter   = new DatesFilter();
        const searchFilter  = new SearchFilter();

        const getMapParams = () => {
            const dateRange = datesFilter.getValue();
            return {dateRange};
        };

        const gridParams = {
            url:        '/admin/loadCountries',
            tileId:     'totalCountries',
            tableId:    'countries-table',

            dateRangeGrid:      true,
            calculateTotals:    true,
            totals: {
                type: 'country',
                columns: ['total_visit', 'total_account', 'total_ip'],
            },

            getParams: function() {
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {dateRange, searchValue};
            }
        };

        const mapParams = {
            getParams:      getMapParams,
            tooltipString:  'user',
            tooltipField:   'total_account'
        };

        new Map(mapParams);
        new CountriesGrid(gridParams);
    }
}
