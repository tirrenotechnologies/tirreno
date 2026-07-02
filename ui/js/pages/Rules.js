import {BasePage} from './Base.js?v=0.10.0';
import {ThresholdsForm} from '../parts/ThresholdsForm.js?v=0.10.0';
import {InstantSearch} from '../parts/InstantSearch.js?v=0.10.0';
import {ApplyRulesPresetPopUp} from '../parts/popup/ApplyRulesPresetPopUp.js?v=0.10.0';
import {RulesGridActionButtons} from '../parts/button/RulesGridActionButtons.js?v=0.10.0';
import {RulesGrid} from '../parts/grid/Rules.js?v=0.10.0';
import {fireEvent} from '../parts/utils/Event.js?v=0.10.0';

export class RulesPage extends BasePage {
    constructor() {
        super('rules');
    }

    initUi() {
        this.tableId = 'rules-table';

        const gridParams = {
            url:        `${window.app_base}/loadRules`,
            tableId:    'rules-table',

            isSortable:         false,
            orderByLastseen:    false,

            choicesFilterEvents: ['change-rules-set'],

            getParams: this.getParamsSection,
        };

        const me = this;

        new RulesGrid(gridParams);
        new RulesGridActionButtons(this.tableId);
        new InstantSearch(this.tableId, [3, 5]);
        new ThresholdsForm();
        new ApplyRulesPresetPopUp();
    }
}
