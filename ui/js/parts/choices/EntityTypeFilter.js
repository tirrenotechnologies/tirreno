import {BaseFilter} from './BaseFilter.js?v=0.10.0';
import {
    renderEntityTypeSelectorItem,
    renderEntityTypeSelectorChoice,
} from '../DataRenderers.js?v=0.10.0';

export class EntityTypeFilter extends BaseFilter {
    constructor() {
        super(
            '#entity-type-selectors',
            renderEntityTypeSelectorItem,
            renderEntityTypeSelectorChoice,
            'entityTypeFilterChanged'
        );
    }
}
