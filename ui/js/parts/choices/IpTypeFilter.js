import {BaseFilter} from './BaseFilter.js?v=2';
import {
    renderIpTypeSelectorItem,
    renderIpTypeSelectorChoice,
} from '../DataRenderers.js?v=2';

export class IpTypeFilter extends BaseFilter {
    constructor() {
        super('#ip-type-selectors', renderIpTypeSelectorItem, renderIpTypeSelectorChoice, 'ipTypeFilterChanged');
    }
}
