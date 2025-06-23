import {Loader} from './Loader.js?v=2';
import {handleAjaxError} from './utils/ErrorHandler.js?v=2';

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

        $.ajax({
            url: `/admin/loadDashboardStat?token=${token}`,
            type: 'get',
            scope: me,
            data: params,
            success: me.onLoad,
            error: handleAjaxError,
        });
    }

    onLoad(data, status) {
        if ('success' == status) {
            this.scope.loader.stop();

            const el = document.querySelector(`.${this.scope.config.mode} .title`);
            el.innerHTML = `<p class="periodTotal">${data.total}</p><p class="allTimeTotal">${data.allTimeTotal}</p>`;
        }
    }

    onDateFilterChanged() {
        this.loadData();
    }
}
