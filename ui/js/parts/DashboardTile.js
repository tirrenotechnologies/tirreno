import {Loader} from './Loader.js?v=0.10.0';
import {handleAjaxError} from './utils/ErrorHandler.js?v=0.10.0';
import {replaceChildren} from './utils/Functions.js?v=0.10.0';
import {fireEvent} from './utils/Event.js?v=0.10.0';

export class DashboardTile {
    constructor(tilesParams) {
        const me = this;
        this.config = tilesParams;

        this.loader = new Loader();

        if (!this.config.sequential) {
            const onDateFilterChanged = this.onDateFilterChanged.bind(this);
            window.addEventListener('dateFilterChanged', onDateFilterChanged, false);

            this.initLoad();

        }
    }

    startLoader() {
        const el = document.querySelector(`.${this.config.mode} .title`);
        this.loader.start(el);
    }

    loadData() {
        const me     = this;
        const token  = document.head.querySelector('[name=\'csrf-token\'][content]').content;

        let params   = this.config.getParams().dateRange;
        params.mode  = this.config.mode;

        if (!this.config.sequential) {
            this.startLoader();
        }

        fireEvent('dateFilterChangedCaught');

        $.ajax({
            url: `${window.app_base}/loadDashboardStat?token=${token}`,
            type: 'GET',
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

            //const total = document.createElement('p');
            //total.className = 'allTimeTotal';
            //total.textContent = data.allTimeTotal;

            frag.appendChild(period);
            //frag.appendChild(total);

            replaceChildren(document.querySelector(`.${this.scope.config.mode} .title`), frag);
        }
    }

    onDateFilterChanged() {
        this.loadData();
    }
}
