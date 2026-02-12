import {BasePage} from './Base.js?v=2';
import {UsageStatsGrid} from '../parts/grid/UsageStats.js?v=2';
import {EnrichAllPopUp} from '../parts/popup/EnrichAllPopUp.js?v=2';

export class ApiPage extends BasePage {
    constructor() {
        super('api');
    }

    initUi() {
        const onSelectChange = this.onSelectChange.bind(this);
        this.versionSelect.addEventListener('change', onSelectChange, false);

        const gridParams = {
            url:        `${window.app_base}/admin/loadUsageStats`,
            tableId:    'usage-stats-table',
            tileId:     'totalUsageStats',

            isSortable: false,

            getParams: function() {
                return {};
            }
        };

        new UsageStatsGrid(gridParams);
        new EnrichAllPopUp();
    }

    onSelectChange(e) {
        const value = event.target.value;

        this.snippets.forEach(txt => {
            const container = txt.closest('div');
            const isHidden = container.classList.contains('is-hidden');
            if (!isHidden) {
                container.classList.add('is-hidden');
            }
        });

        const pre = document.getElementById(value);
        pre.closest('div').classList.remove('is-hidden');
    }

    get versionSelect() {
        return document.querySelector('select[name=version]');
    }

    get snippets() {
        return document.querySelectorAll('.tirreno');
    }
}
