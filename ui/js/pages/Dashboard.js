import {BasePage} from './Base.js';
import {SequentialLoad} from '../parts/SequentialLoad.js?v=2';
import {DatesFilter} from '../parts/DatesFilter.js?v=2';
import {DashboardTile} from '../parts/DashboardTile.js?v=2';
import {TopTenGrid} from '../parts/grid/TopTen.js?v=2';
import {
    renderClickableImportantUserWithScoreTile,
    renderClickableCountry,
    renderClickableResourceWithoutQuery,
    renderClickableIpWithCountry,
} from '../parts/DataRenderers.js?v=2';

export class DashboardPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        const datesFilter = new DatesFilter(true);

        const getParams = () => {
            const dateRange = datesFilter.getValue();
            return {dateRange};
        };

        const topTenUsersGridParams = {
            getParams:          getParams,
            mode:               'mostActiveUsers',
            tableId:            'most-active-users-table',
            dateRangeGrid:      true,
            renderItemColumn:   renderClickableImportantUserWithScoreTile,
        };

        const topTenCountriesGridParams = {
            getParams:          getParams,
            mode:               'mostActiveCountries',
            tableId:            'most-active-countries-table',
            dateRangeGrid:      true,
            renderItemColumn:   renderClickableCountry,
        };

        const topTenResourcesGridParams = {
            getParams:          getParams,
            mode:               'mostActiveUrls',
            tableId:            'most-active-urls-table',
            dateRangeGrid:      true,
            renderItemColumn:   renderClickableResourceWithoutQuery,
        };

        const topTenIpsWithMostUsersGridParams = {
            getParams:          getParams,
            mode:               'ipsWithTheMostUsers',
            tableId:            'ips-with-the-most-users-table',
            dateRangeGrid:      true,
            renderItemColumn:   renderClickableIpWithCountry,
        };

        const topTenUsersWithMostLoginFailGridParams = {
            getParams:          getParams,
            mode:               'usersWithMostLoginFail',
            tableId:            'users-with-most-login-fail-table',
            dateRangeGrid:      true,
            renderItemColumn:   renderClickableImportantUserWithScoreTile,
        };

        const topTenUsersWithMostIpsGridParams = {
            getParams:          getParams,
            mode:               'usersWithMostIps',
            tableId:            'users-with-most-ips-table',
            dateRangeGrid:      true,
            renderItemColumn:   renderClickableImportantUserWithScoreTile,
        };

        const elements = [
            //[DashboardTile,   {getParams: getParams, mode: 'totalEvents'}],
            [DashboardTile,     {getParams: getParams, mode: 'totalUsers'}],
            [DashboardTile,     {getParams: getParams, mode: 'totalIps'}],
            [DashboardTile,     {getParams: getParams, mode: 'totalCountries'}],
            [DashboardTile,     {getParams: getParams, mode: 'totalUrls'}],
            [DashboardTile,     {getParams: getParams, mode: 'totalUsersForReview'}],
            [DashboardTile,     {getParams: getParams, mode: 'totalBlockedUsers'}],
            [TopTenGrid,        topTenUsersGridParams],
            [TopTenGrid,        topTenCountriesGridParams],
            [TopTenGrid,        topTenResourcesGridParams],
            [TopTenGrid,        topTenIpsWithMostUsersGridParams],
            [TopTenGrid,        topTenUsersWithMostLoginFailGridParams],
            [TopTenGrid,        topTenUsersWithMostIpsGridParams],
        ];

        new SequentialLoad(elements);
    }
}
