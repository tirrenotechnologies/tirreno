import {BasePage} from './Base.js?v=0.10.0';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=0.10.0';
import {Map} from '../parts/Map.js?v=0.10.0';
import {DatesFilter} from '../parts/DatesFilter.js?v=0.10.0';
import {SearchFilter} from '../parts/SearchFilter.js?v=0.10.0';
import {CountriesGrid} from '../parts/grid/Countries.js?v=0.10.0';

export class CountriesPage extends BasePage {
    constructor() {
        super('countries');
    }

    initUi() {
        const datesFilter   = new DatesFilter();
        const searchFilter  = new SearchFilter();

        const getMapParams = () => {
            const dateRange = datesFilter.getValue();
            return {dateRange};
        };

        this.setBaseFilters(datesFilter, searchFilter);

        const gridParams = {
            url:        `${window.app_base}/loadCountries`,
            tileId:     'totalCountries',
            tableId:    'countries-table',

            dateRangeGrid:      true,
            timeFrameTotalUrl:  `${window.app_base}/loadCountriesTimeFrameTotal`,
            totals: {
                type: 'country',
                columns: ['total_visit', 'total_account', 'total_ip'],
            },

            getParams: this.getParamsSection,
        };

        const mapParams = {
            getParams:      getMapParams,
            tooltipString:  'user',
            tooltipField:   'total_account'
        };

        const elements = [
            [Map,               mapParams],
            [CountriesGrid,     gridParams],
        ];

        new SequentialLoad(elements);
    }
}
