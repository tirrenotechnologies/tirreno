import {BaseFilter} from './BaseFilter.js?v=2';
import {
    renderRuleSelectorItem,
    renderRuleSelectorChoice,
} from '../DataRenderers.js?v=2';

export class RulesFilter extends BaseFilter {
    constructor() {
        super('#rule-selectors', renderRuleSelectorItem, renderRuleSelectorChoice, 'rulesFilterChanged');
    }
}
