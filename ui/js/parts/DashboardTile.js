import {Loader} from './Loader.js?v=2';
import {handleAjaxError} from './utils/ErrorHandler.js?v=2';
import {fireEvent} from './utils/Event.js?v=2';

export class DashboardTile {

    constructor(tilesParams) {
        const me = this;
        this.config = tilesParams;

        this.loader = new Loader();

        const onDateFilterChanged = this.onDateFilterChanged.bind(this);
        window.addEventListener('dateFilterChanged', onDateFilterChanged, false);

        this.loadData();
    }

    loadData() {
        const me     = this;
        const token  = document.head.querySelector('[name=\'csrf-token\'][content]').content;

        let params   = this.config.getParams().dateRange;
        params.mode  = this.config.mode;

        const el = document.querySelector(`.${this.config.mode} .title`);
        this.loader.start(el);

        fireEvent('dateFilterChangedCaught');

        $.ajax({
            url: `/admin/loadDashboardStat?token=${token}`,
            type: 'get',
            scope: me,
            data: params,
            success: me.onLoad,
            error: handleAjaxError,
            complete: function() {
                fireEvent('dateFilterChangedCompleted');
            },
        });
    }

    onLoad(data, status) {
        if ('success' == status) {
            this.scope.loader.stop();

            const frag = document.createDocumentFragment();

            const period = document.createElement('p');
            if (this.scope.config.mode === 'totalUsersForReview') {
                period.className = 'periodTotalYellow';
            } else if (this.scope.config.mode === 'totalBlockedUsers') {
                period.className = 'periodTotalRed';
            } else {
                period.className = 'periodTotal';
            }
            period.textContent = data.total;

            const total = document.createElement('p');
            total.className = 'allTimeTotal';
            total.textContent = data.allTimeTotal;

            frag.appendChild(period);
            frag.appendChild(total);

            const el = document.querySelector(`.${this.scope.config.mode} .title`);
            el.replaceChildren(frag);
        }
    }

    onDateFilterChanged() {
        this.loadData();
    }
}
