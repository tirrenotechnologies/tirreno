import {BasePage} from './Base.js';
import {UsageStatsGrid} from '../parts/grid/UsageStats.js?v=2';
import {EnrichAllPopUp} from '../parts/EnrichAllPopUp.js?v=2';

export class ApiPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        const onSelectChange = this.onSelectChange.bind(this);
        this.versionSelect.addEventListener('change', onSelectChange, false);

        const onTextAreaClick = this.onTextAreaClick.bind(this);
        this.snippetTextareas.forEach(txt => txt.addEventListener('click', onTextAreaClick, false));

        const gridParams = {
            url:        '/admin/loadUsageStats',
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

    onTextAreaClick(e) {
        const txt = e.target;
        const value = txt.value;

        txt.setSelectionRange(0, value.length);
    }

    onSelectChange(e) {
        const value = event.target.value;

        this.snippetTextareas.forEach(txt => {
            const container = txt.closest('div');
            const isHidden = container.classList.contains('is-hidden');
            if (!isHidden) {
                container.classList.add('is-hidden');
            }
        });

        const textarea = document.getElementById(value);
        textarea.closest('div').classList.remove('is-hidden');
    }

    get versionSelect() {
        return document.querySelector('select[name=version]');
    }

    get snippetTextareas() {
        return document.querySelectorAll('.code-snippet');
    }
}
