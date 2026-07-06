import {BasePage} from './Base.js?v=0.10.0';
import {UsageStatsGrid} from '../parts/grid/UsageStats.js?v=0.10.0';
import {EnrichAllPopUp} from '../parts/popup/EnrichAllPopUp.js?v=0.10.0';
import {ResetApiKeyPopUp} from '../parts/popup/ResetApiKeyPopUp.js?v=0.10.0';
import {closest} from '../parts/utils/Functions.js?v=0.10.0';

export class ApiPage extends BasePage {
    constructor() {
        super('api');
    }

    initUi() {
        const onSelectChange = this.onSelectChange.bind(this);
        this.versionSelect.addEventListener('change', onSelectChange, false);

        const gridParams = {
            url:        `${window.app_base}/loadUsageStats`,
            tableId:    'usage-stats-table',
            tileId:     'totalUsageStats',

            isSortable: false,

            getParams: function() {
                return {};
            }
        };

        new UsageStatsGrid(gridParams);
        new EnrichAllPopUp();
        new ResetApiKeyPopUp();
    }

    onSelectChange(e) {
        const value = event.target.value;

        this.snippets.forEach(txt => {
            const container = closest(txt, 'div');
            const isHidden = container.classList.contains('is-hidden');
            if (!isHidden) {
                container.classList.add('is-hidden');
            }
        });

        const pre = document.getElementById(value);
        closest(pre, 'div').classList.remove('is-hidden');
    }

    get versionSelect() {
        return document.querySelector('select[name=version]');
    }

    get snippets() {
        return document.querySelectorAll('.tirreno');
    }
}
