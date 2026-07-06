import {BaseFilter} from './BaseFilter.js?v=0.10.0';
import {
    renderIpTypeSelectorItem,
    renderIpTypeSelectorChoice,
} from '../DataRenderers.js?v=0.10.0';

export class IpTypeFilter extends BaseFilter {
    constructor() {
        super(
            '#ip-type-selectors',
            renderIpTypeSelectorItem,
            renderIpTypeSelectorChoice,
            'ipTypeFilterChanged'
        );
    }
}
