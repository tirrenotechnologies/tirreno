import {BaseFilter} from './BaseFilter.js?v=2';
import {
    renderEntityTypeSelectorItem,
    renderEntityTypeSelectorChoice,
} from '../DataRenderers.js?v=2';

export class EntityTypeFilter extends BaseFilter {
    constructor() {
        super('#entity-type-selectors', renderEntityTypeSelectorItem, renderEntityTypeSelectorChoice, 'entityTypeFilterChanged');
    }
}
