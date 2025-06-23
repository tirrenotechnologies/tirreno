import {BasePage} from './Base.js';

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
        const datesFilter = new DatesFilter();

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

        //new DashboardTile({getParams: getParams, mode: 'totalEvents'});
        new DashboardTile({getParams: getParams, mode: 'totalUsers'});
        new DashboardTile({getParams: getParams, mode: 'totalIps'});
        new DashboardTile({getParams: getParams, mode: 'totalCountries'});
        new DashboardTile({getParams: getParams, mode: 'totalUrls'});
        new DashboardTile({getParams: getParams, mode: 'totalUsersForReview'});
        new DashboardTile({getParams: getParams, mode: 'totalBlockedUsers'});
        new TopTenGrid(topTenUsersGridParams);
        new TopTenGrid(topTenCountriesGridParams);
        new TopTenGrid(topTenResourcesGridParams);
        new TopTenGrid(topTenUsersWithMostIpsGridParams);
        new TopTenGrid(topTenUsersWithMostLoginFailGridParams);
        new TopTenGrid(topTenIpsWithMostUsersGridParams);
    }
}
