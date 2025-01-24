import {Loader} from './Loader.js?v=2';
import {handleAjaxError} from './utils/ErrorHandler.js?v=2';

export class DashboardTiles {

    constructor(tilesParams) {
        const me = this;
        this.config = tilesParams;
        this.loaders = {};
        const elems = [
            'totalIps', 'totalUsers', 'totalEvents', 'totalUrls',
            'totalCountries', 'totalUsersForReview', 'totalBlockedUsers'
        ];

        elems.forEach(elem => {
            me.loaders[elem] = new Loader();
        });

        const onDateFilterChanged = this.onDateFilterChanged.bind(this);
        window.addEventListener('dateFilterChanged', onDateFilterChanged, false);

        this.loadData();
    }

    loadData() {
        const me     = this;
        const params = this.config.getParams();
        const token  = document.head.querySelector('[name=\'csrf-token\'][content]').content;

        for (const property in this.loaders) {
            const el = document.querySelector(`.${property} .title`);
            this.loaders[property].start(el);
        }

        $.ajax({
            url: `/admin/loadDashboardStat?token=${token}`,
            type: 'get',
            scope: me,
            data: params.dateRange,
            success: me.onLoad,
            error: handleAjaxError,
        });
    }

    onLoad(data, status) {
        if ('success' == status) {
            for (const property in this.scope.loaders) {
                this.scope.loaders[property].stop();
            }

            this.scope.updateTotal('totalIps',              data.ips,            data.ipsAllTime);
            this.scope.updateTotal('totalUsers',            data.users,          data.usersAllTime);
            this.scope.updateTotal('totalEvents',           data.events,         data.eventsAllTime);
            this.scope.updateTotal('totalUrls',             data.resources,      data.resourcesAllTime);
            this.scope.updateTotal('totalCountries',        data.countries,      data.countriesAllTime);
            this.scope.updateTotal('totalBlockedUsers',     data.blockedUsers,   data.blockedUsersAllTime);
            this.scope.updateTotal('totalUsersForReview',   data.usersForReview, data.usersForReviewAllTime);
        }
    }

    updateTotal(tile, total, allTimeTotal) {
        const cls = `.${tile} .title`;
        const el  = document.querySelector(cls);

        el.innerHTML = `<p class="periodTotal">${total}</p><p class="allTimeTotal">${allTimeTotal}</p>`;
    }

    onDateFilterChanged() {
        this.loadData();
    }
}
