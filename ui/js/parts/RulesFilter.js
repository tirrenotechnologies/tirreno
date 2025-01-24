import {fireEvent} from './utils/Event.js?v=2';
import {
    renderRuleSelectorItem,
    renderRuleSelectorChoice,
} from './DataRenderers.js?v=2';

export class RulesFilter {
    constructor() {
        const choices = new Choices('#rule-selectors select', {
            removeItemButton: true,
            allowHTML: true,
            callbackOnCreateTemplates: function(strToEl) {
                const {classNames, itemSelectText} = this.config;
                return {
                    item: function({classNames}, data) {return strToEl(renderRuleSelectorItem(classNames, data));},
                    choice: function({classNames}, data) {return strToEl(renderRuleSelectorChoice(classNames, data, itemSelectText));},
                };
            }
        });
        choices.passedElement.element.addEventListener(
            'change',
            () => fireEvent('rulesFilterChanged')
        );
    }

    getValues() {
        return Array.from(document.querySelector('#rule-selectors select').options)
            .filter(option => option.selected)
            .map(option => option.value);
    }
}
