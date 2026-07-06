import {BasePopUp} from './BasePopUp.js?v=0.10.0';

export class ApplyRulesPresetPopUp extends BasePopUp {
    constructor() {
        const formParams = {
            mainButtonId:       'apply-rules-preset-btn',
            confirmButtonId:    'confirm-apply-rules-preset-button',
            formId:             'apply-rules-preset-form',
            popupId:            'apply-rules-preset-popup',
        };

        super(formParams);

        const onSelectValueChange = this.onSelectValueChange.bind(this);
        this.applyRulesPresetSelector.addEventListener('change', onSelectValueChange, false);
    }

    onSelectValueChange(e) {
        this.mainButton.disabled = !this.applyRulesPresetSelector.value;
    }

    get applyRulesPresetSelector() {
        return document.getElementById('rules-preset');
    }
}
